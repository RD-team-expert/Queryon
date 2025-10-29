<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pizza_coaching_actions', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('cognito_id');
            $table->string('action_name');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('cognito_id')
                  ->references('cognito_id')
                  ->on('pizza_coaching_action_plans')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizza_coaching_actions');
    }
};
