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
    }

    protected function ensureOnlyOneArtistPortfolioImage($work)
    {
        if ($work->is_artist_portfolio_image) {
            Work::withoutEvents(function () use ($work) {
                Work::where('id', '!=', $work->id)
                    ->where('is_artist_portfolio_image', true)
                    ->update(['is_artist_portfolio_image' => false]);
            });
        }
    }
}
