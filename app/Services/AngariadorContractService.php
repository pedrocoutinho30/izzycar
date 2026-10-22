<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse as Response;

/**
 * Contrato de angariação em PDF, com o mesmo layout do contrato de cotação
 * (ContractService). Gerado na hora, não é guardado.
 */
class AngariadorContractService
{
    /** Dias de pré-aviso para denunciar o contrato. */
    public const PRE_AVISO_DIAS = 15;

    /** Horas, após a entrega da viatura ao cliente, para pagar a comissão. */
    public const PRAZO_PAGAMENTO_HORAS = 48;

    /**
     * Dados do perfil que o contrato precisa e que podem faltar; o PDF deixa
     * linhas em branco para preencher à mão e a ficha avisa o admin.
     *
     * @return array<string, string> campo => etiqueta
     */
    public static function camposEmFalta(User $angariador): array
    {
        $campos = [
            'nif' => 'NIF',
            'iban' => 'IBAN',
            'location' => 'morada',
            'commission_fixed_value' => 'comissão',
        ];

        return array_filter($campos, fn (string $etiqueta, string $campo) => blank($angariador->{$campo}), ARRAY_FILTER_USE_BOTH);
    }

    /** Disco privado onde ficam os contratos assinados. */
    private const DISCO_ASSINADO = 'local';

    public static function guardarAssinado(User $angariador, UploadedFile $ficheiro): void
    {
        self::removerAssinado($angariador);

        $angariador->update([
            'contrato_assinado_path' => $ficheiro->store("angariadores/contratos/{$angariador->id}", self::DISCO_ASSINADO),
            'contrato_assinado_em' => now(),
        ]);
    }

    public static function removerAssinado(User $angariador): void
    {
        if ($angariador->contrato_assinado_path) {
            Storage::disk(self::DISCO_ASSINADO)->delete($angariador->contrato_assinado_path);
        }

        $angariador->update(['contrato_assinado_path' => null, 'contrato_assinado_em' => null]);
    }

    /** Abre o contrato assinado no browser; 404 se não houver ficheiro. */
    public static function respostaAssinado(User $angariador): Response
    {
        $path = $angariador->contrato_assinado_path;
        abort_unless($path && Storage::disk(self::DISCO_ASSINADO)->exists($path), 404);

        $nome = 'contrato_angariador_assinado_' . Str::slug($angariador->full_name) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk(self::DISCO_ASSINADO)->response($path, $nome);
    }

    public static function generatePdf(User $angariador): string
    {
        $settings = Setting::all()->pluck('value', 'label')->toArray();

        // Assinatura digital do prestador (Definições → assinatura_prestador, disco "public").
        $signatureFile = $settings['assinatura_prestador'] ?? null;
        $src = null;
        if (! empty($signatureFile) && Storage::disk('public')->exists($signatureFile)) {
            $path = Storage::disk('public')->path($signatureFile);
            $mime = mime_content_type($path) ?: 'image/png';
            $src = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        }

        $logoPath = public_path('img/logo-contrato.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $comissao = $angariador->commission_fixed_value;

        $data = [
            'angariador' => [
                'nome' => $angariador->full_name,
                'nif' => $angariador->nif,
                'morada' => $angariador->location,
                'telefone' => $angariador->phone,
                'email' => $angariador->email,
                'iban' => $angariador->iban,
            ],
            'prestador' => [
                'nome' => $settings['name'] ?? 'Izzycar',
                'nif' => $settings['vat_number'] ?? '',
                'morada' => 'Oliveira de Azeméis',
                'telefone' => $settings['phone'] ?? null,
                'email' => $settings['email'] ?? null,
            ],
            'comissao' => $comissao !== null ? number_format((float) $comissao, 2, ',', '.') . ' €' : null,
            'prazoPagamentoHoras' => self::PRAZO_PAGAMENTO_HORAS,
            'preAvisoDias' => self::PRE_AVISO_DIAS,
            'logo' => $logo,
            'dataEmissao' => now()->format('d/m/Y'),
            'signaturePath' => $src,
        ];

        return Pdf::loadView('pdf.contract_angariador', $data)->output();
    }
}
