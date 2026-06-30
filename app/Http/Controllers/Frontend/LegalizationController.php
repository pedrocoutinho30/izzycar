<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;

class LegalizationController extends Controller
{
    public function getLegalizationPage()
    {
        $cmsPage = CmsPage::where('slug', 'legalizacao')->with('activeBlocks')->first();
        $cms     = $cmsPage ? $cmsPage->activeBlocks->keyBy('name') : collect();

        return view('frontend.legalization', compact('cms'));
    }
}
