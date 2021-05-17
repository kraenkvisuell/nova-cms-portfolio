<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::create($prefix.'artists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_published')->default(true)->index();
            $table->json('description')->nullable();
            $table->unsignedInteger('portfolio_image')->nullable();
            $table->unsignedInteger('portrait_image')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('browser_title')->nullable();
            $table->json('robots')->nullable();
            $table->unsignedInteger('og_image')->nullable();
            $table->string('bgcolor')->nullable();
            $table->timestamps();
        });

        Schema::create($prefix.'disciplines', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->integer('sort_order')->nullable();
            $table->unsignedBigInteger('discipline_id')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('browser_title')->nullable();
            $table->json('robots')->nullable();
            $table->unsignedInteger('og_image')->nullable();
            $table->string('bgcolor')->nullable();
            $table->timestamps();
        });

        Schema::create($prefix.'artist_discipline', function (Blueprint $table) {
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('discipline_id');
            $table->unsignedBigInteger('work_id')->nullable();
            $table->primary(['artist_id', 'discipline_id']);
        });

        Schema::create($prefix.'categories', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->timestamps();
        });

        Schema::create($prefix.'slideshows', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('artist_id')->constrained($prefix.'artists')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->boolean('is_published')->default(true)->index();
            $table->integer('sort_order')->nullable();
            $table->json('description')->nullable();
            $table->json('slides')->nullable();
            
            $table->json('meta_description')->nullable();
            $table->json('browser_title')->nullable();
            $table->json('robots')->nullable();
            $table->unsignedInteger('og_image')->nullable();
            
            $table->timestamps();
        });

        Schema::create($prefix.'category_slideshow', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('slideshow_id');
            $table->primary(['category_id', 'slideshow_id']);
        });

        Schema::create($prefix.'category_overview_work', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('work_id');
            $table->primary(['category_id', 'work_id']);
        });

        Schema::create($prefix.'works', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('slideshow_id')->constrained($prefix.'slideshows')->onUpdate('cascade')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->unsignedInteger('file')->nullable();
            $table->integer('sort_order')->nullable();
            $table->boolean('show_in_overview')->default(false)->index();
            $table->boolean('is_artist_portfolio_image')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::dropIfExists($prefix.'artists');
    }
};
