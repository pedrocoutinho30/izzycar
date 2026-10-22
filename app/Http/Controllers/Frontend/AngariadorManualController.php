<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

/**
 * Manual do angariador fora do backoffice. A rota exige login (`auth`),
 * só a página de apresentação do programa (`landing`) é pública.
 */
class AngariadorManualController extends Controller
{
    public function index()
    {
        return view('public.manual-angariador');
    }

    /**
     * Página de apresentação do programa a potenciais angariadores —
     * distinta do manual (que é o guia operacional para quem já foi aprovado).
     */
    public function landing()
    {
        return view('public.angariador-landing');
    }
}
