<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Kraenkvisuell\NovaCmsPortfolio\Collections\FilteredArtists;

class ArtistService
{
    public static function filteredResults(
        int $disciplineId = null,
        int $categoryId = null,
        string $needle = ''
    ) {
        return FilteredArtists::get(
            $disciplineId,
            $categoryId,
            $needle
        );
    }
}
