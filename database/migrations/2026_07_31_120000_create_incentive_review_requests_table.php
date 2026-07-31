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
        Schema::create('incentive_review_requests', function (Blueprint $table) {
            $table->id();

            // Cognito Entry Identity (used to match update/delete webhooks)
            $table->string('cognito_id')->unique()->comment('Unique Cognito entry ID (e.g., 1448-2)');
            $table->integer('entry_number')->nullable()->comment('Cognito entry number');

            // Store Manager Section
            $table->string('store_manager_first_name')->nullable();
            $table->string('store_manager_last_name')->nullable();
            $table->date('todays_date')->nullable();
            $table->string('shift')->nullable();
            $table->string('store_label')->nullable()->comment('Store name/label');
            $table->text('issue_details')->nullable()->comment('Details about the incentive review issue');
            $table->json('review_aspects')->nullable()->comment('Selected aspects of the incentive review being addressed');
            $table->date('week_start_date')->nullable()->comment('Start date of the week (Tuesday)');
            $table->date('week_end_date')->nullable()->comment('End date of the week (Monday)');

            // Management Section
            $table->string('manager_first_name')->nullable();
            $table->string('manager_last_name')->nullable();
            $table->string('manager_approval')->nullable()->comment('e.g., Approve, Reject');
            $table->text('decision_notes')->nullable()->comment('FinalDecision if approved, RejectionReason if rejected');

            $table->timestamps();

            // Indexes for common queries
            $table->index('entry_number');
            $table->index('store_label');
            $table->index('week_start_date');
            $table->index('manager_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_review_requests');
    }
};
