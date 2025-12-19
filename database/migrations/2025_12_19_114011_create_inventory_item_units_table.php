<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')
                ->constrained('inventory_items')
                ->cascadeOnDelete();

            $table->string('unit_key');                // e.g. "Case", "LB", "Each2", "ORDER1"
            $table->decimal('value', 12, 2)->nullable();

            $table->timestamps();

            // Prevent duplicates on retries:
            $table->unique(['item_id', 'unit_key']);
            $table->index(['item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item_units');
    }
};
