<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('applications_info', function (Blueprint $table) {
            $table->id();
            $table->string('store')->nullable();
            $table->string('add_term_or_change')->nullable();
            $table->string('plan_choice')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_initial')->nullable();
            $table->date('dob')->nullable();
            $table->string('street_address')->nullable();
            $table->string('street_address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_abbreviation')->nullable();
            $table->string('zip')->nullable();
            $table->string('phone')->nullable();
            $table->string('email_address')->nullable();
            $table->date('date_of_hire')->nullable();
            $table->string('gender')->nullable();
            $table->string('ssn')->nullable();
            $table->string('location')->nullable();
            $table->string('occupation')->nullable();
            $table->decimal('average_hours_worked_per_week', 5, 2)->nullable();
            $table->string('marital_status')->nullable();
            $table->string('coverage_tier')->nullable();
            $table->integer('cognito_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('applications_info');
    }
};
