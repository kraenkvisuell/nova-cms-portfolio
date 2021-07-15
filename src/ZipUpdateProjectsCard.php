<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Laravel\Nova\Card;

class ZipUpdateProjectsCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'zip-update-projects-card';
    }

    public function addMeta($artistId = 0)
    {
        $text = __('nova-cms-portfolio::slideshows.zip_update_projects');

        return $this->withMeta([
            'text' => $text,
            'artistId' => $artistId,
        ]);
    }
}
