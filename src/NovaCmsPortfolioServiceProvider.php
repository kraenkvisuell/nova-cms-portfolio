<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Work;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Skill;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Category;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Console\DummyData;
use Kraenkvisuell\NovaCmsPortfolio\Nova\SkillArtist;
use Kraenkvisuell\NovaCmsPortfolio\Nova\ArtistCategory;
use Kraenkvisuell\NovaCmsPortfolio\Nova\CategorySlideshow;
use Kraenkvisuell\NovaCmsPortfolio\Observers\WorkObserver;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work as WorkModel;
use Kraenkvisuell\NovaCmsPortfolio\Observers\ArtistObserver;
use Kraenkvisuell\NovaCmsPortfolio\Observers\CategoryObserver;
use Kraenkvisuell\NovaCmsPortfolio\Observers\SlideshowObserver;
use Kraenkvisuell\NovaCmsPortfolio\Console\FillArtistCategories;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist as ArtistModel;
use Kraenkvisuell\NovaCmsPortfolio\Observers\DisciplineObserver;
use Kraenkvisuell\NovaCmsPortfolio\Services\ProjectFolderUpload;
use Kraenkvisuell\NovaCmsPortfolio\Observers\ArtistSkillObserver;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category as CategoryModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow as SlideshowModel;
use Kraenkvisuell\NovaCmsPortfolio\Observers\CategorySlideshowObserver;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline as DisciplineModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\ArtistSkill as ArtistSkillModel;
use Kraenkvisuell\NovaCmsPortfolio\Models\CategorySlideshow as CategorySlideshowModel;

class NovaCmsPortfolioServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/nova-cms-portfolio', 'nova-cms-portfolio');

        $this->publishes([
            __DIR__.'/../resources/lang/nova-cms-portfolio' => resource_path('lang/vendor/nova-cms-portfolio'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__.'/../config/nova-cms-portfolio.php' => config_path('nova-cms-portfolio.php'),
        ]);

        $resources = [
            Work::class,
            Artist::class,
            ArtistCategory::class,
            Category::class,
            CategorySlideshow::class,
            SkillArtist::class,
            Slideshow::class,
            Discipline::class,
        ];

        if (config('nova-cms-portfolio.has_skills')) {
            $resources[] = Skill::class;
        }

        Nova::resources($resources);

        // Serve assets
        Nova::serving(function (ServingNova $event) {
            Nova::script('cards', __DIR__.'/../dist/js/cards.js');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                DummyData::class,
                FillArtistCategories::class,
            ]);
        }

        $this->app->booted(function () {
            $this->routes();
        });

        ArtistModel::observe(ArtistObserver::class);
        DisciplineModel::observe(DisciplineObserver::class);
        CategoryModel::observe(CategoryObserver::class);
        WorkModel::observe(WorkObserver::class);
        SlideshowModel::observe(SlideshowObserver::class);
        CategorySlideshowModel::observe(CategorySlideshowObserver::class);
        ArtistSkillModel::observe(ArtistSkillObserver::class);
    }

    public function register()
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang/nova-cms-portfolio', 'nova-cms-portfolio');

        $this->app->bind('project-folder-upload', function () {
            return new ProjectFolderUpload();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/nova-cms-portfolio.php',
            'nova-cms-portfolio'
        );
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
                ->prefix('nova-vendor/nova-cms-portfolio')
                ->group(__DIR__.'/../routes/api.php');
    }
}
