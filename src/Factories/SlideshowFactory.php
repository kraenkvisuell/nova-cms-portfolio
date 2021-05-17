<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class SlideshowFactory extends Factory
{
    protected $model = Slideshow::class;

    public function definition()
    {
        $locale = app()->getLocale();
        $title = ucwords($this->faker->unique()->words(3, true));
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'description' => [ $locale => $this->faker->sentences() ],
            'meta_description' => [ $locale => $this->faker->sentence ],
            'browser_title' => [ $locale => $this->faker->words ],
            'robots' => [
                'index' => true,
                'follow' => true,
            ]
        ];
    }
}
