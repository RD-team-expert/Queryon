<?php

namespace App\Http\Controllers\Pizza;

use App\Models\Pizza\DepositDeliveryData;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class DSQR_Controller extends Controller
{
    public function daily($store, $date)
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
    public function weekly($store, $startdate, $enddate)
    {
        if (empty($store) || empty($startdate) || empty($enddate)) {
            return response()->noContent();
        }

        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startdate) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $enddate)) {
            return response()->json(['error' => 'Invalid date format, expected YYYY-MM-DD'], 400);
        }

        try {
            $start = Carbon::parse($startdate)->startOfDay();
            $end   = Carbon::parse($enddate)->endOfDay();

            if ($end->lessThan($start)) {
                return response()->json(['error' => 'End date must be after start date'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date value'], 400);
        }

        $startValue = $start->toDateString();
        $endValue   = $end->toDateString();

        // Get data between start and end date
        $weeklyDepositDelivery = DepositDeliveryData::where('HookFranchiseeNum', $store)
            ->whereBetween('HookWorkDaysDate', [$startValue, $endValue])
            ->get();

        return response()->json([
            'weeklyDepositDelivery' => $weeklyDepositDelivery,
        ]);
    }

}
