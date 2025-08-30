<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use  \App\Models\Page;

use App\Models\FormProposal;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Frontend\PageController;

class SellingController extends Controller
{
  
    public function getSellingPage()
    {
        $data = Page::where('slug', 'venda-automovel')
            ->with('contents')
            ->firstOrFail();

        $data->contents = $data->contents->mapWithKeys(function ($content) {
            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'enum_venda') {
                $content->field_name = 'enum';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [$content->field_name => $content->field_value];
        });

        return view('frontend.legalization', compact('data'));
    }

}
