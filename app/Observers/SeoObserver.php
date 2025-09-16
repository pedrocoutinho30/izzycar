<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSeo;

class SeoObserver
{
    public function saved(Model $model)
    {
        if (request()->has('seo')) {
            $seoData = request()->input('seo', []);
           
            if (method_exists($model, 'seo')) {
                $model->seo()->updateOrCreate([], $seoData);
            }
        }
    }
}
