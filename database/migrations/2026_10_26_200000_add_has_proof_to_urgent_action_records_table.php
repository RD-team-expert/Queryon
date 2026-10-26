<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urgent_action_records', function (Blueprint $table) {
            // Cognito sends "Yes"/"No"; kept as text so the value lands in the report as-is.
            // Nullable because records submitted before the field existed have no answer.
            $table->string('has_proof')->nullable()->after('manager_last_name');
        });
    }

    public function down(): void
    {
        Schema::table('urgent_action_records', function (Blueprint $table) {
            $table->dropColumn('has_proof');
        });
    }
};
