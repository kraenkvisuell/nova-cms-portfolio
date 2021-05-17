<?php

namespace Kraenkvisuell\NovaCmsPortfolio\Tests;

use Kraenkvisuell\NovaCmsPortfolio\Models\Page;
use Kraenkvisuell\NovaCmsPortfolio\NovaPagesServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NovaPagesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        include_once __DIR__ . '/../database/migrations/2020_08_05_000000_create_pages_table.php';

        (new \CreatePagesTable)->up();
    }

    /** @test */
    public function it_can_save_a_page()
    {
        $page = new Page();

        $page->title = 'Foo!';
        $page->slug = 'foo';
        $page->save();

        $savedPage = Page::find($page->id);

        $this->assertSame($savedPage->title, 'Foo!');
    }

    /** @test */
    public function only_one_page_can_be_home_on_create_new()
    {
        $firstPage = Page::create([
            'title' => 'First',
            'slug' => 'first',
            'is_home' => true,
        ]);

        $this->assertTrue($firstPage->fresh()->is_home == 1);

        $secondPage = Page::create([
            'title' => 'Second',
            'slug' => 'second',
            'is_home' => true,
        ]);

        $this->assertTrue($secondPage->fresh()->is_home == 1);
        $this->assertTrue($firstPage->fresh()->is_home == 0);
    }

    /** @test */
    public function only_one_page_can_be_home_on_update()
    {
        $firstPage = Page::create([
            'title' => 'First',
            'slug' => 'first',
            'is_home' => true,
        ]);

        $this->assertTrue($firstPage->fresh()->is_home == 1);

        $secondPage = Page::create([
            'title' => 'Second',
            'slug' => 'second',
            'is_home' => false,
        ]);

        $this->assertTrue($secondPage->fresh()->is_home == 0);
        $this->assertTrue($firstPage->fresh()->is_home == 1);

        $secondPage->update(['is_home' => true]);

        $this->assertTrue($secondPage->fresh()->is_home == 1);
        $this->assertTrue($firstPage->fresh()->is_home == 0);
    }
}
