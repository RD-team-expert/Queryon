<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_submissions', function (Blueprint $table) {
            $table->id();

            // Cognito entry number: Entry.Number
            $table->unsignedBigInteger('external_submission_number')->unique();

            $table->string('emp_name')->nullable();
            $table->string('store_manager_name')->nullable();
            $table->string('store')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->date('date')->nullable();               // TodaysDate
            $table->string('inventory_type')->nullable();   // InventoryType

            $table->boolean('is_accepted')->nullable();
            $table->longText('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_submissions');
    }
};
