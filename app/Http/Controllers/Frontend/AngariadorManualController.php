<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

/**
 * Versão pública do manual do angariador — sem necessidade de login,
 * para poder ser partilhada livremente com quem se pretenda angariar.
 */
class AngariadorManualController extends Controller
{
    public function index()
    {
        return view('public.manual-angariador');
    }
}
