<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hiring_hires_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('hiring_requests');
            $table->string('emp_first_name');
            $table->string('emp_middle_name');
            $table->string('emp_last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('available_shifts')->nullable();
            $table->string('work_days')->nullable();
            $table->text('alta_clock_in_out_img');
            $table->text('paychex_profile_img');
            $table->text('paychex_direct_deposit_img');
            $table->text('signed_contract_img');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hiring_hires_info');
    }
};
