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
        Schema::create('new_hire_exception_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_hire_exception_id')->constrained('new_hire_exceptions')->cascadeOnDelete();

            $table->string('name_full')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('shifts_worked')->nullable();
            $table->decimal('hours_worked', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->decimal('hours_exception', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_hire_exception_items');
    }
};
