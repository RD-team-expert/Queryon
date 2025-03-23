<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clock_in_out_data', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('AC_No')->nullable(); // AC-No.
            $table->string('Name')->nullable(); // Name
            $table->date('Date')->nullable(); // Date
            $table->time('On_duty')->nullable(); // On duty
            $table->time('Off_duty')->nullable(); // Off duty
            $table->time('Clock_In')->nullable(); // Clock In
            $table->time('Clock_Out')->nullable(); // Clock Out
            $table->string('Late')->nullable(); // Late
            $table->string('Early')->nullable(); // Early
            $table->string('Work_Time')->nullable(); // Work Time
            $table->string('Department')->nullable(); // Department
            $table->integer('Entry_Number')->nullable();
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clock_in_out_data');
    }
};