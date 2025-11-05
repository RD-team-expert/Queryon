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
        Schema::create('direct_deposit_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_id')->constrained('form_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->string('ssn')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_num')->nullable();
            $table->string('routing_number')->nullable();
            $table->longText('attachment_link')->nullable();
            $table->enum('account_type', ['checking', 'saving'])->nullable();
            $table->boolean('acknowledge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_deposit_updates');
    }
};
