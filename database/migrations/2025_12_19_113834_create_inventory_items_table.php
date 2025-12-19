<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('inventory_submissions')
                ->cascadeOnDelete();

            // Key like "404", "389", or Arabic text keys
            $table->string('item_key');

            $table->timestamps();

            // Prevent duplicates on retries:
            $table->unique(['submission_id', 'item_key']);
            $table->index(['submission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
