<?php

namespace App\Services;

use App\Models\Legalization;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

/**
 * Preenche o template do Modelo 1460/1 da AT (Pedidos no âmbito do Imposto
 * sobre Veículos — ISV), resources/pdf-templates/Mod_1460_1.pdf.
 *
 * As coordenadas usadas abaixo (em pontos, espaço da página original
 * 595.3x841.9pt) foram calibradas diretamente sobre o PDF original,
 * seguindo o mesmo método usado no Modelo 9 (Modelo9PdfService).
 */
class Modelo1460PdfService
{
    private const TEMPLATE_PATH = 'pdf-templates/Mod_1460_1.pdf';

    // Dados extra do veículo específicos deste modelo (CO2, partículas,
    // matrícula estrangeira/nacional, DAV) — os restantes (marca, modelo,
    // cilindrada, chassis) já vêm de modelo9_dados/legalização.
    public const CAMPOS_EXTRA_TEXTO = [
        'co2', 'particulas', 'pais_matricula_estrangeira',
        'data_primeira_matricula', 'data_matricula_nacional',
        'dav_numero', 'dav_data', 'dav_alfandega',
        'pedido_1_1_artigo',
    ];

    public const CAMPOS_EXTRA_BOOLEAN = [
        'novo', 'usado', 'matricula_estrangeira', 'matricula_nacional',
        'pedido_1_beneficio_fiscal', 'pedido_1_1_isencao_isv',
    ];

    // Checkboxes "Novo/sem matrícula" e "Usado".
    private const CHECKBOXES_ESTADO = [
        'novo'  => [484.63, 355.25, 494.63, 364.92],
        'usado' => [542.15, 355.25, 552.15, 364.92],
    ];

    // Checkboxes "Matrícula: estrangeira" e "Matrícula: nacional".
    private const CHECKBOXES_MATRICULA = [
        'matricula_estrangeira' => [163.69, 389.75, 173.69, 399.42],
        'matricula_nacional'    => [152.19, 406.75, 162.19, 416.42],
    ];

    // Checkboxes da secção PEDIDOS — só "1. Benefício fiscal" e
    // "1.1 Isenção do ISV" estão calibradas por agora.
    private const CHECKBOXES_PEDIDOS = [
        'pedido_1_beneficio_fiscal' => [81.66, 501.61, 93.95, 513.52],
        'pedido_1_1_isencao_isv'    => [98.01, 523.10, 108.01, 532.77],
    ];

    // Campos de linha simples — [x_inicio, y_baseline, x_max].
    private const LINE_FIELDS = [
        // Requerente/Proprietário
        'nome'              => [186.40, 235.95, 552.75],
        'nif'               => [113.45, 252.95, 303.75],
        'num_identificacao' => [394.60, 252.95, 552.75],
        'morada'            => [158.40, 269.95, 552.75],
        'codigo_postal_p1'  => [137.0, 286.95, 154.0],
        'codigo_postal_p2'  => [159.0, 286.95, 186.0],
        'telefone'          => [158.40, 303.95, 303.75],
        'email'             => [347.35, 303.95, 552.75],

        // Identificação do Veículo
        'marca'      => [110.50, 366.11, 202.50],
        'modelo'     => [255.10, 366.11, 286.30],
        'cilindrada' => [347.35, 366.11, 368.0],
        'chassis'    => [133.15, 383.11, 286.30],
        'co2'        => [371.35, 383.11, 393.55],
        'particulas' => [508.0, 383.11, 518.0],
        'pais_matricula_estrangeira'   => [206.90, 400.11, 343.00],
        'data_primeira_matricula_dia'  => [464.90, 400.11, 490.40],
        'data_primeira_matricula_mes'  => [488.0, 400.11, 500.0],
        'data_primeira_matricula_ano'  => [524.0, 400.11, 552.90],
        'data_matricula_nacional_dia'  => [347.35, 417.11, 368.0],
        'data_matricula_nacional_mes'  => [371.35, 417.11, 388.0],
        'data_matricula_nacional_ano'  => [393.55, 417.11, 430.90],
        'dav_numero'    => [110.50, 434.11, 255.10],
        'dav_data_dia'  => [304.0, 434.11, 322.0],
        'dav_data_mes'  => [326.0, 434.11, 336.0],
        'dav_data_ano'  => [345.0, 434.11, 371.35],
        'dav_alfandega' => [452.0, 434.11, 552.90],

        // Pedidos — art.º da isenção (1.1)
        'pedido_1_1_artigo' => [432.40, 533.96, 503.25],

        // Representante Legal (página 3)
        'rl_nome'     => [191.35, 683.27, 552.80],
        'rl_morada'   => [134.70, 700.27, 552.80],
        'rl_nif'      => [113.40, 717.27, 290.60],
        'rl_qualidade' => [354.35, 717.27, 552.80],
        'rl_data_dia' => [104.0, 734.27, 138.0],
        'rl_data_mes' => [145.0, 734.27, 154.0],
        'rl_data_ano' => [163.0, 734.27, 189.0],
    ];

