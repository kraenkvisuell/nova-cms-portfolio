<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Observers;

use Kraenkvisuell\NovaCmsPortfolio\Models\CategorySlideshow;

class CategorySlideshowObserver
{
    public function created(CategorySlideshow $categorySlideshow)
    {
        $categorySlideshow->moveToStart();
    }
}
