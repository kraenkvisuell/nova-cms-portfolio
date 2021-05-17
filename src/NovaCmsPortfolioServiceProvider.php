<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Work;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Category;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Slideshow;
use Kraenkvisuell\NovaCmsPortfolio\Nova\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Console\DummyData;
use Kraenkvisuell\NovaCmsPortfolio\Observers\WorkObserver;
use Kraenkvisuell\NovaCmsPortfolio\Models\Work as WorkModel;

class NovaCmsPortfolioServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/nova-cms-portfolio', 'nova-cms-portfolio');

        $this->publishes([
            __DIR__.'/../resources/lang/nova-cms-portfolio' => resource_path('lang/vendor/nova-cms-portfolio'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->publishes([
            __DIR__ . '/../config/nova-cms-portfolio.php' => config_path('nova-cms-portfolio.php'),
        ]);

        Nova::resources([
            Work::class,
            Artist::class,
            Category::class,
            Slideshow::class,
            Discipline::class,
        ]);

        // Serve assets
        Nova::serving(function (ServingNova $event) {
            Nova::script('cards', __DIR__.'/../dist/js/cards.js');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                DummyData::class,
            ]);
        }

        $this->app->booted(function () {
            $this->routes();
        });

        WorkModel::observe(WorkObserver::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-cms-portfolio.php',
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
