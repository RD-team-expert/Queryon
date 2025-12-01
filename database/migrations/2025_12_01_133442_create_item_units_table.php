<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');  // ✅ ADDED THIS
            $table->string('name');
            $table->decimal('value', 10, 2)->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onUpdate('no action')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
