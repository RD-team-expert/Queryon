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
        Schema::create('pizza_av_team_pay', function (Blueprint $table) {
            $table->id();
            $table->integer('store')->nullable();
            $table->date('date')->nullable();
            $table->integer('emp_id')->nullable();
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->decimal('hourly_pay', 10, 2)->nullable();
            $table->decimal('total_hours', 10, 2)->nullable();
            $table->decimal('total_tips', 10, 2)->nullable();
            $table->decimal('positive', 10, 2)->nullable();
            $table->decimal('money_owed', 10, 2)->nullable();
            $table->decimal('amazon_wm_others', 10, 2)->nullable();
            $table->decimal('base_pay', 10, 2)->nullable();
            $table->decimal('performance_bonus', 10, 2)->nullable();
            $table->decimal('gross_pay', 10, 2)->nullable();
            $table->decimal('team_profit_sharing', 10, 2)->nullable();
            $table->decimal('bread_boost_bonus', 10, 2)->nullable();
            $table->decimal('extra_pay', 10, 2)->nullable();
            $table->decimal('total_deduction', 10, 2)->nullable();
            $table->decimal('tax_allowans', 10, 2)->nullable();
            $table->decimal('rent_pmt', 10, 2)->nullable();
            $table->decimal('phone_pmt', 10, 2)->nullable();
            $table->decimal('utilities', 10, 2)->nullable();
            $table->decimal('others', 10, 2)->nullable();
            $table->decimal('company_loan', 10, 2)->nullable();
            $table->decimal('legal', 10, 2)->nullable();
            $table->decimal('car', 10, 2)->nullable();
            $table->decimal('labor', 10, 2)->nullable();
            $table->string('lc_audit')->nullable();
            $table->decimal('customer_service', 10, 2)->nullable();
            $table->decimal('upselling', 10, 2)->nullable();
            $table->decimal('inventory', 10, 2)->nullable();
            $table->decimal('pne_audit_fail', 10, 2)->nullable();
            $table->decimal('sales', 10, 2)->nullable();
            $table->decimal('final_score', 10, 2)->nullable();
            $table->decimal('total_tax', 10, 2)->nullable();
            $table->decimal('tax_dif', 10, 2)->nullable();
            $table->boolean('at')->nullable();
            $table->decimal('apt_cost', 10, 2)->nullable();
            $table->decimal('apt_cost_per_store', 10, 2)->nullable();
            $table->decimal('utilities_cost', 10, 2)->nullable();
            $table->decimal('phone_cost', 10, 2)->nullable();
            $table->timestamps();

            // Add composite unique constraint
            $table->unique(['store', 'date', 'emp_id'], 'unique_store_date_emp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pizza_av_team_pay');
    }
};
