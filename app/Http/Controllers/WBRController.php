<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Feedback;
use App\Models\ReimbursementRequest;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;

class WBRController extends Controller
{
    public function exportJson(string $store, string $date): JsonResponse
    {
        $day = CarbonImmutable::parse($date)->startOfDay();

        [$weekStart, $weekEnd] = $this->isoBusinessWeek($day);

        $storeNumber = $this->extractStoreNumber($store);

        $feedbacks = Feedback::query()
            ->whereBetween('submitted_at', [
                $weekStart->startOfDay(),
                $weekEnd->endOfDay(),
            ])
            ->get()
            ->filter(function (Feedback $feedback) use ($storeNumber) {
                return $this->storeLabelMatchesStoreNumber($feedback->store_label, $storeNumber);
            })
            ->values();

        $complaints = Complaint::query()
            ->whereBetween('submitted_at', [
                $weekStart->startOfDay(),
                $weekEnd->endOfDay(),
            ])
            ->get()
            ->filter(function (Complaint $complaint) use ($storeNumber) {
                return $this->storeLabelMatchesStoreNumber($complaint->store_label, $storeNumber);
            })
            ->values();

        $moneyOwed = ReimbursementRequest::query()
            ->whereBetween('expense_date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->get()
            ->filter(function (ReimbursementRequest $request) use ($storeNumber) {
                return $this->storeLabelMatchesStoreNumber($request->store_label, $storeNumber);
            })
            ->values();

        return response()->json([
            'store' => $store,
            'store_number' => $storeNumber,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),

            'complaints' => $complaints,
            'feedbacks' => $feedbacks,
            'money_owed' => $moneyOwed,
        ]);
    }

    private function isoBusinessWeek(CarbonImmutable $date): array
    {
        $start = $date->startOfWeek(CarbonInterface::TUESDAY);

        return [$start, $start->addDays(6)];
    }

    private function extractStoreNumber(string $store): int
    {
        $lastSegment = substr($store, -2);

        return (int) $lastSegment;
    }

    private function storeLabelMatchesStoreNumber(?string $storeLabel, int $storeNumber): bool
    {
        if ($storeLabel === null) {
            return false;
        }

        /*
         * Matches store labels like:
         * "Montgomery CIN - 12"
         * "Broad - 3"
         *
         * It extracts the number after the final dash.
         */
        if (!preg_match('/-\s*(\d+)\s*$/', $storeLabel, $matches)) {
            return false;
        }

        return (int) $matches[1] === $storeNumber;
    }
}