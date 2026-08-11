<?php

namespace App\Services;

use App\Models\Legalization;
use Mpdf\Mpdf;

/**
 * Preenche o template do Modelo 9 do IMT (resources/pdf-templates/modelo9-imt.pdf)
 * com os dados do cliente e da viatura associados a uma legalização.
 *
 * As coordenadas usadas abaixo (em pontos, espaço da página original 595x841pt)
 * foram calibradas diretamente sobre o PDF original do IMT — cada "comb" representa
 * os traços verticais que dividem o campo em quadrados de uma letra, e cada "line"
 * representa uma linha simples (sem divisões) onde o texto é escrito livremente.
 */
class Modelo9PdfService
{
    private const TEMPLATE_PATH = 'pdf-templates/modelo9-imt.pdf';

    // Campos extra do veículo (não existentes na legalização/cliente), pedidos ao utilizador
    // e guardados em modelo9_dados. Marca, modelo, combustível, matrícula e homologação já
    // vêm da legalização e não fazem parte desta lista.
    public const CAMPOS_EXTRA_TEXTO = [
        'categoria', 'tipo', 'cor', 'chassis', 'motor', 'num_cilindros', 'cilindrada',
        'pneus_frente', 'pneus_retaguarda', 'peso_max_frente', 'peso_max_retaguarda',
        'poder_elevacao', 'tipo_caixa', 'comprimento_caixa', 'largura_caixa',
        'distancia_eixos', 'peso_bruto_total', 'tara', 'portas_total', 'portas_direita',
        'portas_esquerda', 'portas_retaguarda', 'lotacao', 'matricula_anterior',
        'matricula_anterior_data', 'pais_origem', 'anotacoes_especiais', 'pretensao_outra_motivo',
    ];

    public const CAMPOS_EXTRA_BOOLEAN = [
        'reboque', 'rebocavel', 'com_travao', 'sem_travao',
        'pretensao_atribuicao_matricula', 'pretensao_cancelamento_matricula',
        'pretensao_emissao_certificado', 'pretensao_duplicado_certificado',
        'pretensao_substituicao_certificado', 'pretensao_homologacao',
        'pretensao_certidao', 'pretensao_apreensao', 'pretensao_levantamento_apreensao',
        'pretensao_alteracoes_caracteristicas', 'pretensao_inspecao', 'pretensao_outra',
    ];

    // Opções da secção "Pretensão Relativa a Veículos" do formulário — usadas para
    // gerar as checkboxes no formulário de administração (chave => rótulo, por
    // esta ordem, correspondente à disposição em 4 colunas do PDF original).
    public const PRETENSAO_LABELS = [
        'pretensao_atribuicao_matricula'      => 'Atribuição de Matrícula',
        'pretensao_cancelamento_matricula'    => 'Cancelamento de Matrícula',
        'pretensao_emissao_certificado'       => 'Emissão de Certificado de Matrícula',
        'pretensao_duplicado_certificado'     => 'Duplicado de Certificado de Matrícula',
        'pretensao_substituicao_certificado'  => 'Substituição de Certificado de Matrícula',
        'pretensao_homologacao'               => 'Homologação',
        'pretensao_certidao'                  => 'Certidão',
        'pretensao_apreensao'                 => 'Apreensão',
        'pretensao_levantamento_apreensao'    => 'Levantamento de Apreensão',
        'pretensao_alteracoes_caracteristicas' => 'Alterações das Características',
        'pretensao_inspecao'                  => 'Inspeção',
        'pretensao_outra'                     => 'Outra / Motivo do Pedido',
    ];

