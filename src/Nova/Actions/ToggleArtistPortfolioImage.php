<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;

class ToggleArtistPortfolioImage extends Action
{
    public function name()
    {
        return __('nova-cms-portfolio::works.toggle_artist_portfolio_image');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $work->update(['is_artist_portfolio_image' => !$work->is_artist_portfolio_image]);
    }

    public function fields()
    {
        return [];
    }
}
