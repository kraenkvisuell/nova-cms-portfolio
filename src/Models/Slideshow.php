<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Kraenkvisuell\NovaCmsMedia\API;
use Spatie\EloquentSortable\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kraenkvisuell\NovaCmsPortfolio\Factories\SlideshowFactory;
use Kraenkvisuell\NovaCmsPortfolio\Traits\Publishable;

class Slideshow extends Model implements Sortable
{
    use HasFactory;
    use Publishable;
    use SortableTrait;
    use HasTranslations;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
    ];
    
    protected $guarded = [];

    public function getTable()
    {
        return config('nova-cms-portfolio.db_prefix').'slideshows';
    }

    protected static function newFactory()
    {
        return SlideshowFactory::new();
    }

    public $translatable = [
        'description',
        'browser_title',
        'meta_description',
    ];

    protected $casts = [
        'robots' => 'array',
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, config('nova-cms-portfolio.db_prefix').'category_slideshow');
    }

    public function getDiscipline()
    {
        if ($this->discipline) {
            return $this->discipline;
        }

        return $this->artist->disciplines->first();
    }

    public function buildSortQuery()
    {
        return static::query()->where('artist_id', $this->artist_id);
    }

    public function workFilenames()
    {
        $filenames = [];
        foreach ($this->works as $work) {
            $filenames[] = API::getOriginalName($work->file);
        }
        ray($filenames);
        return $filenames;
    }

    public function scopePresentable($builder)
    {
        return $builder->where('is_published', true)
            ->has('works');
    }

    public function scopePresentableForOverview($builder)
    {
        return $builder->where('is_published', true)
            ->whereHas('works', function ($q) {
                $q->where('show_in_overview', true);
            });
    }
}
