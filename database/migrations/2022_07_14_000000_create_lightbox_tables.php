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

        Schema::create($prefix.'lightboxes', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->index();
            $table->timestamps();
        });

        Schema::create($prefix.'lightbox_works', function (Blueprint $table) {
            $table->id();
            $table->string('lightbox_uid')->index();
            $table->unsignedBigInteger('work_id')->index();
            $table->unsignedInteger('sort_order')->nullable();
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

        Schema::dropIfExists($prefix.'lightboxes');
        Schema::dropIfExists($prefix.'lightbox_works');
    }
};
