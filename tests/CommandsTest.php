<?php

namespace Kraenkvisuell\NovaCms\Tests;

use Illuminate\Support\Facades\Artisan;
use Kraenkvisuell\NovaCms\Models\Page;
use Kraenkvisuell\NovaCms\NovaPagesServiceProvider;
use Orchestra\Testbench\TestCase;

class CommandsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NovaPagesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__.'/../database/migrations/2020_08_05_000000_create_pages_table.php';

        (new \CreatePagesTable)->up();
    }

    /** @test */
    public function init_creates_home_page()
    {
        $this->assertTrue(Page::where('is_home', true)->count() == 0);

        $this->artisan('nova-pages:init');

        $this->assertTrue(Page::where('is_home', true)->count() == 1);
    }

    /** @test */
    public function init_does_not_create_second_home_page_and_returns_correct_info()
    {
        $this->withoutMockingConsoleOutput();

        Page::create([
            'slug' => 'home',
            'title' => 'Home',
            'is_home' => true,
        ]);

        $this->assertTrue(Page::where('is_home', true)->count() == 1);
        $this->assertTrue(Page::count() == 1);

        $this->artisan('nova-pages:init');

        $this->assertTrue(Page::where('is_home', true)->count() == 1);
        $this->assertTrue(Page::count() == 1);
        $this->assertSame('Home page already existed.'.PHP_EOL, Artisan::output());
    }
}
