<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;

class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition()
    {
        $locale = app()->getLocale();
        $name = $this->faker->unique()->name;
        $slug = Str::slug($name);
        return [
            'name' => $name,
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
