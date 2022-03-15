<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use OptimistDigital\NovaSortable\Traits\HasSortableManyToManyRows;

class CategorySlideshow extends Resource
{
    use HasSortableManyToManyRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function label()
    {
        return config('nova-cms-portfolio.custom_slideshows_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshows'));
    }

    public static function singularLabel()
    {
        return config('nova-cms-portfolio.custom_slideshow_label')
            ?: ucfirst(__('nova-cms-portfolio::slideshows.slideshow'));
    }

    public function fields(Request $request)
    {
        $workLabel = __(config('nova-cms-portfolio.custom_works_label'))
                       ?: __('nova-cms-portfolio::works.works');

        $fields = [

            Stack::make($workLabel, [
                Text::make('', function () {
                    $html = '<p class="text-xs">'.$this->artist->name.' / '.$this->title.'</p>';
                    foreach ($this->works->take(config('nova-cms-portfolio.max_thumbnails') ?: 3) as $work) {
                        if (nova_cms_mime($work->file) == 'video') {
                            $html .= '<video
                                autoplay muted loop playsinline
                                class="w-auto h-12 mr-1 inline-block"
                            >
                                <source src="'.nova_cms_file($work->file).'" type="video/'.nova_cms_extension($work->file).'">
                            </video>';
                        } else {
                            $html .= '<img 
                                class="w-auto h-12 mr-1 inline-block"
                                src="'.nova_cms_image($work->file, 'thumb').'" 
                            />';
                        }
                    }

                    return $html;
                })->asHtml(),
            ])
            ->onlyOnIndex(),

            Stack::make('', [
                Line::make('', function () {
                    return '<button
                        onclick="window.open(\'/nova/resources/slideshows/'.$this->id.'\', \'_blank\');"
                        class="
                            btn btn-xs btn-primary
                        "
                        >Zur Bildstrecke</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex(),
        ];

        return $fields;
    }
}
