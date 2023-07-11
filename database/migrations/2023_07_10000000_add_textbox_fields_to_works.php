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

        Schema::table($prefix.'works', function (Blueprint $table) {
            $table->boolean('is_textbox')->default(false)->index();
            $table->json('textbox_text')->nullable();
            $table->string('bgcolor', 50)->nullable();
            $table->string('textbox_order', 50)->default('image_text');
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

        Schema::table($prefix.'works', function (Blueprint $table) {
            $table->dropColumn('is_textbox');
            $table->dropColumn('textbox_text');
            $table->dropColumn('bgcolor');
            $table->dropColumn('textbox_order');
        });
    }
};
