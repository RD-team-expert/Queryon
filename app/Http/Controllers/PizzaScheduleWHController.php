<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceSchedule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PizzaScheduleWHController extends Controller
{
    /**
     * Export working hours CSV showing employee count per hour for each day for all stores
     */
    public function exportCsv(Request $request, $date)
    {
        try {
            Log::info("PizzaSchedule WH Export - Date: {$date}");

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
            }

            // Get all unique stores from attendance records for this date
            $stores = AttendanceSchedule::where('schedule_date', $date)
                ->whereNotNull('emp_id')
                ->distinct()
                ->pluck('store')
                ->toArray();

            if (empty($stores)) {
                return response()->json(['message' => 'No attendance data found for the specified date'], 404);
            }

            // Define working hours from 11 AM to 2 AM (next day)
            $workingHours = $this->getWorkingHours();

            // Initialize CSV data
            $csvData = [];
            $headers = ['store', 'schedule_date', 'hour', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'mon'];
            $csvData[] = $headers;

            // Loop through each store
            foreach ($stores as $store) {
                // Get all attendance records for this store and date
                $attendanceRecords = AttendanceSchedule::where('store', $store)
                    ->where('schedule_date', $date)
                    ->whereNotNull('emp_id')
                    ->get();

                // Calculate employee counts for each hour for this store
                foreach ($workingHours as $hour) {
                    $row = [
                        'store' => $store,
                        'schedule_date' => $date,
                        'hour' => $hour,
                        'tue' => $this->countEmployeesAtHour($attendanceRecords, 'tue', $hour),
                        'wed' => $this->countEmployeesAtHour($attendanceRecords, 'wed', $hour),
                        'thu' => $this->countEmployeesAtHour($attendanceRecords, 'thu', $hour),
                        'fri' => $this->countEmployeesAtHour($attendanceRecords, 'fri', $hour),
                        'sat' => $this->countEmployeesAtHour($attendanceRecords, 'sat', $hour),
                        'sun' => $this->countEmployeesAtHour($attendanceRecords, 'sun', $hour),
                        'mon' => $this->countEmployeesAtHour($attendanceRecords, 'mon', $hour)
                    ];
                    $csvData[] = array_values($row);
                }
            }

            // Generate CSV content
            $csvContent = $this->arrayToCsv($csvData);

            // Create filename with all stores or date only
            $filename = "working_hours_all_stores_{$date}.csv";

            Log::info("PizzaSchedule WH Export - Successfully exported working hours data for all stores");

            // Return CSV as download
            return response($csvContent)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('PizzaSchedule WH export error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error occurred during working hours CSV export',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * Get working hours from 11 AM to 2 AM
     */
    private function getWorkingHours(): array
    {
        $hours = [];

        // 11 AM to 11 PM (same day)
        for ($hour = 11; $hour <= 23; $hour++) {
            $hours[] = sprintf('%02d:00', $hour);
        }

        // 12 AM to 2 AM (next day)
        for ($hour = 0; $hour <= 2; $hour++) {
            $hours[] = sprintf('%02d:00', $hour);
        }

        return $hours;
    }

    /**
     * Count employees working at a specific hour for a given day
     */
    private function countEmployeesAtHour($attendanceRecords, string $day, string $targetHour): int
    {
        $count = 0;
        $targetTime = Carbon::createFromFormat('H:i', $targetHour);

        foreach ($attendanceRecords as $record) {
            $inField = $day . '_in';
            $outField = $day . '_out';

            $timeIn = $record->$inField;
            $timeOut = $record->$outField;

            // Skip if either time is null
            if (!$timeIn || !$timeOut) {
                continue;
            }

            try {
                // Parse time strings (assuming format HH:MM:SS or HH:MM)
                $startTime = Carbon::createFromFormat('H:i:s', $timeIn);
                $endTime = Carbon::createFromFormat('H:i:s', $timeOut);

                // Handle case where end time is next day (like 2 AM)
                if ($endTime->lt($startTime)) {
                    $endTime->addDay();
                }

                // Adjust target time if it's in the early morning hours (next day)
                $checkTime = clone $targetTime;
                if ($targetTime->hour <= 2) {
                    $checkTime->addDay();
                }

                // Check if employee is working at this hour
                // Employee works from start time (inclusive) to end time (exclusive)
                if ($checkTime->gte($startTime) && $checkTime->lt($endTime)) {
                    $count++;
                }

            } catch (\Exception $e) {
                // Try parsing with different format if HH:MM:SS fails
                try {
                    $startTime = Carbon::createFromFormat('H:i', $timeIn);
                    $endTime = Carbon::createFromFormat('H:i', $timeOut);

                    if ($endTime->lt($startTime)) {
                        $endTime->addDay();
                    }

                    $checkTime = clone $targetTime;
                    if ($targetTime->hour <= 2) {
                        $checkTime->addDay();
                    }

                    if ($checkTime->gte($startTime) && $checkTime->lt($endTime)) {
                        $count++;
                    }

                } catch (\Exception $e2) {
                    Log::warning("Could not parse time for employee {$record->emp_id} on {$day}: {$timeIn} - {$timeOut}");
                    continue;
                }
            }
        }

        return $count;
    }

    /**
     * Convert array to CSV string
     */
    private function arrayToCsv(array $data): string
    {
        $output = '';
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($row as $field) {
                $field = (string)$field;
                // Escape field if it contains comma, quote, or newline
                if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n") || str_contains($field, "\r")) {
                    $field = '"' . str_replace('"', '""', $field) . '"';
                }
                $csvRow[] = $field;
            }
            $output .= implode(',', $csvRow) . "\n";
        }
        return $output;
    }


    /**********************************/

public function exportCsvRange(Request $request, $start_date, $end_date)
{
    try {
        // CRITICAL: Disable all output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', 300);

        Log::info("PizzaSchedule WH Range Export - Start: {$start_date}, End: {$end_date}");

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        if (Carbon::parse($end_date)->lt(Carbon::parse($start_date))) {
            return response()->json(['message' => 'End date must be after start date'], 422);
        }

        $tuesdayDates = $this->getTuesdaysBetweenDates($start_date, $end_date);

        if (empty($tuesdayDates)) {
            return response()->json(['message' => 'No Tuesdays found in the specified date range'], 404);
        }

        $workingHours = $this->getWorkingHours();
        $filename = "working_hours_tuesdays_{$start_date}_to_{$end_date}.csv";

        return response()->stream(function() use ($tuesdayDates, $workingHours) {
            // CRITICAL: Disable output buffering inside the stream
            if (ob_get_level()) ob_end_clean();

            $output = fopen('php://output', 'w');

            // Write headers
            fputcsv($output, ['store', 'schedule_date', 'hour', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun', 'mon']);

            // CRITICAL: Flush immediately after headers
            if (ob_get_level()) ob_flush();
            flush();

            $processedDates = 0;

            foreach ($tuesdayDates as $date) {
                $processedDates++;
                Log::info("Processing {$processedDates}/" . count($tuesdayDates) . ": {$date}");

                $allRecords = AttendanceSchedule::where('schedule_date', $date)
                    ->whereNotNull('emp_id')
                    ->get()
                    ->groupBy('store');

                if ($allRecords->isEmpty()) {
                    Log::warning("No data for: {$date}");
                    continue;
                }

                foreach ($allRecords as $store => $attendanceRecords) {
                    foreach ($workingHours as $hour) {
                        fputcsv($output, [
                            $store,
                            $date,
                            $hour,
                            $this->countEmployeesAtHour($attendanceRecords, 'tue', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'wed', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'thu', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'fri', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'sat', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'sun', $hour),
                            $this->countEmployeesAtHour($attendanceRecords, 'mon', $hour)
                        ]);
                    }
                }

                // CRITICAL: Flush after each date
                if (ob_get_level()) ob_flush();
                flush();
            }

            fclose($output);
            Log::info("Export completed - {$processedDates} dates processed");

        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

    } catch (\Exception $e) {
        Log::error('Export error: ' . $e->getMessage());
        return response()->json(['message' => 'Export failed', 'error' => $e->getMessage()], 500);
    }
}



/**
 * Get all Tuesday dates between start_date and end_date
 */
private function getTuesdaysBetweenDates(string $start_date, string $end_date): array
{
    $tuesdays = [];
    $startDate = Carbon::createFromFormat('Y-m-d', $start_date);
    $endDate = Carbon::createFromFormat('Y-m-d', $end_date);

    // Clone to avoid mutating the original date
    $currentDate = $startDate->copy();

    // If start date is not Tuesday, move to next Tuesday
    if (!$currentDate->isTuesday()) {
        $currentDate->next(Carbon::TUESDAY);
    }

    // Loop through and collect all Tuesdays
    while ($currentDate->lte($endDate)) {
        $tuesdays[] = $currentDate->format('Y-m-d');
        $currentDate->addWeek(); // Move to next Tuesday
    }

    return $tuesdays;
}

}
