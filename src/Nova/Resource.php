<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Nova;

use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    protected static function applyOrderings($query, array $orderings)
    {
        if (empty($orderings) && property_exists(static::class, 'orderBy')) {
            $orderings = static::$orderBy;
        }

        if (empty($orderings) && method_exists(static::class, 'orderBy')) {
            $orderings = static::orderBy();
        }

        return parent::applyOrderings($query, $orderings);
    }
}
