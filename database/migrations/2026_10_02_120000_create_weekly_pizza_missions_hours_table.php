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
        Schema::create('weekly_pizza_missions_hours', function (Blueprint $table) {
            $table->id();

            // Cognito Entry Identity (used to match update/delete webhooks)
            $table->string('cognito_id')->unique()->comment('Unique Cognito entry ID (e.g., 1471-2)');
            $table->integer('entry_number')->nullable()->comment('Cognito entry number');

            $table->string('name_full')->nullable();
            $table->integer('week_number')->nullable();
            $table->json('days_filled')->nullable()->comment('WhichDaysYouWannaFillOut');

            // Total hours of each day
            $table->decimal('tuesday_total_hours', 8, 2)->nullable();
            $table->decimal('wednesday_total_hours', 8, 2)->nullable();
            $table->decimal('thursday_total_hours', 8, 2)->nullable();
            $table->decimal('friday_total_hours', 8, 2)->nullable();
            $table->decimal('saturday_total_hours', 8, 2)->nullable();
            $table->decimal('sunday_total_hours', 8, 2)->nullable();
            $table->decimal('monday_total_hours', 8, 2)->nullable();

            $table->timestamps();

            $table->index('entry_number');
            $table->index('week_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_pizza_missions_hours');
    }
};
