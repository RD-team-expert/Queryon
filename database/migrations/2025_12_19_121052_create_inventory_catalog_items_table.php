<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_catalog_items', function (Blueprint $table) {
            $table->id();

            // This is the key that appears in payload: e.g. "404", "389", "DELIVERY", "6210A", Arabic keys, etc.
            $table->string('item_key')->unique();

            // Human friendly name for reporting/export
            $table->string('item_name');

            $table->timestamps();

            $table->index('item_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_catalog_items');
    }
};
