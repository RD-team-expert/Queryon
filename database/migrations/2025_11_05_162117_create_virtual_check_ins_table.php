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
        Schema::create('virtual_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_id')->constrained('form_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('will_show_up')->nullable();
            $table->longText('unable_to_show_up_reason')->nullable();
            $table->boolean('vto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_check_ins');
    }
};
