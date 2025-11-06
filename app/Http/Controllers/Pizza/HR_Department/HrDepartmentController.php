<?php


namespace App\Http\Controllers\Pizza\HR_Department;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use App\Services\HR_Department\Helpers\MappingService;

use App\Models\Pizza\HR_Department\FormRequest;

class HrDepartmentController extends Controller
{

    protected $mappingService;

    // ✅ Inject the service using constructor dependency injection
    public function __construct(MappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }
    // CRUD
    public function create(Request $request)
    {

        $data = $request->all();
        $mappedData = $this->mappingService->formMap($data);

        
        return response()->json(['message' => 'HR Department Controller']);
    }
    public function update(Request $request)
    {

        $data = $request->all();
        $mappedData = $this->mappingService->formMap($data);


        return response()->json(['message' => 'HR Department Controller']);
    }
    public function delete(Request $request)
    {

        $data = $request->all();
        $formId   = $data['Entry']['Number'] ?? null;

        $store = FormRequest::findOrFail($formId);
        $store->delete();

        return response()->json(['message' => 'HR Department Controller']);

    }

    //Helpers


    /* give back the language and form type and form id
        -helps for fast delete update (id)
        -helps for the languasge and form type to map the data accordingly
    */

}
