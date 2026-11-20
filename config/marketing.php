<?php

/**
 * Marketing Studio.
 *
 * `topics`: biblioteca de TEMAS para posts educativos e de serviços. Só traz o
 * título e uma indicação de estrutura; o texto (sobretudo o fiscal/legal) é
 * escrito por quem publica. `legal_check` = tema fiscal/legal: o editor avisa
 * "Verificar a legislação atual antes de publicar" e pede confirmação ao exportar.
 */
return [
    'ai_model' => env('MARKETING_AI_MODEL', 'gpt-4o-mini'),
    'ai_daily_limit' => (int) env('MARKETING_AI_DAILY_LIMIT', 30),

    'topics' => [
        // Educativos
        ['key' => 'isv', 'type' => 'educativo', 'title' => 'O que é o ISV?', 'template' => 'dica-rapida', 'legal_check' => true, 'hint' => 'Explica o conceito em linguagem simples e remete para o simulador do site.'],
        ['key' => 'dav', 'type' => 'educativo', 'title' => 'O que é a DAV?', 'template' => 'pergunta-frequente', 'legal_check' => true, 'hint' => 'Pergunta e resposta curta: o que é e para que serve.'],
        ['key' => 'homologacao', 'type' => 'educativo', 'title' => 'Homologação de viaturas importadas', 'template' => 'passo-a-passo', 'legal_check' => true, 'hint' => 'Passos do processo, do mais cedo ao mais tarde.'],
        ['key' => 'coc', 'type' => 'educativo', 'title' => 'O que é o COC?', 'template' => 'pergunta-frequente', 'legal_check' => false, 'hint' => 'Pergunta e resposta curta sobre o documento.'],
        ['key' => 'eletrico', 'type' => 'educativo', 'title' => 'Importar um elétrico', 'template' => 'educativo-carrossel', 'legal_check' => true, 'hint' => 'Capa e um ponto por slide: vantagens, cuidados, documentação.'],
        ['key' => 'custos', 'type' => 'educativo', 'title' => 'Custos de importar uma viatura', 'template' => 'educativo-carrossel', 'legal_check' => true, 'hint' => 'Um custo ou componente por slide, sem valores que mudem.'],
        ['key' => 'importar-vs-comprar', 'type' => 'educativo', 'title' => 'Importar ou comprar em Portugal?', 'template' => 'educativo-carrossel', 'legal_check' => false, 'hint' => 'Prós e contras de cada opção, um por slide.'],
        ['key' => 'usado', 'type' => 'educativo', 'title' => 'Importar um usado: o que verificar', 'template' => 'erros-comuns', 'legal_check' => false, 'hint' => 'Lista curta do que ver antes de decidir.'],
        ['key' => 'documentacao', 'type' => 'educativo', 'title' => 'Documentos necessários para importar', 'template' => 'passo-a-passo', 'legal_check' => true, 'hint' => 'Um documento por passo e para que serve.'],
        ['key' => 'mitos-importacao', 'type' => 'educativo', 'title' => 'Mitos sobre importar carros', 'template' => 'mito-verdade', 'legal_check' => false, 'hint' => 'Mito e verdade lado a lado, um par por slide.'],
        ['key' => 'erros-importacao', 'type' => 'educativo', 'title' => 'Erros comuns ao importar', 'template' => 'erros-comuns', 'legal_check' => false, 'hint' => 'Erro e como evitá-lo, até 5.'],
        ['key' => 'isencao-transferencia-residencia', 'type' => 'educativo', 'title' => 'Isenção por transferência de residência', 'template' => 'pergunta-frequente', 'legal_check' => true, 'hint' => 'Pergunta e resposta; confirma sempre as condições em vigor.'],

        // Serviços
        ['key' => 'servico-importacao', 'type' => 'servico', 'title' => 'Importação de viaturas', 'template' => 'servico', 'legal_check' => false, 'hint' => 'O que fazemos, em até 4 pontos.'],
        ['key' => 'servico-legalizacao', 'type' => 'servico', 'title' => 'Legalização de viaturas', 'template' => 'servico', 'legal_check' => false, 'hint' => 'O que tratamos por ti, em até 4 pontos.'],
        ['key' => 'servico-consultoria', 'type' => 'servico', 'title' => 'Consultoria na escolha da viatura', 'template' => 'servico', 'legal_check' => false, 'hint' => 'Como ajudamos a escolher, em até 4 pontos.'],
        ['key' => 'servico-simulacao', 'type' => 'servico', 'title' => 'Simulação de importação', 'template' => 'servico', 'legal_check' => true, 'hint' => 'O que a simulação mostra; sem valores nem taxas na imagem.'],
        ['key' => 'servico-isv', 'type' => 'servico', 'title' => 'Cálculo do ISV', 'template' => 'servico', 'legal_check' => true, 'hint' => 'Como ajudamos com o ISV; sem valores nem taxas na imagem.'],
        ['key' => 'servico-apoio-documental', 'type' => 'servico', 'title' => 'Apoio documental', 'template' => 'servico', 'legal_check' => false, 'hint' => 'Que documentos acompanhamos, em até 4 pontos.'],
        ['key' => 'servico-transporte', 'type' => 'servico', 'title' => 'Transporte da viatura', 'template' => 'servico', 'legal_check' => false, 'hint' => 'Como tratamos do transporte, em até 4 pontos.'],
    ],
];
