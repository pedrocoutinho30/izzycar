<?php

namespace App\Services;

use App\Models\Legalization;
use Mpdf\Mpdf;

/**
 * Preenche o template do Modelo 1 RA — Requerimento de Registo Automóvel do
 * IRN (resources/pdf-templates/modelo1ra-registo-automovel.pdf) com os dados
 * do veículo e do cliente (comprador) associados a uma legalização.
 *
 * Marca sempre "Registo inicial de propriedade" (Q2) e preenche a matrícula e
 * marca/modelo do veículo (Q1) e os dados do comprador — sujeito ativo (Q3):
 * nome, NIF, residência, código postal, localidade e nº de identificação.
 * O vendedor (Q4) não é preenchido.
 *
 * As coordenadas usadas abaixo (em pontos, espaço da página original A4,
 * 595x842pt) foram extraídas diretamente dos campos de formulário (AcroForm)
 * do PDF original do IRN — o template em si não tem campos interativos
 * preenchíveis no resultado final (é importado como imagem de fundo, tal como
 * o Modelo 9 e o Modelo 1460/1), mas os retângulos desses campos servem de
 * referência exata para onde escrever por cima.
 */
class Modelo1RaPdfService
{
    private const TEMPLATE_PATH = 'pdf-templates/modelo1ra-registo-automovel.pdf';

    // Checkbox "Registo inicial de propriedade" (Q2) — sempre marcada.
    private const CHECKBOX_REGISTO_INICIAL = [161.1, 217.3, 176.1, 231.7];

    // Campos de linha simples — [x_inicio, y_baseline, x_max].
    private const LINE_FIELDS = [
        'matricula'      => [78.4, 185.5, 199.8],
        'marca'          => [245.0, 185.5, 414.0],
        'nome'           => [149.8, 518.7, 567.3],
        'nif'            => [79.2, 533.3, 187.5],
        'residencia'     => [24.6, 548.8, 567.3],
        'localidade'     => [245.6, 563.8, 507.8],
        'num_identificacao' => [110.4, 578.2, 254.4],
        'cod_postal_1'   => [91.2, 563.6, 139.9],
        'cod_postal_2'   => [150.8, 563.6, 186.8],
    ];

    public function generate(Legalization $legalization): string
    {
        $legalization->loadMissing('client');
        $client = $legalization->client;

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0,
            'margin_header' => 0, 'margin_footer' => 0,
        ]);

        $mpdf->setSourceFile(resource_path(self::TEMPLATE_PATH));
        $tplIdx = $mpdf->importPage(1);
        $mpdf->useTemplate($tplIdx);

        $this->markCheckbox($mpdf, self::CHECKBOX_REGISTO_INICIAL);

        $this->writeLine($mpdf, 'matricula', $legalization->matricula);
        $this->writeLine($mpdf, 'marca', trim(($legalization->marca ?? '').' '.($legalization->modelo ?? '')));

        if ($client) {
            $this->writeLine($mpdf, 'nome', $client->name);
            $this->writeLine($mpdf, 'nif', $client->vat_number);
            $this->writeLine($mpdf, 'residencia', $client->address);
            $this->writeLine($mpdf, 'localidade', $client->city);
            $this->writeLine($mpdf, 'num_identificacao', str_replace(' ', '', $client->identification_number ?? ''));

            $partesCp = array_pad(explode('-', $client->postal_code ?? ''), 2, '');
            $this->writeLine($mpdf, 'cod_postal_1', trim($partesCp[0]));
            $this->writeLine($mpdf, 'cod_postal_2', trim($partesCp[1]));
        }

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function pt2mm(float $pt): float
    {
        return $pt * 25.4 / 72;
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

        [$x0, $yBaseline, $xMax] = self::LINE_FIELDS[$fieldKey];
        $upper = mb_strtoupper($text, 'UTF-8');
        $width = $this->pt2mm($xMax - $x0) - 0.5;

        // Encolhe a fonte se o texto não couber na linha disponível (até um mínimo de 6pt)
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
}
