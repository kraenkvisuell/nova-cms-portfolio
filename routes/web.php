<?php

use Illuminate\Support\Facades\Request;

if (
    ! Request::is(substr(config('nova.path'), 1).'*')
    && ! Request::is('nova-api/*')
    && ! Request::is('api/*')
    && ! Request::is('draft/*')
) {
    //
}