    // Caixas da secção "Pretensão Relativa a Veículos" — coordenadas extraídas
    // diretamente dos desenhos vectoriais do PDF original (8.5x8.5pt cada),
    // marcadas apenas se o respetivo dado em modelo9_dados for verdadeiro.
    private const CHECKBOXES_PRETENSAO = [
        'pretensao_atribuicao_matricula'      => [60.10, 158.92, 68.60, 167.43],
        'pretensao_cancelamento_matricula'    => [60.10, 177.92, 68.60, 186.43],
        'pretensao_emissao_certificado'       => [60.10, 194.92, 68.60, 203.43],
        'pretensao_duplicado_certificado'     => [60.07, 212.66, 68.58, 221.16],
        'pretensao_substituicao_certificado'  => [240.07, 157.92, 248.58, 166.43],
        'pretensao_homologacao'               => [240.07, 176.27, 248.58, 184.77],
        'pretensao_certidao'                  => [240.07, 194.08, 248.58, 202.59],
        'pretensao_apreensao'                 => [240.07, 210.78, 248.58, 219.28],
        'pretensao_levantamento_apreensao'    => [425.50, 146.26, 434.00, 154.77],
        'pretensao_alteracoes_caracteristicas' => [425.38, 166.86, 433.88, 175.36],
        'pretensao_inspecao'                  => [425.40, 184.57, 433.91, 193.08],
        'pretensao_outra'                     => [322.91, 200.41, 331.42, 208.91],
    ];

    // Caixas condicionais (secção do reboque), marcadas apenas se o respetivo dado
    // em modelo9_dados for verdadeiro.
    private const CHECKBOXES_CONDICIONAIS = [
        'reboque'    => [186.76, 490.88, 195.26, 499.38],
        'rebocavel'  => [61.91, 597.63, 70.42, 606.13],
        'com_travao' => [125.84, 597.63, 134.34, 606.13],
        'sem_travao' => [192.1, 597.63, 200.61, 606.13],
    ];

