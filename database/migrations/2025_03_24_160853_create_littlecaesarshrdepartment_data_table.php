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
        Schema::create('LITTLECAESARSHRDEPARTMENT_Data', function (Blueprint $table) {
            $table->id();
            $table->string('HookLanguage')->nullable();
            $table->string('HookStore')->nullable();
            $table->string('Hookالمتجر')->nullable();
            $table->string('HookAlmacenar')->nullable();
            $table->string('HookSelectYourRequestType')->nullable();
            $table->string('Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات')->nullable();
            $table->string('HookSeleccioneSuTipoDeSolicitud')->nullable();
            $table->integer('EntryNum')->nullable();
            $table->date('DateSubmitted')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('LITTLECAESARSHRDEPARTMENT_Data');
    }
};