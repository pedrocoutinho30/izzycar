<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSeo;

class Page extends Model
{

    use HasSeo;
    
    protected $fillable = ['page_type_id', 'title', 'slug', 'menu_id', 'parent_id'];

    public function pageType()
    {
        return $this->belongsTo(PageType::class);
    }

    public function contents()
    {
        return $this->hasMany(PageContent::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'page_id');
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
