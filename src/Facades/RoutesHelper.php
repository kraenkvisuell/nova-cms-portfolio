<?php

namespace Kraenkvisuell\NovaCms\Facades;

use Illuminate\Support\Facades\Facade;

class RoutesHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'routes-helper';
    }
}
