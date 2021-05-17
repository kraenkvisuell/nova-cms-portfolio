<?php

use Illuminate\Support\Facades\Route;
use Kraenkvisuell\NovaCmsPortfolio\Controllers\WorksController;
use Kraenkvisuell\NovaCmsPortfolio\Controllers\SlideshowsController;

Route::post('/works/create-from-file/{slideshow}/{fileId}', WorksController::class . '@createFromFile');
Route::post('/slideshows/reorder-works/{slideshow}', SlideshowsController::class . '@reorderWorks');
