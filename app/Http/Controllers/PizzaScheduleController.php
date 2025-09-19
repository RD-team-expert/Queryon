<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmpInfo;
use App\Models\AttendanceSchedule;
use App\Models\WeeklyScheduleSummary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PizzaScheduleController extends Controller
{
    public function store(Request $request)
    {
        try {
            $jsonData = $request->json()->all();
            Log::info("PizzaSchedule - Received JSON data:\n" . json_encode($jsonData, JSON_PRETTY_PRINT));
            if (!isset($jsonData['rows']) || !is_array($jsonData['rows'])) {
                return response()->json(['message' => 'Invalid data: rows missing or not an array'], 422);
            }

            $store = $jsonData['Store'] ?? null;
            $scheduleDate = $jsonData['ScheduleDate'] ?? null;
            $rows = $jsonData['rows'];
            $fieldMapping = $this->getFieldMapping();

            $empInfoData = [];
            $attendanceData = [];
            $weeklySummaryData = [];

            foreach ($rows as $index => $row) {
    try {
        $empRow = [
            'store' => $store,
            'schedule_date' => $scheduleDate
        ];
        $attRow = [
            'store' => $store,
            'schedule_date' => $scheduleDate
        ];
        $weekRow = [
            'store' => $store,
            'schedule_date' => $scheduleDate
        ];
        foreach ($row as $jsonKey => $value) {
            if (!isset($fieldMapping[$jsonKey])) continue;

            $dbColumn = $fieldMapping[$jsonKey];
            $processedValue = $this->processFieldValue($value, $dbColumn);
            // Add emp_id and name to all tables for proper relationships and identification
            if ($dbColumn === 'emp_id' || $dbColumn === 'name') {
                $empRow[$dbColumn] = $processedValue;
                $attRow[$dbColumn] = $processedValue;
                $weekRow[$dbColumn] = $processedValue;
            } else {
                // Determine which table the field belongs to
                if ($this->isEmpInfoField($dbColumn)) {
                    $empRow[$dbColumn] = $processedValue;
                } elseif ($this->isAttendanceField($dbColumn)) {
                    $attRow[$dbColumn] = $processedValue;
                } elseif ($this->isWeeklySummaryField($dbColumn)) {
                    $weekRow[$dbColumn] = $processedValue;
                }
            }

        }
        $empInfoData[] = $empRow;
        $attendanceData[] = $attRow;
        $weeklySummaryData[] = $weekRow;
    } catch (\Exception $e) {
        Log::warning("PizzaSchedule - Error processing row {$index}: " . $e->getMessage());
    }
}

            if (empty($empInfoData)) {
                return response()->json(['message' => 'No valid employee info data to process'], 422);
            }

            $totalProcessed = 0;

            DB::transaction(function () use ($empInfoData, $attendanceData, $weeklySummaryData, &$totalProcessed) {
                // Upsert Employee Info
                $this->chunkUpsert(EmpInfo::class, $empInfoData, ['store','emp_id', 'schedule_date']);
                Log::info("PizzaSchedule - Upserted " . count($empInfoData) . " employee info records");

                // Upsert Attendance Schedule
                $this->chunkUpsert(AttendanceSchedule::class, $attendanceData, ['store','emp_id', 'schedule_date']);
                Log::info("PizzaSchedule - Upserted " . count($attendanceData) . " attendance schedule records");

                // Upsert Weekly Schedule Summary
                $this->chunkUpsert(WeeklyScheduleSummary::class, $weeklySummaryData, ['store','emp_id', 'schedule_date']);
                Log::info("PizzaSchedule - Upserted " . count($weeklySummaryData) . " weekly summary records");

                $totalProcessed = count($empInfoData);
            });

            Log::info("PizzaSchedule - Successfully upserted " . $totalProcessed . " complete records across all tables");

            return response()->json([
                'message' => 'Data upserted successfully across all tables',
                'records_processed' => $totalProcessed,
                'total_received' => count($rows),
                'tables_updated' => [
                    'emp_info' => count($empInfoData),
                    'attendance_schedule' => count($attendanceData),
                    'weekly_schedule_summary' => count($weeklySummaryData)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('PizzaSchedule upsert error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if (str_contains($e->getMessage(), 'MySQL server has gone away')) {
                return response()->json([
                    'message' => 'Database connection timeout. Please try with smaller data sets or contact administrator.',
                    'error' => 'MySQL timeout error'
                ], 500);
            }

            if (str_contains($e->getMessage(), 'Data truncated')) {
                return response()->json([
                    'message' => 'Data truncation error. Database schema may need to be updated. Please run migrations and try again.',
                    'error' => 'Data truncation error'
                ], 422);
            }

            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'message' => 'Duplicate entry found during upsert operation.',
                    'error' => 'Duplicate key error'
                ], 422);
            }

            return response()->json([
                'message' => 'Server error occurred during upsert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine if field belongs to Employee Info table
     */
    private function isEmpInfoField(string $field): bool
    {
        $empFields = [
            'store', 'schedule_date', 'hired_date', 'hourly_base_pay', 'hourly_performance_pay', 'totally_pay',
            'position', 'is_1099', 'uniform', 'num_of_shirts', 'emp_id', 'formula_emp_not_getting_hours_wanted',
            'at_only', 'family', 'car', 'dob', 'name', 'red_in_schedule', 'reads_in_schedule', 'emp_id_alt',
            'new_team_member', 'da_safety_score', 'attendance', 'score', 'notes', 'num_of_days',
            'reads_in_schedule_2', 'cross_trained', 'preference', 'pt_ft', 'name_alt', 'rating', 'maximum_hours',
            'hours_given'
        ];
        return in_array($field, $empFields);
    }

    /**
     * Determine if field belongs to Attendance Schedule table
     */
    private function isAttendanceField(string $field): bool
    {
        $attFields = [
            'store', 'schedule_date', 'name', 'emp_id',
            // All daily schedule fields (Tuesday through Monday)
            'tue_vci', 'tue_in', 'tue_out', 'tue_status', 'tue_total_hrs', 'tue_op', 'tue_m', 'tue_l', 'tue_c',
            'tue_status_f', 'tue_hours_cost', 'tue_hours', 'tue_sales', 'tue_4hrs',
            'wed_vc', 'wed_in', 'wed_out', 'wed_status', 'wed_total_hrs', 'wed_op', 'wed_m', 'wed_l', 'wed_c',
            'wed_status2', 'wed_hours_cost', 'wed_hours', 'wed_sales', 'wed_hrs',
            'thu_vci', 'thu_in', 'thu_out', 'thu_status', 'thu_total_hrs', 'thu_op', 'thu_m', 'thu_l', 'thu_c',
            'thu_status_formula', 'thu_hours_cost', 'thu_hours', 'thu_sales', 'thu_4hrs',
            'fri_vci30', 'fri_in', 'fri_out', 'fri_status', 'fri_total_hrs', 'fri_op', 'fri_m', 'fri_l', 'fri_c',
            'fri_status_formula', 'fri_hours_cost', 'fri_hours', 'fri_sales', 'fri_4hrs',
            'sat_vci44', 'sat_in', 'sat_out', 'sat_status', 'sat_total_hrs', 'sat_op', 'sat_m', 'sat_l', 'sat_c',
            'sat_status_formula', 'sat_hours_cost', 'sat_hours', 'sat_sales', 'sat_4hrs',
            'sun_vci', 'sun_in', 'sun_out', 'sun_status', 'sun_total_hrs', 'sun_op', 'sun_m', 'sun_l', 'sun_c',
            'sun_status_formula', 'sun_hours_cost', 'sun_hours', 'sun_sales', 'sun_4hrs',
            'mon_vci', 'mon_in', 'mon_out', 'mon_status', 'mon_total_hrs', 'mon_op', 'mon_m', 'mon_l', 'mon_c',
            'mon_status_formula', 'mon_hours_cost', 'mon_hours', 'mon_sales', 'mon_4hrs'
        ];
        return in_array($field, $attFields);
    }

    /**
     * Determine if field belongs to Weekly Schedule Summary table
     */
    private function isWeeklySummaryField(string $field): bool
    {
        $weeklyFields = [
            'store', 'schedule_date', 'name', 'emp_id', 'x', 'oje', 'off_both_we', 'status_not_filled', 'hours_not_given',
            'dh_not_scheduled', 'headcount', 'weekend_not_filling_status', 'weekly_hours', 'ot_calc', 'both_weekends', 'px',
            't', 're', 'vci87', 'excused_absence', 'unexcused_absence', 'late', 'tenure_in_months',
            'hourly_base_pay_alt', 'hourly_performance_pay_alt', 'totally_pay_alt', 'position_alt', 'is_1099_alt', 'total_pay'
        ];
        return in_array($field, $weeklyFields);
    }

    /**
     * Perform chunked upsert operation for any model
     */
    private function chunkUpsert($model, array $data, array $uniqueBy)
    {
        if (empty($data)) return;

        $chunks = array_chunk($data, 50);
        foreach ($chunks as $chunkIndex => $chunk) {
            try {
                $updateColumns = array_diff((new $model)->getFillable(), $uniqueBy);
                $model::upsert($chunk, $uniqueBy, $updateColumns);
                Log::info("Processed chunk {$chunkIndex} for " . class_basename($model) . ": " . count($chunk) . " records");
            } catch (\Exception $e) {
                Log::error("Error processing chunk {$chunkIndex} for " . class_basename($model) . ": " . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * Map JSON field names to database column names
     */
    private function getFieldMapping(): array
    {
        return [
            // Basic fields
            'Hired Date' => 'hired_date',
            'HOURLY BASE PAY' => 'hourly_base_pay',
            'HOURLY PERFORMANCE PAY' => 'hourly_performance_pay',
            'TOTALLY PAY' => 'totally_pay',
            'POSITION' => 'position',
            '1099' => 'is_1099',
            'Unifrom' => 'uniform',
            '# Of shirts' => 'num_of_shirts',
            'EMP ID' => 'emp_id',
            'Formula # OF EMP NOT GETTING THE HOURS WANTED' => 'formula_emp_not_getting_hours_wanted',
            'AT only' => 'at_only',
            'Family' => 'family',
            'Car' => 'car',
            'DOB' => 'dob',
            'Name' => 'name',
            'Red in Schedule' => 'red_in_schedule',
            'Reads in Schedule' => 'reads_in_schedule',
            'EMP ID_' => 'emp_id_alt',
            'New Team Member' => 'new_team_member',
            'DA SAFETY Score' => 'da_safety_score',
            'Attendance' => 'attendance',
            'Score' => 'score',
            'Notes' => 'notes',
            '# oF Days' => 'num_of_days',
            'Reads in Schedule3' => 'reads_in_schedule_2',
            'Cross Trained' => 'cross_trained',
            'Prefrence' => 'preference',
            'PT/FT' => 'pt_ft',
            'Name2' => 'name_alt',
            'Rating' => 'rating',
            'Maximum hours' => 'maximum_hours',
            'Hours given' => 'hours_given',

            // Tuesday fields
            'Tue_VCI' => 'tue_vci',
            'Tue_IN' => 'tue_in',
            'Tue_OUT' => 'tue_out',
            'Tue_STATUS' => 'tue_status',
            'Tue_Total HRS' => 'tue_total_hrs',
            'Tue_OP' => 'tue_op',
            'Tue_M' => 'tue_m',
            'Tue_L' => 'tue_l',
            'Tue_C' => 'tue_c',
            'Tue_Status F' => 'tue_status_f',
            'Tue_Hours Cost' => 'tue_hours_cost',
            'Tue_Hours' => 'tue_hours',
            'Tue_Sales' => 'tue_sales',
            'Tue_4hrs' => 'tue_4hrs',

            // Wednesday fields
            'Wed_VC' => 'wed_vc',
            'Wed_IN' => 'wed_in',
            'Wed_OUT' => 'wed_out',
            'Wed_STATUS' => 'wed_status',
            'Wed_Total HRS' => 'wed_total_hrs',
            'Wed_OP' => 'wed_op',
            'Wed_M' => 'wed_m',
            'Wed_L' => 'wed_l',
            'Wed_C' => 'wed_c',
            'Wed_Status2' => 'wed_status2',
            'Wed_Hours $' => 'wed_hours_cost',
            'Wed_Hours' => 'wed_hours',
            'Wed_Sales' => 'wed_sales',
            'Wed_hrs' => 'wed_hrs',

            // Thursday fields
            'Thu_VCI' => 'thu_vci',
            'Thu_IN' => 'thu_in',
            'Thu_OUT' => 'thu_out',
            'Thu_STATUS' => 'thu_status',
            'Thu_Total HRS' => 'thu_total_hrs',
            'Thu_OP' => 'thu_op',
            'Thu_M' => 'thu_m',
            'Thu_L' => 'thu_l',
            'Thu_C' => 'thu_c',
            'Thu_status formula' => 'thu_status_formula',
            'Thu_Hours $' => 'thu_hours_cost',
            'Thu_Hours' => 'thu_hours',
            'Thu_Sales' => 'thu_sales',
            'Thu_4hrs' => 'thu_4hrs',

            // Friday fields
            'Fri_VCI30' => 'fri_vci30',
            'Fri_IN' => 'fri_in',
            'Fri_OUT' => 'fri_out',
            'Fri_STATUS' => 'fri_status',
            'Fri_Total HRS' => 'fri_total_hrs',
            'Fri_OP' => 'fri_op',
            'Fri_M' => 'fri_m',
            'Fri_L' => 'fri_l',
            'Fri_C' => 'fri_c',
            'Fri_status formula' => 'fri_status_formula',
            'Fri_Hours $' => 'fri_hours_cost',
            'Fri_Hours' => 'fri_hours',
            'Fri_Sales' => 'fri_sales',
            'Fri_4hrs' => 'fri_4hrs',

            // Saturday fields
            'Sat_VCI44' => 'sat_vci44',
            'Sat_IN' => 'sat_in',
            'Sat_OUT' => 'sat_out',
            'Sat_STATUS' => 'sat_status',
            'Sat_Total HRS' => 'sat_total_hrs',
            'Sat_OP' => 'sat_op',
            'Sat_M' => 'sat_m',
            'Sat_L' => 'sat_l',
            'Sat_C' => 'sat_c',
            'Sat_status formula' => 'sat_status_formula',
            'Sat_Hours $' => 'sat_hours_cost',
            'Sat_Hours' => 'sat_hours',
            'Sat_Sales' => 'sat_sales',
            'Sat_4hrs' => 'sat_4hrs',

            // Sunday fields
            'Sun_VCI' => 'sun_vci',
            'Sun_IN' => 'sun_in',
            'Sun_OUT' => 'sun_out',
            'Sun_STATUS' => 'sun_status',
            'Sun_Total HRS' => 'sun_total_hrs',
            'Sun_OP' => 'sun_op',
            'Sun_M' => 'sun_m',
            'Sun_L' => 'sun_l',
            'Sun_C' => 'sun_c',
            'Sun_status formula' => 'sun_status_formula',
            'Sun_Hours $' => 'sun_hours_cost',
            'Sun_Hours' => 'sun_hours',
            'Sun_Sales' => 'sun_sales',
            'Sun_4hrs' => 'sun_4hrs',

            // Monday fields
            'Mon_VCI' => 'mon_vci',
            'Mon_IN' => 'mon_in',
            'Mon_OUT' => 'mon_out',
            'Mon_STATUS' => 'mon_status',
            'Mon_Total HRS' => 'mon_total_hrs',
            'Mon_OP' => 'mon_op',
            'Mon_M' => 'mon_m',
            'Mon_L' => 'mon_l',
            'Mon_C' => 'mon_c',
            'Mon_Status formula' => 'mon_status_formula',
            'Mon_Hours $' => 'mon_hours_cost',
            'Mon_Hours' => 'mon_hours',
            'Mon_Sales' => 'mon_sales',
            'Mon_4hrs' => 'mon_4hrs',

            // Additional fields
            'X' => 'x',
            'OJE' => 'oje',
            'Off Both WE' => 'off_both_we',
            'Status not filled' => 'status_not_filled',
            'Hours not given' => 'hours_not_given',
            'DH not Scheduled' => 'dh_not_scheduled',
            'Headcount' => 'headcount',
            'Weekend & Not fillint satus' => 'weekend_not_filling_status',
            'Hours86' => 'weekly_hours',
            'OT Calc' => 'ot_calc',
            'Both Weekends' => 'both_weekends',
            'PX' => 'px',
            'T' => 't',
            'RE' => 're',
            'VCI87' => 'vci87',
            'Excused Absence ' => 'excused_absence',
            'UnExcused Absence ' => 'unexcused_absence',
            'Late' => 'late',
            'Tenure in Months' => 'tenure_in_months',
            'HOURLY BASE PAY_' => 'hourly_base_pay_alt',
            'HOURLY PERFORMANCE PAY_' => 'hourly_performance_pay_alt',
            'TOTALLY PAY_' => 'totally_pay_alt',
            'POSITION_' => 'position_alt',
            'IS_1099' => 'is_1099_alt',
            'Total Pay' => 'total_pay',
        ];
    }

    /**
     * Process field values for proper data types with better validation
     */
    private function processFieldValue($value, string $column): mixed
    {
        // Handle null values
        if ($value === null || $value === '') {
            return null;
        }

        // Handle date fields
        if (in_array($column, ['hired_date', 'schedule_date', 'dob'])) {
            // Excel date numbers need to be converted
            if (is_numeric($value)) {
                try {
                    // Excel date serial number to date conversion
                    $unixTimestamp = ($value - 25569) * 86400;
                    return date('Y-m-d', $unixTimestamp);
                } catch (\Exception $e) {
                    Log::warning("Date conversion error for value {$value} in column {$column}: " . $e->getMessage());
                    return null;
                }
            }
            // ISO date string
            if (is_string($value) && strpos($value, 'T') !== false) {
                try {
                    return date('Y-m-d', strtotime($value));
                } catch (\Exception $e) {
                    Log::warning("Date parsing error for value {$value} in column {$column}: " . $e->getMessage());
                    return null;
                }
            }
            return $value;
        }

        // Handle time fields (decimal hours to time format)
        if ((str_contains($column, '_in') || str_contains($column, '_out')) && $column != "tenure_in_months") {
            if (is_numeric($value)) {
                try {
                    // Convert decimal hours to HH:MM:SS with bounds checking
                    $totalHours = $value * 24;

                    // Cap at 23:59:59 to prevent MySQL time overflow
                    if ($totalHours >= 24) {
                        $totalHours = fmod($totalHours, 24);
                    }

                    $hours = floor($totalHours);
                    $minutes = floor(($totalHours - $hours) * 60);
                    $seconds = floor((($totalHours - $hours) * 60 - $minutes) * 60);

                    // Ensure values are within valid ranges
                    $hours = max(0, min(23, $hours));
                    $minutes = max(0, min(59, $minutes));
                    $seconds = max(0, min(59, $seconds));

                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } catch (\Exception $e) {
                    Log::warning("Time conversion error for value {$value} in column {$column}: " . $e->getMessage());
                    return null;
                }
            }
        }

        // Special handling for tenure_in_months as string
        // Add more explicit logging for tenure_in_months processing
        if ($column === 'tenure_in_months') {
    $decimalValue = is_numeric($value) ? round((float) $value, 2) : null;
    Log::info("Processing tenure_in_months: original value = {$value}, converted to decimal = {$decimalValue}");
    return $decimalValue;
}



        // Handle decimal fields with precision limits
        if (in_array($column, [
            'hourly_base_pay', 'hourly_performance_pay', 'totally_pay', 'maximum_hours',
            'hours_given', 'hourly_base_pay_alt',
            'hourly_performance_pay_alt', 'totally_pay_alt', 'total_pay',
            'weekly_hours', 'ot_calc'
        ])) {
            if (is_numeric($value)) {
                $roundedValue = round(floatval($value), 2);
                if ($roundedValue > 9999999999.99) {
                    Log::warning("Extremely large value detected, capping at 9999999999.99: {$roundedValue} for column {$column}");
                    return 9999999999.99;
                }
                return $roundedValue;
            }
        }

        // Handle hour-related decimal fields
        if (str_contains($column, 'total_hrs') || str_contains($column, 'hours_cost') ||
            str_contains($column, 'hours') || str_contains($column, 'sales')) {
            if (is_numeric($value)) {
                $roundedValue = round(floatval($value), 2);
                if ($roundedValue > 9999999999.99) {
                    Log::warning("Extremely large value detected, capping at 9999999999.99: {$roundedValue} for column {$column}");
                    return 9999999999.99;
                }
                return $roundedValue;
            }
        }

        // Truncate string values that might be too long for varchar fields
        if (is_string($value)) {
            $maxLength = match(true) {
                str_contains($column, 'status') => 50,
                in_array($column, ['tue_vci', 'wed_vc', 'thu_vci', 'fri_vci30', 'sat_vci44', 'sun_vci', 'mon_vci']) => 2,
                in_array($column, ['tue_op', 'wed_op', 'thu_op', 'fri_op', 'sat_op', 'sun_op', 'mon_op']) => 2,
                in_array($column, ['tue_m', 'wed_m', 'thu_m', 'fri_m', 'sat_m', 'sun_m', 'mon_m']) => 2,
                in_array($column, ['tue_l', 'wed_l', 'thu_l', 'fri_l', 'sat_l', 'sun_l', 'mon_l']) => 2,
                in_array($column, ['tue_c', 'wed_c', 'thu_c', 'fri_c', 'sat_c', 'sun_c', 'mon_c']) => 2,
                $column === 'notes' => null,

                default => 50
            };
            if ($maxLength && strlen($value) > $maxLength) {
                Log::info("Truncating value for {$column}: " . substr($value, 0, 20) . "...");
                return substr($value, 0, $maxLength);
            }
        }

        return $value;
    }

public function exportCsv(Request $request, $date)
{
    try {
        Log::info("PizzaSchedule CSV Export - Date: {$date}");

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        // Get data from all three tables (filtering ONLY by date - includes ALL stores)
        $empInfoData = EmpInfo::where('schedule_date', $date)
            ->get()
            ->toArray();

        $attendanceData = AttendanceSchedule::where('schedule_date', $date)
            ->get()
            ->map(function($item) {
                return $item->toArray();
            })
            ->toArray();

        $weeklySummaryData = WeeklyScheduleSummary::where('schedule_date', $date)
            ->get()
            ->toArray();

        if (empty($empInfoData) && empty($attendanceData) && empty($weeklySummaryData)) {
            return response()->json(['message' => 'No data found for the specified date'], 404);
        }

        // Create reverse field mapping (database column to JSON field name)
        $reverseFieldMapping = array_flip($this->getFieldMapping());

        // Get all unique employee IDs with their store combinations
        $allEmpStoreIds = collect()
            ->merge(collect($empInfoData)->map(function($item) {
                return $item['emp_id'] . '|' . $item['store'];
            }))
            ->merge(collect($attendanceData)->map(function($item) {
                return $item['emp_id'] . '|' . $item['store'];
            }))
            ->merge(collect($weeklySummaryData)->map(function($item) {
                return $item['emp_id'] . '|' . $item['store'];
            }))
            ->unique()
            ->filter()
            ->sort()
            ->values();

        if ($allEmpStoreIds->isEmpty()) {
            return response()->json(['message' => 'No employee data found'], 404);
        }

        // Index data by emp_id + store combination to avoid overwrites
        $empInfoByKey = collect($empInfoData)->keyBy(function($item) {
            return $item['emp_id'] . '|' . $item['store'];
        });
        $attendanceByKey = collect($attendanceData)->keyBy(function($item) {
            return $item['emp_id'] . '|' . $item['store'];
        });
        $weeklyByKey = collect($weeklySummaryData)->keyBy(function($item) {
            return $item['emp_id'] . '|' . $item['store'];
        });

        // Prepare CSV data
        $csvData = [];

        // Headers - Include Store column
        $headers = ['ScheduleDate', 'Store'];
        $fieldMapping = $this->getFieldMapping();
        foreach ($fieldMapping as $jsonField => $dbColumn) {
            $headers[] = $jsonField;
        }
        $csvData[] = $headers;

        // Data rows - Process ALL employees from ALL stores
        foreach ($allEmpStoreIds as $empStoreId) {
            list($empId, $store) = explode('|', $empStoreId);

            $row = [
                'ScheduleDate' => $date,
                'Store' => $store
            ];

            $empInfo = $empInfoByKey->get($empStoreId);
            $attendance = $attendanceByKey->get($empStoreId);
            $weekly = $weeklyByKey->get($empStoreId);

            // Process all other fields from field mapping
            foreach ($fieldMapping as $jsonField => $dbColumn) {
                $value = null;

                if ($empInfo && isset($empInfo[$dbColumn])) {
                    $value = $empInfo[$dbColumn];
                } elseif ($attendance && isset($attendance[$dbColumn])) {
                    $value = $attendance[$dbColumn];
                } elseif ($weekly && isset($weekly[$dbColumn])) {
                    $value = $weekly[$dbColumn];
                }

                $row[$jsonField] = $this->formatCsvValue($value, $dbColumn);
            }

            // Create ordered row based on headers
            $orderedRow = [];
            foreach ($headers as $header) {
                $orderedRow[] = $row[$header] ?? '';
            }

            $csvData[] = $orderedRow;
        }

        // Generate CSV content
        $csvContent = $this->arrayToCsv($csvData);

        // Filename includes date only (data from all stores)
        $filename = "pizza_schedule_all_stores_{$date}.csv";

        Log::info("PizzaSchedule CSV Export - Successfully exported " . (count($csvData) - 1) . " records from all stores");

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    } catch (\Exception $e) {
        Log::error('PizzaSchedule CSV export error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'message' => 'Error occurred during CSV export',
            'error' => $e->getMessage()
        ], 500);
    }
}



/**
 * Format values for CSV export
 */
private function formatCsvValue($value, string $column): string
{
    if ($value === null) {
        return '';
    }

    // Handle date fields - convert back to readable format
    if (in_array($column, ['hired_date', 'schedule_date', 'dob'])) {
        if ($value) {
            try {
                // Handle both date objects and date strings
                if (is_string($value)) {
                    return date('m/d/Y', strtotime($value));
                }
                return date('m/d/Y', strtotime($value));
            } catch (\Exception $e) {
                return (string)$value;
            }
        }
    }

    // Handle time fields - they should already be in HH:MM:SS format from database
    if ((str_contains($column, '_in') || str_contains($column, '_out')) && $column != "tenure_in_months") {
        if ($value) {
            // If it's already a time string, return as is
            if (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                return $value;
            }
            // If it's a time object, convert to string
            return (string)$value;
        }
        return '';
    }

    // Handle numeric fields - format appropriately
    if (is_numeric($value)) {
        // For currency/pay fields, format with 2 decimals
        if (in_array($column, [
            'hourly_base_pay', 'hourly_performance_pay', 'totally_pay',
            'hourly_base_pay_alt', 'hourly_performance_pay_alt', 'totally_pay_alt', 'total_pay'
        ])) {
            return number_format((float)$value, 2, '.', '');
        }

        // For hours and tenure, format with 2 decimals
        if (str_contains($column, 'hours') || str_contains($column, 'hrs') ||
            $column === 'weekly_hours' || $column === 'tenure_in_months' || $column === 'ot_calc') {
            return number_format((float)$value, 2, '.', '');
        }

        // For sales fields
        if (str_contains($column, 'sales')) {
            return number_format((float)$value, 2, '.', '');
        }

        // For other numeric fields, preserve original format
        return (string)$value;
    }

    // Handle boolean fields
    if (in_array($column, ['is_1099', 'is_1099_alt'])) {
        return $value ? '1' : '0';
    }

    // Return as string, handling any special characters for CSV
    return (string)$value;
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
