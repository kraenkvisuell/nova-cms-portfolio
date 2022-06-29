<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Laravel\Nova\Card;

class CreateProjectsViaUploadCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'create-projects-via-upload-card';
    }

    public function addMeta($artistId = 0)
    {
        return $this->withMeta([
            'artistId' => $artistId,
            'headline' => __('nova-cms-portfolio::create_via_folder_upload.headline'),
            'intro' => __('nova-cms-portfolio::create_via_folder_upload.intro'),
        ]);
    }
}
