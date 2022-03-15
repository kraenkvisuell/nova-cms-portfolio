<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleShowInOverview extends Action
{
    public function name()
    {
        return __('AN/AUS: In Künstler-Übersicht zeigen, wenn ALLE KATEGORIEN ausgewählt ist');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $work->update(['show_in_overview' => ! $work->show_in_overview]);
    }

    public function fields()
    {
        return [];
    }
}
