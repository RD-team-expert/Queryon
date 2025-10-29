<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pizza_coaching_action_plans', function (Blueprint $table) {
            $table->string( 'cognito_id')->primary(); // Non-auto-increment primary key
            $table->string('manager_first_name');
            $table->string('manager_last_name');
            $table->string('store');
            $table->string('emp_first_name');
            $table->string('emp_last_name');
            $table->text('description_of_the_incident');
            $table->text('coaching_plan');
            $table->date('date');
            $table->string('cap_type');
            $table->integer('re_evaluation_after')->nullable();
            $table->string('director_first_name')->nullable();
            $table->string('director_last_name')->nullable();
            $table->string('director_is_accepted')->nullable();
            $table->text('director_rejection_reason')->nullable();
            $table->timestamps();

            // Add index on cognito_id
            $table->index('cognito_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizza_coaching_action_plans');
    }
};
