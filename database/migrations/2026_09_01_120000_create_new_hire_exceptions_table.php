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
        Schema::create('new_hire_exceptions', function (Blueprint $table) {
            $table->id();

            // Cognito Entry Identity (used to match update/delete webhooks)
            $table->string('cognito_id')->unique()->comment('Unique Cognito entry ID (e.g., 1442-111)');
            $table->integer('entry_number')->nullable()->comment('Cognito entry number, fallback match on update/delete');

            $table->string('store_manager_full_name')->nullable();
            $table->string('store_label')->nullable();
            $table->integer('week')->nullable();
            $table->timestamp('submitted_date')->nullable();
            $table->string('status')->nullable();

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
        Schema::dropIfExists('new_hire_exceptions');
    }
};
