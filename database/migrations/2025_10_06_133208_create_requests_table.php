<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hiring_requests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('store');
            $table->date('date_of_request');
            $table->integer('num_of_emp_needed');
            $table->date('desired_start_date')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('supervisors_first_name')->nullable();
            $table->string('supervisors_last_name')->nullable();
            $table->boolean('supervisors_accept')->nullable();
            $table->text('supervisors_notes')->nullable();
            $table->string('hr_first_name')->nullable();
            $table->string('hr_last_name')->nullable();
            $table->integer('hr_num_of_hires')->nullable();
            $table->integer('cognito_id');

            $table->unique('cognito_id');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hiring_requests');
    }
};
