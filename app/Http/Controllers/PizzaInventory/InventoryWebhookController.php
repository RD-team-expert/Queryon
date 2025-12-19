<?php

namespace App\Http\Controllers\PizzaInventory;

use App\Http\Controllers\Controller;
use App\Models\PizzaInventory\InventorySubmission;
use App\Models\PizzaInventory\InventoryItem;
use App\Models\PizzaInventory\InventoryItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InventoryWebhookController extends Controller
{
    public function create(Request $request)
    {
        return $this->upsertSubmission($request, 'create');
    }

    public function update(Request $request)
    {
        return $this->upsertSubmission($request, 'update');
    }

    public function delete(Request $request)
    {
        $data = $request->json()->all();

        $submissionNumber = data_get($data, 'Entry.Number');
        if (!$submissionNumber) {
            return response()->json(['status' => 'error', 'message' => 'Missing Entry.Number'], 400);
        }

        InventorySubmission::where('external_submission_number', $submissionNumber)->delete();

        Log::info('Inventory webhook delete', [
            'external_submission_number' => $submissionNumber,
            'entry_action' => data_get($data, 'Entry.Action'),
        ]);

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Export CSV of all submissions + items + units
     */
public function exportCsv()
    {
        $submissions = InventorySubmission::with(['items.units', 'items.catalog'])
            ->orderByDesc('id')
            ->get();

        $headers = [
            'external_submission_number',
            'emp_name',
            'store_manager_name',
            'store',
            'email',
            'phone',
            'date',
            'inventory_type',
            'is_accepted',
            'rejection_reason',
            'item_key',
            'item_name',
            'unit_key',
            'value',
        ];

        $filename = 'pizza_inventory_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Response::streamDownload(function () use ($submissions, $headers) {
            $out = fopen('php://output', 'w');

            // ✅ UTF-8 BOM for Excel / Arabic correctness
            fprintf($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);

            foreach ($submissions as $submission) {
                $status = $submission->is_accepted === true
                    ? 'Approved'
                    : ($submission->is_accepted === false ? 'Rejected' : '');

                $date = optional($submission->date)->format('Y-m-d');

                // Submission without items
                if ($submission->items->isEmpty()) {
                    fputcsv($out, [
                        $submission->external_submission_number,
                        $submission->emp_name,
                        $submission->store_manager_name,
                        $submission->store,
                        $submission->email,
                        $submission->phone,
                        $date,
                        $submission->inventory_type,
                        $status,
                        $submission->rejection_reason,
                        '',
                        '',
                        '',
                        '',
                    ]);
                    continue;
                }

                foreach ($submission->items as $item) {
                    $itemName = $item->catalog?->item_name ?? '';

                    // Item without units
                    if ($item->units->isEmpty()) {
                        fputcsv($out, [
                            $submission->external_submission_number,
                            $submission->emp_name,
                            $submission->store_manager_name,
                            $submission->store,
                            $submission->email,
                            $submission->phone,
                            $date,
                            $submission->inventory_type,
                            $status,
                            $submission->rejection_reason,
                            $item->item_key,
                            $itemName,
                            '',
                            '',
                        ]);
                        continue;
                    }

                    // One row per unit
                    foreach ($item->units as $unit) {
                        fputcsv($out, [
                            $submission->external_submission_number,
                            $submission->emp_name,
                            $submission->store_manager_name,
                            $submission->store,
                            $submission->email,
                            $submission->phone,
                            $date,
                            $submission->inventory_type,
                            $status,
                            $submission->rejection_reason,
                            $item->item_key,
                            $itemName,
                            $unit->unit_key,
                            $unit->value,
                        ]);
                    }
                }
            }

            fclose($out);
        }, $filename, [
            // ✅ Ensure UTF-8 is declared
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // -------------------------
    // Core webhook logic
    // -------------------------

    private function upsertSubmission(Request $request, string $endpointType)
    {
        $data = $request->json()->all();

        // Minimal logs only (don’t log full payload: PII + big signature)
        Log::info('Inventory webhook received', [
            'endpoint' => $endpointType,
            'external_submission_number' => data_get($data, 'Entry.Number'),
            'entry_action' => data_get($data, 'Entry.Action'),
            'inventory_type' => data_get($data, 'InventoryType'),
            'date' => data_get($data, 'TodaysDate'),
        ]);

        // Validate required pieces
        $validator = Validator::make($data, [
            'Entry.Number' => 'required',
            'TodaysDate' => 'nullable|date',
            'InventoryType' => 'nullable|string',
            'Email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payload',
                'errors' => $validator->errors(),
            ], 422);
        }

        $submissionNumber = (int) data_get($data, 'Entry.Number');

        try {
            $result = DB::transaction(function () use ($data, $submissionNumber) {

                // Upsert submission
                $mapped = $this->mapSubmission($data);

                /** @var InventorySubmission $submission */
                $submission = InventorySubmission::updateOrCreate(
                    ['external_submission_number' => $submissionNumber],
                    $mapped
                );

                // Idempotency: always rebuild items/units from payload
                // (safe on retries + ensures update correctness)
                $submission->items()->delete(); // cascades units

                // Insert items/units from the correct inventory array
                $this->storeInventory($submission, $data);

                return $submission;
            });

            return response()->json([
                'status' => $endpointType === 'create' ? 'created' : 'updated',
                'external_submission_number' => $result->external_submission_number,
            ]);
        } catch (\Throwable $e) {
            Log::error('Inventory webhook failed', [
                'endpoint' => $endpointType,
                'external_submission_number' => $submissionNumber,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 500);
        }
    }

    private function mapSubmission(array $data): array
    {
        $acceptanceValue = data_get($data, 'CorrespondenceInternalUseOnly.AcceptanceRejection');
        $isAccepted = null;

        if (is_string($acceptanceValue)) {
            // Only treat explicit values as meaningful
            $isAccepted = ($acceptanceValue === 'Approved') ? true : (($acceptanceValue === 'Rejected') ? false : null);
        }

        // Optional: parse Entry.DateUpdated to datetime
        $entryDateUpdated = data_get($data, 'Entry.DateUpdated');

        return [
            'external_submission_number' => (int) data_get($data, 'Entry.Number'),

            'emp_name' => trim((string) data_get($data, 'Name.First', '') . ' ' . (string) data_get($data, 'Name.Last', '')),
            'store_manager_name' => trim((string) data_get($data, 'StoreManagerName.First', '') . ' ' . (string) data_get($data, 'StoreManagerName.Last', '')),
            'phone' => data_get($data, 'Phone'),
            'email' => data_get($data, 'Email'),
            'date' => data_get($data, 'TodaysDate'),
            'store' => data_get($data, 'Store'),
            'inventory_type' => data_get($data, 'InventoryType'),

            'is_accepted' => $isAccepted,
            'rejection_reason' => data_get($data, 'CorrespondenceInternalUseOnly.RejectionReason'),

        ];
    }

    /**
     * Pick the correct inventory array from payload and store.
     * Supports "Daily - يومي", "Weekly - اسبوعي", "Period - دوري"
     */
    private function storeInventory(InventorySubmission $submission, array $data): void
    {
        $inventoryType = (string) ($submission->inventory_type ?? '');

        $map = [
            'Daily - يومي' => 'DailyInventory',
            'Weekly - اسبوعي' => 'WeeklyInventory',
            'Period - دوري' => 'PeriodInventory',
        ];

        $arrayName = $map[$inventoryType] ?? null;

        // If missing/unknown, try best-effort fallback:
        if (!$arrayName) {
            // Choose the first non-empty inventory array if inventory_type is unexpected
            foreach (['DailyInventory', 'WeeklyInventory', 'PeriodInventory'] as $candidate) {
                if (!empty($data[$candidate]) && is_array($data[$candidate])) {
                    $arrayName = $candidate;
                    break;
                }
            }
        }

        if (!$arrayName || empty($data[$arrayName]) || !is_array($data[$arrayName])) {
            return; // nothing to store
        }

        foreach ($data[$arrayName] as $itemKey => $units) {
            if (!is_array($units)) {
                continue;
            }

            // Create the item (unique(submission_id,item_key) protects on edge)
            $item = InventoryItem::create([
                'submission_id' => $submission->id,
                'item_key' => (string) $itemKey,
            ]);

            foreach ($units as $unitKey => $value) {
                if ($this->isEmptyValue($value)) {
                    continue;
                }

                $numeric = $this->cleanNumericValue($value);

                // Skip values we can't parse as numeric
                if ($numeric === null) {
                    continue;
                }

                InventoryItemUnit::create([
                    'item_id' => $item->id,
                    'unit_key' => (string) $unitKey,
                    'value' => $numeric,
                ]);
            }
        }
    }

    private function isEmptyValue($value): bool
    {
        if ($value === null) return true;
        if ($value === '') return true;
        if (is_string($value) && strtolower(trim($value)) === 'null') return true;
        return false;
    }

    /**
     * Convert numeric-ish values to float (supports strings like "1,234.50", "$12", "  5 ")
     */
    private function cleanNumericValue($value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') return null;

        // Remove commas and currency-ish characters but keep minus and dot
        $cleaned = preg_replace('/[^\d\.\-]/', '', str_replace(',', '', $value));

        if ($cleaned === '' || $cleaned === '-' || $cleaned === '.') {
            return null;
        }

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }


}
