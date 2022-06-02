<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

class Work extends Model implements Sortable
{
    use SortableTrait;
    use HasTranslations;
    use HasTags;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
    ];

    protected $casts = [
        'represents_artist_in_discipline_category' => 'array',
        'show_in_overview_category' => 'array',
    ];

    protected $guarded = [];

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix') . 'works';
    }

    public $translatable = [
        'title',
        'slug',
        'description',
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

        if (!$this->embed_code_ratio) {
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

    public function overviewCategorySlugs()
    {
        $slugs = [];

        if (!is_array($this->show_in_overview_category) || !$this->show_in_overview_category) {
            return [];
        }

        foreach ($this->show_in_overview_category as $categoryId => $bool) {
            if ($bool) {
                $slugs[] = optional($this->slideshow->categories->firstWhere('id', $categoryId))->slug;
            }
        }

        return $slugs;
    }

    public function representationCategorySlugs()
    {
        $slugs = [];

        if (!is_array($this->represents_artist_in_discipline_category) || !$this->represents_artist_in_discipline_category) {
            return [];
        }

        foreach ($this->represents_artist_in_discipline_category as $categoryId => $bool) {
            if ($bool) {
                $categoryId = Str::afterLast($categoryId, '_');
                $slugs[] = optional($this->slideshow->categories->firstWhere('id', $categoryId))->slug;
            }
        }

        return $slugs;
    }

    public function actualPosition()
    {
        foreach ($this->slideshow->works as $index => $work) {
            if ($work->id == $this->id) {
                return $index + 1;
            }
        }

        return 1;
    }
}
