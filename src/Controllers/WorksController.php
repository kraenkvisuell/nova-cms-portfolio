<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class WorksController
{
    public function createFromFile(Slideshow $slideshow, $fileId)
    {
        $existingFilenames = $slideshow->workFilenames();
        $filename = API::getOriginalName($fileId);

        if (!in_array($filename, $existingFilenames)) {
            $slideshow->works()->create([
                'file' => $fileId,
            ]);
        }

        return ['success' => true];
    }
}
