<?php

namespace Kraenkvisuell\NovaCmsPortfolio;

use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;
use Laravel\Nova\Card;

class SlideshowArtistCard extends Card
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
        return 'slideshow-artist-card';
    }

    public function addMeta($slideshowId = 0)
    {
        $slideshow = Slideshow::find($slideshowId);

        $text = __(
            'nova-cms-portfolio::artists.back_to_wildcard',
            ['artist' => __(config('nova-cms-portfolio.custom_artist_label'))
                ?: __('nova-cms-portfolio::artists.artist'),
            ]
        );

        $url = config('nova.path').'/resources/artists/'.$slideshow?->artist_id;

        if (config('nova-cms-portfolio.has_single_category_filter')) {
            $filters = [
                [
                    'class' => 'Kraenkvisuell\\NovaCmsPortfolio\\Nova\\Filters\\CategoryFilter',
                    'value' => $slideshow->categories?->first()?->id,
                ],
            ];

            $filters = base64_encode(json_encode($filters));

            $url .= '?slideshows_page=1&slideshows_filter='.$filters;
        }

        return $this->withMeta([
            'text' => $text,
            'url' => $url,
        ]);
    }
}
