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
            $table->decimal('hourly_pay', 8, 2)->nullable()->after('hours')->comment('Maintenance Work only');
            $table->decimal('gas', 8, 2)->nullable()->after('hourly_pay')->comment('Maintenance Work only');
            $table->decimal('invoice', 8, 2)->nullable()->after('gas')->comment('Maintenance Work only');
            $table->json('proof')->nullable()->after('invoice')->comment('Maintenance Work only: uploaded files');
            $table->decimal('money_owed', 8, 2)->nullable()->after('proof')->comment('MGMT Work only');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->dropColumn(['hourly_pay', 'gas', 'invoice', 'proof', 'money_owed']);
        });
    }
};
