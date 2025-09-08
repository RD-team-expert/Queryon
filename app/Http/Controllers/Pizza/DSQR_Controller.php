<?php

namespace App\Http\Controllers\Pizza;

use App\Models\Pizza\DepositDeliveryData;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DSQR_Controller extends Controller
{
    public function index($store, $date)
    {
        if (empty($store) || empty($date)) {
            return response()->noContent();
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['error' => 'Invalid date format, expected YYYY-MM-DD'], 400);
        }

        try {
            $day   = Carbon::parse($date);
            $start = $day->copy()->startOfWeek(CarbonInterface::TUESDAY)->startOfDay();
            $end   = $start->copy()->addDays(6)->endOfDay(); // Tuesday -> Monday
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date value'], 400);
        }

        $startValue = $start->toDateString();
        $endValue   = $end->toDateString();

        // Weekly collections
        $weeklyDepositDelivery = DepositDeliveryData::where('HookFranchiseeNum', $store)
            ->whereBetween('HookWorkDaysDate', [$startValue, $endValue])
            ->get();


        // Return requested data as JSON
        return response()->json([
            'weeklyDepositDelivery'   => $weeklyDepositDelivery,
        ]);
    }
}
