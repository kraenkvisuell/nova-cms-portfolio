<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class SlideshowObserver
{
    public function created(Slideshow $slideshow)
    {
        $slideshow->moveToStart();
    }

    public function saved(Slideshow $slideshow)
    {
        if (
            $slideshow->artist
            && request()->get('categories')
            && is_array(json_decode(request()->get('categories')))
        ) {
            $this->syncArtistCategories(
                $slideshow->artist,
                json_decode(request()->get('categories'))
            );
        }
    }

    protected function syncArtistCategories($artist, $categories)
    {
        $artist->categories()->sync([]);
        foreach ($categories as $category) {
            $artist->categories()->attach($category->id);
        }
    }
}
