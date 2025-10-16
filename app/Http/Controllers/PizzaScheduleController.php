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

        if (!isset($jsonData['rows']) || !is_array($jsonData['rows'])) {
            return response()->json(['message' => 'Invalid data: rows missing or not an array'], 422);
        }

        if (!isset($jsonData['Store']) || !isset($jsonData['ScheduleDate'])) {
            return response()->json(['message' => 'Store and ScheduleDate are required'], 422);
        }

        $store = $jsonData['Store'];
        $scheduleDate = $jsonData['ScheduleDate'];
        $rows = $jsonData['rows'];

        Log::info("PizzaSchedule - Processing {$store} for {$scheduleDate}: " . count($rows) . " rows");

        // Build data arrays
        [$empInfoData, $attendanceData, $weeklySummaryData] = $this->buildDataArrays($rows, $store, $scheduleDate);

        if (empty($empInfoData)) {
            return response()->json(['message' => 'No valid employee data to process'], 422);
        }

        $totalProcessed = 0;

        DB::transaction(function () use ($empInfoData, $attendanceData, $weeklySummaryData, $store, $scheduleDate, &$totalProcessed) {

            // STEP 1: Upsert EmpInfo (parent records)
            $this->upsertEmpInfo($empInfoData);

            // STEP 2: Get emp_info ID mapping
            $empInfoMap = $this->getEmpInfoMap($store, $scheduleDate);

            // STEP 3: DELETE old child records for this store/date
            $empInfoIds = array_values($empInfoMap);

            $deletedAttendance = AttendanceSchedule::where('store', $store)
                ->where('schedule_date', $scheduleDate)
                ->whereIn('schedule_emp_info_id', $empInfoIds)
                ->delete();

            $deletedWeekly = WeeklyScheduleSummary::where('store', $store)
                ->where('schedule_date', $scheduleDate)
                ->whereIn('schedule_emp_info_id', $empInfoIds)
                ->delete();

            Log::info("Deleted old records - Attendance: {$deletedAttendance}, Weekly: {$deletedWeekly}");

            // STEP 4: INSERT fresh data with simple shift numbering
            $this->insertChildRecords(AttendanceSchedule::class, $attendanceData, $empInfoMap);
            $this->insertChildRecords(WeeklyScheduleSummary::class, $weeklySummaryData, $empInfoMap);

            $totalProcessed = count($empInfoData);
        });

        Log::info("✓ PizzaSchedule - Successfully processed {$totalProcessed} records");

        return response()->json([
            'success' => true,
            'message' => 'Data processed successfully',
            'records_processed' => $totalProcessed,
            'tables_updated' => [
                'emp_info' => count($empInfoData),
                'attendance_schedule' => count($attendanceData),
                'weekly_schedule_summary' => count($weeklySummaryData)
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('PizzaSchedule Error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Insert child records with simple sequential shift counting
 */
private function insertChildRecords($model, array $data, array $empInfoMap): void
{
    if (empty($data)) {
        Log::info("No data to insert for " . class_basename($model));
        return;
    }

    $processedData = [];
    $shiftCounters = [];

    foreach ($data as $row) {
        if (!isset($row['emp_id']) || !isset($empInfoMap[$row['emp_id']])) {
            continue;
        }

        $empInfoId = $empInfoMap[$row['emp_id']];
        $row['schedule_emp_info_id'] = $empInfoId;

        // Simple sequential shift counting per employee
        if (!isset($shiftCounters[$empInfoId])) {
            $shiftCounters[$empInfoId] = 0;
        }

        $shiftCounters[$empInfoId]++;
        $row['shift_count'] = $shiftCounters[$empInfoId];

        // Remove emp_id since we have schedule_emp_info_id
        unset($row['emp_id']);

        $processedData[] = $row;
    }

    // Insert in chunks
    if (!empty($processedData)) {
        $chunks = array_chunk($processedData, 100);
        foreach ($chunks as $chunk) {
            $model::insert($chunk);
        }
        Log::info("✓ Inserted " . count($processedData) . " " . class_basename($model) . " records");
    }
}


    /**
     * Build data arrays from JSON rows
     */
    private function buildDataArrays(array $rows, string $store, string $scheduleDate): array
    {
        $empInfoData = [];
        $attendanceData = [];
        $weeklySummaryData = [];
        $fieldMapping = $this->getFieldMapping();

        foreach ($rows as $index => $row) {
            try {
                $empRow = ['store' => $store, 'schedule_date' => $scheduleDate];
                $attRow = ['store' => $store, 'schedule_date' => $scheduleDate];
                $weekRow = ['store' => $store, 'schedule_date' => $scheduleDate];

                foreach ($row as $jsonKey => $value) {
                    if (!isset($fieldMapping[$jsonKey])) continue;

                    $dbColumn = $fieldMapping[$jsonKey];
                    $processedValue = $this->processFieldValue($value, $dbColumn);

                    // Distribute to appropriate tables
                    if ($dbColumn === 'emp_id' || $dbColumn === 'name') {
                        $empRow[$dbColumn] = $processedValue;
                        $attRow[$dbColumn] = $processedValue;
                        $weekRow[$dbColumn] = $processedValue;
                    } elseif ($this->isEmpInfoField($dbColumn)) {
                        $empRow[$dbColumn] = $processedValue;
                    } elseif ($this->isAttendanceField($dbColumn)) {
                        $attRow[$dbColumn] = $processedValue;
                    } elseif ($this->isWeeklySummaryField($dbColumn)) {
                        $weekRow[$dbColumn] = $processedValue;
                    }
                }

                if (isset($empRow['emp_id'])) {
                    $empInfoData[] = $empRow;
                    $attendanceData[] = $attRow;
                    $weeklySummaryData[] = $weekRow;
                }

            } catch (\Exception $e) {
                Log::warning("Error processing row {$index}: " . $e->getMessage());
            }
        }

        Log::info("Built data: EmpInfo=" . count($empInfoData) . ", Attendance=" . count($attendanceData) . ", Weekly=" . count($weeklySummaryData));

        return [$empInfoData, $attendanceData, $weeklySummaryData];
    }

    /**
     * Upsert EmpInfo records
     */
    private function upsertEmpInfo(array $data): void
    {
        $chunks = array_chunk($data, 100);

        foreach ($chunks as $chunk) {
            $updateColumns = array_diff((new EmpInfo)->getFillable(), ['store', 'schedule_date', 'emp_id']);
            EmpInfo::upsert($chunk, ['store', 'schedule_date', 'emp_id'], $updateColumns);
        }

        Log::info("✓ Upserted " . count($data) . " EmpInfo records");
    }

    /**
     * Get emp_id to emp_info.id mapping
     */
    private function getEmpInfoMap(string $store, string $scheduleDate): array
    {
        return EmpInfo::where('store', $store)
            ->where('schedule_date', $scheduleDate)
            ->pluck('id', 'emp_id')
            ->toArray();
    }


    private function isEmpInfoField(string $field): bool
    {
        return in_array($field, [
            'hired_date', 'hourly_base_pay', 'hourly_performance_pay', 'totally_pay',
            'position', 'is_1099', 'uniform', 'num_of_shirts', 'formula_emp_not_getting_hours_wanted',
            'at_only', 'family', 'car', 'dob', 'red_in_schedule', 'reads_in_schedule', 'emp_id_alt',
            'new_team_member', 'da_safety_score', 'attendance', 'score', 'notes', 'num_of_days',
            'reads_in_schedule_2', 'cross_trained', 'preference', 'pt_ft', 'name_alt', 'rating',
            'maximum_hours', 'hours_given'
        ]);
    }

    /**
     * Determine if field belongs to Attendance Schedule table
     */
    private function isAttendanceField(string $field): bool
    {
        return str_contains($field, 'tue_') || str_contains($field, 'wed_') ||
               str_contains($field, 'thu_') || str_contains($field, 'fri_') ||
               str_contains($field, 'sat_') || str_contains($field, 'sun_') ||
               str_contains($field, 'mon_');
    }

    /**
     * Determine if field belongs to Weekly Schedule Summary table
     */
    private function isWeeklySummaryField(string $field): bool
    {
        return in_array($field, [
            'x', 'oje', 'off_both_we', 'status_not_filled', 'hours_not_given',
            'dh_not_scheduled', 'headcount', 'weekend_not_filling_status', 'weekly_hours',
            'ot_calc', 'both_weekends', 'px', 't', 're', 'vci87', 'excused_absence',
            'unexcused_absence', 'late', 'tenure_in_months', 'hourly_base_pay_alt',
            'hourly_performance_pay_alt', 'totally_pay_alt', 'position_alt', 'is_1099_alt', 'total_pay'
        ]);
    }

    /**
     * Get field mapping from JSON keys to database columns
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
     * Process field value based on column type
     */
    private function processFieldValue($value, string $column): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle date fields
        if (in_array($column, ['hired_date', 'schedule_date', 'dob'])) {
            if (is_numeric($value)) {
                try {
                    $unixTimestamp = ($value - 25569) * 86400;
                    return date('Y-m-d', $unixTimestamp);
                } catch (\Exception $e) {
                    Log::warning("Date conversion error for value {$value} in column {$column}: " . $e->getMessage());
                    return null;
                }
            }
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
                    $totalHours = $value * 24;
                    $totalHours = round($totalHours, 10);

                    if ($totalHours >= 24) {
                        $totalHours = fmod($totalHours, 24);
                    }

                    $hours = floor($totalHours);
                    $minutes = floor(($totalHours - $hours) * 60);
                    $seconds = floor((($totalHours - $hours) * 60 - $minutes) * 60);

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

        // Handle tenure_in_months
        if ($column === 'tenure_in_months') {
            return is_numeric($value) ? round((float) $value, 2) : null;
        }

        // Handle decimal fields with precision limits
        if (in_array($column, [
            'hourly_base_pay', 'hourly_performance_pay', 'totally_pay', 'maximum_hours',
            'hours_given', 'hourly_base_pay_alt', 'hourly_performance_pay_alt',
            'totally_pay_alt', 'total_pay', 'weekly_hours', 'ot_calc'
        ])) {
            if (is_numeric($value)) {
                $roundedValue = round(floatval($value), 2);
                if ($roundedValue > 9999999999.99) {
                    Log::warning("Large value detected, capping: {$roundedValue} for column {$column}");
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
                    Log::warning("Large value detected, capping: {$roundedValue} for column {$column}");
                    return 9999999999.99;
                }
                return $roundedValue;
            }
        }

        // Truncate string values
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
                return substr($value, 0, $maxLength);
            }
        }

        return $value;
    }

    public function exportCsv(Request $request, $date = null)
{
    try {
        Log::info("PizzaSchedule CSV Export - Date: " . ($date ?? 'ALL'));

        // Validate format only if date is provided
        if (!empty($date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        // Retrieve data: if date is provided -> filter; otherwise get all
        $empInfoQuery = EmpInfo::with(['attendanceSchedules', 'weeklyScheduleSummaries']);

        if (!empty($date)) {
            $empInfoQuery->where('schedule_date', $date);
        }

        $empInfoData = $empInfoQuery->get();

        if ($empInfoData->isEmpty()) {
            return response()->json(['message' => 'No data found' . ($date ? " for {$date}" : '')], 404);
        }

        $fieldMapping = $this->getFieldMapping();
        $csvData = [];

        // Headers
        $headers = ['ScheduleDate', 'Store', 'Shift_Count'];
        foreach ($fieldMapping as $jsonField => $dbColumn) {
            $headers[] = $jsonField;
        }
        $csvData[] = $headers;

        // Process each EmpInfo record
        foreach ($empInfoData as $empInfo) {
            $attendanceRecords = $empInfo->attendanceSchedules->sortBy('shift_count');
            $weeklyRecords = $empInfo->weeklyScheduleSummaries->sortBy('shift_count');

            $scheduleDate = $empInfo->schedule_date ?? ($date ?? 'N/A');

            if ($attendanceRecords->isEmpty() && $weeklyRecords->isEmpty()) {
                $row = [
                    'ScheduleDate' => $scheduleDate,
                    'Store' => $empInfo->store,
                    'Shift_Count' => 1
                ];

                foreach ($fieldMapping as $jsonField => $dbColumn) {
                    $value = $empInfo->getAttribute($dbColumn);
                    $row[$jsonField] = $this->formatCsvValue($value, $dbColumn);
                }

                $orderedRow = [];
                foreach ($headers as $header) {
                    $orderedRow[] = $row[$header] ?? '';
                }
                $csvData[] = $orderedRow;
            } else {
                $maxShifts = max($attendanceRecords->count(), $weeklyRecords->count());

                for ($shiftIndex = 0; $shiftIndex < max(1, $maxShifts); $shiftIndex++) {
                    $attendance = $attendanceRecords->get($shiftIndex);
                    $weekly = $weeklyRecords->get($shiftIndex);

                    $shiftCount = $attendance ? $attendance->shift_count : ($weekly ? $weekly->shift_count : $shiftIndex + 1);

                    $row = [
                        'ScheduleDate' => $scheduleDate,
                        'Store' => $empInfo->store,
                        'Shift_Count' => $shiftCount
                    ];

                    foreach ($fieldMapping as $jsonField => $dbColumn) {
                        $value = null;

                        if ($empInfo->getAttribute($dbColumn) !== null) {
                            $value = $empInfo->getAttribute($dbColumn);
                        } elseif ($attendance && $attendance->getAttribute($dbColumn) !== null) {
                            $value = $attendance->getAttribute($dbColumn);
                        } elseif ($weekly && $weekly->getAttribute($dbColumn) !== null) {
                            $value = $weekly->getAttribute($dbColumn);
                        }

                        $row[$jsonField] = $this->formatCsvValue($value, $dbColumn);
                    }

                    $orderedRow = [];
                    foreach ($headers as $header) {
                        $orderedRow[] = $row[$header] ?? '';
                    }
                    $csvData[] = $orderedRow;
                }
            }
        }

        $csvContent = $this->arrayToCsv($csvData);
        $filename = $date ? "pizza_schedule_all_stores_{$date}.csv" : "pizza_schedule_all_stores_all_dates.csv";

        Log::info("CSV Export - Successfully exported " . (count($csvData) - 1) . " records");

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');

    } catch (\Exception $e) {
        Log::error('CSV export error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'message' => 'Error occurred during CSV export',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Export CSV for a given date
     */
    // public function exportCsv(Request $request, $date)
    // {
    //     try {
    //         Log::info("PizzaSchedule CSV Export - Date: {$date}");

    //         if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    //             return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
    //         }

    //         $empInfoData = EmpInfo::with(['attendanceSchedules', 'weeklyScheduleSummaries'])
    //             ->where('schedule_date', $date)
    //             ->get();

    //         if ($empInfoData->isEmpty()) {
    //             return response()->json(['message' => 'No data found for the specified date'], 404);
    //         }

    //         $fieldMapping = $this->getFieldMapping();
    //         $csvData = [];

    //         // Headers
    //         $headers = ['ScheduleDate', 'Store', 'Shift_Count'];
    //         foreach ($fieldMapping as $jsonField => $dbColumn) {
    //             $headers[] = $jsonField;
    //         }
    //         $csvData[] = $headers;

    //         // Process each EmpInfo record
    //         foreach ($empInfoData as $empInfo) {
    //             $attendanceRecords = $empInfo->attendanceSchedules->sortBy('shift_count');
    //             $weeklyRecords = $empInfo->weeklyScheduleSummaries->sortBy('shift_count');

    //             if ($attendanceRecords->isEmpty() && $weeklyRecords->isEmpty()) {
    //                 $row = [
    //                     'ScheduleDate' => $date,
    //                     'Store' => $empInfo->store,
    //                     'Shift_Count' => 1
    //                 ];

    //                 foreach ($fieldMapping as $jsonField => $dbColumn) {
    //                     $value = $empInfo->getAttribute($dbColumn);
    //                     $row[$jsonField] = $this->formatCsvValue($value, $dbColumn);
    //                 }

    //                 $orderedRow = [];
    //                 foreach ($headers as $header) {
    //                     $orderedRow[] = $row[$header] ?? '';
    //                 }
    //                 $csvData[] = $orderedRow;
    //             } else {
    //                 $maxShifts = max($attendanceRecords->count(), $weeklyRecords->count());

    //                 for ($shiftIndex = 0; $shiftIndex < max(1, $maxShifts); $shiftIndex++) {
    //                     $attendance = $attendanceRecords->get($shiftIndex);
    //                     $weekly = $weeklyRecords->get($shiftIndex);

    //                     $shiftCount = $attendance ? $attendance->shift_count : ($weekly ? $weekly->shift_count : $shiftIndex + 1);

    //                     $row = [
    //                         'ScheduleDate' => $date,
    //                         'Store' => $empInfo->store,
    //                         'Shift_Count' => $shiftCount
    //                     ];

    //                     foreach ($fieldMapping as $jsonField => $dbColumn) {
    //                         $value = null;

    //                         if ($empInfo->getAttribute($dbColumn) !== null) {
    //                             $value = $empInfo->getAttribute($dbColumn);
    //                         } elseif ($attendance && $attendance->getAttribute($dbColumn) !== null) {
    //                             $value = $attendance->getAttribute($dbColumn);
    //                         } elseif ($weekly && $weekly->getAttribute($dbColumn) !== null) {
    //                             $value = $weekly->getAttribute($dbColumn);
    //                         }

    //                         $row[$jsonField] = $this->formatCsvValue($value, $dbColumn);
    //                     }

    //                     $orderedRow = [];
    //                     foreach ($headers as $header) {
    //                         $orderedRow[] = $row[$header] ?? '';
    //                     }
    //                     $csvData[] = $orderedRow;
    //                 }
    //             }
    //         }

    //         $csvContent = $this->arrayToCsv($csvData);
    //         $filename = "pizza_schedule_all_stores_{$date}.csv";

    //         Log::info("CSV Export - Successfully exported " . (count($csvData) - 1) . " records");

    //         return response($csvContent)
    //             ->header('Content-Type', 'text/csv')
    //             ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
    //             ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
    //             ->header('Pragma', 'no-cache')
    //             ->header('Expires', '0');

    //     } catch (\Exception $e) {
    //         Log::error('CSV export error: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());

    //         return response()->json([
    //             'message' => 'Error occurred during CSV export',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Format values for CSV export
     */
    private function formatCsvValue($value, string $column): string
    {
        if ($value === null) {
            return '';
        }

        // Handle date fields
        if (in_array($column, ['hired_date', 'schedule_date', 'dob'])) {
            if ($value) {
                try {
                    if (is_string($value)) {
                        return date('m/d/Y', strtotime($value));
                    }
                    return date('m/d/Y', strtotime($value));
                } catch (\Exception $e) {
                    return (string)$value;
                }
            }
        }

        // Handle time fields
        if ((str_contains($column, '_in') || str_contains($column, '_out')) && $column != "tenure_in_months") {
            if ($value) {
                if (is_string($value) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                    return $value;
                }
                return (string)$value;
            }
            return '';
        }

        // Handle numeric fields
        if (is_numeric($value)) {
            if (in_array($column, [
                'hourly_base_pay', 'hourly_performance_pay', 'totally_pay',
                'hourly_base_pay_alt', 'hourly_performance_pay_alt', 'totally_pay_alt', 'total_pay'
            ])) {
                return number_format((float)$value, 2, '.', '');
            }

            if (str_contains($column, 'hours') || str_contains($column, 'hrs') ||
                $column === 'weekly_hours' || $column === 'tenure_in_months' || $column === 'ot_calc') {
                return number_format((float)$value, 2, '.', '');
            }

            if (str_contains($column, 'sales')) {
                return number_format((float)$value, 2, '.', '');
            }

            return (string)$value;
        }

        // Handle boolean fields
        if (in_array($column, ['is_1099', 'is_1099_alt'])) {
            return $value ? '1' : '0';
        }

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
