<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use KraenkVisuell\NovaSortable\Traits\HasSortableManyToManyRows;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class SkillArtist extends Resource
{
    use HasSortableManyToManyRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Artist::class;

    public static $title = 'name';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.artists_sortable_dropdown') ?: false;
    }

    public static function label()
    {
        return config('nova-cms-portfolio.custom_artists_label')
            ?: ucfirst(__('nova-cms-portfolio::artists.artists'));
    }

    public static function singularLabel()
    {
        return config('nova-cms-portfolio.custom_artist_label')
            ?: ucfirst(__('nova-cms-portfolio::artists.artist'));
    }

    public function fields(Request $request)
    {
        $fields = [

            Text::make(ucfirst(__('nova-cms-portfolio::artists.title')), 'name')
                ->onlyOnIndex(),


            Stack::make('', [
                Line::make('', function () {
                    $html = '<div>';

                    if ($this->skill_description && $this->skill_description != '[]') {
                        $html .= '<span class="text-green-500">Voll ausgefüllt.</span>';
                    } else {
                        $html .= '<span class="text-red-500">Nicht voll ausgefüllt (auf Website unsichtbar).</span>';
                    }

                    $html .= '</div>';
                    return $html;
                })->asHtml(),
            ])
                ->onlyOnIndex(),

            Stack::make('', [
                Line::make('', function () {
                    return '<button
                        onclick="window.open(\'/nova/resources/artists/'.$this->id.'\', \'_blank\');"
                        class="
                            btn btn-xs btn-primary
                        "
                        >Zum Künstler</button>';
                })->asHtml(),
            ])
            ->onlyOnIndex(),
        ];

        return $fields;
    }
}
