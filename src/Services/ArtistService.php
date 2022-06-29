<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Kraenkvisuell\NovaCmsPortfolio\Collections\FilteredArtists;
use Kraenkvisuell\NovaCmsPortfolio\Objects\ArtistWithFilledCategories;

class ArtistService
{
    public static function filteredResults(
        int $disciplineId = null,
        int $categoryId = null,
        string $needle = '',
        int $workLimit = 10,
        string $sortOrder = 'alphabetical'
    ) {
        return FilteredArtists::get(
            $disciplineId,
            $categoryId,
            $needle,
            $workLimit,
            $sortOrder
        );
    }

    public static function findWithFilledCategories(
        int $id,
        int $workLimit = 10
    ) {
        return ArtistWithFilledCategories::find($id, $workLimit);
    }
}
