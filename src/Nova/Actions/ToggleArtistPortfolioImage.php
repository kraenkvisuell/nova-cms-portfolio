<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleArtistPortfolioImage extends Action
{
    public function name()
    {
        return __('AN/AUS').': '
        .config('nova-cms-portfolio.custom_is_artist_portfolio_image_label')
            ?: __('Ist Künstler-Portfolio-Bild');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $work->update(['is_artist_portfolio_image' => ! $work->is_artist_portfolio_image]);
    }

    public function fields()
    {
        return [];
    }
}
