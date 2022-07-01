<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsMedia\MediaLibrary;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistDisciplineImage;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleArtistPortfolioImage;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleRepresentsArtistInCategory;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleShowInOverview;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Actions\ToggleShowInOverviewCategory;
use KraenkVisuell\NovaSortable\Traits\HasSortableRows;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Spatie\TagsField\Tags;

class Work extends Resource
{
    use HasSortableRows;

    public static $model = \Kraenkvisuell\NovaCmsPortfolio\Models\Work::class;

    public static $title = 'title';

    public static $sortable = false;

    public static $searchable = false;

    public static $displayInNavigation = false;

    public static $perPageViaRelationship = 1000;

    public static $orderBy = [
        'sort_order' => 'asc',
    ];

    public static function sortableHasDropdown()
    {
        return config('nova-cms-portfolio.works_sortable_dropdown') ?: false;
    }

    public static function label()
    {
        return config('nova-cms-portfolio.custom_works_label') ?: __('nova-cms-portfolio::works.works');
    }

    public static function singularLabel()
    {
        return config('nova-cms-portfolio.custom_work_label') ?: __('nova-cms-portfolio::works.work');
    }

    public function fields(Request $request)
    {
        $uploadOnly = config('nova-cms-portfolio.media.upload_only') ?: false;

        $showInOverviewLabel = config('nova-cms-portfolio.custom_show_in_overview_label')
            ?: __('In Künstler-Übersicht zeigen, wenn ALLE KATEGORIEN ausgewählt ist');

        $isArtistPortfolioImageLabel = config('nova-cms-portfolio.custom_is_artist_portfolio_image_label')
            ?: __('Ist Künstler-Portfolio-Bild');

        $overviewCategoriesLabel = config('nova-cms-portfolio.custom_overview_categories_label')
            ?: __('nova-cms-portfolio::works.overview_categories');

        $fields = [];

        $fields[] = MediaLibrary::make(__('nova-cms::content_blocks.file'), 'file')
            ->uploadOnly($uploadOnly)
            ->onlyOnForms();

        $fields[] = Line::make('', function () {
            $html = '<a 
                href="'.nova_cms_file($this->file).'"
                download
            >';

            if (nova_cms_mime($this->file) == 'video') {
                $html .= '<video
                        autoplay muted loop playsinline
                        class="w-auto h-12 mr-1 inline-block"
                    >
                        <source src="'.nova_cms_file($this->file).'" type="video/'.nova_cms_extension($this->file).'">
                    </video>';
            } else {
                $html .= '<img 
                        class="w-auto h-12 mr-1 inline-block"
                        src="'.nova_cms_image($this->file, 'thumb').'" 
                    />';
            }

            $html .= '</a>';

            return $html;
        })->asHtml();

        $fields[] = Stack::make('Details', [
            Line::make('', function () {
                return '
                <div class="text-xs whitespace-normal">'.($this->title ?: '-').'</div>
                <div class="text-xs whitespace-normal">'.$this->media_file?->original_name.'</div>
                ';
            })->asHtml(),
        ]);

        $settingsStack = [];
        $settingsStack[] = Line::make('', function () use ($showInOverviewLabel) {
            if ($this->show_in_overview) {
                return '<span class="text-xs font-bold uppercase">'
                .$showInOverviewLabel
                .'</span>';
            }

            return '';
        })->asHtml();

        $settingsStack[] = Line::make('', function () use ($isArtistPortfolioImageLabel) {
            if ($this->is_artist_portfolio_image) {
                return '<span class="text-xs font-bold uppercase">'
                .$isArtistPortfolioImageLabel
                .'</span>';
            }

            return '';
        })->asHtml();

        if (config('nova-cms-portfolio.has_show_in_overview_category')) {
            $settingsStack[] = Line::make('', function () use ($overviewCategoriesLabel) {
                if (is_array($this->overviewCategoryTitles()) && $this->overviewCategoryTitles()) {
                    return '<span class="text-xs uppercase">'
                    .$overviewCategoriesLabel
                    .': <span class="font-bold">'
                    .implode(', ', $this->overviewCategoryTitles())
                    .'</span></span>';
                }

                return '';
            })->asHtml();
        }

        if (config('nova-cms-portfolio.has_represents_artist_in_discipline_category')) {
            $settingsStack[] = Line::make('', function () {
                if (is_array($this->representationCategorySlugs()) && $this->representationCategorySlugs()) {
                    return '<span class="text-xs uppercase">'
                    .__('In allgemeiner Kategorie-Übersicht zeigen')
                    .': <span class="font-bold">'
                    .implode(', ', $this->representationCategorySlugs())
                    .'</span></span>';
                }

                return '';
            })->asHtml();
        }

        $settingsStack[] = Line::make('', function () {
            if ($this->is_artist_discipline_image) {
                return '<span class="text-xs font-bold uppercase">'
                .__('nova-cms-portfolio::works.is_artist_discipline_image')
                .'</span>';
            }

            return '';
        })->asHtml();

        $fields[] = Stack::make('Settings', $settingsStack);

        $fields[] = Textarea::make(__('nova-cms-portfolio::works.embed_code'), 'embed_code')
            ->onlyOnForms();

        $fields[] = Text::make(__('nova-cms-portfolio::works.embed_url'), 'embed_url')
            ->onlyOnForms();

        $fields[] = Text::make(__('nova-cms-portfolio::works.embed_code_ratio'), 'embed_code_ratio')
            ->onlyOnForms();

        $fields[] = Text::make(__('nova-cms::pages.title'), 'title')
            ->translatable()
            ->nullable()
            ->onlyOnForms();

        $fields[] = Textarea::make(__('nova-cms-portfolio::portfolio.description'), 'description')
            ->translatable()
            ->nullable()
            ->onlyOnForms();

        $fields[] = Tags::make('Tags')
            ->onlyOnForms();

        $fields[] = Boolean::make($showInOverviewLabel, 'show_in_overview')
            ->onlyOnForms();

        $fields[] = BooleanGroup::make(
                __('In Künstler-Übersicht zeigen, wenn eine einzelne Kategorie ausgewählt ist'),
                'show_in_overview_category'
            )
            ->options(function () use ($request) {
                $slideshow = $this->slideshow ?: Slideshow::find($request->viaResourceId);
                $options = [];

                foreach (optional($slideshow)->categories ?: [] as $category) {
                    $options[$category->id] = $category->title;
                }

                return $options;
            })
            ->onlyOnForms();

        $fields[] = Boolean::make(__('nova-cms-portfolio::works.is_artist_portfolio_image'), 'is_artist_portfolio_image')
            ->onlyOnForms();

        $fields[] = Boolean::make(__('nova-cms-portfolio::works.is_artist_discipline_image'), 'is_artist_discipline_image')
            ->onlyOnForms();

        $fields[] = Select::make(__('nova-cms-portfolio::works.width_in_overview'), 'width_in_overview')
            ->options([
                'regular' => __('nova-cms-portfolio::width_in_overview.regular'),
                'double' => __('nova-cms-portfolio::width_in_overview.double'),
            ])
            ->onlyOnForms()
            ->default('regular')
            ->required();

        $fields[] = Select::make(__('nova-cms-portfolio::works.width_in_frame'), 'width_in_frame')
            ->options([
                'full' => __('nova-cms-portfolio::width_in_frame.full'),
                'two_thirds' => __('nova-cms-portfolio::width_in_frame.two_thirds'),
                'half' => __('nova-cms-portfolio::width_in_frame.half'),
            ])
            ->onlyOnForms()
            ->default('full')
            ->required();

        $fields[] = BooleanGroup::make(
                __('In allgemeiner Kategorie-Übersicht zeigen'),
                'represents_artist_in_discipline_category'
            )
            ->options(function () use ($request) {
                $slideshow = $this->slideshow ?: Slideshow::find($request->viaResourceId);

                $disciplineId = optional(optional($slideshow)->getDiscipline())->id;

                $options = [];

                foreach (optional($slideshow)->categories ?: [] as $category) {
                    $options[$disciplineId.'_'.$category->id] = $category->title;
                }

                return $options;
            })
            ->onlyOnForms();

        return $fields;
    }

    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/slideshows/'.$resource->slideshow_id;
    }

    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/slideshows/'.$resource->slideshow_id;
    }

    public function actions(Request $request)
    {
        $actions = [
            ToggleArtistPortfolioImage::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            ToggleShowInOverview::make()
                ->onlyOnTableRow()
                ->withoutConfirmation(),

            // ToggleArtistDisciplineImage::make()
            //     ->onlyOnTableRow()
            //     ->withoutConfirmation(),
        ];

        if ($request->viaResourceId) {
            $categoryIds = Cache::remember('novaSlideshowCategoryIds.'.$request->viaResourceId, now()->addSeconds(15), function () use ($request) {
                return Slideshow::find($request->viaResourceId)->categories->pluck('id');
            });
            session(['lastNovaSlideshowCategoryIds' => $categoryIds]);
        }

        if (config('nova-cms-portfolio.has_show_in_overview_category')) {
            foreach (session('lastNovaSlideshowCategoryIds') ?: [] as $categoryId) {
                $actions[] = ToggleShowInOverviewCategory::make($categoryId)
                ->onlyOnTableRow()
                ->withoutConfirmation();
            }
        }

        if (config('nova-cms-portfolio.has_represents_artist_in_discipline_category')) {
            foreach (session('lastNovaSlideshowCategoryIds') ?: [] as $categoryId) {
                $actions[] = ToggleRepresentsArtistInCategory::make($categoryId)
                ->onlyOnTableRow()
                ->withoutConfirmation();
            }
        }

        return $actions;
    }
}
