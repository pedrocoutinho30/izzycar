<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ManualController extends Controller
{
    public function index()
    {
        return view('admin.v2.manual.index');
    }

    public function angariador()
    {
        return view('admin.v2.manual.angariador');
    }

    public function angariadorFaq()
    {
        return view('admin.v2.manual.faq');
    }
}
