<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_schedule_summary', function (Blueprint $table) {
            $table->id();
            $table->string('store', 50)->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('name', 50)->nullable();
            $table->bigInteger('emp_id')->nullable();
            $table->integer('x')->nullable();
            $table->integer('oje')->nullable();
            $table->integer('off_both_we')->nullable();
            $table->integer('status_not_filled')->nullable();
            $table->integer('hours_not_given')->nullable();
            $table->integer('dh_not_scheduled')->nullable();
            $table->integer('headcount')->nullable();
            $table->integer('weekend_not_filling_status')->nullable();
            $table->decimal('weekly_hours', 12, 2)->nullable();
            $table->decimal('ot_calc', 12, 2)->nullable();
            $table->integer('both_weekends')->nullable();
            $table->integer('px')->nullable();
            $table->integer('t')->nullable();
            $table->integer('re')->nullable();
            $table->integer('vci87')->nullable();
            $table->integer('excused_absence')->nullable();
            $table->integer('unexcused_absence')->nullable();
            $table->integer('late')->nullable();
            $table->decimal('tenure_in_months', 12, 2)->nullable();
            $table->decimal('hourly_base_pay_alt', 12, 2)->nullable();
            $table->decimal('hourly_performance_pay_alt', 12, 2)->nullable();
            $table->decimal('totally_pay_alt', 12, 2)->nullable();
            $table->string('position_alt', 50)->nullable();
            $table->string('is_1099_alt', 50)->nullable();
            $table->decimal('total_pay', 12, 2)->nullable();
            $table->timestamps();

            $table->index(['emp_id', 'schedule_date']);
            $table->unique(['store','emp_id', 'schedule_date'], 'weekly_summary_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_schedule_summary');
    }
};
