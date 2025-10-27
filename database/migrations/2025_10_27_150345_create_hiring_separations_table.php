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
        Schema::create('hiring_separations', function (Blueprint $table) {
            $table->id();

            // Store Manager Information
            $table->string('store_manager_first_name');
            $table->string('store_manager_last_name');
            $table->string('franchisee_store');
            $table->date('date_of_request');

            // Pizza Employee Information
            $table->string('pizza_emp_first_name');
            $table->string('pizza_emp_last_name');
            $table->string('pizza_emp_paychex_id');
            $table->string('separation_type')->nullable();
            $table->date('final_w_date')->nullable();

            // Supervisor Information
            $table->string('supervisor_first_name')->nullable();
            $table->string('supervisor_last_name')->nullable();
            $table->boolean('supervisor_accepted')->default(false);

            // Hiring Specialist Information
            $table->string('hiring_specialist_first_name')->nullable();
            $table->string('hiring_specialist_last_name')->nullable();
            $table->boolean('hiring_completed_separation')->default(false);
            $table->date('hiring_date_finished')->nullable();

            // Cognito ID
            $table->integer('cognito_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hiring_separations');
    }
};
