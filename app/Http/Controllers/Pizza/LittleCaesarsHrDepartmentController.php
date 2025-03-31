<?php

namespace App\Http\Controllers\Pizza;

use App\Http\Controllers\Controller;
use App\Models\Pizza\LittleCaesarsHrDepartmentData;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LittleCaesarsHrDepartmentController extends Controller
{
    // Store function to insert data into the table
    public function store(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Format the date properly
        $dateSubmitted = null;
        if (isset($data['Entry']['DateSubmitted']) && !empty($data['Entry']['DateSubmitted'])) {
            $dateSubmitted = Carbon::parse($data['Entry']['DateSubmitted'])->format('Y-m-d');
        }

        // Extract data and map to database columns
        $littleCaesarsData = LittleCaesarsHrDepartmentData::create([
            'HookLanguage' => $data['HookMain']['HookLanguage'] ?? null,
            'HookStore' => $data['HookMain']['HookEnglish']['HookStore']['Label'] ?? null,
            'Hookالمتجر' => $data['HookMain']['HookArabic']['Hookالمتجر']['Label'] ?? null,
            'HookAlmacenar' => $data['HookMain']['HookEspañol']['HookAlmacenar']['Label'] ?? null,
            'HookSelectYourRequestType' => $data['HookMain']['HookEnglish']['HookSelectYourRequestType'] ?? null,
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات' => $data['HookMain']['HookArabic']['Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات'] ?? null,
            'HookSeleccioneSuTipoDeSolicitud' => $data['HookMain']['HookEspañol']['HookSeleccioneSuTipoDeSolicitud'] ?? null,
            'EntryNum' => $data['Entry']['Number'] ?? null,
            'DateSubmitted' => $dateSubmitted,
        ]);

        return response()->json(['message' => 'Record stored successfully!', 'data' => $littleCaesarsData], 201);
    }

    // Update function to update an existing record
    public function update(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by EntryNum
        $littleCaesarsData = LittleCaesarsHrDepartmentData::where('EntryNum', $data['Entry']['Number'])->first();

        if (!$littleCaesarsData) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Format the date properly
        $dateSubmitted = null;
        if (isset($data['Entry']['DateSubmitted']) && !empty($data['Entry']['DateSubmitted'])) {
            $dateSubmitted = Carbon::parse($data['Entry']['DateSubmitted'])->format('Y-m-d');
        }

        // Update the record with the new data
        $littleCaesarsData->update([
            'HookLanguage' => $data['HookMain']['HookLanguage'] ?? null,
            'HookStore' => $data['HookMain']['HookEnglish']['HookStore']['Label'] ?? null,
            'Hookالمتجر' => $data['HookMain']['HookArabic']['Hookالمتجر']['Label'] ?? null,
            'HookAlmacenar' => $data['HookMain']['HookEspañol']['HookAlmacenar']['Label'] ?? null,
            'HookSelectYourRequestType' => $data['HookMain']['HookEnglish']['HookSelectYourRequestType'] ?? null,
            'Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات' => $data['HookMain']['HookArabic']['Hookماهونوعطلبك": "✅ الشكاوى أو الملاحظات'] ?? null,
            'HookSeleccioneSuTipoDeSolicitud' => $data['HookMain']['HookEspañol']['HookSeleccioneSuTipoDeSolicitud'] ?? null,
            'EntryNum' => $data['Entry']['Number'] ?? null,
            'DateSubmitted' => $dateSubmitted,
        ]);

        return response()->json(['message' => 'Record updated successfully!', 'data' => $littleCaesarsData], 200);
    }

    // Destroy function to delete a record by EntryNum
    public function destroy(Request $request)
    {
        // Decode the JSON data from the request
        $data = json_decode($request->getContent(), true);

        // Find the record by EntryNum
        $littleCaesarsData = LittleCaesarsHrDepartmentData::where('EntryNum', $data['Entry']['Number'])->first();

        if (!$littleCaesarsData) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        // Delete the record
        $littleCaesarsData->delete();

        return response()->json(['message' => 'Record deleted successfully!'], 200);
    }
}