<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Kraenkvisuell\NovaCms\Facades\ContentParser;
use Kraenkvisuell\NovaCms\Traits\HasContentBlocks;
use Kraenkvisuell\NovaCmsBlocks\Value\BlocksCast;
use Kraenkvisuell\NovaCmsPortfolio\Traits\Publishable;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasContentBlocks;
    use Publishable;
    use HasTranslations;

    protected $guarded = [];

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix') . 'projects';
    }

    public $translatable = [
        'title',
        'slug',
        'abstract',
        'industry',
        'format',
        'skills',
        'browser_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
    ];

    protected $casts = [
        'robots' => 'array',
        'main_content' => BlocksCast::class,
    ];

    public $contentBlockFields = [
        'main_content',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
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
        return $this->belongsToMany(Discipline::class, config('nova-cms-portfolio.db_prefix') . 'project_discipline');
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, config('nova-cms-portfolio.db_prefix') . 'artist_project');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, config('nova-cms-portfolio.db_prefix') . 'project_category')
            ->withPivot(['sort_order'])
            ->orderBy(config('nova-cms-portfolio.db_prefix') . 'project_category.sort_order')
            ->using(ProjectCategory::class);
    }

    public function url()
    {
        $locales = config('nova-translatable.locales');
        $locale = app()->getLocale();

        // Multi-language
        if (is_array($locales) and count($locales) > 1) {
            return route('nova-project-multi', ['locale' => $locale, 'project' => $this->slug]);
        }

        return route('nova-project-single', ['project' => $this->slug]);
    }

    public function portfolioImage()
    {
        if (config('nova-cms-portfolio.has_custom_portfolio_image') && $this->portfolio_image) {
            return $this->portfolio_image;
        }

        if ($this->works->count()) {
            $markedWork = $this->works->where('is_project_portfolio_image', true)->first();

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

    public function portfolioImages()
    {
        $images = [];

        if (config('nova-cms-portfolio.has_custom_portfolio_image') && $this->portfolio_image) {
            $images[] = $this->portfolio_image;
        }
        if ($this->works->count()) {
            $limit = config('nova-cms-portfolio.number_of_portfolio_images') - count($images);

            if ($limit > 0) {
                foreach (
                    $this->works->where('is_project_portfolio_image', true)->take($limit)
                    as $work
                ) {
                    $images[] = $work->file;
                }
            }

            if (config('nova-cms-portfolio.has_portfolio_images_fallback')) {
                $limit = config('nova-cms-portfolio.number_of_portfolio_images') - count($images);

                if ($limit > 0) {
                    foreach (
                        $this->works->where('show_in_overview', true)->take($limit)
                        as $work
                    ) {
                        $images[] = $work->file;
                    }
                }

                $limit = config('nova-cms-portfolio.number_of_portfolio_images') - count($images);

                if ($limit > 0) {
                    foreach (
                        $this->works->take($limit)
                        as $work
                    ) {
                        $images[] = $work->file;
                    }
                }
            }
        }

        return $images;
    }

    public function skillImage()
    {
        if ($this->skill_image) {
            return $this->skill_image;
        }

        return $this->portfolioImage();
    }

    public function startpageImages()
    {
        $images = [];

        foreach (
            $this->works->where('is_startpage_image', true)->take(1)
            as $work
        ) {
            $images[] = $work->file;
        }

        return $images;
    }

    public function overviewImages()
    {
        $worksWith = [
            'slideshow' => function ($b) {
                $b->select([
                    'id',
                    'slug',
                    'title',
                    'sort_order',
                ])
                    ->with([
                        'works' => function ($b) {
                            $b->select([
                                'id',
                                'slideshow_id',
                            ]);
                        },

                    ]);
            },
        ];

        $prefix = config('nova-cms-portfolio.db_prefix');

        $categoryId = $this->categories
            ->filter(function ($category) {
                return ! stristr($category->slug, 'commission');
            })
            ->first()
            ?->id;

        $worksBuilder = $this->works()
            ->limit(config('nova-cms-portfolio.max_overview_thumbnails'))
            ->with($worksWith)
            ->join($prefix . 'slideshows as slideshows_alias', 'slideshows_alias.id', '=', $prefix . 'works.slideshow_id')
            ->orderByDesc($prefix . 'works.show_in_overview')
            ->orderBy($prefix . 'works.sort_order')
            ->orderBy($prefix . 'slideshows_alias.sort_order')
            ->orderByDesc($prefix . 'works.id')
            ->whereDoesntHave('slideshow', function (Builder $b) {
                $b->whereHas('categories', function (Builder $b) {
                    $b->where('title->en', 'like', 'Commission%');
                });
            });

        $worksBuilder->where(function (Builder $b) use ($categoryId) {
            $b->whereHas('slideshow', function (Builder $b) use ($categoryId) {
                $b->where('is_published', true)
                    ->whereHas('categories', function (Builder $b) use ($categoryId) {
                        $b->where('id', $categoryId);
                    });
            })->orWhere('show_in_overview', true);
        });

        $images = [];

        foreach (
            $worksBuilder->get()
            as $work
        ) {
            $images[] = $work->file;
        }

        return $images;
    }

    public function startpageImage()
    {
        return $this->startpageImages()[0] ?? null;
    }

    public function slideshowCategories()
    {
        $slideshows = $this->slideshows;

        $categories = collect([]);

        foreach ($slideshows as $slideshow) {
            foreach ($slideshow->categories as $category) {
                $categories->push($category);
            }
        }

        return $categories->unique('id')->sortBy('title');
    }

    public function categoriesForDiscipline($disciplineId = null)
    {
        if (! $disciplineId) {
            $disciplineId = Discipline::first()?->id;
        }

        $slideshows = $this->slideshows;

        if ($disciplineId) {
            $slideshows = $slideshows->filter(function ($slideshow) use ($disciplineId) {
                return ! $slideshow->disciplines
                    || $slideshow->disciplines->pluck('id')->contains($disciplineId);
            });
        }

        $categories = collect([]);

        foreach ($slideshows as $slideshow) {
            foreach ($slideshow->categories as $category) {
                $categories->push($category);
            }
        }

        return $categories->unique('id')->sortBy('title');
    }

    public function workForDiscipline($disciplineId)
    {
        $markedWork = $this->works()
            ->where('is_project_discipline_image', true)
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
            $q->where(function ($q) use ($disciplineId) {
                $q->whereNull('discipline_id')
                    ->orWhere('discipline_id', $disciplineId);
            })
                ->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('id', $categoryId);
                });
        })
            ->where('represents_project_in_discipline_category->' . $disciplineId . '_' . $categoryId, true)
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

    public function socialLinks()
    {
        $socialLinks = collect([]);

        $this->social_links->each(function ($item) use (&$socialLinks) {
            $socialLinks->push(
                ContentParser::produceAttributes($item->getAttributes())
            );
        });

        return $socialLinks;
    }

    public function testimonials()
    {
        $testimonials = collect([]);

        $this->testimonials->each(function ($item) use (&$testimonials) {
            $testimonials->push(
                ContentParser::produceAttributes($item->getAttributes())
            );
        });

        return $testimonials;
    }
}
