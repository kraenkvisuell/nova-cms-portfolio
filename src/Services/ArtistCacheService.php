<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Services;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ArtistCacheService
{
    public static function refreshCachesWhereNeeded(
        Artist $artist
    ) {
        foreach (config('nova-translatable.locales') ?: [] as $localeKey => $localeName) {
            Cache::forget('DisciplinesWithArtists.'.$localeKey);
        }

        foreach (config('nova-translatable.locales') ?: [] as $localeKey => $localeName) {
            for ($workLimit = 0; $workLimit <= 10; $workLimit++) {
                Cache::forget('ArtistWithFilledCategories.'.$artist->id.'.'.($workLimit ?: '').'.'.$localeKey);
            }
        }
    }
}
