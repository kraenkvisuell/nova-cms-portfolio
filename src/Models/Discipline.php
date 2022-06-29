<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Factories\DisciplineFactory;
use Kraenkvisuell\NovaCmsPortfolio\Traits\QueryableBySlug;
use Kraenkvisuell\NovaCmsPortfolio\Traits\QueryableByTranslation;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

class Discipline extends Model implements Sortable
{
    use HasFactory;
    use SortableTrait;
    use HasTranslations;
    use QueryableBySlug;
    use QueryableByTranslation;

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
        'meta_keywords',
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
                    'slideshows.categories',
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
