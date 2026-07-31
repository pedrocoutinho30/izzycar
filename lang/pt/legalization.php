<?php

return [

    // Página pública de acompanhamento
    'page_title'        => 'Estado da Legalização',
    'badge'              => 'Acompanhamento do processo',
    'matricula_label'    => 'Matrícula',
    'progress_done'      => ':done de :total passos concluídos',

    'steps_title' => 'Passos do Processo',
    'steps_sub'   => 'Estado actual da legalização da sua viatura',
    'current_badge' => 'Em curso',

    'docs_title' => 'Documentos',
    'docs_sub'   => ':uploaded de :total documentos recebidos',

    'invoice_title'    => 'Fatura do Serviço',
    'invoice_sub'      => 'A sua fatura já está disponível para download',
    'invoice_download' => 'Descarregar fatura',

    'footer_question' => 'Tem dúvidas sobre o seu processo?',
    'footer_contact'  => 'Contacte-nos',

    // Passos do processo (1 a 7)
    'passos' => [
        1 => 'Obter número de homologação nacional no IMT',
        2 => 'Inspeção B e obtenção do Modelo 112',
        3 => 'Preenchimento da DAV no portal das Finanças',
        4 => 'Fazer o pagamento do ISV',
        5 => 'Fazer chapas de matrícula e contratar seguro',
        6 => 'Entregar Modelo 9 no IMT',
        7 => 'Registo inicial na Conservatória do Registo Automóvel',
    ],

    // Descrição de cada documento (sem a sigla — é adicionada automaticamente)
    'documentos' => [
        'CMATR'                  => 'Documento estrangeiro equiv. DUA (Livrete/título de registo)',
        'DHTEC'                  => 'Formulário Modelo 9 IMT',
        'FATDV'                  => 'Fatura de compra ou declaração de venda',
        'CCEUR'                  => 'Certificado de conformidade (COC)',
        'guia_transporte'        => 'Guia de transporte (opcional, dependendo do tipo de transporte)',
        'CCIDA'                  => 'Cartão de cidadão do proprietário',
        'CINSP'                  => 'Certificado de inspeção (Modelo 112)',
        'dav'                    => 'DAV (Declaração Aduaneira de Veículo)',
        'declaracao_homologacao' => 'Declaração de Homologação',
        'PRODH'                  => 'Procuração ou documento de habilitação',
        'CRESI'                  => 'Certificado de residência oficial',
        'DVIDQ'                  => 'Documentos de vida quotidiana',
        'CSTRI'                  => 'Certidão de situação tributária',
        'MD1460'                 => 'Pedido de isenção de ISV',
    ],

    // Email de acompanhamento
    'email_subject'    => 'Acompanhe o Estado da Legalização da sua Viatura',
    'email_heading'    => 'Acompanhamento da Legalização',
    'email_tagline'    => 'Izzycar — Importação de Automóveis',
    'email_greeting'   => 'Olá, :name!',
    'email_intro'       => 'Já iniciámos o processo de legalização da sua viatura. Pode acompanhar o estado de cada etapa e dos documentos necessários em tempo real, através do link abaixo — sem necessidade de login.',
    'email_cta'         => 'Acompanhar Estado da Legalização',
    'email_note'        => 'Este link é pessoal e pode ser acedido a qualquer momento.',
    'email_question'    => 'Tem dúvidas?',
    'email_contact'     => 'Contacte-nos',
    'email_disclaimer'  => 'Este é um email automático, por favor não responda diretamente a esta mensagem.',

];
