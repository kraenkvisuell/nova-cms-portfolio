<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Collections;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;

class FilteredArtists
{
    public static function get(
        ?int $disciplineId,
        ?int $categoryId,
        ?string $needle,
        ?int $workLimit,
        ?string $sortOrder,
    ) {
        $cacheKey = 'filteredartists_'.$disciplineId
            .'_'.$categoryId
            .'_'.$needle
            .'_'.$workLimit
            .'_'.$sortOrder;

        //Cache::tags('artists')->forget($cacheKey);

        // return Cache::tags('artists')->rememberForever(
        //     $cacheKey,
        //     function () use ($disciplineId, $categoryId, $needle, $workLimit, $sortOrder) {
        $artistsBuilder = Artist::where('is_published', true)
                    ->with([
                        'categories',
                        'disciplines' => function ($b) {
                            $b->select([
                                'id',
                                'title',
                                'slug',
                            ]);
                        },
                    ]);

        $needle = trim(strtolower($needle));

        if ($needle) {
            $discipline = static::getDisciplineFromNeedle($needle);

            if ($discipline) {
                $needle = '';
                $disciplineId = $discipline->id;
            } else {
                $category = static::getCategoryFromNeedle($needle);
                if ($category) {
                    $needle = '';
                    $categoryId = $category->id;
                }
            }
        }

        if ($needle) {
            $artists = static::getArtistsForNeedle($needle, $artistsBuilder);
        } else {
            $artists = static::getArtistsForFilters($disciplineId, $categoryId, $artistsBuilder);
        }

        $results = [];

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

        foreach ($artists as $artist) {
            $worksBuilder = $artist->works()
                        ->limit($workLimit)
                        ->with($worksWith)
                        ->join($prefix.'slideshows as slideshows_alias', 'slideshows_alias.id', '=', $prefix.'works.slideshow_id')
                        ->orderByDesc($prefix.'works.show_in_overview')
                        ->orderBy($prefix.'works.sort_order')
                        ->orderBy($prefix.'slideshows_alias.sort_order')
                        ->orderByDesc($prefix.'works.id')
                        ->whereDoesntHave('slideshow', function (Builder $b) {
                            $b->whereHas('categories', function (Builder $b) {
                                $b->where('title->en', 'like', 'Commission%');
                            });
                        });

            if ($needle) {
            } else {
                $workCategoryId = $categoryId ?: $artist->categories
                    ->filter(function ($category) {
                        return ! stristr($category->slug, 'commission');
                    })
                    ->first()
                    ?->id;

                $worksBuilder->where(function (Builder $b) use ($workCategoryId) {
                    $b->whereHas('slideshow', function (Builder $b) use ($workCategoryId) {
                        $b->where('is_published', true)
                            ->whereHas('categories', function (Builder $b) use ($workCategoryId) {
                                $b->where('id', $workCategoryId);
                            });
                    })->orWhere('show_in_overview', true);
                });
            }

            $works = [];

            foreach ($worksBuilder->get() as $work) {
                $imgUrls = [
                    'original' => nova_cms_image($work->file),
                ];
                foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                    $imgUrls[$sizeKey] = nova_cms_image($work->file, $sizeKey);
                }

                $works[] = [
                    'id' => $work->id,
                    'imgUrls' => $imgUrls,
                    'positionInSlideshow' => $work->actualPosition(),
                    'ratio' => $work->fileRatio(),
                    'slideshow' => [
                        'id' => $work->slideshow_id,
                        'slug' => $work->slideshow->slug,
                        'title' => $work->slideshow->title,
                    ],
                    'embedUrl' => $work->embedUrl(),
                ];
            }

            $disciplines = [];

            foreach ($artist->disciplines as $discipline) {
                $disciplines[] = [
                    'id' => $discipline->id,
                    'slug' => $discipline->slug,
                    'title' => $discipline->title,
                ];
            }

            $results[] = [
                'artist' => [
                    'id' => $artist->id,
                    'disciplines' => $disciplines,
                    'name' => $artist->name,
                    'slug' => $artist->slug,
                ],
                'works' => $works,
            ];
        }

        $results = collect($results);

        if ($sortOrder == 'alphabetical') {
            $results = $results->sortBy(function ($result) {
                return Str::of($result['artist']['name'])->afterLast(' ');
            });
        }

        return $results->values()->all();
        // });
    }

    protected static function getDisciplineFromNeedle($needle)
    {
        return Discipline::queryByTranslation('title', $needle)
                ->first()
            ?: Discipline::queryByTranslation('slug', $needle)
                ->first();
    }

    protected static function getCategoryFromNeedle($needle)
    {
        return Category::queryByTranslation('title', $needle)
                ->first()
            ?: Category::queryByTranslation('slug', $needle)
                ->first();
    }

    protected static function getArtistsForNeedle($needle, $artistsBuilder)
    {
        return $artistsBuilder->get();
    }

    protected static function getArtistsForFilters($disciplineId, $categoryId, $artistsBuilder)
    {
        if ($disciplineId) {
            $artistsBuilder->whereHas('disciplines', function (Builder $b) use ($disciplineId) {
                $b->where('id', $disciplineId);
            });
        }

        if ($categoryId) {
            $artistsBuilder->whereHas('slideshows', function (Builder $b) use ($categoryId) {
                $b->where('is_published', true)
                    ->whereHas('categories', function (Builder $b) use ($categoryId) {
                        $b->where('id', $categoryId);
                    });
            });
        }

        return $artistsBuilder->get();
    }
}
