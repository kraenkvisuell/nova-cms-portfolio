<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Traits;

trait Publishable
{
    public static function scopePublished($builder)
    {
        return $builder->where('is_published', true);
    }
}
