<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClockingDataTable extends Migration
{
    public function up()
    {
        Schema::create('clocking_data', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->integer('AC_No'); // Employee ID (AC-No)
            $table->string('Name'); // Employee Name
            $table->date('Date'); // Date of clock-in
            $table->time('Clock_In')->nullable(); // Time of clock-in
            $table->time('Clock_Out')->nullable(); // Time of clock-out
            $table->integer('Entry_Number'); // Entry number (you can customize this logic if necessary)
            $table->timestamps(); // Created and updated timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('clocking_data');
    }
}
