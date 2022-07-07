<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Traits;

use KraenkVisuell\NovaSortable\Traits\HasSortableRows;

if (config('nova-cms-portfolio.slideshows_sortable')) {
    trait OptionalHasSortableRows
    {
        use HasSortableRows;
    }
} else {
    trait OptionalHasSortableRows
    {
    }
}
