<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kraenkvisuell\NovaCmsPortfolio\Factories\DisciplineFactory;

class Discipline extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;
    use HasTranslations;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];
    
    protected $guarded = [];

    protected static function newFactory()
    {
        return DisciplineFactory::new();
    }

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'disciplines';
    }

    public $translatable = [
        'title',
        'slug',
        'description',
        'browser_title',
        'meta_description',
    ];

    protected $casts = [
        'robots' => 'array',
    ];

    public function getTitleForDropdownAttribute()
    {
        return $this->title;
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, config('nova-cms-portfolio.db_prefix').'artist_discipline');
    }

    public function getCategories()
    {
        $categories = collect([]);

        $this->artists
            ->where('is_published', true)
            ->each(function ($artist) use (&$categories) {
                $artist->slideshows->each(function ($slideshow) use (&$categories) {
                    $slideshow->categories->each(function ($category) use (&$categories) {
                        $categories->push($category);
                    });
                });
            });

        $categories = $categories->unique('title')->sortBy('title')->all();

        return $categories;
    }

    public function getCachedCategories()
    {
        return Cache::remember('disciplineCategories.'.app()->getLocale(), now()->addSeconds(5), function () {
            return $this->getCategories();
        });
    }

    public static function getWithSortedArtists()
    {
        return static::ordered()
            ->with('artists', function ($q) {
                $q->with([
                        'slideshows.categories'
                    ])
                    ->where('is_published', true)
                    ->has('slideshows')
                    ->orderBy('name');
            })
            ->whereHas('artists', function ($q) {
                $q->where('is_published', true)
                ->has('slideshows');
            })
            ->get();
    }

    public static function getCachedWithSortedArtists()
    {
        return Cache::remember('discipliesWithSortedArtists.'.app()->getLocale(), now()->addSeconds(5), function () {
            return static::getWithSortedArtists();
        });
    }

    public static function getCachedWithSortedArtistsAndCategories()
    {
        $disciplines = static::getCachedWithSortedArtists();

        $disciplines->each(function ($discipline) {
            $discipline->categories = $discipline->getCategories();
        });

        return $disciplines;
    }

    public static function getVisibleFilled()
    {
        return static::ordered()
            ->whereHas('artists', function ($q) {
                $q->where('is_published', true)
                ->has('slideshows');
            })
            ->select('title', 'slug')
            ->get();
    }
}
