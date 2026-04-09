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
        Schema::create('employee_sick_hours', function (Blueprint $table) {
            $table->id();
            $table->string('external_entry_number')->unique();
            $table->string('store_label');
            $table->string('store_manager_name');
            $table->string('employee_name');
            $table->date('date');
            $table->decimal('amount_of_sick_hours', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_sick_hours');
    }
};
