<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Collections;

use Illuminate\Database\Eloquent\Builder;
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
        $artistsBuilder = Artist::where('is_published', true)
            ->with([
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

        foreach ($artists as $artist) {
            $worksBuilder = $artist->works()
                ->limit($workLimit)
                ->with($worksWith)
                ->orderByDesc('show_in_overview')
                ->orderBy('sort_order')
                ->orderByDesc('id');

            if ($needle) {
            } else {
                if ($categoryId) {
                    $worksBuilder->whereHas('slideshow', function (Builder $b) use ($categoryId) {
                        $b->where('is_published', true)
                            ->whereHas('categories', function (Builder $b) use ($categoryId) {
                                $b->where('id', $categoryId);
                            });
                    });
                }
            }

            $works = [];

            foreach ($worksBuilder->get() as $work) {
                $imgUrls = [];
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
            $results = $results->sortBy('artist.name');
        }

        return $results->values()->all();
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
                $b->has('works')
                    ->where('is_published', true)
                    ->whereHas('categories', function (Builder $b) use ($categoryId) {
                        $b->where('id', $categoryId);
                    });
            });
        }

        return $artistsBuilder->get();
    }
}
