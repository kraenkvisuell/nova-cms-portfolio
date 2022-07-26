<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kraenkvisuell\NovaCmsMedia\API;
use Kraenkvisuell\NovaCmsPortfolio\Factories\SlideshowFactory;
use Kraenkvisuell\NovaCmsPortfolio\Traits\Publishable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

class Slideshow extends Model implements Sortable
{
    use HasFactory;
    use Publishable;
    use SortableTrait;
    use HasTranslations;

    public $sortable = [
        'order_column_name' => 'sort_order',
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
        return $this->hasMany(Work::class)->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, config('nova-cms-portfolio.db_prefix').'category_slideshow')
            ->withPivot(['sort_order'])
            ->using(CategorySlideshow::class);
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
            $originalName = trim(API::getOriginalName($work->file));
            if ($originalName) {
                $filenames[] = $originalName;
            }
        }

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

    public function workForNews()
    {
        $markedWork = $this->works()
            ->where('is_artist_portfolio_image', true)
            ->first();

        if (! $markedWork) {
            $markedWork = $this->works()
                ->where('show_in_overview', true)
                ->first();
        }

        if (! $markedWork) {
            $markedWork = $this->works()->first();
        }

        return $markedWork;
    }

    public function refreshWorksOrder()
    {
        $newPosition = 1;
        foreach ($this->works as $work) {
            Work::withoutEvents(function () use ($work, $newPosition) {
                $work->update(['sort_order' => $newPosition]);
            });

            $newPosition++;
        }
    }
}