    // Página (1-based) de cada campo de LINE_FIELDS — todos os campos sem
    // entrada aqui assumem-se na página 1.
    private const CAMPOS_PAGINA = [
        'rl_nome' => 3, 'rl_morada' => 3, 'rl_nif' => 3, 'rl_qualidade' => 3,
        'rl_data_dia' => 3, 'rl_data_mes' => 3, 'rl_data_ano' => 3,
    ];

    public function generate(Legalization $legalization): string
    {
        $legalization->loadMissing('client');
        $client = $legalization->client;
        $m9     = $legalization->modelo9_dados ?? [];
        $dados  = $legalization->modelo1460_dados ?? [];

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0,
            'margin_header' => 0, 'margin_footer' => 0,
        ]);

        $mpdf->setSourceFile(resource_path(self::TEMPLATE_PATH));
        $tplIdx = $mpdf->importPage(1);
        $mpdf->useTemplate($tplIdx);

        // --- Requerente (dados do cliente, à excepção de email/telefone) ---
        if ($client) {
            $this->writeLine($mpdf, 'nome', mb_strtoupper($client->name ?? '', 'UTF-8'));
            $this->writeLine($mpdf, 'nif', $client->vat_number);
            $this->writeLine($mpdf, 'num_identificacao', str_replace(' ', '', $client->identification_number ?? ''));
            $this->writeLine($mpdf, 'morada', $client->address);

            $partesCp = array_pad(explode('-', $client->postal_code ?? ''), 2, '');
            $this->writeLine($mpdf, 'codigo_postal_p1', $partesCp[0]);
            $this->writeLine($mpdf, 'codigo_postal_p2', $partesCp[1]);
        }
        // Izzycar usa sempre o seu próprio contacto, independentemente do cliente.
        $this->writeLine($mpdf, 'telefone', '928459346');
        $this->writeLine($mpdf, 'email', 'GERAL@IZZYCAR.PT');

        // --- Identificação do Veículo (reaproveita marca/modelo/matrícula da
        // legalização e cilindrada/chassis já guardados no Modelo 9) ---
        $this->writeLine($mpdf, 'marca', $legalization->marca);
        $this->writeLine($mpdf, 'modelo', $legalization->modelo);
        $this->writeLine($mpdf, 'cilindrada', $m9['cilindrada'] ?? null);
        $this->writeLine($mpdf, 'chassis', $m9['chassis'] ?? null);

        foreach (self::CHECKBOXES_ESTADO as $key => $box) {
            if (!empty($dados[$key])) {
                $this->markCheckbox($mpdf, $box);
            }
        }
        foreach (self::CHECKBOXES_MATRICULA as $key => $box) {
            if (!empty($dados[$key])) {
                $this->markCheckbox($mpdf, $box);
            }
        }

        $this->writeLine($mpdf, 'co2', $dados['co2'] ?? null);
        $this->writeLine($mpdf, 'particulas', $dados['particulas'] ?? null);
        $this->writeLine($mpdf, 'pais_matricula_estrangeira', $dados['pais_matricula_estrangeira'] ?? null);
        $this->writeDatePartes($mpdf, 'data_primeira_matricula', $dados['data_primeira_matricula'] ?? null);
        $this->writeDatePartes($mpdf, 'data_matricula_nacional', $dados['data_matricula_nacional'] ?? null);
        $this->writeLine($mpdf, 'dav_numero', $dados['dav_numero'] ?? null);
        $this->writeDatePartes($mpdf, 'dav_data', $dados['dav_data'] ?? null);
        $this->writeLine($mpdf, 'dav_alfandega', $dados['dav_alfandega'] ?? null);

        // --- Pedidos ---
        foreach (self::CHECKBOXES_PEDIDOS as $key => $box) {
            if (!empty($dados[$key])) {
                $this->markCheckbox($mpdf, $box);
            }
        }
        $this->writeLine($mpdf, 'pedido_1_1_artigo', $dados['pedido_1_1_artigo'] ?? null);

        // --- Representante Legal (página 3, valores fixos da Izzycar) ---
        $mpdf->AddPage();
        $tplIdx = $mpdf->importPage(2);
        $mpdf->useTemplate($tplIdx);
        $mpdf->AddPage();
        $tplIdx = $mpdf->importPage(3);
        $mpdf->useTemplate($tplIdx);

        $this->writeLine($mpdf, 'rl_nome', 'José Pedro Miranda Nunes Coutinho');
        $this->writeLine($mpdf, 'rl_morada', 'Rua Bento Landureza, 245, 3720-261 Oliveira de Azeméis');
        $this->writeLine($mpdf, 'rl_nif', '242414958');
        $this->writeLine($mpdf, 'rl_qualidade', 'Mandatário');

        $hoje = now();
        $this->writeLine($mpdf, 'rl_data_dia', $hoje->format('d'));
        $this->writeLine($mpdf, 'rl_data_mes', $hoje->format('m'));
        $this->writeLine($mpdf, 'rl_data_ano', $hoje->format('Y'));

        $this->drawAssinatura($mpdf);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function pt2mm(float $pt): float
    {
        return $pt * 25.4 / 72;
    }

    private function writeDatePartes(Mpdf $mpdf, string $prefix, ?string $isoDate): void
    {
        if (empty($isoDate)) {
            return;
        }
        try {
            $data = \Carbon\Carbon::parse($isoDate);
        } catch (\Throwable $e) {
            return;
        }
        $this->writeLine($mpdf, $prefix . '_dia', $data->format('d'));
        $this->writeLine($mpdf, $prefix . '_mes', $data->format('m'));
        $this->writeLine($mpdf, $prefix . '_ano', $data->format('Y'));
    }

    private function markCheckbox(Mpdf $mpdf, array $box): void
    {
        [$x0, $y0, $x1, $y1] = $box;
        $mpdf->SetFont('dejavusans', 'B', 10);
        $mpdf->SetTextColor(0, 0, 0);
        $cx = $this->pt2mm(($x0 + $x1) / 2);
        $cy = $this->pt2mm(($y0 + $y1) / 2);
        $mpdf->SetXY($cx - 2, $cy - 2.2);
        $mpdf->Cell(4, 4, 'X', 0, 0, 'C');
    }

    private function writeLine(Mpdf $mpdf, string $fieldKey, ?string $text): void
    {
        if (!isset(self::LINE_FIELDS[$fieldKey]) || empty($text)) {
            return;
        }

        $pagina = self::CAMPOS_PAGINA[$fieldKey] ?? 1;
        if ($mpdf->page !== $pagina) {
            return;
        }

        [$x0, $yBaseline, $xMax] = self::LINE_FIELDS[$fieldKey];
        $upper = mb_strtoupper($text, 'UTF-8');
        $width = $this->pt2mm($xMax - $x0) - 0.5;

        $fontSize = 8.5;
        $mpdf->SetFont('dejavusans', '', $fontSize);
        while ($fontSize > 6 && $mpdf->GetStringWidth($upper) > $width) {
            $fontSize -= 0.5;
            $mpdf->SetFont('dejavusans', '', $fontSize);
        }

        $mpdf->SetTextColor(0, 0, 0);
        $mpdf->SetXY($this->pt2mm($x0) + 0.5, $this->pt2mm($yBaseline) - 3.2);
        $mpdf->Cell($width, 3.2, $upper, 0, 0, 'L');
    }

    /**
     * Desenha a assinatura do representante (imagem guardada nas
     * Configurações, label "assinatura_prestador") na página 3. Se o
     * ficheiro não existir em disco, não desenha nada (sem quebrar o PDF).
     */
    private function drawAssinatura(Mpdf $mpdf): void
    {
        $setting = Setting::where('label', 'assinatura_prestador')->first();
        if (!$setting || !$setting->value || !Storage::disk('public')->exists($setting->value)) {
            return;
        }

        $path = Storage::disk('public')->path($setting->value);
        $x0 = 260.0;
        $xMax = 552.80;
        $lineY = 734.27;
        $height = 20.0; // pt

        try {
            [$imgWidthPx, $imgHeightPx] = getimagesize($path);
            $ratio = $imgWidthPx / max($imgHeightPx, 1);
            $widthPt = min($height * $ratio, $xMax - $x0);
        } catch (\Throwable $e) {
            $widthPt = min(80, $xMax - $x0);
        }

        $mpdf->Image($path, $this->pt2mm($x0), $this->pt2mm($lineY - $height - 1), $this->pt2mm($widthPt), $this->pt2mm($height));
    }
}
