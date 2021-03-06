<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use KraenkVisuell\NovaSortable\Traits\HasSortableManyToManyRows;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class CategorySlideshow extends Resource
{
    use HasSortableManyToManyRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.slideshows_sortable_dropdown') ?: false;
    }

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
                Text::make('', function () use ($request) {
                    $html = '<p class="text-xs">'.$this->artist->name.' / '.$this->title.'</p>';
                    foreach (
                        $this->works->filter(function ($work) use ($request) {
                            return @$work->represents_artist_in_discipline_category['1_'.$request->viaResourceId];
                        })
                        as $work
                    ) {
                        $html .= '<a 
                            href="'.nova_cms_file($work->file).'"
                            download
                        >';

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

                        $html .= '</a>';
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
