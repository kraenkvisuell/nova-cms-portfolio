<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kraenkvisuell\NovaCmsPortfolio\Factories\CategoryFactory;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    protected static function newFactory()
    {
        return CategoryFactory::new();
    }

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'categories';
    }

    public $translatable = [
        'title',
        'slug',
        'browser_title',
        'description',
        'meta_description',
        'meta_keywords',
    ];

    public function getTitleForDropdownAttribute()
    {
        return $this->title;
    }

    public function slideshows()
    {
        return $this->belongsToMany(Slideshow::class, config('nova-cms-portfolio.db_prefix').'category_slideshow')
            ->withPivot(['sort_order']);
    }
}
