<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleStartpageImage extends Action
{
    public function name()
    {
        return __('AN/AUS').': '.
        (
            config('nova-cms-portfolio.custom_is_startpage_image_label')
            ?: __('Ist Startseiten-Bild')
        );
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $work->update(['is_startpage_image' => ! $work->is_startpage_image]);
    }

    public function fields()
    {
        return [];
    }
}
