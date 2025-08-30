<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use  \App\Models\Page;

class PageController extends Controller
{

  

    public function getEnumValues($enum)
    {
        // Remove colchetes e aspas, transforma em array de IDs
        $ids = json_decode($enum, true);

        if (!is_array($ids)) {
            return [];
        }

        // Busca as páginas correspondentes aos IDs
        $pages = Page::whereIn('id', $ids)->get();

        // Monta um array com o conteúdo de cada página
        $result = [];
        foreach ($pages as $page) {
            $data = [];
            foreach ($page->contents as $content) {
                $data[$content->field_name] = $content->field_value;
            }
            $result[] = $data;
        }

        return $result;
    }
}
