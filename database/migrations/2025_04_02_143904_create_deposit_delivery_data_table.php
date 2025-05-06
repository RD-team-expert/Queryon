<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositDeliveryDataTable extends Migration
{
    public function up()
    {
        Schema::create('deposit_delivery_data', function (Blueprint $table) {
            $table->id();
            //daily deposit change and tips
            $table->string('HookFranchiseeNum')->nullable();
            $table->date('HookWorkDaysDate')->nullable();
            $table->decimal('HookDepositAmount', 20, 2)->nullable();
            $table->decimal('HookHowMuchTips', 20, 2)->nullable();
            $table->decimal('HookOverShort', 20, 2)->nullable();
            $table->decimal('HookAltimetricWaste', 20, 2)->nullable();
            $table->decimal('HookEmployeesWorkingHours', 20, 2)->nullable();
            // delivery
            $table->string('Hook_MostLovedRestaurant')->nullable();
            $table->string('Hook_OptimizationScore')->nullable();
            $table->decimal('Hook_RatingsAverageRating', 20, 2)->nullable();
            $table->decimal('Hook_CancellationsSalesLost2', 20, 2)->nullable();
            $table->decimal('Hook_MissingOrIncorrectErrorCharges', 20, 2)->nullable();
            $table->decimal('Hook_AvoidableWaitMSec2', 20, 2)->nullable();
            $table->decimal('Hook_TotalDasherWaitMSec', 20, 2)->nullable();
            $table->string('Hook_1TopMissingOrIncorrectItem')->nullable();
            $table->decimal('Hook_DowntimeHMM', 20, 2)->nullable();
            $table->string('Hook_ReviewsResponded')->nullable();
            $table->string('Hook_NAOT_RatingsAverageRating')->nullable();
            $table->string('Hook_NAOT_CancellationsSalesLost')->nullable();
            // For the column "Hook_NAOT_Missing or Incorrect Error Charges", remove spaces and use underscores.
            $table->string('Hook_NAOT_MissingOrIncorrectErrorCharges')->nullable();
            $table->string('Hook_NAOT_AvoidableWaitMSec')->nullable();
            $table->string('Hook_NAOT_TotalDasherWaitMSec')->nullable();
            $table->string('Hook_NAOT_DowntimeHMM')->nullable();
            $table->decimal('Hook_CustomerReviewsOverview', 20, 2)->nullable();
            $table->decimal('Hook_CostOfRefunds', 20, 2)->nullable();
            $table->decimal('Hook_UnfulfilledOrderRate', 20, 2)->nullable();
            $table->decimal('Hook_TimeUnavailableDuringOpenHoursHhmm', 20, 2)->nullable();
            $table->string('Hook_TopInaccurateItem')->nullable();
            // Because "Hook_ReviewsResponded" was already used, we rename the second occurrence.
            $table->string('Hook_ReviewsResponded_2')->nullable();
            $table->string('Hook_NAOT_CustomerReviewsOverview')->nullable();
            $table->string('Hook_NAOT_CostOfRefunds')->nullable();
            $table->string('Hook_NAOT_UnfulfilledOrderRate')->nullable();
            $table->string('Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm')->nullable();
            $table->decimal('Hook_Rating', 20, 2)->nullable();
            $table->decimal('Hook_FoodWasGood', 20, 2)->nullable();
            $table->decimal('Hook_DeliveryWasOnTime', 20, 2)->nullable();
            $table->decimal('Hook_OrderWasAccurate', 20, 2)->nullable();
            $table->string('Hook_NAOT_Rating')->nullable();
            $table->string('Hook_NAOT_FoodWasGood')->nullable();
            // Removed the extra comma from the column name below.
            $table->string('Hook_NAOT_DeliveryWasOnTime')->nullable();
            $table->string('Hook_NAOT_OrderWasAccurate')->nullable();
            $table->integer('Entry_Number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposit_delivery_data');
    }
}
