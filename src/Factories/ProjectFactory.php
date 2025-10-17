<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Kraenkvisuell\NovaCmsPortfolio\Models\Project;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        $locale = app()->getLocale();
        $title = $this->faker->word;
        $slug = Str::slug($title);

        return [
            'name' => $title,
            'slug' => $slug,
            'meta_description' => [$locale => $this->faker->sentence],
            'browser_title' => [$locale => $this->faker->words],
            'robots' => [
                'index' => true,
                'follow' => true,
            ],
        ];
    }
}
