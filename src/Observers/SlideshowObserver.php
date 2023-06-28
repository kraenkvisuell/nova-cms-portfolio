<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class SlideshowObserver
{
    public function created(Slideshow $slideshow)
    {
        $slideshow->moveToStart();
    }

    public function saved(Slideshow $slideshow)
    {
        $artist = $slideshow->artist;
        if (!$artist && $slideshow['artist_id']) {
            $artist = Artist::find($slideshow['artist_id']);
        }

        if (
            $artist
            && is_array(json_decode(request()->get('categories')))
        ) {
            $this->syncArtistCategories(
                $artist,
                json_decode(request()->get('categories'))
            );
        }

        $slideshow->refreshWorksOrder();

        // Cache::tags('artists')->flush();
    }

    public function deleted(Slideshow $slideshow)
    {
        // Cache::tags('artists')->flush();
    }

    public function reordered(Slideshow $slideshow)
    {
        $slideshow->refreshWorksOrder();
    }

    protected function syncArtistCategories($artist, $categories)
    {
        $artist->categories()->sync([]);

        foreach ($artist->slideshowCategories() as $category) {
            $artist->categories()->syncWithoutDetaching($category->id);
        }

        foreach ($categories as $category) {
            $artist->categories()->syncWithoutDetaching($category->id);
        }
    }
}
