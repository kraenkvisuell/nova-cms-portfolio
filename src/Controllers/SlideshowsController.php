<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class SlideshowsController
{
    public function reorderWorks(Slideshow $slideshow)
    {
        foreach ($slideshow->works as $position => $work) {
            $work->update(['sort_order' => $position + 1]);
        }
        

        return ['success' => true];
    }
}
