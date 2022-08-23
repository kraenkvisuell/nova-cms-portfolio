<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Category;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class ChangeCategory extends Action
{
    public function name()
    {
        return __('Change category');
    }

    public function handle(ActionFields $fields, Collection $slideshows)
    {
        $categories = collect(json_decode(request()->get('categories')))
            ->pluck('id')->all();

        foreach ($slideshows as $slideshow) {
            $slideshow->categories()->sync($categories);
            $slideshow->touch();
        }

        if ($fields->afterwards == 'go_to_other_category') {
        }
    }

    public function fields()
    {
        return [
            BelongsToManyField::make('Kategorien', 'categories', Category::class)
            ->optionsLabel('title')
            ->required()
            ->rules('required'),

            Select::make(
                    ucfirst(__('nova-cms-portfolio::portfolio.afterwards')),
                    'afterwards'
                )
                ->required()
                ->rules('required')
                ->options([
                    'go_to_other_category' => __(
                        'nova-cms-portfolio::portfolio.go_where_moved_to'
                    ),
                    'stay_on_this_category' => __(
                        'nova-cms-portfolio::portfolio.stay_here'
                    ),

                ])
                ->default('go_to_other_category'),
        ];
    }
}
