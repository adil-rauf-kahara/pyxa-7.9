<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('survey_user')) {
            return;
        }

        Schema::create('survey_user', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('survey_id');
            $table->integer('point');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_user');
    }
};
