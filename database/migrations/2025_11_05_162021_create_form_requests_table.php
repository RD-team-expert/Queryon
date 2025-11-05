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
        Schema::create('form_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->nullable()->constrained('languages')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onUpdate('cascade')->onDelete('set null');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('request_date')->nullable();
            $table->foreignId('request_type_id')->nullable()->constrained('request_types')->onUpdate('cascade')->onDelete('set null');
            $table->string('manager_first_name')->nullable();
            $table->string('manager_last_name')->nullable();
            $table->string('manager_title')->nullable();
            $table->longText('manager_note')->nullable();
            $table->boolean('manager_issue_is_solved')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_requests');
    }
};
