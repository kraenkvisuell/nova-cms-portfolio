<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;

class Work extends Model implements Sortable
{
    use SortableTrait;
    use HasTranslations;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
    ];

    protected $casts = [
        'represents_artist_in_discipline_category' => 'array',
    ];
    
    protected $guarded = [];

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'works';
    }

    public $translatable = [
        'title',
        'slug',
    ];

    public function slideshow()
    {
        return $this->belongsTo(Slideshow::class);
    }

    public function buildSortQuery()
    {
        return static::query()->where('slideshow_id', $this->slideshow_id);
    }
}
