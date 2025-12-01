<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->integer('submission_id')->unique();
            $table->string('emp_name');
            $table->string('store_manager_name');
            $table->string('store');
            $table->string('email');
            $table->string('phone');
            $table->date('date');
            $table->string('inventory_type')->nullable();
            $table->boolean('is_accepted')->nullable();
            $table->longText('rejection_reason')->nullable();
            $table->timestamps();

            // Index for faster lookups
            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
