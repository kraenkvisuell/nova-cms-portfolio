<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Console;

use Illuminate\Console\Command;
use Kraenkvisuell\NovaCmsPortfolio\Seeders\DummySeeder;

class DummyData extends Command
{
    public $signature = 'cms-portfolio:dummy-data';

    public function handle()
    {
        $this->info('seeding dummy data');

        if (\Kraenkvisuell\NovaCmsPortfolio\Models\Discipline::count() == 0) {
            $this->call('db:seed', [
                'class' => DummySeeder::class,
            ]);
        } else {
            $this->warn('did not seed data because tables are not empty.');
        }
    }
}
