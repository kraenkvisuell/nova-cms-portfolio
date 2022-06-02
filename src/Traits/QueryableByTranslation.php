<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Traits;

trait QueryableByTranslation
{
    public static function queryByTranslation($field, $translation)
    {
        $builder = static::where($field . '->' . app()->getLocale(), $translation);
        if (!$builder->count()) {
            $builder = static::where($field . '->' . app()->getFallbackLocale(), $translation);
        }

        return $builder;
    }
}
