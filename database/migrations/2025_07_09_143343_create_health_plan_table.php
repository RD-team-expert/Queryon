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
        Schema::create('health_plan', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('first_name')->nullable(); // VARCHAR for first name, can be null
            $table->string('last_name')->nullable(); // VARCHAR for last name, can be null
            $table->string('email')->nullable(); // VARCHAR for email, unique and can be null
            $table->string('store')->nullable(); // VARCHAR for store, can be null
            $table->string('onboarding_offboarding')->nullable(); // VARCHAR for onboarding/offboarding status, can be null
            $table->date('working_start_date')->nullable(); // DATE for working start date, can be null
            $table->date('working_end_date')->nullable(); // DATE for working end date, can be null
            $table->text('reason')->nullable(); // TEXT for reason, can be null
            $table->integer('form_id')->nullable();
            $table->timestamps(); // Adds 'created_at' and 'updated_at' DATETIME columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_plan');
    }
};
