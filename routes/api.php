<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NVT\ExportEMPDataController;
use App\Http\Controllers\NVT\ExportRDODataController;
use App\Http\Controllers\NVT\Export_Late_Early_Controller;
use App\Http\Controllers\NVT\EmployeesDataController;
use App\Http\Controllers\NVT\RDO_Data_Controller;
use App\Http\Controllers\NVT\LateEarlyController;

use App\Http\Controllers\Pizza\Health_Plan_Controller;
use App\Http\Controllers\Pizza\DepositDeliveryDataController;



/**************************  NVT  **********************/
//********** Employees Data Form **************//
//create
Route::post('/employees-data', [EmployeesDataController::class, 'create']);
//update
Route::post('/employees-data/update', [EmployeesDataController::class, 'update']);
//delete
Route::post('/employees-data/delete', [EmployeesDataController::class, 'destroy']);

//********** RDO Data Form **************//
//create
Route::post('/rdo_data/create', [RDO_Data_Controller::class, 'create']);
//update
Route::post('/rdo_data/update', [RDO_Data_Controller::class, 'update']);
//delete
Route::post('/rdo_data/destroy', [RDO_Data_Controller::class, 'destroy']);

//********** Late_Early Data Form **************//
Route::post('/store-late-early', [LateEarlyController::class, 'store']);
Route::post('/update-late-early', [LateEarlyController::class, 'update']);
Route::post('/delete-late-early', [LateEarlyController::class, 'destroy']);


/**************************  PIZZA  **********************/

/**********HealthPlan************/
Route::post('/pizza/healthplan/create', [Health_Plan_Controller::class, 'create']);
Route::post('/pizza/healthplan/update', [Health_Plan_Controller::class, 'update']);
Route::post('/pizza/healthplan/delete', [Health_Plan_Controller::class, 'delete']);

// Export routes
Route::get('/pizza/healthplan/excel', [Health_Plan_Controller::class, 'exportToCsv']);

/**********EndHealthPlan************/

/****LITTLECAESARSHRDEPARTMENT*****/
Route::post('/pizza/littlecaesars/create', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'store']);
Route::post('/pizza/littlecaesars/update', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'update']);
Route::post('/pizza/littlecaesars/delete', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'destroy']);

// Export routes
Route::get('/pizza/littlecaesars/excel', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'exportToExcel']);

/************* deposit delivery ************/

Route::post('pizza/deposit-delivery-data', [DepositDeliveryDataController::class, 'create']);
Route::post('/deposit-delivery/update', [App\Http\Controllers\Pizza\DepositDeliveryDataController::class, 'update']);
Route::post('/deposit-delivery/delete', [App\Http\Controllers\Pizza\DepositDeliveryDataController::class, 'destroy']);


//**************Exporters************/
//Csvs And excel endpoints
Route::middleware('check.secret')->group(function () {

    Route::get('/export', [ExportEMPDataController::class, 'export']);
    Route::get('/rdo_data/export', [ExportRDODataController::class, 'export']);
    Route::get('/export-late-early', [Export_Late_Early_Controller::class, 'export']);
    Route::get('/pizza/littlecaesars/export', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'export']);
    Route::get('/deposit-delivery/export', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export-excel', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'exportToExcel']);
    Route::get('/deposit-delivery/export-excel/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'exportToExcel']);
   });

// Json
Route::middleware('auth.verify')->group(function () {

    Route::get('/export-late-early/data', [Export_Late_Early_Controller::class, 'getData']);
    Route::get('/get-data', [ExportEMPDataController::class, 'getData']);
    Route::get('/rdo_data/data', [ExportRDODataController::class, 'getData']);
    Route::get('/pizza/healthplan/data', [Health_Plan_Controller::class, 'getData']);
    Route::get('/pizza/littlecaesars/data', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'getData']);
    Route::get('/deposit-delivery/get-data/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'getData']);
    Route::get('/deposit-delivery/get-data', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'getData']);

});

