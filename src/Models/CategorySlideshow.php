<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CategorySlideshow extends Pivot implements Sortable
{
    use SortableTrait;

    public $primaryKey = 'doid';
    public $incrementing = true;
    public $timestamps = false;

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'category_slideshow';
    }

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    public function buildSortQuery()
    {
        return static::query()
          ->where('category_id', $this->category_id);
    }
}
