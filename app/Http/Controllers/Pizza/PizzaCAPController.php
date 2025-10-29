<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\PizzaCoachingActionPlan;
use App\Models\Pizza\PizzaCoachingAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PizzaCAPController extends Controller
{
    /**
     * Create a new coaching action plan
     */
    public function create(Request $request)
    {
        try {
            $data = $request->json()->all();
            $mappedData = $this->mapJsonToDatabase($data);

            DB::beginTransaction();

            // Create the action plan
            $actionPlan = PizzaCoachingActionPlan::create([
                'cognito_id' => $mappedData['cognito_id'],
                'manager_first_name' => $mappedData['manager_first_name'],
                'manager_last_name' => $mappedData['manager_last_name'],
                'store' => $mappedData['store'],
                'emp_first_name' => $mappedData['emp_first_name'],
                'emp_last_name' => $mappedData['emp_last_name'],
                'description_of_the_incident' => $mappedData['description_of_the_incident'],
                'coaching_plan' => $mappedData['coaching_plan'],
                'date' => $mappedData['date'],
                'cap_type' => $mappedData['cap_type'],
                're_evaluation_after' => $mappedData['re_evaluation_after'],
                'director_first_name' => $mappedData['director_first_name'],
                'director_last_name' => $mappedData['director_last_name'],
                'director_is_accepted' => $mappedData['director_is_accepted'],
                'director_rejection_reason' => $mappedData['director_rejection_reason'],
            ]);

            // Create associated actions
            if (!empty($mappedData['actions'])) {
                foreach ($mappedData['actions'] as $actionName) {
                    PizzaCoachingAction::create([
                        'cognito_id' => $mappedData['cognito_id'],
                        'action_name' => $actionName,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coaching action plan created successfully',
                'data' => $actionPlan->load('actions')
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating coaching action plan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create coaching action plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing coaching action plan
     */
   public function update(Request $request)
{
    try {
        // Log the incoming request
        Log::info('=== CAP Update Request Started ===');
        Log::info('Request Headers', [
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method()
        ]);

        // Log raw request data
        $data = $request->json()->all();
        Log::info('Raw JSON Data Received', ['data' => $data]);

        // Map the data
        Log::info('Starting data mapping');
        $mappedData = $this->mapJsonToDatabase($data);
        Log::info('Mapped Data', ['mapped_data' => $mappedData]);

        // Check if cognito_id exists
        if (!$mappedData['cognito_id']) {
            Log::error('Cognito ID is missing from mapped data');
            return response()->json([
                'success' => false,
                'message' => 'Cognito ID is required'
            ], 400);
        }

        Log::info('Looking for action plan', ['cognito_id' => $mappedData['cognito_id']]);

        DB::beginTransaction();
        Log::info('Database transaction started');

        // Find the action plan
        $actionPlan = PizzaCoachingActionPlan::where('cognito_id', $mappedData['cognito_id'])->first();

        if (!$actionPlan) {
            Log::warning('Action plan not found, creating new one', [
                'cognito_id' => $mappedData['cognito_id']
            ]);

            // If not found, create it instead
            $actionPlan = PizzaCoachingActionPlan::create([
                'cognito_id' => $mappedData['cognito_id'],
                'manager_first_name' => $mappedData['manager_first_name'],
                'manager_last_name' => $mappedData['manager_last_name'],
                'store' => $mappedData['store'],
                'emp_first_name' => $mappedData['emp_first_name'],
                'emp_last_name' => $mappedData['emp_last_name'],
                'description_of_the_incident' => $mappedData['description_of_the_incident'],
                'coaching_plan' => $mappedData['coaching_plan'],
                'date' => $mappedData['date'],
                'cap_type' => $mappedData['cap_type'],
                're_evaluation_after' => $mappedData['re_evaluation_after'],
                'director_first_name' => $mappedData['director_first_name'],
                'director_last_name' => $mappedData['director_last_name'],
                'director_is_accepted' => $mappedData['director_is_accepted'],
                'director_rejection_reason' => $mappedData['director_rejection_reason'],
            ]);

            Log::info('New action plan created', ['cognito_id' => $actionPlan->cognito_id]);
        } else {
            Log::info('Action plan found, updating', [
                'cognito_id' => $actionPlan->cognito_id,
                'existing_data' => $actionPlan->toArray()
            ]);

            // Update the existing plan
            $updated = $actionPlan->update([
                'manager_first_name' => $mappedData['manager_first_name'],
                'manager_last_name' => $mappedData['manager_last_name'],
                'store' => $mappedData['store'],
                'emp_first_name' => $mappedData['emp_first_name'],
                'emp_last_name' => $mappedData['emp_last_name'],
                'description_of_the_incident' => $mappedData['description_of_the_incident'],
                'coaching_plan' => $mappedData['coaching_plan'],
                'date' => $mappedData['date'],
                'cap_type' => $mappedData['cap_type'],
                're_evaluation_after' => $mappedData['re_evaluation_after'],
                'director_first_name' => $mappedData['director_first_name'],
                'director_last_name' => $mappedData['director_last_name'],
                'director_is_accepted' => $mappedData['director_is_accepted'],
                'director_rejection_reason' => $mappedData['director_rejection_reason'],
            ]);

            Log::info('Action plan update result', [
                'updated' => $updated,
                'new_data' => $actionPlan->fresh()->toArray()
            ]);
        }

        // Delete existing actions
        Log::info('Deleting existing actions', ['cognito_id' => $mappedData['cognito_id']]);
        $deletedCount = PizzaCoachingAction::where('cognito_id', $mappedData['cognito_id'])->delete();
        Log::info('Existing actions deleted', ['count' => $deletedCount]);

        // Create new actions
        if (!empty($mappedData['actions'])) {
            Log::info('Creating new actions', ['actions' => $mappedData['actions']]);

            foreach ($mappedData['actions'] as $index => $actionName) {
                $action = PizzaCoachingAction::create([
                    'cognito_id' => $mappedData['cognito_id'],
                    'action_name' => $actionName,
                ]);

                Log::info('Action created', [
                    'index' => $index,
                    'action_id' => $action->id,
                    'action_name' => $actionName
                ]);
            }
        } else {
            Log::warning('No actions to create');
        }

        DB::commit();
        Log::info('Database transaction committed');

        $finalData = $actionPlan->fresh()->load('actions');
        Log::info('Final action plan data', ['data' => $finalData->toArray()]);

        Log::info('=== CAP Update Request Completed Successfully ===');

        return response()->json([
            'success' => true,
            'message' => 'Coaching action plan updated successfully',
            'data' => $finalData
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();

        Log::error('=== CAP Update Request Failed ===');
        Log::error('Exception occurred during update', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update coaching action plan',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Delete a coaching action plan
     */
    public function delete(Request $request)
    {
        try {
            $data = $request->json()->all();
            $cognitoId = $data['Entry']['Number'] ?? null;

            if (!$cognitoId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cognito ID is required'
                ], 400);
            }

            DB::beginTransaction();

            // Find and delete the action plan (cascade will delete associated actions)
            $actionPlan = PizzaCoachingActionPlan::findOrFail($cognitoId);
            $actionPlan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Coaching action plan deleted successfully'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting coaching action plan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete coaching action plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map JSON data to database structure
     */
    private function mapJsonToDatabase(array $data): array
    {
        $managerSection = $data['ManagerSection'] ?? [];
        $directorSection = $data['DirectorSection'] ?? [];
        $entry = $data['Entry'] ?? [];

        // Extract cognito_id from Entry->Number
        $cognitoId = $entry['Number'] ?? null;

        // Extract manager name
        $managerName = $managerSection['manager_name'] ?? [];
        $managerFirstName = $managerName['First'] ?? null;
        $managerLastName = $managerName['Last'] ?? null;

        // Extract employee name
        $empName = $managerSection['emp_name'] ?? [];
        $empFirstName = $empName['First'] ?? null;
        $empLastName = $empName['Last'] ?? null;

        // Extract store
        $store = $managerSection['store']['Label'] ?? null;

        // Extract director name
        $directorName = $directorSection['director_name'] ?? [];
        $directorFirstName = $directorName['First'] ?? null;
        $directorLastName = $directorName['Last'] ?? null;

        // Collect actions from actions_1 and actions_2
        $actions = [];
        if (isset($managerSection['actions_1']) && is_array($managerSection['actions_1'])) {
            $actions = array_merge($actions, $managerSection['actions_1']);
        }
        if (isset($managerSection['actions_2']) && is_array($managerSection['actions_2'])) {
            $actions = array_merge($actions, $managerSection['actions_2']);
        }

        return [
            'cognito_id' => $cognitoId,
            'manager_first_name' => $managerFirstName,
            'manager_last_name' => $managerLastName,
            'store' => $store,
            'emp_first_name' => $empFirstName,
            'emp_last_name' => $empLastName,
            'description_of_the_incident' => $managerSection['description_of_the_incident'] ?? null,
            'coaching_plan' => $managerSection['coaching_plan'] ?? null,
            'date' => $managerSection['date'] ?? null,
            'cap_type' => $managerSection['cap_type'] ?? null,
            're_evaluation_after' => $managerSection['ReevaluationAfterweeks'] ?? null,
            'director_first_name' => $directorFirstName ?? null,
            'director_last_name' => $directorLastName ?? null,
            'director_is_accepted' => $directorSection['director_is_accepted'] ?? null,
            'director_rejection_reason' => $directorSection['director_rejection_reason'] ?? null,
            'actions' => $actions,
        ];
    }
}
