<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;

class DisciplineObserver
{
    public function saved(Discipline $discipline)
    {
        // Cache::tags('artists')->flush();
        // Cache::tags('disciplines')->flush();
    }

    public function deleted(Discipline $discipline)
    {
        // Cache::tags('artists')->flush();
        // Cache::tags('disciplines')->flush();
    }
}
