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

        Schema::table($prefix.'slideshows', function (Blueprint $table) {
            $table->boolean('is_production')->default(false);
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

        Schema::table($prefix.'slideshows', function (Blueprint $table) {
            $table->dropColumn('is_slideshow');
        });
    }
};
