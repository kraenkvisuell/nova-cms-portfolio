<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use KraenkVisuell\NovaSortable\Traits\HasSortableManyToManyRows;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class ArtistCategory extends Resource
{
    use HasSortableManyToManyRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Category::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.category_sortable_dropdown') ?: false;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::categories.categories'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::categories.category'));
    }

    public function authorizedToDetach(NovaRequest $request, $model, $relationship)
    {
        return false;
    }

    public function fields(Request $request)
    {
        $fields = [
            Text::make(__('nova-cms-portfolio::portfolio.title'), 'title')
                ->translatable(),

        ];

        return $fields;
    }
}
