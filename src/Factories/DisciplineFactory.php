<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;

class DisciplineFactory extends Factory
{
    protected $model = Discipline::class;

    public function definition()
    {
        $locale = app()->getLocale();
        $title = ucfirst($this->faker->unique()->word);
        $slug = Str::slug($title);

        return [
            'title' => [$locale => $title],
            'slug' => [$locale => $slug],
            'description' => [$locale => $this->faker->sentences()],
            'meta_description' => [$locale => $this->faker->sentence],
            'browser_title' => [$locale => $this->faker->words],
            'robots' => [
                'index' => true,
                'follow' => true,
            ],
        ];
    }
}
