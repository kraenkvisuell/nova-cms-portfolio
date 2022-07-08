<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleVisibilityInOverview extends Action
{
    public function name()
    {
        return __('AN/AUS').': '
            .(config('nova-cms-portfolio.custom_visible_in_artist_overview_label')
            ?: __('nova-cms-portfolio::slideshows.toggle_visibility_in_artist_overview'));
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $slideshow = $models->first();
        $slideshow->update(['is_visible_in_overview' => ! $slideshow->is_visible_in_overview]);
    }

    public function fields()
    {
        return [];
    }
}
