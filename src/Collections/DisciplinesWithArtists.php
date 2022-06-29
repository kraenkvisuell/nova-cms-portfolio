<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Collections;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;

class DisciplinesWithArtists
{
    public static function get()
    {
        //Cache::forget('DisciplinesWithArtists.' . app()->getLocale());
        return Cache::remember('DisciplinesWithArtists.'.app()->getLocale(), now()->addDays(7), function () {
            $disciplines = Discipline::ordered()
                ->has('artists')
                ->with(['artists' => function ($b) {
                    $b->where('is_published', true)
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
                    $actualImage = $artist->portfolioImage();

                    $imgUrls = [];
                    foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                        if ($actualImage) {
                            $imgUrls[$sizeKey] = nova_cms_image($actualImage, $sizeKey);
                        } else {
                            $imgUrls[$sizeKey] = nova_cms_empty_image();
                        }
                    }

                    $artists[] = [
                        'id' => $artist->id,
                        'slug' => $artist->slug,
                        'name' => $artist->name,
                        'portfolioImage' => [
                            'imgUrls' => $imgUrls,
                            'ratio' => $actualImage ? nova_cms_ratio($actualImage) : 1,
                        ],
                    ];
                }

                $result = [
                    'id' => $discipline->id,
                    'title' => $discipline->title,
                    'slug' => $discipline->slug,
                    'artists' => collect($artists)->sortBy('name')->values()->all(),
                ];

                $results[] = $result;
            }

            return $results;
        });
    }
}
