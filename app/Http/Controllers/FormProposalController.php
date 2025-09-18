<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormProposalController extends Controller
{
    //
    public function index()
    {

        $forms = \App\Models\FormProposal::orderBy('created_at', 'desc')->paginate(10);
        return view('form_proposal.index', compact('forms'));
    }

    public function show($id)
    {
        $form = \App\Models\FormProposal::findOrFail($id);
        return view('form_proposal.show', compact('form'));
    }
}
