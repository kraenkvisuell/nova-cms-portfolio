<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kraenkvisuell\NovaCmsBlocks\Value\BlocksCast;
use Kraenkvisuell\NovaCmsPortfolio\Factories\ArtistFactory;
use Spatie\Translatable\HasTranslations;

class Artist extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $guarded = [];

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'artists';
    }

    public $translatable = [
        'description',
        'browser_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'robots' => 'array',
        'testimonials' => BlocksCast::class,
    ];

    protected static function newFactory()
    {
        return ArtistFactory::new();
    }

    public function slideshows()
    {
        return $this->hasMany(Slideshow::class)
            ->orderBy('sort_order');
    }

    public function works()
    {
        return $this->hasManyThrough(Work::class, Slideshow::class);
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, config('nova-cms-portfolio.db_prefix').'artist_discipline');
    }

    public function url()
    {
        $locales = config('nova-translatable.locales');
        $locale = app()->getLocale();

        // Multi-language
        if (is_array($locales) and count($locales) > 1) {
            return route('nova-artist-multi', ['locale' => $locale, 'artist' => $this->slug]);
        }

        return route('nova-artist-single', ['artist' => $this->slug]);
    }

    public function portfolioImage()
    {
        if ($this->portfolio_image) {
            return $this->portfolio_image;
        }

        if ($this->works->count()) {
            $markedWork = $this->works->where('is_artist_portfolio_image', true)->first();

            if (! $markedWork) {
                $markedWork = $this->works->where('show_in_overview', true)->first();
            }

            if (! $markedWork) {
                $markedWork = $this->works->first();
            }

            if ($markedWork) {
                return $markedWork->file;
            }
        }
    }

    public function categoriesForDiscipline($disciplineId)
    {
        $slidewhows = $this->slideshows;

        if ($disciplineId) {
            $slidewhows = $slidewhows->filter(function ($slideshow) use ($disciplineId) {
                return ! $slideshow->disciplines
                    || $slideshow->disciplines->pluck('id')->contains($disciplineId);
            });
        }

        $categories = collect([]);

        foreach ($slidewhows as $slidewhow) {
            foreach ($slidewhow->categories as $category) {
                $categories->push($category);
            }
        }

        return $categories->unique('id')->sortBy('title');
    }

    public function workForDiscipline($disciplineId)
    {
        $markedWork = $this->works()
            ->where('is_artist_discipline_image', true)
            ->first();

        if ($markedWork) {
            return $markedWork;
        }

        $slideshow = $this->slideshows()
            ->where('discipline_id', $disciplineId)
            ->has('works')
            ->first();

        if (! $slideshow) {
            $slideshow = $this->slideshows()
                ->has('works')
                ->first();
        }

        if ($slideshow) {
            $markedWork = $slideshow->works()
                ->where('show_in_overview', true)
                ->first();

            if (! $markedWork) {
                $markedWork = $slideshow->works()->first();
            }

            return $markedWork;
        }

        return new Work();
    }

    public function workForDisciplineUrl($disciplineId)
    {
        $work = $this->workForDiscipline($disciplineId);

        return $work && $work->file ? nova_cms_image($work->file) : null;
    }

    public function worksForCategory($categoryId, $disciplineId)
    {
        $works = $this->works()->whereHas('slideshow', function ($q) use ($categoryId, $disciplineId) {
            $q->where(function ($q) use ($categoryId, $disciplineId) {
                $q->whereNull('discipline_id')
                  ->orWhere('discipline_id', $disciplineId);
            })
            ->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
        })
        ->where('represents_artist_in_discipline_category->'.$disciplineId.'_'.$categoryId, true)
        ->get();

        if ($works->count()) {
            return $works;
        }

        $works = $this->works()->whereHas('slideshow', function ($q) use ($categoryId, $disciplineId) {
            $q->where(function ($q) use ($disciplineId) {
                $q->whereNull('discipline_id')
                  ->orWhere('discipline_id', $disciplineId);
            })
            ->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
        })->limit(2)->get();

        return $works;
    }
}
