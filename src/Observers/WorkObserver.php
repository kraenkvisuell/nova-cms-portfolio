<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Kraenkvisuell\NovaCmsPortfolio\Models\Work;

class WorkObserver
{
    public function created(Work $work)
    {
        $this->ensureOnlyOneArtistPortfolioImage($work);
    }

    public function updated(Work $work)
    {
        $this->ensureOnlyOneArtistPortfolioImage($work);
        $this->ensureOnlyOneArtistDisciplineImage($work);
    }

    protected function ensureOnlyOneArtistPortfolioImage($work)
    {
        if ($work->is_artist_portfolio_image) {
            Work::withoutEvents(function () use ($work) {
                Work::where('id', '!=', $work->id)
                    ->whereHas('slideshow', function ($q) use ($work) {
                        $q->where('artist_id', $work->slideshow->artist_id);
                    })
                    ->where('is_artist_portfolio_image', true)
                    ->update(['is_artist_portfolio_image' => false]);
            });
        }
    }

    protected function ensureOnlyOneArtistDisciplineImage($work)
    {
        if ($work->is_artist_discipline_image) {
            Work::withoutEvents(function () use ($work) {
                Work::where('id', '!=', $work->id)
                    ->whereHas('slideshow', function ($q) use ($work) {
                        $q->where('artist_id', $work->slideshow->artist_id);
                    })
                    ->where('is_artist_discipline_image', true)
                    ->update(['is_artist_discipline_image' => false]);
            });
        }
    }
}
