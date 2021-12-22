<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Laravel\Nova\Card;

class ImagesCard extends Card
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
        return 'edit-slideshow-card';
    }

    public function addMeta($slideshowId = 0)
    {
        $slideshow = Slideshow::find($slideshowId);

        $text = __(
            'nova-cms-portfolio::slideshows.edit_wildcard',
            ['slideshow' => __(config('nova-cms-portfolio.custom_slideshow_label'))
                ?: __('nova-cms-portfolio::slideshows.slideshow'),
            ]
        );

        return $this->withMeta([
            'text' => $text,
            'url' => config('nova.path').'/resources/slideshows/'
                .$slideshowId
                .'/edit?viaResource=artists&viaResourceId='
                .$slideshow->artist_id
                .'&viaRelationship=slideshows',
        ]);
    }
}
