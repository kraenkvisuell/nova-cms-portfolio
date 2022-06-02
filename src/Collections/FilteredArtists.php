<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Collections;

use Illuminate\Database\Eloquent\Builder;
use Kraenkvisuell\NovaCmsMedia\Core\Model;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;

class FilteredArtists
{
    public static function get(
        int $disciplineId = null,
        int $categoryId = null,
        string $needle = '',
        int $workLimit = 10
    ) {
        $artistsBuilder = Artist::where('is_published', true);

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

        foreach ($artists as $artist) {
            $worksBuilder = $artist->works();

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

            foreach ($worksBuilder->limit($workLimit)->get() as $work) {
                $imgUrls = [];
                foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                    $imgUrls[$sizeKey] = nova_cms_image($work->file, $sizeKey);
                }

                $ratio = 1;

                $works[] = [
                    'imgUrls' => $imgUrls,
                    'ratio' => nova_cms_ratio($work->file),
                ];
            }

            $results[] = [
                'id' => $artist->id,
                'name' => $artist->name,
                'slug' => $artist->slug,
                'works' => $works,
            ];
        }

        return collect($results)->sortBy('name')->all();
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
