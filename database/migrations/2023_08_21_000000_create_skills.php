<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::create($prefix.'skills', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->integer('sort_order')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('browser_title')->nullable();
            $table->json('robots')->nullable();
            $table->unsignedInteger('og_image')->nullable();
            $table->string('bgcolor')->nullable();
            $table->timestamps();
        });

        Schema::create($prefix.'artist_skill', function (Blueprint $table) {
            $table->id('doid');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('skill_id');
            $table->index(['artist_id', 'skill_id']);
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

        Schema::dropIfExists($prefix.'skills');
        Schema::dropIfExists($prefix.'artist_skill');
    }
};
