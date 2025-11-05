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
        Schema::create('request_time_offs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_id')->constrained('form_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('num_of_days_needed')->nullable();
            $table->longText('reason_for_days_off')->nullable();
            $table->string('is_willing_to_work_outside_availability')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_time_offs');
    }
};
