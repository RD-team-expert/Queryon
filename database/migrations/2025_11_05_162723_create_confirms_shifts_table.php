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
        Schema::create('confirms_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flex_confirm_id')->constrained('flex_confirms')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('flex_shift_id')->constrained('flex_shifts')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('confirms_shifts');
    }
};
