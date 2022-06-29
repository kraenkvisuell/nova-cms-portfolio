<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Seeders;

use Illuminate\Database\Seeder;
use Kraenkvisuell\NovaCmsPortfolio\Models\Artist;
use Kraenkvisuell\NovaCmsPortfolio\Models\Category;
use Kraenkvisuell\NovaCmsPortfolio\Models\Discipline;
use Kraenkvisuell\NovaCmsPortfolio\Models\Slideshow;

class DummySeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Discipline::factory(2)->create();

        Artist::factory(4)
            ->has(
                Slideshow::factory(2)
                ->has(
                    Category::factory(2)
                )
            )
            ->create();

        $disciplines = Discipline::pluck('id');

        foreach (Artist::all() as $artistIndex => $artist) {
            $disciplineIndex = 0;
            if ($artistIndex > 1) {
                $disciplineIndex = 1;
            }

            $artist->disciplines()->attach($disciplines[$disciplineIndex]);
        }
    }
}
