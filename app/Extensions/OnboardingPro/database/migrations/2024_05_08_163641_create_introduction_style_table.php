<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('introduction_style')) {
            return;
        }

        Schema::create('introduction_style', function (Blueprint $table) {
            $table->id();
            $table->string('title_size')->nullable();
            $table->string('description_size')->nullable();
            $table->string('background_color')->nullable();
            $table->string('title_color')->nullable();
            $table->string('description_color')->nullable();
            $table->string('dark_background_color')->nullable();
            $table->string('dark_title_color')->nullable();
            $table->string('dark_description_color')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('introduction_style');
    }
};
