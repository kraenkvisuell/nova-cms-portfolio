<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleSlideshowIsPublished extends Action
{
    public function name()
    {
        return __('nova-cms-portfolio::slideshows.toggle_is_published');
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $slideshow = $models->first();
        $slideshow->update(['is_published' => ! $slideshow->is_published]);
    }

    public function fields()
    {
        return [];
    }
}
