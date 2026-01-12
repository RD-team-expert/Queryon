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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            
            // Cognito Form Info
            $table->string('cognito_id')->unique()->comment('Unique Cognito form ID (e.g., 1318-97)');
            $table->string('form_id')->nullable()->comment('Form ID from Cognito');
            $table->string('form_internal_name')->nullable()->comment('Form internal name');
            $table->string('form_name')->nullable()->comment('Form display name');
            
            // Details Section
            $table->string('approval_reason')->nullable()->comment('What is the thing that you need approval for');
            $table->text('why')->nullable()->comment('Reason for the approval request');
            $table->string('requester_first_name')->nullable();
            $table->string('requester_last_name')->nullable();
            $table->date('request_date')->nullable()->comment('Todays date from form');
            $table->string('store_id')->nullable()->comment('Store ID');
            $table->string('store_label')->nullable()->comment('Store name/label');
            $table->string('consulted_manager_first_name')->nullable();
            $table->string('consulted_manager_last_name')->nullable();
            
            // The Final Decision Section
            $table->string('decision')->nullable()->comment('Approval decision');
            $table->text('decision_notes')->nullable()->comment('Notes about the decision');
            
            // Entry Metadata
            $table->integer('entry_number')->nullable()->comment('Cognito entry number');
            $table->string('entry_admin_link')->nullable();
            $table->timestamp('entry_date_created')->nullable();
            $table->timestamp('entry_date_submitted')->nullable();
            $table->timestamp('entry_date_updated')->nullable();
            $table->string('entry_public_link', 500)->nullable();
            $table->string('entry_final_view_link', 500)->nullable();
            $table->string('document_1_link', 500)->nullable();
            $table->string('document_2_link', 500)->nullable();
            
            // Entry Origin Info
            $table->string('origin_ip_address')->nullable();
            $table->string('origin_city')->nullable();
            $table->string('origin_country_code')->nullable();
            $table->string('origin_region')->nullable();
            $table->string('origin_timezone')->nullable();
            $table->string('origin_user_agent', 500)->nullable();
            $table->boolean('origin_is_imported')->default(false);
            
            // Entry User Info
            $table->string('user_email')->nullable();
            $table->string('user_name')->nullable();
            
            // Entry Status
            $table->string('entry_action')->nullable()->comment('e.g., Submit, Update');
            $table->string('entry_role')->nullable()->comment('e.g., Public, Admin');
            $table->string('entry_status')->nullable()->comment('e.g., Submitted, Approved');
            $table->integer('entry_version')->default(1);
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index('entry_number');
            $table->index('store_id');
            $table->index('request_date');
            $table->index('decision');
            $table->index('entry_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
