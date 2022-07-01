<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Objects;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ArtistWithFilledCategories
{
    public static function find(int $id, int $workLimit)
    {
        Cache::forget('ArtistWithFilledCategories.'.$id.'.'.$workLimit.'.'.app()->getLocale());

        return Cache::remember(
            'ArtistWithFilledCategories.'.$id.'.'.$workLimit.'.'.app()->getLocale(),
            now()->addDays(7),
            function () use ($id, $workLimit) {
                $artist = Artist::where('id', $id)
                ->with([
                    'disciplines',
                    'slideshows' => function ($b) {
                        $b->has('works')
                            ->where('is_published', true)
                            ->where('is_visible_in_overview', true)
                            ->with([
                                'works',
                                'categories' => function ($b) {
                                    $b->select(['id', 'title', 'slug']);
                                },
                            ]);
                    },
                    'slideshows.categories' => function ($b) {
                        $b->select(['id', 'title', 'slug']);
                    },
                ])
                ->first();

                if (! $artist) {
                    return null;
                }

                $disciplines = [];

                foreach ($artist->disciplines as $discipline) {
                    $disciplines[] = [
                        'id' => $discipline->id,
                        'slug' => $discipline->slug,
                        'title' => $discipline->title,
                    ];
                }

                $categories = [];

                foreach ($artist->categories() as $category) {
                    $slideshows = $artist
                        ->slideshows
                        ->filter(function ($slideshow) use ($category) {
                            return $slideshow->categories->where('id', $category->id)
                                ->count();
                        });

                    $categorySlideshows = [];

                    foreach ($slideshows as $slideshow) {
                        $works = $slideshow->works
                            ->where('show_in_overview', true);

                        if (! $works->count()) {
                            $works = $slideshow->works
                                ->take($workLimit);
                        }

                        $slideshowWorks = [];

                        foreach ($works as $work) {
                            $imgUrls = [];
                            foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                                $imgUrls[$sizeKey] = nova_cms_image($work->file, $sizeKey);
                            }

                            $slideshowWorks[] = [
                                'id' => $work->id,
                                'imgUrls' => $imgUrls,
                                'positionInSlideshow' => $work->actualPosition(),
                                'ratio' => nova_cms_ratio($work->file),
                            ];
                        }

                        $categorySlideshows[] = [
                            'id' => $slideshow->id,
                            'title' => $slideshow->title,
                            'slug' => $slideshow->slug,
                            'works' => $slideshowWorks,
                        ];
                    }

                    $categories[] = [
                        'id' => $category->id,
                        'slug' => $category->slug,
                        'title' => $category->title,
                        'slideshows' => $categorySlideshows,
                    ];
                }

                $socialLinks = [];

                foreach ($artist->social_links as $socialLink) {
                    $socialLinks[] = [
                        'title' => $socialLink->title,
                        'url' => @$socialLink->link_url->{app()->getLocale()},
                        'slug' => $socialLink->slug,
                        'icon' => $socialLink->link_icon,
                        'svg' => $socialLink->svg_tag,
                    ];
                }

                $portraitImage = null;

                if ($artist->portrait_image) {
                    $portraitImage = [
                        'imgUrls' => [],
                    ];

                    foreach (config('nova-cms-media.resize.sizes') ?: [] as $sizeKey => $sizeValue) {
                        $portraitImage['imgUrls'][$sizeKey] = nova_cms_image($artist->portrait_image, $sizeKey);
                    }
                }

                return [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'description' => $artist->description,
                    'email' => $artist->email,
                    'website' => $artist->website,
                    'socialLinks' => $socialLinks,
                    'portraitImage' => $portraitImage,
                    'disciplines' => $disciplines,
                    'categories' => $categories,
                ];
            }
        );
    }
}
