<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Console;

use Illuminate\Console\Command;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class FillArtistCategories extends Command
{
    public $signature = 'cms-portfolio:fill-artist-categories';

    public function handle()
    {
        $this->info('filling categories');

        $artists = Artist::with(['slideshows.categories'])->get();

        foreach ($artists as $artist) {
            $this->info('-----');
            $this->info('filling artist '.$artist->name);

            foreach ($artist->slideshowCategories() as $category) {
                $this->info('syncing '.$category->title);
                $artist->categories()->syncWithoutDetaching($category->id);
            }
        }
    }
}
