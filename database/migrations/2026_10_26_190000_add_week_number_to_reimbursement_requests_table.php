<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursement_requests', function (Blueprint $table) {
            // Week# is now entered on the form instead of being derived from the expense date
            $table->unsignedTinyInteger('week_number')->nullable()->after('expense_date');

            // Expense Date was hidden on the Cognito form, so submissions now arrive without it
            $table->date('expense_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reimbursement_requests', function (Blueprint $table) {
            $table->dropColumn('week_number');

            $table->date('expense_date')->nullable(false)->change();
        });
    }
};
