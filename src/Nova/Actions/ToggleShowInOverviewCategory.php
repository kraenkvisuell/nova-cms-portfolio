<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;

class ToggleShowInOverviewCategory extends Action
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function name()
    {
        $category = Category::find($this->categoryId);
        return __('nova-cms-portfolio::works.toggle_show_in_overview_category').': '.$category->title;
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        $categories = $work->show_in_overview_category;

        if (!$categories) {
            $categories = [];
        }

        if (!isset($categories[$this->categoryId])) {
            $categories[$this->categoryId] = true;
        } else {
            $categories[$this->categoryId] = ! $categories[$this->categoryId];
        }

        $work->update(['show_in_overview_category' => $categories]);
    }

    public function fields()
    {
        return [];
    }
}
