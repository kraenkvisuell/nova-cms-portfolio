<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Kraenkvisuell\NovaCms\Facades\RoutesHelper;

if (
    !Request::is(substr(config('nova.path'), 1) . '*')
    && !Request::is('nova-api/*')
    && !Request::is('api/*')
    && !Request::is('draft/*')
) {
    
    // Route::get(RoutesHelper::prefix().'/{locale}/{page}', 'Kraenkvisuell\NovaCms\Controllers\PagesController@show')->name('nova-page-multi');
    // Route::get(RoutesHelper::prefix().'/', 'Kraenkvisuell\NovaCms\Controllers\PagesController@show')->name('nova-page-single-home');
    // Route::get(RoutesHelper::prefix().'/{page}', 'Kraenkvisuell\NovaCms\Controllers\PagesController@show')->name('nova-page-single');
}
