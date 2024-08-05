<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Factories\CategoryFactory;
use Kraenkvisuell\NovaCmsPortfolio\Traits\QueryableByTranslation;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;
    use QueryableByTranslation;

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
        'og_title',
        'og_description',
    ];

    protected $casts = [
        'robots' => 'array',
    ];

    public function getTitleForDropdownAttribute()
    {
        return $this->title;
    }

    public function slideshows()
    {
        $builder = $this->belongsToMany(Slideshow::class, config('nova-cms-portfolio.db_prefix').'category_slideshow')
            ->withPivot(['sort_order']);

        return $builder->with('artist')->using(CategorySlideshow::class);
    }

    public function filtered_slideshows()
    {
        $builder = $this->belongsToMany(Slideshow::class, config('nova-cms-portfolio.db_prefix').'category_slideshow')
            ->withPivot(['sort_order']);

        if (config('nova-cms-portfolio.category_slideshows_are_filtered')) {
            $builder->withWhereHas('works', function($b){
                $b->where('represents_artist_in_discipline_category->1_'.$this->id, true);
            });
        }

        return $builder->with('artist')->using(CategorySlideshow::class);
    }

    public static function getCached()
    {
        return Cache::remember('cachedCategories.'.app()->getLocale(), now()->addSeconds(10), function () {
            return static::all()->sortBy('title')->all();
        });
    }

    public static function getCachedIdBySlug($slug)
    {
        return Cache::tags('categories')->rememberForever(
            'category.getCachedIdBySlug.'.$slug,
            function () use ($slug) {
                return static::where('slug->'.app()->getLocale(), $slug)->first()?->id ?: 0;
            });
    }
}
