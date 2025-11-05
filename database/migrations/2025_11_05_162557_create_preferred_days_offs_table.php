<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('preferred_days_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('update_availability_id')->constrained('update_availabilities')->onUpdate('cascade')->onDelete('cascade');
            $table->string('day');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preferred_days_offs');
    }
};
