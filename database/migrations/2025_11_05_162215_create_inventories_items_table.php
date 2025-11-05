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
        Schema::create('inventories_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_id')->constrained('form_requests')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('inventory_type_id')->nullable()->constrained('inventory_types')->onUpdate('cascade')->onDelete('no action');
            $table->foreignId('unit_id')->nullable()->constrained('units')->onUpdate('cascade')->onDelete('no action');
            $table->string('name_of_item')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories_items');
    }
};
