<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::create($prefix.'artist_category', function (Blueprint $table) {
            $table->id('doid');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedInteger('sort_order')->nullable();
            $table->index(['artist_id', 'category_id']);
        });
    }

    public function down()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::dropIfExists($prefix.'artist_category');
    }
};