    // Campos "comb" (uma letra por quadrado) — 'ticks' são os limites de cada célula
    // (n ticks = n-1 células) e 'baseline' é o y (pt) onde assenta a linha de escrita.
    private const COMB_FIELDS = [
        'nome_l1' => ['baseline' => 251.66, 'ticks' => [91.59, 101.59, 111.59, 121.59, 131.59, 141.59, 151.59, 161.59, 171.59, 181.59, 191.59, 201.59, 211.59, 221.59, 231.59, 241.59, 251.59, 261.59, 271.59, 281.59, 291.59, 302.71, 312.71, 322.71, 332.71, 342.71, 352.71, 362.71, 372.71, 382.71, 392.71, 402.71, 412.71, 422.71, 432.71, 442.71, 454.71, 466.89, 476.89, 486.89, 496.89, 506.89, 516.89, 526.89, 536.89]],
        'nome_l2' => ['baseline' => 271.66, 'ticks' => [57.04, 67.04, 77.04, 87.04, 97.04, 107.04, 117.04, 127.04, 137.04, 147.04, 157.04, 167.04, 177.04, 187.04, 197.04, 207.04, 217.04, 227.04, 237.04, 247.04, 257.04, 267.04, 277.04, 287.04, 297.04, 307.04, 317.04, 327.04, 337.04, 347.04, 357.04, 367.04, 377.04, 387.04, 397.04, 407.04, 417.04, 427.04, 437.04, 447.04, 457.04, 467.04, 477.04, 487.04, 497.04, 507.04, 517.04, 527.04, 537.04]],
        'morada' => ['baseline' => 292.45, 'ticks' => [97.47, 107.47, 117.47, 128.42, 138.42, 148.42, 158.42, 168.42, 178.42, 188.42, 198.42, 208.42, 218.42, 228.42, 238.42, 248.42, 258.42, 268.42, 278.42, 288.42, 298.42, 308.42, 318.42, 328.42, 338.42, 348.42, 358.42, 368.42, 378.42, 388.42, 398.42, 408.42, 418.42, 428.42, 438.42, 448.42, 458.42, 468.42, 478.42, 488.42, 498.42, 508.42, 518.42, 528.42, 538.42]],
        'localidade' => ['baseline' => 315.45, 'ticks' => [108.42, 118.42, 128.42, 138.42, 148.42, 158.42, 168.42, 178.42, 188.42, 198.42, 208.42, 218.42, 228.42, 238.42, 248.42, 258.42, 268.42, 278.42, 288.42, 298.42, 308.42, 318.42]],
        'codigo_postal' => ['baseline' => 315.45, 'ticks' => [385.6, 395.6, 405.6, 415.6, 425.6, 435.6, 445.6, 455.6, 465.6, 478.31, 488.31, 498.31, 508.31, 518.31, 528.31, 538.31]],
        'localidade_l2' => ['baseline' => 334.96, 'ticks' => [57.98, 67.98, 77.98, 87.98, 97.98, 107.98, 117.98, 127.98, 137.98, 147.98, 157.98, 167.98, 177.98, 187.98, 197.98, 207.98, 217.98, 227.98, 237.98, 247.98, 257.98, 267.98, 277.98]],
        'nif' => ['baseline' => 334.96, 'ticks' => [306.2, 316.2, 326.2, 336.2, 346.2, 356.2, 366.2, 376.2, 386.2, 396.2]],
        'telefone' => ['baseline' => 334.96, 'ticks' => [448.12, 458.12, 468.12, 478.12, 488.12, 498.12, 508.12, 518.12, 528.12, 538.12]],
        'email' => ['baseline' => 354.7, 'ticks' => [223.16, 233.16, 243.16, 253.16, 263.16, 273.16, 283.16, 293.16, 303.16, 313.16, 323.15, 333.15, 343.15, 353.15, 363.15, 373.15, 383.15, 394.15, 404.15, 414.15, 424.15, 434.15, 444.15, 454.15, 464.15, 474.15, 484.15, 494.15, 504.15, 514.15, 525.73, 537.73]],
        'doc_identificacao' => ['baseline' => 378.7, 'ticks' => [168.61, 178.61, 188.61, 198.61, 208.61, 218.61, 228.61, 238.61, 248.61, 258.61, 268.61]],
        'validade_ano' => ['baseline' => 402.55, 'ticks' => [441.68, 451.69, 461.69, 471.69, 481.69]],
        'validade_mes' => ['baseline' => 402.55, 'ticks' => [489.36, 499.36, 509.36]],
        'validade_dia' => ['baseline' => 402.55, 'ticks' => [516.87, 526.87, 536.87]],
        'data_req_ano' => ['baseline' => 451.73, 'ticks' => [86.53, 96.53, 106.53, 116.53, 126.53]],
        'data_req_mes' => ['baseline' => 451.73, 'ticks' => [137.03, 147.03, 157.03]],
        'data_req_dia' => ['baseline' => 451.73, 'ticks' => [167.03, 177.03, 187.03]],
    ];

    // Campos de linha simples (Características do Veículo) — [x_inicio, y_baseline, x_max].
    private const LINE_FIELDS = [
        'pretensao_outra_motivo' => [333.0, 219.47, 448.0],
        'matricula_1'         => [90.9, 496.7, 103.9],
        'matricula_2'         => [110.9, 496.7, 123.9],
        'matricula_3'         => [130.9, 496.7, 143.9],
        'marca'               => [349.0, 497.9, 539.2],
        'modelo'              => [87.0, 512.1, 302.2],
        'homologacao'         => [360.0, 512.1, 539.2],
        'categoria'           => [96.0, 527.1, 205.7],
        'tipo'                => [231.0, 527.1, 329.7],
        'cor'                 => [349.0, 527.1, 539.2],
        'chassis'             => [98.0, 542.1, 300.7],
        'motor'               => [349.0, 542.1, 539.2],
        'combustivel'         => [100.0, 557.1, 238.0],
        'num_cilindros'       => [300.0, 557.1, 397.6],
        'cilindrada'          => [439.0, 557.1, 539.2],
        'pneus_frente'        => [126.0, 572.1, 319.5],
        'pneus_retaguarda'    => [368.0, 572.1, 539.2],
        'peso_max_frente'     => [164.0, 587.1, 354.0],
        'peso_max_retaguarda' => [401.0, 587.1, 539.2],
        'poder_elevacao'      => [423.0, 602.1, 539.2],
        'tipo_caixa'          => [103.0, 617.1, 354.0],
        'comprimento_caixa'   => [459.0, 617.1, 539.2],
        'largura_caixa'       => [116.0, 632.1, 205.6],
        'distancia_eixos'     => [276.0, 632.1, 354.2],
        'peso_bruto_total'    => [414.0, 632.1, 539.2],
        'tara'                => [78.0, 648.8, 131.5],
        'portas_total'        => [194.0, 648.8, 214.2],
        'portas_direita'      => [254.0, 648.8, 274.2],
        'portas_esquerda'     => [322.0, 648.8, 342.5],
        'portas_retaguarda'   => [400.0, 648.8, 420.5],
        'lotacao'             => [467.0, 648.8, 539.2],
        'matricula_anterior'      => [117.0, 663.8, 205.6],
        // Campo "Data" da matrícula anterior — 3 blocos separados por traços já
        // impressos no template (ANO-MÊS-DIA, a mesma convenção usada nos outros
        // campos de data deste formulário). Coordenadas dos traços/sublinhados
        // extraídas dos desenhos do PDF original.
        'matricula_anterior_data_ano' => [241.27, 664.75, 270.69],
        'matricula_anterior_data_mes' => [280.04, 664.75, 293.04],
        'matricula_anterior_data_dia' => [300.04, 664.75, 313.04],
        'pais_origem'         => [374.0, 664.8, 539.2],
        'anotacoes_especiais' => [124.0, 680.8, 539.2],
    ];

