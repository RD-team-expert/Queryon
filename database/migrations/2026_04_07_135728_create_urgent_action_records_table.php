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
        Schema::create('urgent_action_records', function (Blueprint $table) {
            $table->id();

            $table->string('external_entry_number')->unique();

            $table->string('employee_first_name');
            $table->string('employee_last_name');

            $table->date('today_date');

            $table->string('store_label');

            $table->string('action_taken');
            $table->text('why');

            $table->string('manager_first_name');
            $table->string('manager_last_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urgent_action_records');
    }
};
