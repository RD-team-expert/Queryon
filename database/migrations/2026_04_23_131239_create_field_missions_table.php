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
        Schema::create('field_missions', function (Blueprint $table) {
            $table->id();
            $table->string('entry_id')->unique();
            $table->enum('team', ['Maintenance', 'USA MGMT']); // USA MGMT | Maintenance
            $table->string('finance_name');
            $table->integer('payment_for_week');
            $table->string('employee_name');
            $table->integer('total_hour');
            $table->integer('hour_pay');
            $table->decimal('mony_owed', 10, 2);
            $table->decimal('total_pay', 10, 2);
            $table->decimal('total_deduction', 10, 2);
            $table->decimal('net_pay', 10, 2);

            $table->string('miles2')->nullable();
            $table->decimal('fuel', 10, 2)->nullable();

            $table->dateTime('submitted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_missions');
    }
};
