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
        Schema::table('field_missions', function (Blueprint $table) {
            // Rename column from mony_owed to money_owed
            $table->renameColumn('mony_owed', 'money_owed');
        });

        Schema::table('field_missions', function (Blueprint $table) {
            // Make money_owed nullable
            $table->decimal('money_owed', 10, 2)->nullable()->change();

            // Add your new column here
            $table->string('invoices_amount')->nullable()->after('fuel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_missions', function (Blueprint $table) {
            // Remove the new column
            $table->dropColumn('invoices_amount');

            // Make money_owed required again
            $table->decimal('money_owed', 10, 2)->nullable(false)->change();
        });

        Schema::table('field_missions', function (Blueprint $table) {
            // Rename back
            $table->renameColumn('money_owed', 'mony_owed');
        });
    }
};
