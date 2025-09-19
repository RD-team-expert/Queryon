<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_emp_info', function (Blueprint $table) {
            $table->id();
            $table->string('store', 50)->nullable();
            $table->date('schedule_date')->nullable();
            $table->date('hired_date')->nullable();
            $table->decimal('hourly_base_pay', 12, 2)->nullable();
            $table->decimal('hourly_performance_pay', 12, 2)->nullable();
            $table->decimal('totally_pay', 12, 2)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('is_1099', 50)->nullable();
            $table->string('uniform', 50)->nullable();
            $table->integer('num_of_shirts')->nullable();
            $table->bigInteger('emp_id')->nullable();
            $table->integer('formula_emp_not_getting_hours_wanted')->nullable();
            $table->string('at_only', 50)->nullable();
            $table->string('family', 50)->nullable();
            $table->string('car', 50)->nullable();
            $table->date('dob')->nullable();
            $table->string('name', 50)->nullable();
            $table->text('red_in_schedule')->nullable();
            $table->string('reads_in_schedule', 50)->nullable();
            $table->bigInteger('emp_id_alt')->nullable();
            $table->string('new_team_member', 50)->nullable();
            $table->string('da_safety_score', 50)->nullable();
            $table->integer('attendance')->nullable();
            $table->string('score', 100)->nullable();
            $table->text('notes')->nullable();
            $table->integer('num_of_days')->nullable();
            $table->string('reads_in_schedule_2', 50)->nullable();
            $table->string('cross_trained', 50)->nullable();
            $table->string('preference', 50)->nullable();
            $table->string('pt_ft', 50)->nullable();
            $table->string('name_alt', 50)->nullable();
            $table->string('rating', 50)->nullable();
            $table->decimal('maximum_hours', 12, 2)->nullable();
            $table->decimal('hours_given', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['emp_id', 'schedule_date']);
            $table->unique(['store','emp_id', 'schedule_date'], 'emp_info_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_emp_info');
    }
};
