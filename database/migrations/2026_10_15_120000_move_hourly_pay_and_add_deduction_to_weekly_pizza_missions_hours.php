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
        Schema::table('weekly_pizza_missions_hours', function (Blueprint $table) {
            $table->decimal('hourly_pay', 8, 2)->nullable()->after('days_filled')->comment('HourlyPay (one rate for the whole week)');
        });

        Schema::table('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->dropColumn('hourly_pay');
            $table->decimal('deduction', 8, 2)->nullable()->after('invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->dropColumn('deduction');
            $table->decimal('hourly_pay', 8, 2)->nullable()->after('hours')->comment('Maintenance Work only');
        });

        Schema::table('weekly_pizza_missions_hours', function (Blueprint $table) {
            $table->dropColumn('hourly_pay');
        });
    }
};
