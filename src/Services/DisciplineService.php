<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Kraenkvisuell\NovaCmsPortfolio\Collections\DisciplinesWithArtists;

class DisciplineService
{
    public static function listWithArtists()
    {
        return DisciplinesWithArtists::get();
    }
}
