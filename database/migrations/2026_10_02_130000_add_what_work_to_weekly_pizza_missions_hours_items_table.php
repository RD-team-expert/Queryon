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
        Schema::table('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->string('what_work')->nullable()->after('work_type')->comment('WhatWork (e.g. MGMT Work, Maintenance Work)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->dropColumn('what_work');
        });
    }
};
