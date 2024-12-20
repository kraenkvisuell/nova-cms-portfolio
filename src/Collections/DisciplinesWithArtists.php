<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Collections;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;
use Laravel\Nova\Fields\Boolean;

class DisciplinesWithArtists
{
    public static function get()
    {
        //return Cache::tags('artists')->rememberForever('DisciplinesWithArtists.'.app()->getLocale(), function () {

        $disciplines = Discipline::ordered()
                ->has('artists')
                ->with(['artists' => function ($b) {
                    $b->where('is_published', true);

                    if (config('nova-cms-portfolio.has_productions')) {
                        $b->where('is_external', false);
                    }

                    $b->with(['works'])
                        ->select([
                            'id',
                            'name',
                            'slug',
                            'is_published',
                        ]);
                }])
                ->get();

        $results = [];

        foreach ($disciplines as $discipline) {
            $artists = [];

            foreach ($discipline->artists as $artist) {
                $portfolioImages = [];

                foreach ($artist->portfolioImages() as $portfolioImage) {
                    $item = [
                        'imgUrls' => [],
                        'ratio' => $portfolioImage ? nova_cms_ratio($portfolioImage) : 1,
                    ];

                    foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                        if ($portfolioImage) {
                            $item['imgUrls'][$sizeKey] = nova_cms_image($portfolioImage, $sizeKey);
                        } else {
                            $item['imgUrls'][$sizeKey] = nova_cms_empty_image();
                        }
                    }

                    $portfolioImages[] = $item;
                }

                $artists[] = [
                    'id' => $artist->id,
                    'slug' => $artist->slug,
                    'name' => $artist->name,
                    'portfolioImages' => $portfolioImages,
                ];
            }

            $result = [
                'id' => $discipline->id,
                'title' => $discipline->title,
                'slug' => $discipline->slug,
                'artists' => collect($artists)
                    ->sortBy(function ($result) {
                        return Str::of($result['name'])->afterLast(' ');
                    })
                    ->values()
                    ->all(),
            ];

            $results[] = $result;
        }

        return $results;
        //});
    }
}
