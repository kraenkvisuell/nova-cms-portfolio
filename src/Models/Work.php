<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsMedia\Core\Model as MediaModel;
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
        return config('nova-cms-portfolio.db_prefix').'works';
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

    public function media_file()
    {
        return $this->belongsTo(MediaModel::class, 'file');
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

    public function fileRatio()
    {
        // try custom ratio
        $arr = explode(':', $this->custom_ratio);

        if (count($arr) == 2 && intval($arr[0]) && intval($arr[1])) {
            return intval($arr[0]) / intval($arr[1]);
        }

        // try actual file ratio
        $ratio = nova_cms_ratio($this->file);

        if ($ratio) {
            return $ratio;
        }

        // try embed code ratio
        $arr = explode(':', $this->embed_code_ratio);

        if (count($arr) == 2 && intval($arr[0]) && intval($arr[1])) {
            return intval($arr[0]) / intval($arr[1]);
        }

        return 16 / 9;
    }

    public function overviewCategorySlugs()
    {
        $slugs = [];

        if (! is_array($this->show_in_overview_category) || ! $this->show_in_overview_category) {
            return [];
        }

        foreach ($this->show_in_overview_category as $categoryId => $bool) {
            if ($bool) {
                $slugs[] = optional($this->slideshow->categories->firstWhere('id', $categoryId))->slug;
            }
        }

        return $slugs;
    }

    public function overviewCategoryTitles()
    {
        $titles = [];

        if (! is_array($this->show_in_overview_category) || ! $this->show_in_overview_category) {
            return [];
        }

        foreach ($this->show_in_overview_category as $categoryId => $bool) {
            if ($bool) {
                $titles[] = optional($this->slideshow->categories->firstWhere('id', $categoryId))->title;
            }
        }

        return $titles;
    }

    public function representationCategorySlugs()
    {
        $slugs = [];

        if (! is_array($this->represents_artist_in_discipline_category) || ! $this->represents_artist_in_discipline_category) {
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
