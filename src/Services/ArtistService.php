<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Kraenkvisuell\NovaCmsPortfolio\Collections\FilteredArtists;
use Kraenkvisuell\NovaCmsPortfolio\Objects\ArtistWithFilledCategories;

class ArtistService
{
    public static function filteredResults(
        int $disciplineId = 0,
        int $categoryId = 0,
        string $needle = '',
        int $workLimit = 8,
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
