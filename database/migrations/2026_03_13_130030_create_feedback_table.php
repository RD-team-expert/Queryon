<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('external_entry_number')->unique();

            $table->string('form_id')->nullable();
            $table->string('form_internal_name')->nullable();
            $table->string('form_name')->nullable();
            $table->string('form_url_name')->nullable();

            $table->text('improvement_feedback')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();

            $table->string('store_label')->nullable();

            $table->string('valued_respected_appreciated')->nullable();
            $table->unsignedTinyInteger('valued_respected_appreciated_rating')->nullable();

            $table->string('work_schedule_satisfaction')->nullable();
            $table->unsignedTinyInteger('work_schedule_satisfaction_rating')->nullable();

            $table->string('gm_email')->nullable();
            $table->string('director_email')->nullable();

            $table->string('entry_status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('updated_at_external')->nullable();

            $table->json('payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