    public function generate(Legalization $legalization): string
    {
        $legalization->loadMissing('client');
        $client = $legalization->client;
        $dados  = $legalization->modelo9_dados ?? [];

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0,
            'margin_header' => 0, 'margin_footer' => 0,
        ]);

        $mpdf->setSourceFile(resource_path(self::TEMPLATE_PATH));
        $tplIdx = $mpdf->importPage(1);
        $mpdf->useTemplate($tplIdx);

        foreach (self::CHECKBOXES_PRETENSAO as $key => $box) {
            if (!empty($dados[$key])) {
                $this->markCheckbox($mpdf, $box);
            }
        }

        foreach (self::CHECKBOXES_CONDICIONAIS as $key => $box) {
            if (!empty($dados[$key])) {
                $this->markCheckbox($mpdf, $box);
            }
        }

        // --- Requerente (dados do cliente) ---
        if ($client) {
            $nome = mb_strtoupper($client->name ?? '', 'UTF-8');
            [$nomeL1, $nomeL2] = $this->splitAtWordBoundary($nome, 44, 48);
            $this->writeComb($mpdf, 'nome_l1', $nomeL1);
            $this->writeComb($mpdf, 'nome_l2', $nomeL2);

            $this->writeComb($mpdf, 'morada', $client->address ?? '');
            $this->writeComb($mpdf, 'localidade', $client->city ?? '');
            $this->writeComb($mpdf, 'localidade_l2', $client->city ?? '');
            $this->writeComb($mpdf, 'codigo_postal', $client->postal_code ?? '');
            $this->writeComb($mpdf, 'nif', $client->vat_number ?? '');
            $this->writeComb($mpdf, 'doc_identificacao', str_replace(' ', '', $client->identification_number ?? ''));

            if ($client->validate_identification_number) {
                $validade = \Carbon\Carbon::parse($client->validate_identification_number);
                $this->writeComb($mpdf, 'validade_ano', $validade->format('Y'));
                $this->writeComb($mpdf, 'validade_mes', $validade->format('m'));
                $this->writeComb($mpdf, 'validade_dia', $validade->format('d'));
            }
        }

        // Contacto da Izzycar (usado sempre, independentemente do cliente)
        $this->writeComb($mpdf, 'telefone', '928459346');
        $this->writeComb($mpdf, 'email', 'GERAL@IZZYCAR.PT');

        // DATA (junto à ASSINATURA) fica em branco de propósito — é o cliente
        // que a preenche à mão no momento em que assina o pedido.

        // --- Características do Veículo ---
        if ($legalization->matricula) {
            $partes = array_pad(explode('-', $legalization->matricula), 3, '');
            $this->writeLine($mpdf, 'matricula_1', $partes[0]);
            $this->writeLine($mpdf, 'matricula_2', $partes[1]);
            $this->writeLine($mpdf, 'matricula_3', $partes[2]);
        }
        $this->writeLine($mpdf, 'marca', $legalization->marca);
        $this->writeLine($mpdf, 'modelo', $legalization->modelo);
        $this->writeLine($mpdf, 'homologacao', $legalization->num_homologacao);
        $this->writeLine($mpdf, 'combustivel', $legalization->combustivel);

        // Data da matrícula anterior — guardada como data ISO (input type="date")
        // em modelo9_dados['matricula_anterior_data'], escrita em 3 campos
        // separados (ano/mês/dia) para respeitar os traços já impressos no PDF.
        if (!empty($dados['matricula_anterior_data'])) {
            try {
                $dataAnterior = \Carbon\Carbon::parse($dados['matricula_anterior_data']);
                $this->writeLine($mpdf, 'matricula_anterior_data_ano', $dataAnterior->format('Y'));
                $this->writeLine($mpdf, 'matricula_anterior_data_mes', $dataAnterior->format('m'));
                $this->writeLine($mpdf, 'matricula_anterior_data_dia', $dataAnterior->format('d'));
            } catch (\Throwable $e) {
                // valor não reconhecido como data — deixa os 3 campos em branco
            }
        }

        foreach (self::LINE_FIELDS as $key => $coords) {
            $ignorados = [
                'matricula_1', 'matricula_2', 'matricula_3', 'marca', 'modelo', 'homologacao', 'combustivel',
                'matricula_anterior_data_ano', 'matricula_anterior_data_mes', 'matricula_anterior_data_dia',
            ];
            if (in_array($key, $ignorados)) {
                continue; // já tratados acima (vêm da legalização ou precisam de parsing especial)
            }
            if (!empty($dados[$key])) {
                $this->writeLine($mpdf, $key, (string) $dados[$key]);
            }
        }

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }

    private function pt2mm(float $pt): float
    {
        return $pt * 25.4 / 72;
    }

    /**
     * Divide um texto em duas partes (para a 2ª linha do NOME), cortando
     * na última palavra completa que caiba no limite da 1ª linha.
     */
    private function splitAtWordBoundary(string $text, int $maxLine1, int $maxLine2): array
    {
        if (mb_strlen($text) <= $maxLine1) {
            return [$text, ''];
        }

        $chunk = mb_substr($text, 0, $maxLine1);
        $lastSpace = mb_strrpos($chunk, ' ');

        if ($lastSpace === false) {
            return [$chunk, mb_substr($text, $maxLine1, $maxLine2)];
        }

        $line1 = mb_substr($chunk, 0, $lastSpace);
        $resto = trim(mb_substr($text, $lastSpace));

        return [$line1, mb_substr($resto, 0, $maxLine2)];
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

    private function writeComb(Mpdf $mpdf, string $fieldKey, ?string $text): void
    {
        if (!isset(self::COMB_FIELDS[$fieldKey]) || empty($text)) {
            return;
        }

        $field    = self::COMB_FIELDS[$fieldKey];
        $ticks    = $field['ticks'];
        $baseline = $this->pt2mm($field['baseline']) - 1;
        $chars    = mb_str_split(mb_strtoupper($text, 'UTF-8'));

        $mpdf->SetFont('dejavusans', '', 9);
        $mpdf->SetTextColor(0, 0, 0);

        $maxChars = count($ticks) - 1;
        foreach ($chars as $i => $ch) {
            if ($i >= $maxChars || $ch === ' ') {
                continue;
            }
            $centerX = $this->pt2mm(($ticks[$i] + $ticks[$i + 1]) / 2);
            $mpdf->SetXY($centerX - 1.6, $baseline - 3);
            $mpdf->Cell(3.2, 3.2, $ch, 0, 0, 'C');
        }
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
