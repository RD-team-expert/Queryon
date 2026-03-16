<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('external_entry_number')->unique();

            $table->text('improvement_feedback')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->unsignedTinyInteger('valued_respected_appreciated_rating')->nullable();

            $table->unsignedTinyInteger('work_schedule_satisfaction_rating')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
