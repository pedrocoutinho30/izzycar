<?php

// Tradução parcial: as restantes mensagens de validação caem no fallback (lang/en).
// Aqui ficam as da política de palavras-passe (Password::defaults()) e as que ela usa.
return [
    'confirmed' => 'A confirmação de :attribute não coincide.',
    'min' => [
        'string' => ':Attribute tem de ter pelo menos :min caracteres.',
    ],
    'password' => [
        'letters' => ':Attribute tem de conter pelo menos uma letra.',
        'mixed' => ':Attribute tem de conter pelo menos uma letra maiúscula e uma minúscula.',
        'numbers' => ':Attribute tem de conter pelo menos um número.',
        'symbols' => ':Attribute tem de conter pelo menos um símbolo.',
        'uncompromised' => 'Este valor de :attribute apareceu numa fuga de dados. Escolha outra password.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'required_with' => 'O campo :attribute é obrigatório quando :values está preenchido.',
    'same' => ':Attribute e :other têm de ser iguais.',
    'attributes' => [
        'password' => 'a password',
        'new_password' => 'a nova password',
        'password_confirmation' => 'a confirmação da password',
    ],
];
