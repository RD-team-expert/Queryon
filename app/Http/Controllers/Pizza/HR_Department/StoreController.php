<?php


namespace App\Http\Controllers\Pizza\HR_Department;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use App\Models\Pizza\HR_Department\Store;


class StoreController extends Controller
{
    /**
     * Map incoming JSON data to Store attributes
     */
    protected function mapJsonData(array $data): array
    {
        $mapped = [
            'id' => $data['Entry']['Number'] ?? null,
            'name' => $data['StoreName'] ?? null,
            'franchise' => $data['StoreFranchise'] ?? null,
            'store_email' => $data['StoreEmail'] ?? null,
            'first_manager_email' => $data['_1stManagerEmail'] ?? null,
            'second_manager_email' => $data['_2ndManagerEmail'] ?? null,
        ];

        return $mapped;
    }


    /**
     * Create a new store
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $jsonData = $request->all();
            $mappedData = $this->mapJsonData($jsonData);


            // Validate the mapped data
            $validator = Validator::make($mappedData, [
                'id' => 'required|integer|unique:stores,id',
                'name' => 'required|string|max:255',
                'franchise' => 'nullable|string|max:255',
                'store_email' => 'nullable|email|max:255',
                'first_manager_email' => 'nullable|email|max:255',
                'second_manager_email' => 'nullable|email|max:255',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            $store = Store::create($mappedData);

            return response()->json([
                'success' => true,
                'message' => 'Store created successfully',
                'data' => $store
            ], 201);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create store',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update or create a store
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $jsonData = $request->all();
            $mappedData = $this->mapJsonData($jsonData);


            // Validate the mapped data
            $validator = Validator::make($mappedData, [
                'id' => 'required|integer',
                'name' => 'required|string|max:255',
                'franchise' => 'nullable|string|max:255',
                'store_email' => 'nullable|email|max:255',
                'first_manager_email' => 'nullable|email|max:255',
                'second_manager_email' => 'nullable|email|max:255',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            $store = Store::updateOrCreate(
                ['id' => $mappedData['id']],
                $mappedData
            );

            return response()->json([
                'success' => true,
                'message' => 'Store updated or created successfully',
                'data' => $store
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update or create store',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Delete a store
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $jsonData = $request->all();
            $mappedData = $this->mapJsonData($jsonData);


            // Validate the ID
            $validator = Validator::make($mappedData, [
                'id' => 'required|integer|exists:stores,id',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }


            $store = Store::findOrFail($mappedData['id']);

            $store->delete();

            return response()->json([
                'success' => true,
                'message' => 'Store deleted successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete store',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
