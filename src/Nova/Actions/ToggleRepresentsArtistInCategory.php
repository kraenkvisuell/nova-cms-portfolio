<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

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

        return __('AN/AUS: In allgemeiner Kategorie-Übersicht zeigen').': '.$category->title;
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $work = $models->first();

        $discipline = $work->slideshow->artist->disciplines->first();
        $categories = $work->represents_artist_in_discipline_category;
        $categoryId = $discipline->id.'_'.$this->categoryId;

        if (! $categories) {
            $categories = [];
        }

        if (! isset($categories[$categoryId])) {
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
