<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

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

    public function embedRatio()
    {
        $defaultRatio = (9 / 16) * 100;

        if (! $this->embed_code_ratio) {
            return $defaultRatio;
        }

        $arr = explode(':', $this->embed_code_ratio);

        if (count($arr) < 2) {
            return $defaultRatio;
        }

        $width = intval($arr[0]) ?: 16;

        $height = intval($arr[1]) ?: 9;

        return ($height / $width) * 100;
    }
}
