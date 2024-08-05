<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Factories\SkillFactory;
use Kraenkvisuell\NovaCmsPortfolio\Traits\QueryableBySlug;
use Kraenkvisuell\NovaCmsPortfolio\Traits\QueryableByTranslation;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

class Skill extends Model implements Sortable
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
        return SkillFactory::new();
    }

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'skills';
    }

    public $translatable = [
        'title',
        'slug',
        'description',
        'browser_title',
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

    public function artists()
    {
        $builder = $this->belongsToMany(Artist::class, config('nova-cms-portfolio.db_prefix').'artist_skill')
            ->withPivot(['sort_order']);


        return $builder->using(ArtistSkill::class);
    }

    public function filtered_artists()
    {
        $builder = $this->belongsToMany(Artist::class, config('nova-cms-portfolio.db_prefix').'artist_skill')
            ->withPivot(['sort_order']);


        return $builder->using(ArtistSkill::class);
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

    public static function getCachedIdBySlug($slug)
    {
        return Cache::tags('skills')->rememberForever(
            'skill.getCachedIdBySlug.'.$slug,
            function () use ($slug) {
                return static::where('slug->'.app()->getLocale(), $slug)->first()?->id ?: 0;
            });
    }

    public static function getCachedWithSortedArtists()
    {
        return Cache::remember('skillsWithSortedArtists.'.app()->getLocale(), now()->addSeconds(5), function () {
            return static::getWithSortedArtists();
        });
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
