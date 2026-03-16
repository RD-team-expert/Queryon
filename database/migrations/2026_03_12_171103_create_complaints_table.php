<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {

            $table->id();
            // Cognito entry number
            $table->unsignedInteger('external_entry_number')->unique();

            // Complaint fields
            $table->text('issue')->nullable();
            $table->text('suggestion')->nullable();

            // User information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->date('complaint_date')->nullable();

            $table->string('manager_informed')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
