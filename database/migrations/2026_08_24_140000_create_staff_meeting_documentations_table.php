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
        Schema::create('staff_meeting_documentations', function (Blueprint $table) {
            $table->id();

            // Cognito Entry Identity (used to match delete webhooks)
            $table->string('cognito_id')->unique()->comment('Unique Cognito entry ID (e.g., 1449-25)');
            $table->integer('entry_number')->nullable()->comment('Cognito entry number');

            $table->date('meeting_date')->nullable()->comment('TadaysDate');
            $table->string('store_label')->nullable()->comment('Store name/label');

            $table->text('attendance_screenshot_url')->nullable()->comment('Screenshot showing who attended');
            $table->text('reports_screenshot_url')->nullable()->comment('Screenshot showing the reports that were shared');

            $table->text('meeting_outcome')->nullable();
            $table->text('notes')->nullable();

            $table->json('general_managers')->nullable()->comment('Full names of attending general managers');
            $table->json('store_managers')->nullable()->comment('Full names of attending store managers');
            $table->json('specialists')->nullable()->comment('Full names of attending specialists');

            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();

            $table->index('entry_number');
            $table->index('store_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_meeting_documentations');
    }
};
