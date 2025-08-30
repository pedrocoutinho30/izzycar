<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageType extends Model
{
    const NEWS = 'Notícias';
    const CATEGORIES = 'Categorias';
    const LEGALIZATIONS = 'Legalização';
    const IMPORTS = 'Importação';
    const SELLING = 'Venda Automóvel';
    
    protected $fillable = ['name', 'slug'];


    public function fields()
    {
        return $this->hasMany(PageTypeField::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
