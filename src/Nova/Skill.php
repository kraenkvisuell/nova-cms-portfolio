<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\TabsOnEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Laravel\Nova\Fields\BelongsToMany;
use KraenkVisuell\NovaSortable\Traits\HasSortableRows;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Manogi\Tiptap\Tiptap;
use Timothyasp\Color\Color;

class Skill extends Resource
{
    use TabsOnEdit;
    use HasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Skill::class;

    public static $sortable = false;

    public static $searchable = false;

    public function title()
    {
        return $this->resource->title;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::skills.skills'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::skills.skill'));
    }

    public static function authorizedToViewAny(Request $request)
    {
        return Auth::user()->cms_role != 'artist';
    }

    public function fields(Request $request)
    {
        $tabs = [];

        $tabs[__('nova-cms::settings.settings')] = [
            Text::make(__('nova-cms-portfolio::portfolio.title'), 'title')
                ->rules('required')
                ->translatable()
                ->onlyOnForms(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->rules('required')
                ->translatable()
                ->help(__('nova-cms-portfolio::artists.slug_explanation'))
                ->onlyOnForms(),
        ];

        $fields = [
            Stack::make('Details', [
                Line::make('', 'title')->asBase(),
                Line::make('', function () {
                    return '/'.$this->slug;
                })->asSmall(),
            ]),

            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),
        ];

        if (config('nova-cms-portfolio.has_skill_artists')) {
            $fields[] = BelongsToMany::make('Artists', 'filtered_artists', SkillArtist::class);
        }

        return $fields;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/skills';
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/skills';
    }
}
