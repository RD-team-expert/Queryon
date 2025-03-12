<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCapDataEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('cap_data_entries', function (Blueprint $table) {
            $table->id();
            $table->string('hook_date_of_incident')->nullable();
            $table->string('hook_cap_type')->nullable();
            $table->string('hook_managements_statement')->nullable();
            $table->string('hook_name_person_doing_the_cap_id')->nullable();
            $table->string('hook_name_person_doing_the_cap_label')->nullable();
            $table->string('hook_email_person_doing_the_cap_label')->nullable();
            $table->string('hook_employee_name_id')->nullable();
            $table->string('hook_employee_name_label')->nullable();
            $table->string('hook_employee_email_label')->nullable();
            $table->string('hook_title_person_doing_the_cap')->nullable();
            $table->string('hook_the_email_person_doing_the_cap')->nullable();
            $table->string('hook_the_email_employee_email')->nullable();
            $table->string('hook_depatmant_name_filter')->nullable();
            $table->string('hook_emp_section')->nullable();
            $table->string('hook_employees_statement')->nullable();
            $table->string('admin_link')->nullable();
            $table->integer('number')->nullable();
            $table->string('timestamp')->nullable();
            $table->string('cap_link')->nullable();
            $table->string('emp_link')->nullable();
            $table->string('view1_link')->nullable();
            $table->string('upper_management_link')->nullable();
            $table->string('the_next_manager_above_link')->nullable();
            $table->string('upper_manager_in_the_department_link')->nullable();
            $table->string('witness1_link')->nullable();
            $table->string('witness2_link')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cap_data_entries');
    }
}
