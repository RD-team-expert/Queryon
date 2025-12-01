<?php

namespace App\Http\Controllers\PizzaInventory;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PizzaInventory\Submission;
use App\Models\PizzaInventory\Item;
use App\Models\PizzaInventory\ItemUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->json()->all();
        Log::info('Webhook Create called', ['payload' => $data]);

        try {
            DB::transaction(function () use ($data) {
                $mapped = $this->mapSubmission($data);

                $submission = Submission::updateOrCreate(
                    ['submission_id' => $mapped['submission_id']],
                    $mapped
                );

                $this->loopInventory($submission->submission_id, $data, $mapped['inventory_type']);
            });

            Log::info('Submission created successfully', [
                'submission_id' => $data['Entry']['Number'] ?? null,
            ]);

            return response()->json(['status' => 'created']);
        } catch (\Throwable $e) {
            Log::error('Error on Create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Create failed'], 500);
        }
    }

    public function update(Request $request)
    {
        $data = $request->json()->all();
        Log::info('Webhook Update called', ['payload' => $data]);

        $submissionId = $data['Entry']['Number'] ?? null;
        if (!$submissionId) {
            Log::warning('Update called but missing submission_id', ['payload' => $data]);
            return response()->json(['status' => 'error', 'message' => 'Missing submission id'], 400);
        }

        try {
            DB::transaction(function () use ($data, $submissionId) {
                $submission = Submission::where('submission_id', $submissionId)->first();

                if ($submission) {
                    $submission->update($this->mapSubmission($data));

                    // delete related items and units (units cascade from items)
                    Item::where('submission_id', $submissionId)->delete();

                    $this->loopInventory(
                        $submissionId,
                        $data,
                        $data['InventoryType'] ?? $submission->inventory_type
                    );

                    Log::info('Submission and items updated', ['submission_id' => $submissionId]);
                } else {
                    Log::warning('Update tried for missing submission', ['submission_id' => $submissionId]);
                }
            });

            return response()->json(['status' => 'updated']);
        } catch (\Throwable $e) {
            Log::error('Error on Update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Update failed'], 500);
        }
    }

    public function delete(Request $request)
    {
        $data = $request->json()->all();
        Log::info('Webhook Delete called', ['payload' => $data]);

        $submissionId = $data['Entry']['Number'] ?? null;
        if (!$submissionId) {
            Log::warning('Delete called but missing submission_id', ['payload' => $data]);
            return response()->json(['status' => 'error', 'message' => 'Missing submission id'], 400);
        }

        try {
            Submission::where('submission_id', $submissionId)->delete();
            Log::info('Submission deleted', ['submission_id' => $submissionId]);

            return response()->json(['status' => 'deleted']);
        } catch (\Throwable $e) {
            Log::error('Error on Delete', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Delete failed'], 500);
        }
    }

    // ========== Helpers ==========

    protected function mapSubmission(array $data): array
    {

         $isAccepted = null;
            if (isset($data['CorrespondenceInternalUseOnly']['AcceptanceRejection'])) {
                $acceptanceValue = $data['CorrespondenceInternalUseOnly']['AcceptanceRejection'];
                $isAccepted = ($acceptanceValue === 'Approved');
            }


        return [
            'submission_id'      => $data['Entry']['Number'] ?? null,
            'emp_name'           => trim(($data['Name']['First'] ?? '') . ' ' . ($data['Name']['Last'] ?? '')),
            'store_manager_name' => trim(($data['StoreManagerName']['First'] ?? '') . ' ' . ($data['StoreManagerName']['Last'] ?? '')),
            'phone'              => $data['Phone'] ?? null,
            'email'              => $data['Email'] ?? null,
            'date'               => $data['TodaysDate'] ?? null,
            'store'              => $data['Store'] ?? null,
            'inventory_type'     => $data['InventoryType'] ?? null,
            'is_accepted'           => $isAccepted ?? null,
            'rejection_reason'      => $data['CorrespondenceInternalUseOnly']['RejectionReason'] ?? null,
        ];
    }

    protected function loopInventory(int $submissionId, array $data, ?string $inventoryType): void
    {
        $arrayMap = [
            'Daily - يومي'    => 'DailyInventory',
            'Weekly - اسبوعي' => 'WeeklyInventory',
            'Period - دوري'   => 'PeriodInventory',
        ];

        $arrayName = $arrayMap[$inventoryType] ?? null;
        if (!$arrayName || empty($data[$arrayName]) || !is_array($data[$arrayName])) {
            Log::info('No inventory array to loop', [
                'submission_id'  => $submissionId,
                'inventory_type' => $inventoryType,
            ]);
            return;
        }

        foreach ($data[$arrayName] as $itemKey => $units) {
            if (!is_array($units)) {
                Log::warning('Invalid units array for item', [
                    'item_key' => $itemKey,
                    'units' => $units,
                ]);
                continue;
            }

            $item = Item::create([
                'submission_id' => $submissionId,
                'item'          => $itemKey,
            ]);

            foreach ($units as $unitName => $value) {
                // Skip null/empty values
                if ($value === null || $value === '' || $value === 'null') {
                    continue;
                }

                // Clean and parse value - returns null if cannot parse
                $cleanValue = $this->cleanNumericValue($value);

                // If cannot parse to number, skip (treat as null)
                if ($cleanValue === null) {
                    Log::warning('Skipped unparseable numeric value', [
                        'item_id' => $item->id,
                        'unit_name' => $unitName,
                        'raw_value' => $value,
                    ]);
                    continue;
                }

                ItemUnit::create([
                    'item_id' => $item->id,
                    'name'    => $unitName,
                    'value'   => $cleanValue,
                ]);
            }
        }
    }

    /**
     * Clean and convert value to float. Returns null if cannot parse.
     */
    private function cleanNumericValue($value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);
        $cleaned = preg_replace('/[^\d.-]/', '', $value);

        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return null; // Cannot parse = null (skipped)
    }
}
