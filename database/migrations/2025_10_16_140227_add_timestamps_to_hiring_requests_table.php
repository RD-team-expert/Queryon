<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            // Adds nullable created_at and updated_at columns
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('hiring_requests', function (Blueprint $table) {
            $table->dropTimestamps(); // Removes created_at and updated_at
        });
    }
};
