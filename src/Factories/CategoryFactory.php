<?php
namespace Kraenkvisuell\NovaCmsPortfolio\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $locale = app()->getLocale();
        $title = ucfirst($this->faker->unique()->word);
        $slug = Str::slug($title);
        return [
            'title' => [ $locale => $title ],
            'slug' => [ $locale => $slug ],
        ];
    }
}
