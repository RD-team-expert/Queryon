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
        Schema::create('weekly_pizza_missions_hours_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_pizza_missions_hour_id')
                ->constrained('weekly_pizza_missions_hours', 'id', 'wpmh_items_wpmh_id_foreign')
                ->cascadeOnDelete();

            $table->string('day')->comment('Tuesday ... Monday');
            $table->string('work_type')->nullable();
            $table->text('note')->nullable();
            $table->string('store_label')->nullable()->comment('Only set when the work is in a store');
            $table->decimal('hours', 8, 2)->nullable();

            $table->index('day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_pizza_missions_hours_items');
    }
};
