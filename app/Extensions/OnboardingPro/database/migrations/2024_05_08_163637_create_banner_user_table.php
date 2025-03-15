<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('banner_user')) {
            return;
        }

        Schema::create('banner_user', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('banner_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_user');
    }
};
