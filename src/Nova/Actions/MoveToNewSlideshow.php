<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;

class MoveToNewSlideshow extends Action
{
    public function name()
    {
        return ucfirst(__(
            'nova-cms-portfolio::works.move_to_new_slideshow',
            [
                'attribute' => config('nova-cms-portfolio.custom_slideshow_label')
                               ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshow')),
            ]
        ));
    }

    public function handle(ActionFields $fields, Collection $works)
    {
        $oldSlideshow = Slideshow::find($works->first()->slideshow_id);

        $newSlideshow = Slideshow::create([
            'artist_id' => $oldSlideshow->artist_id,
            'title' => $fields->title,
            'slug' => $fields->slug,
            'is_published' => $fields->is_published,
            'is_visible_in_overview' => $fields->is_visible_in_overview,
        ]);

        foreach ($works as $work) {
            $work->slideshow_id = $newSlideshow->id;
            $work->sort_order += 1000;
            $work->save();
        }

        $newSlideshow->categories()->attach($fields->category_id);

        $oldSlideshow->refreshWorksOrder();
        $newSlideshow->refreshWorksOrder();

        if ($fields->afterwards == 'go_to_other_slideshow') {
            return Action::push('/resources/slideshows/'.$newSlideshow->id);
        }
    }

    public function fields()
    {
        $currentSlideshow = Slideshow::where('id', request()->input('viaResourceId'))
            ->with([
                'categories',
            ])
            ->first();

        $categoryOptions = Category::all()->sortBy('title')->pluck('title', 'id');

        $visibleInArtistOverviewLabel = config('nova-cms-portfolio.custom_visible_in_artist_overview_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.visible_in_artist_overview'));

        return [
            Text::make(__('nova-cms::pages.title'), 'title')
                ->required()
                ->rules('required'),

            Slug::make(__('nova-cms::pages.slug'), 'slug')->from('title')
                ->rules('required'),

            Select::make(
                    __('nova-cms-portfolio::categories.category'),
                    'category_id'
                )
                ->required()
                ->rules('required')
                ->options($categoryOptions)
                ->default($currentSlideshow->categories->first()->id),

            Boolean::make(ucfirst(__('nova-cms-portfolio::portfolio.published')), 'is_published')
                ->default(true),

            Boolean::make($visibleInArtistOverviewLabel, 'is_visible_in_overview')
                ->default(true),

            Select::make(
                    ucfirst(__('nova-cms-portfolio::portfolio.afterwards')),
                    'afterwards'
                )
                ->required()
                ->rules('required')
                ->options([
                    'go_to_other_slideshow' => __(
                        'nova-cms-portfolio::portfolio.go_where_moved_to'
                    ),
                    'stay_on_this_slideshow' => __(
                        'nova-cms-portfolio::portfolio.stay_here'
                    ),

                ])
                ->default('go_to_other_slideshow'),

        ];
    }

    protected function getSlideshows()
    {
        return Cache::remember(
            'nova.artist.projects.'.request()->input('viaResourceId'),
            now()->addSeconds(5),
            function () {
                $currentSlideshow = Slideshow::where('id', request()->input('viaResourceId'))
                    ->with([
                        'artist.slideshows',
                        'artist.categories',
                        'categories',
                    ])
                    ->first();

                $slideshows = [];

                foreach ($currentSlideshow->artist->categories as $category) {
                    foreach ($currentSlideshow->artist->slideshows as $slideshow) {
                        if (
                            $currentSlideshow->id != $slideshow->id
                            && $slideshow->categories->firstWhere('id', $category->id)
                        ) {
                            $slideshows[$slideshow->id] = $category->title.': '.$slideshow->title;
                        }
                    }
                }

                return $slideshows;
            }
        );
    }
}
