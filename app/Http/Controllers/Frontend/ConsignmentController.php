<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Http\Controllers\Frontend\PageController;

class ConsignmentController extends Controller
{
    public function getConsignmentPage()
    {
        $data = Page::where('slug', 'consignacao-automovel')
            ->with('contents')
            ->firstOrFail();

        $data->contents = $data->contents->mapWithKeys(function ($content) {
            // Verifica se o campo é enum e se for obtém os valores
            if ($content->field_name == 'enum') {
                $content->field_name = 'enum';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [$content->field_name => $content->field_value];
        });

        return view('frontend.consignment', compact('data'));
    }
}
