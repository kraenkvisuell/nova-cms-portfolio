<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Controllers;

use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class PortfolioController
{
    public function show()
    {
        //
    }

    protected function getArtistAndSetLocale($args)
    {
        $locale = count($args) > 1 ? $args[0] : app()->getLocale();
        $slug = count($args) > 1 ? $args[1] : $args[0];

        if (count($args) > 1) {
            app()->setLocale($locale);
        }

        return Artist::where('slug->' . $locale, $slug)->first();
    }
}
