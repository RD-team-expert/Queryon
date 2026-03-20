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
        Schema::create('employee_transitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('external_entry_number')->unique();
            $table->string('store_manager_name')->nullable();
            $table->string('employee_full_name')->nullable();
            $table->string('from_store')->nullable();
            $table->string('to_store')->nullable();
            $table->decimal('hours', 5, 2)->nullable();
            $table->date('transition_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_transitions');
    }
};
