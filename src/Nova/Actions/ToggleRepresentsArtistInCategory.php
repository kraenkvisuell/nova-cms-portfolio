<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;

class ToggleRepresentsArtistInCategory extends Action
{
    protected $categoryId;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function name()
    {
        $category = Category::find($this->categoryId);
        return __('nova-cms-portfolio::works.toggle_represents_artist_in_discipline_category').': '.$category->title;
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();
        ray($work);
        $discipline = $work->slideshow->artist->disciplines->first();
        $categories = $work->represents_artist_in_discipline_category;
        $categoryId = $discipline->id.'_'.$this->categoryId;

        if (!$categories) {
            $categories = [];
        }


        if (!isset($categories[$categoryId])) {
            $categories[$categoryId] = true;
        } else {
            $categories[$categoryId] = ! $categories[$categoryId];
        }

        $work->update(['represents_artist_in_discipline_category' => $categories]);
    }

    public function fields()
    {
        return [];
    }
}