<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dependents_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications_info')->onDelete('cascade');
            $table->integer('count')->nullable();
            $table->string('dependent_first_name')->nullable();
            $table->string('dependent_middle_initial')->nullable();
            $table->string('dependent_last_name')->nullable();
            $table->string('ssn')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('dependent_type')->nullable();
            $table->integer('cognito_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dependents_info');
    }
};
