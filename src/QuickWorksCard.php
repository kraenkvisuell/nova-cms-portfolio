<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Laravel\Nova\Card;

class QuickWorksCard extends Card
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
        return 'quick-works-card';
    }

    public function addMeta($slideshowId = 0)
    {
        $text = __(
            'nova-cms-portfolio::slideshows.quick_wildcard_upload',
            ['works' => __(config('nova-cms-portfolio.custom_works_label'))
                ?: __('nova-cms-portfolio::works.works'),
            ]
        );

        return $this->withMeta([
            'text' => $text,
            'slideshowId' => $slideshowId,
        ]);
    }
}
