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

        Schema::table($prefix.'works', function (Blueprint $table) {
            $table->boolean('is_artist_discipline_image')->default(false);
            $table->dropColumn('represents_artist_in_discipline');
        });
    }
};
