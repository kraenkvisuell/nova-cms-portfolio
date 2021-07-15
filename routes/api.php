<?php

use Illuminate\Support\Facades\Route;
use Kraenkvisuell\NovaCmsPortfolio\Controllers\ProjectsController;
use Kraenkvisuell\NovaCmsPortfolio\Controllers\SlideshowsController;
use Kraenkvisuell\NovaCmsPortfolio\Controllers\WorksController;

Route::post('/works/create-from-file/{slideshow}/{fileId}', WorksController::class.'@createFromFile');
Route::post('/slideshows/reorder-works/{slideshow}', SlideshowsController::class.'@reorderWorks');
Route::post('/artists/projects-from-zip-file/{artist}', ProjectsController::class.'@createFromZipFile');
