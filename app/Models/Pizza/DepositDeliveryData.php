<?php

namespace App\Models\Pizza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositDeliveryData extends Model
{
    use HasFactory;

    protected $table = 'deposit_delivery_data';

    protected $fillable = [
        'HookStoreNum',
        'HookTodayIs',
        'HookWorkDaysDate',
        'HookTotalChange',
        'HookAmountInSafe',
        'HookHowMuchTips',
        'HookDepositAmount',
        'Hook_MostLovedRestaurant',
        'Hook_OptimizationScore',
        'Hook_RatingsAverageRating',
        'Hook_CancellationsSalesLost2',
        'Hook_MissingOrIncorrectErrorCharges',
        'Hook_AvoidableWaitMSec2',
        'Hook_TotalDasherWaitMSec',
        'Hook_1TopMissingOrIncorrectItem',
        'Hook_DowntimeHMM',
        'Hook_ReviewsResponded',
        'Hook_NAOT_RatingsAverageRating',
        'Hook_NAOT_CancellationsSalesLost',
        'Hook_NAOT_MissingOrIncorrectErrorCharges',
        'Hook_NAOT_AvoidableWaitMSec',
        'Hook_NAOT_TotalDasherWaitMSec',
        'Hook_NAOT_DowntimeHMM',
        'Hook_CustomerReviewsOverview',
        'Hook_CostOfRefunds',
        'Hook_UnfulfilledOrderRate',
        'Hook_TimeUnavailableDuringOpenHoursHhmm',
        'Hook_TopInaccurateItem',
        'Hook_ReviewsResponded_2',
        'Hook_NAOT_CustomerReviewsOverview',
        'Hook_NAOT_CostOfRefunds',
        'Hook_NAOT_UnfulfilledOrderRate',
        'Hook_NAOT_TimeUnavailableDuringOpenHoursHhmm',
        'Hook_Rating',
        'Hook_FoodWasGood',
        'Hook_DeliveryWasOnTime',
        'Hook_OrderWasAccurate',
        'Hook_NAOT_Rating',
        'Hook_NAOT_FoodWasGood',
        'Hook_NAOT_DeliveryWasOnTime',
        'Hook_NAOT_OrderWasAccurate',
        'Entry_Number',
    ];
}
