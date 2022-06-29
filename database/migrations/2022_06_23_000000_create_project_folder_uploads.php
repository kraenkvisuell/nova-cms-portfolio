<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::create($prefix.'slideshow_folder_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->string('status')->default('success')->index();
            $table->string('reason')->nullable();
            $table->string('root_folder')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('slideshow')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        $prefix = config('nova-cms-portfolio.db_prefix');

        Schema::dropIfExists($prefix.'slideshow_folder_uploads');
    }
};
