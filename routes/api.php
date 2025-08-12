<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NVT\EmployeesDataController;
use App\Http\Controllers\NVT\RDO_Data_Controller;
use App\Http\Controllers\NVT\LateEarlyController;

use App\Http\Controllers\Pizza\Health_Plan_Controller;
use App\Http\Controllers\Pizza\DepositDeliveryController;
use App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController;


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
Route::post('/pizza/littlecaesars/create', [LittleCaesarsHrDepartmentController::class, 'store']);
Route::post('/pizza/littlecaesars/update', [LittleCaesarsHrDepartmentController::class, 'update']);
Route::post('/pizza/littlecaesars/delete', [LittleCaesarsHrDepartmentController::class, 'destroy']);

// Export routes
Route::get('/pizza/littlecaesars/excel', [LittleCaesarsHrDepartmentController::class, 'exportToExcel']);

/************* deposit delivery ************/

Route::post('pizza/deposit-delivery-data', [DepositDeliveryController::class, 'create']);
Route::post('/deposit-delivery/update', [DepositDeliveryController::class, 'update']);
Route::post('/deposit-delivery/delete', [DepositDeliveryController::class, 'destroy']);


//**************Exporters************/
//Csvs And excel endpoints
Route::middleware('check.secret')->group(function () {

    Route::get('/export', [EmployeesDataController::class, 'export']);
    Route::get('/rdo_data/export', [RDO_Data_Controller::class, 'export']);
    Route::get('/export-late-early', [LateEarlyController::class, 'export']);
    Route::get('/pizza/littlecaesars/export', [LittleCaesarsHrDepartmentController::class, 'export']);
    Route::get('/deposit-delivery/export', [DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export/{start_date?}/{end_date?}/{franchisee_num?}', [DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export-excel', [DepositDeliveryController::class, 'exportToExcel']);
    Route::get('/deposit-delivery/export-excel/{start_date?}/{end_date?}/{franchisee_num?}', [DepositDeliveryController::class, 'exportToExcel']);
});

// Json
Route::middleware('auth.verify')->group(function () {

    Route::get('/export-late-early/data', [LateEarlyController::class, 'getData']);
    Route::get('/get-data', [EmployeesDataController::class, 'getData']);
    Route::get('/rdo_data/data', [RDO_Data_Controller::class, 'getData']);
    Route::get('/pizza/healthplan/data', [Health_Plan_Controller::class, 'getData']);
    Route::get('/pizza/littlecaesars/data', [LittleCaesarsHrDepartmentController::class, 'getData']);
    Route::get('/deposit-delivery/get-data/{start_date?}/{end_date?}/{franchisee_num?}', [DepositDeliveryController::class, 'getData']);
    Route::get('/deposit-delivery/get-data', [DepositDeliveryController::class, 'getData']);

});

