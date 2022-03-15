<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleArtistDisciplineImage extends Action
{
    public function name()
    {
        return __('nova-cms-portfolio::works.toggle_represents_in_discipline');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $work->update(['is_artist_discipline_image' => ! $work->is_artist_discipline_image]);
    }

    public function fields()
    {
        return [];
    }
}
