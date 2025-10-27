<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Eminiarts\Tabs\Tabs;
use Manogi\Tiptap\Tiptap;
use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Eminiarts\Tabs\TabsOnEdit;
use Kraenkvisuell\NovaCms\Tabs\Seo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCms\Facades\ContentBlock;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Artist;
use Kraenkvisuell\BelongsToManyField\BelongsToManyField;

class Project extends Resource
{
    use TabsOnEdit;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Project::class;

    public static $searchable = false;

    public function title()
    {
        return $this->resource->title;
    }

    public static function label()
    {
        return ucfirst(__('nova-cms-portfolio::projects.projects'));
    }

    public static function singularLabel()
    {
        return ucfirst(__('nova-cms-portfolio::projects.project'));
    }


    public function fields(Request $request)
    {
        $uploadOnly = config('nova-cms-news.media.upload_only') ?: false;

        $tabs = [];

        $tabs[__('nova-cms::pages.content')] = [
            MediaLibrary::make(__('nova-cms-news::news_items.overview_image'), 'overview_image')
                ->uploadOnly($uploadOnly),

            Text::make(__('nova-cms::pages.title'), 'title')
                ->required()
                ->rules('required')
                ->translatable(),

            Text::make(__('nova-cms::pages.slug'), 'slug')
                ->required()
                ->rules('required')
                ->translatable()
                ->onlyOnForms(),

            Text::make(__('nova-cms-portfolio::projects.industry'), 'industry')
                ->translatable(),

            Text::make(__('nova-cms-portfolio::projects.format'), 'format')
                ->translatable(),

            Text::make(__('nova-cms-portfolio::skills.skills'), 'skills')
                ->translatable(),

            BelongsToManyField::make(ucfirst(__('nova-cms-portfolio::artists.artists')), 'artists', Artist::class)
                ->optionsLabel('name')
                ->required()
                ->rules('required')
                ->hideFromDetail(),

            Tiptap::make(__('nova-cms-news::news_items.abstract'), 'abstract')
                ->translatable(),

            ContentBlock::field(),
        ];

        $tabs[__('nova-cms::seo.seo')] = Seo::make();

        $fields = [
            (new Tabs(static::singularLabel(), $tabs))->withToolbar(),
        ];

        return $fields;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/projects';
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/projects';
    }
}
