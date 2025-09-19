<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('store', 50)->nullable();
            $table->date('schedule_date')->nullable();
            $table->string('name', 50)->nullable();
            $table->bigInteger('emp_id')->nullable();

            // Tuesday fields
            $table->string('tue_vci', 2)->nullable();
            $table->time('tue_in')->nullable();
            $table->time('tue_out')->nullable();
            $table->string('tue_status', 20)->nullable();
            $table->decimal('tue_total_hrs', 12, 2)->nullable();
            $table->string('tue_op', 2)->nullable();
            $table->string('tue_m', 2)->nullable();
            $table->string('tue_l', 2)->nullable();
            $table->string('tue_c', 2)->nullable();
            $table->integer('tue_status_f')->nullable();
            $table->decimal('tue_hours_cost', 12, 2)->nullable();
            $table->decimal('tue_hours', 12, 2)->nullable();
            $table->decimal('tue_sales', 12, 2)->nullable();
            $table->integer('tue_4hrs')->nullable();

            // Wednesday fields
            $table->string('wed_vc', 2)->nullable();
            $table->time('wed_in')->nullable();
            $table->time('wed_out')->nullable();
            $table->string('wed_status', 20)->nullable();
            $table->decimal('wed_total_hrs', 12, 2)->nullable();
            $table->string('wed_op', 2)->nullable();
            $table->string('wed_m', 2)->nullable();
            $table->string('wed_l', 2)->nullable();
            $table->string('wed_c', 2)->nullable();
            $table->integer('wed_status2')->nullable();
            $table->decimal('wed_hours_cost', 12, 2)->nullable();
            $table->decimal('wed_hours', 12, 2)->nullable();
            $table->decimal('wed_sales', 12, 2)->nullable();
            $table->integer('wed_hrs')->nullable();

            // Thursday fields
            $table->string('thu_vci', 2)->nullable();
            $table->time('thu_in')->nullable();
            $table->time('thu_out')->nullable();
            $table->string('thu_status', 20)->nullable();
            $table->decimal('thu_total_hrs', 12, 2)->nullable();
            $table->string('thu_op', 2)->nullable();
            $table->string('thu_m', 2)->nullable();
            $table->string('thu_l', 2)->nullable();
            $table->string('thu_c', 2)->nullable();
            $table->integer('thu_status_formula')->nullable();
            $table->decimal('thu_hours_cost', 12, 2)->nullable();
            $table->decimal('thu_hours', 12, 2)->nullable();
            $table->decimal('thu_sales', 12, 2)->nullable();
            $table->integer('thu_4hrs')->nullable();

            // Friday fields
            $table->string('fri_vci30', 2)->nullable();
            $table->time('fri_in')->nullable();
            $table->time('fri_out')->nullable();
            $table->string('fri_status', 20)->nullable();
            $table->decimal('fri_total_hrs', 12, 2)->nullable();
            $table->string('fri_op', 2)->nullable();
            $table->string('fri_m', 2)->nullable();
            $table->string('fri_l', 2)->nullable();
            $table->string('fri_c', 2)->nullable();
            $table->integer('fri_status_formula')->nullable();
            $table->decimal('fri_hours_cost', 12, 2)->nullable();
            $table->decimal('fri_hours', 12, 2)->nullable();
            $table->decimal('fri_sales', 12, 2)->nullable();
            $table->integer('fri_4hrs')->nullable();

            // Saturday fields
            $table->string('sat_vci44', 2)->nullable();
            $table->time('sat_in')->nullable();
            $table->time('sat_out')->nullable();
            $table->string('sat_status', 20)->nullable();
            $table->decimal('sat_total_hrs', 12, 2)->nullable();
            $table->string('sat_op', 2)->nullable();
            $table->string('sat_m', 2)->nullable();
            $table->string('sat_l', 2)->nullable();
            $table->string('sat_c', 2)->nullable();
            $table->integer('sat_status_formula')->nullable();
            $table->decimal('sat_hours_cost', 12, 2)->nullable();
            $table->decimal('sat_hours', 12, 2)->nullable();
            $table->decimal('sat_sales', 12, 2)->nullable();
            $table->integer('sat_4hrs')->nullable();

            // Sunday fields
            $table->string('sun_vci', 2)->nullable();
            $table->time('sun_in')->nullable();
            $table->time('sun_out')->nullable();
            $table->string('sun_status', 20)->nullable();
            $table->decimal('sun_total_hrs', 12, 2)->nullable();
            $table->string('sun_op', 2)->nullable();
            $table->string('sun_m', 2)->nullable();
            $table->string('sun_l', 2)->nullable();
            $table->string('sun_c', 2)->nullable();
            $table->integer('sun_status_formula')->nullable();
            $table->decimal('sun_hours_cost', 12, 2)->nullable();
            $table->decimal('sun_hours', 12, 2)->nullable();
            $table->decimal('sun_sales', 12, 2)->nullable();
            $table->integer('sun_4hrs')->nullable();

            // Monday fields
            $table->string('mon_vci', 2)->nullable();
            $table->time('mon_in')->nullable();
            $table->Time('mon_out')->nullable();
            $table->string('mon_status', 20)->nullable();
            $table->decimal('mon_total_hrs', 12, 2)->nullable();
            $table->string('mon_op', 2)->nullable();
            $table->string('mon_m', 2)->nullable();
            $table->string('mon_l', 2)->nullable();
            $table->string('mon_c', 2)->nullable();
            $table->integer('mon_status_formula')->nullable();
            $table->decimal('mon_hours_cost', 12, 2)->nullable();
            $table->decimal('mon_hours', 12, 2)->nullable();
            $table->decimal('mon_sales', 12, 2)->nullable();
            $table->integer('mon_4hrs')->nullable();

            $table->timestamps();

            $table->index(['emp_id', 'schedule_date']);
            $table->unique(['store','emp_id', 'schedule_date'], 'attendance_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_schedule');
    }
};
