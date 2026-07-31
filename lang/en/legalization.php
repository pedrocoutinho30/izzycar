<?php

return [

    // Public tracking page
    'page_title'      => 'Legalization Status',
    'badge'           => 'Process tracking',
    'matricula_label' => 'License plate',
    'progress_done'   => ':done of :total steps completed',

    'steps_title'   => 'Process Steps',
    'steps_sub'     => 'Current status of your vehicle legalization',
    'current_badge' => 'In progress',

    'docs_title' => 'Documents',
    'docs_sub'   => ':uploaded of :total documents received',

    'invoice_title'    => 'Service Invoice',
    'invoice_sub'      => 'Your invoice is now available for download',
    'invoice_download' => 'Download invoice',

    'footer_question' => 'Have questions about your process?',
    'footer_contact'  => 'Contact us',

    // Process steps (1 to 7)
    'passos' => [
        1 => 'Obtain national homologation number at IMT',
        2 => 'Inspection B and obtaining Form 112',
        3 => 'Filling in the DAV on the Tax Authority (Finanças) portal',
        4 => 'Pay the ISV (vehicle tax)',
        5 => 'Get license plates made and take out insurance',
        6 => 'Submit Form 9 at IMT',
        7 => 'Initial registration at the Vehicle Registry Office',
    ],

    // Document descriptions (without the acronym — it is added automatically)
    'documentos' => [
        'CMATR'                  => 'Foreign equivalent of the DUA (logbook / foreign registration certificate)',
        'DHTEC'                  => 'IMT Form 9',
        'FATDV'                  => 'Purchase invoice or bill of sale',
        'CCEUR'                  => 'Certificate of conformity (COC)',
        'guia_transporte'        => 'Transport document (optional, depending on the type of transport)',
        'CCIDA'                  => "Owner's ID card",
        'CINSP'                  => 'Inspection certificate (Form 112)',
        'dav'                    => 'DAV (Vehicle Customs Declaration)',
        'declaracao_homologacao' => 'Homologation declaration',
        'PRODH'                  => 'Power of attorney or authorization document',
        'CRESI'                  => 'Official residency certificate',
        'DVIDQ'                  => 'Proof of daily life in the country of origin',
        'CSTRI'                  => 'Tax and social security compliance certificate',
        'MD1460'                 => 'ISV exemption request',
    ],

    // Tracking email
    'email_subject'   => 'Track the Legalization Status of Your Vehicle',
    'email_heading'   => 'Legalization Tracking',
    'email_tagline'   => 'Izzycar — Car Importer',
    'email_greeting'  => 'Hello, :name!',
    'email_intro'     => 'We have started the legalization process for your vehicle. You can track the status of each step and the required documents in real time, using the link below — no login needed.',
    'email_cta'       => 'Track Legalization Status',
    'email_note'      => 'This link is personal and can be accessed at any time.',
    'email_question'  => 'Have questions?',
    'email_contact'   => 'Contact us',
    'email_disclaimer' => 'This is an automated email, please do not reply directly to this message.',

];
