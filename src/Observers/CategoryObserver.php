<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;

class CategoryObserver
{
    public function saved(Category $category)
    {
        Cache::tags('artists')->flush();
        Cache::tags('categories')->flush();
    }

    public function deleted(Category $category)
    {
        Cache::tags('artists')->flush();
        Cache::tags('categories')->flush();
    }
}
