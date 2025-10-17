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

        Schema::create($prefix . 'projects', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->unsignedInteger('overview_image')->nullable();
            $table->json('abstract')->nullable();
            $table->json('industry')->nullable();
            $table->json('format')->nullable();
            $table->json('main_content')->nullable();
            $table->json('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->json('browser_title')->nullable();
            $table->json('robots')->nullable();
            $table->json('og_title')->nullable();
            $table->json('og_description')->nullable();
            $table->unsignedInteger('og_image')->nullable();
            $table->string('bgcolor')->nullable();
            $table->timestamps();
        });

        Schema::create($prefix . 'project_skill', function (Blueprint $table) {
            $table->id('doid');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('skill_id');
            $table->index(['project_id', 'skill_id']);
        });

        Schema::create($prefix . 'artist_project', function (Blueprint $table) {
            $table->id('doid');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('project_id');
            $table->index(['artist_id', 'project_id']);
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

        Schema::dropIfExists($prefix . 'skills');
        Schema::dropIfExists($prefix . 'artist_skill');
    }
};
