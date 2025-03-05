<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesDataTable extends Migration
{
    public function up()
    {
        Schema::create('Employees_Data', function (Blueprint $table) {
            $table->id();
            $table->string('first_name_english')->nullable();
            $table->string('first_and_last_name_english')->nullable();
            $table->string('last_name_english')->nullable();
            $table->string('first_name_arabic')->nullable();
            $table->string('first_and_last_name_arabic')->nullable();
            $table->string('last_name_arabic')->nullable();
            // Changed from time to date since your JSON sends a date string.
            $table->date('hiring_date')->nullable();
            $table->string('pne_email')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('sy_phone')->nullable();
            $table->string('us_phone')->nullable();
            $table->string('img_link')->nullable();
            $table->longText('about_you')->nullable();
            $table->string('password2')->nullable();
            $table->string('shift')->nullable();
            $table->string('depatment_id')->nullable();
            $table->string('depatment_label')->nullable();
            $table->string('position_id')->nullable();
            $table->string('position_label')->nullable();
            $table->string('direct_manager1_id')->nullable();
            $table->string('direct_manager1_label')->nullable();
            $table->string('is_manager')->nullable();
            $table->string('second_level_manager_id')->nullable();
            $table->string('second_level_manager_label')->nullable();
            $table->string('dm_email_id')->nullable();
            $table->string('dm_email_label')->nullable();
            $table->string('second_email_id')->nullable();
            $table->string('second_email_label')->nullable();
            $table->string('offboarded')->nullable();
            $table->string('direct_manager2_id')->nullable();
            $table->string('direct_manager2_label')->nullable();
            $table->string('dm_email2_id')->nullable();
            $table->string('dm_email2_label')->nullable();
            $table->integer('level')->nullable();
            $table->integer('tier')->nullable();
            $table->string('entry_admin_link')->nullable();
            $table->integer('Cognito_ID')->nullable();
            $table->string('entry_status')->nullable();
            $table->string('entry_public_link')->nullable();
            $table->string('entry_internal_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('Employees_Data');
    }
}
