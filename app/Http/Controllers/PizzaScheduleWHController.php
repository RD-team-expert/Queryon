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
}
