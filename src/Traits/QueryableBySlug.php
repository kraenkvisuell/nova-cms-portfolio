<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Traits;

trait QueryableBySlug
{
    public static function queryBySlug($slug)
    {
        $builder = static::where('slug->'.app()->getLocale(), $slug);
        if (!$builder->count()) {
            $builder = static::where('slug->'.app()->getFallbackLocale(), $slug);
        }

        return $builder;
    }
}
