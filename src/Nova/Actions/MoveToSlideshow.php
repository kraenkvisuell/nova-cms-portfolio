<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova\Actions;

use Laravel\Nova\Fields\Boolean;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class MoveToSlideshow extends Action
{
    public function name()
    {
        return ucfirst(__(
            'nova-cms-portfolio::works.move_to_different_slideshow',
            [
                'attribute' => config('nova-cms-portfolio.custom_slideshow_label')
                    ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshow')),
            ]
        ));
    }

    public function handle(ActionFields $fields, Collection $works)
    {
        $oldSlideshowId = $works->first()->slideshow_id;

        foreach ($works as $work) {
            $newWork = $work->replicate();
            $newWork->slideshow_id = $fields->slideshow_id;
            $newWork->sort_order += 1000;
            $newWork->save();

            if ($fields->remove_here) {
                $work->delete();
            }
        }

        Slideshow::find($oldSlideshowId)->refreshWorksOrder();
        Slideshow::find($fields->slideshow_id)->refreshWorksOrder();

        if ($fields->afterwards == 'go_to_other_slideshow') {
            return Action::push('/resources/slideshows/' . $fields->slideshow_id);
        }
    }

    public function fields()
    {
        $slideshows = $this->getSlideshows();

        return [
            Select::make(
                (
                    config('nova-cms-portfolio.custom_slideshow_label')
                    ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshow'))
                ),
                'slideshow_id'
            )
                ->options($slideshows)
                ->required()
                ->rules('required'),

            Boolean::make(
                ucfirst(__('nova-cms-portfolio::portfolio.remove_here')),
                'remove_here'
            ),

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
            'nova.artist.projects.' . request()->input('viaResourceId'),
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

                foreach ($currentSlideshow?->artist->categories as $category) {
                    foreach ($currentSlideshow?->artist->slideshows as $slideshow) {
                        if (
                            $currentSlideshow->id != $slideshow->id
                            && $slideshow->categories->firstWhere('id', $category->id)
                        ) {
                            $slideshows[$slideshow->id] = $category->title . ': ' . $slideshow->title;
                        }
                    }
                }

                if (!count($slideshows)) {
                    foreach ($currentSlideshow?->artist->slideshows as $slideshow) {
                        if ($currentSlideshow->id != $slideshow->id) {
                            $slideshows[$slideshow->id] = $slideshow->title;
                        }
                    }
                }

                return $slideshows;
            }
        );
    }
}
