<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pizza_AV_Team_Pay_Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PizzaPayController extends Controller
{
    /**
     * Store or update pizza pay data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming JSON array
            $validator = Validator::make($request->all(), [
                '*.store' => 'nullable|integer',
                '*.date' => 'nullable|date',
                '*.emp_id' => 'nullable|integer',
                '*.name' => 'nullable|string|max:255',
                '*.position' => 'nullable|string|max:255',
                '*.hourly_pay' => 'nullable|numeric',
                '*.total_hours' => 'nullable|numeric',
                '*.total_tips' => 'nullable|numeric',
                '*.positive' => 'nullable|numeric',
                '*.money_owed' => 'nullable|numeric',
                '*.amazon_wm_others' => 'nullable|numeric',
                '*.base_pay' => 'nullable|numeric',
                '*.performance_bonus' => 'nullable|numeric',
                '*.gross_pay' => 'nullable|numeric',
                '*.team_profit_sharing' => 'nullable|numeric',
                '*.bread_boost_bonus' => 'nullable|numeric',
                '*.extra_pay' => 'nullable|numeric',
                '*.total_deduction' => 'nullable|numeric',
                '*.tax_allowans' => 'nullable|numeric',
                '*.rent_pmt' => 'nullable|numeric',
                '*.phone_pmt' => 'nullable|numeric',
                '*.utilities' => 'nullable|numeric',
                '*.others' => 'nullable|numeric',
                '*.company_loan' => 'nullable|numeric',
                '*.legal' => 'nullable|numeric',
                '*.car' => 'nullable|numeric',
                '*.labor' => 'nullable|numeric',
                '*.lc_audit' => 'nullable|string|max:255',
                '*.customer_service' => 'nullable|numeric',
                '*.upselling' => 'nullable|numeric',
                '*.inventory' => 'nullable|numeric',
                '*.pne_audit_fail' => 'nullable|numeric',
                '*.sales' => 'nullable|numeric',
                '*.final_score' => 'nullable|numeric',
                '*.total_tax' => 'nullable|numeric',
                '*.tax_dif' => 'nullable|numeric',
                '*.at' => 'nullable|boolean',
                '*.apt_cost' => 'nullable|numeric',
                '*.apt_cost_per_store' => 'nullable|numeric',
                '*.utilities_cost' => 'nullable|numeric',
                '*.phone_cost' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Check if data is empty
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data provided'
                ], 400);
            }

            // Upsert the data
            // The second parameter specifies the unique columns (store, date, emp_id)
            // The third parameter specifies which columns to update if record exists
            Pizza_AV_Team_Pay_Model::upsert(
                $data,
                ['store', 'date', 'emp_id'],
                [
                    'name',
                    'position',
                    'hourly_pay',
                    'total_hours',
                    'total_tips',
                    'positive',
                    'money_owed',
                    'amazon_wm_others',
                    'base_pay',
                    'performance_bonus',
                    'gross_pay',
                    'team_profit_sharing',
                    'bread_boost_bonus',
                    'extra_pay',
                    'total_deduction',
                    'tax_allowans',
                    'rent_pmt',
                    'phone_pmt',
                    'utilities',
                    'others',
                    'company_loan',
                    'legal',
                    'car',
                    'labor',
                    'lc_audit',
                    'customer_service',
                    'upselling',
                    'inventory',
                    'pne_audit_fail',
                    'sales',
                    'final_score',
                    'total_tax',
                    'tax_dif',
                    'at',
                    'apt_cost',
                    'apt_cost_per_store',
                    'utilities_cost',
                    'phone_cost',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data processed successfully',
                'records_processed' => count($data)
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function exportCsv(Request $request)
{
    $fileName = 'pizza_av_team_pay_' . date('Y-m-d_His') . '.csv';

    // Get all data or apply filters if needed
    $query = Pizza_AV_Team_Pay_Model::query();

    // Optional: Add filters based on request parameters
    if ($request->has('store')) {
        $query->where('store', $request->store);
    }

    if ($request->has('date_from')) {
        $query->where('date', '>=', $request->date_from);
    }

    if ($request->has('date_to')) {
        $query->where('date', '<=', $request->date_to);
    }

    if ($request->has('emp_id')) {
        $query->where('emp_id', $request->emp_id);
    }

    $data = $query->get();

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = [
        'ID',
        'Store',
        'Date',
        'Employee ID',
        'Name',
        'Position',
        'Hourly Pay',
        'Total Hours',
        'Total Tips',
        'Positive',
        'Money Owed',
        'Amazon / WM / Others',
        'BASE PAY',
        'PERFORMANCE BONUS',
        'Gross Pay',
        'Team Profit Sharing',
        'Bread Boost Bonus',
        'EXTRA PAY',
        'TOTAL DEDUCTION',
        'Tax Allowans',
        'Rent pmt',
        'Phone pmt',
        'Utilities',
        'Others',
        'Company Loan',
        'Legal',
        'Car',
        'Labor',
        'LC Audit',
        'Customer Service',
        'Upselling',
        'Inventory',
        'PNE Audit Fail',
        'Sales',
        'Final Score',
        'Total Tax',
        'Tax dif',
        'AT',
        'Apt Cost',
        'Apt Cost Per Store',
        'Utilities Cost',
        'Phone Cost',
        'Created At',
        'Updated At'
    ];

    $callback = function() use($data, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($data as $row) {
            $csvRow = [
                $row->id,
                $row->store,
                $row->date,
                $row->emp_id,
                $row->name,
                $row->position,
                $row->hourly_pay,
                $row->total_hours,
                $row->total_tips,
                $row->positive,
                $row->money_owed,
                $row->amazon_wm_others,
                $row->base_pay,
                $row->performance_bonus,
                $row->gross_pay,
                $row->team_profit_sharing,
                $row->bread_boost_bonus,
                $row->extra_pay,
                $row->total_deduction,
                $row->tax_allowans,
                $row->rent_pmt,
                $row->phone_pmt,
                $row->utilities,
                $row->others,
                $row->company_loan,
                $row->legal,
                $row->car,
                $row->labor,
                $row->lc_audit,
                $row->customer_service,
                $row->upselling,
                $row->inventory,
                $row->pne_audit_fail,
                $row->sales,
                $row->final_score,
                $row->total_tax,
                $row->tax_dif,
                $row->at ? 'Yes' : 'No',
                $row->apt_cost,
                $row->apt_cost_per_store,
                $row->utilities_cost,
                $row->phone_cost,
                $row->created_at,
                $row->updated_at
            ];

            fputcsv($file, $csvRow);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
