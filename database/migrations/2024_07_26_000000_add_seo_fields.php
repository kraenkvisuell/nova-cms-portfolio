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

        Schema::table($prefix.'artists', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
        });

        Schema::table($prefix.'slideshows', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
        });

        Schema::table($prefix.'disciplines', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
        });

        Schema::table($prefix.'skills', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
        });

        Schema::table($prefix.'works', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
            $table->unsignedInteger('og_image')->nullable();
        });

        Schema::table($prefix.'categories', function (Blueprint $table) {
            $table->string('og_title')->nullable();
            $table->mediumText('og_description')->nullable();
            $table->unsignedInteger('og_image')->nullable();
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

        Schema::table($prefix.'artists', function (Blueprint $table) {
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });

        Schema::table($prefix.'slideshows', function (Blueprint $table) {
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });

        Schema::table($prefix.'skills', function (Blueprint $table) {
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });

        Schema::table($prefix.'disciplines', function (Blueprint $table) {
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });

        Schema::table($prefix.'works', function (Blueprint $table) {
            $table->dropColumn('og_image');
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });

        Schema::table($prefix.'categories', function (Blueprint $table) {
            $table->dropColumn('og_image');
            $table->dropColumn('og_title');
            $table->dropColumn('og_description');
        });
    }
};
