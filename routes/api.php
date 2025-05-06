<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckSecretHeader;

use App\Http\Controllers\ExportEMPDataController;
use App\Http\Controllers\ExportRDODataController;
use App\Http\Controllers\Export_Late_Early_Controller;
use App\Http\Controllers\ExportCapsDataController;
use App\Http\Controllers\Export_ClockInOutController;

use App\Http\Controllers\CapsController;
use App\Http\Controllers\EmployeesDataController;
use App\Http\Controllers\RDO_Data_Controller;
use App\Http\Controllers\LateEarlyController;
use App\Http\Controllers\ClockInOutController;
use App\Http\Controllers\Pizza\DepositDeliveryDataController;

use App\Http\Controllers\ExportController;


/**************************  NVT  **********************/





Route::middleware([CheckSecretHeader::class])->group(function () {

    // Employees Data Form
    Route::get('/export', [ExportEMPDataController::class, 'export']);

    Route::get('/rdo_data/export', [ExportRDODataController::class, 'export']);

    Route::get('/export-late-early', [Export_Late_Early_Controller::class, 'export']);

    Route::get('/export-caps-data/export', [ExportCapsDataController::class, 'export']);

    Route::get('/export-clock-in-out/export', [Export_ClockInOutController::class, 'export']);

    Route::get('/pizza/littlecaesars/export', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'export']);

    Route::get('/deposit-delivery/export', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'export']);
    Route::get('/deposit-delivery/export-excel', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'exportToExcel']);
    Route::get('/deposit-delivery/export-excel/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'exportToExcel']);


    // Route::get('/final-summary-csv/{start_date?}/{end_date?}/{franchise_store?}', [ExportController::class, 'exportFinalSummaryCsv']);
});
//********** Employees Data Form **************//
//create
Route::post('/employees-data', [EmployeesDataController::class, 'create']);
//update
Route::post('/employees-data/update', [EmployeesDataController::class, 'update']);
//delete
Route::post('/employees-data/delete', [EmployeesDataController::class, 'destroy']);

//Get data
//Export data as CSV
//Export data as CSV
Route::get('/export-to-excel', [ExportEMPDataController::class, 'exportToExcel']);
// Return data as JSON
Route::get('/get-data', [ExportEMPDataController::class, 'getData']);


//********** RDO Data Form **************//
//create
Route::post('/rdo_data/create', [RDO_Data_Controller::class, 'create']);
//update
Route::post('/rdo_data/update', [RDO_Data_Controller::class, 'update']);
//delete
Route::post('/rdo_data/destroy', [RDO_Data_Controller::class, 'destroy']);

//Get data
//Export data as CSV
    //Export data as CSV
    Route::get('/rdo_data/excel', [ExportRDODataController::class, 'exportToExcel']);
    // Return data as JSON
    Route::get('/rdo_data/data', [ExportRDODataController::class, 'getData']);
    // end point to excel

//********** Late_Early Data Form **************//
Route::post('/store-late-early', [LateEarlyController::class, 'store']);
Route::post('/update-late-early', [LateEarlyController::class, 'update']);
Route::post('/delete-late-early', [LateEarlyController::class, 'destroy']);

//get data
// Route to export Late_Early data as CSV for Excel

    // Route to export Late_Early data as CSV for Excel
    Route::get('/export-late-early/excel', [Export_Late_Early_Controller::class, 'exportToExcel']);
    // Route to return all Late_Early data as JSON
    Route::get('/export-late-early/data', [Export_Late_Early_Controller::class, 'getData']);


//********** Caps Data Form **************//

Route::post('/caps/create', [CapsController::class, 'store']);
Route::post('/caps/update', [CapsController::class, 'update']);
Route::post('/caps/destroy', [CapsController::class, 'destroy']);

//get data
// Route to export Caps data as CSV for Excel

    // Route to export Caps data as CSV for Excel
    Route::get('/export-caps-data/excel', [ExportCapsDataController::class, 'exportToExcel']);
    // Route to return all Caps data as JSON
    Route::get('/export-caps-data/data', [ExportCapsDataController::class, 'getData']);
    // Route to export Caps data as downloadable CSV



//********clock in out excel data */


/*************Clock in out data */
// Webhook route to handle incoming JSON data
Route::post('/webhook', [ClockInOutController::class, 'Index']);
Route::post('/clock-in-out/update-by-entry', [ClockInOutController::class, 'updateByEntryNumber']);
Route::post('/clock-in-out/delete-by-entry', [ClockInOutController::class, 'deleteByEntryNumber']);

//export clock in out data
// Route to export Clock In/Out data as CSV for Excel


    // Route to export Clock In/Out data as CSV for Excel
    Route::get('/export-clock-in-out/excel', [Export_ClockInOutController::class, 'exportToExcel']);
    // Route to return all Clock In/Out data as JSON
    Route::get('/export-clock-in-out/data', [Export_ClockInOutController::class, 'getData']);
    // Route to export Clock In/Out data as downloadable CSV




/**************************  PIZZA  **********************/

/****LITTLECAESARSHRDEPARTMENT*****/
/****LITTLECAESARSHRDEPARTMENT*****/
Route::post('/pizza/littlecaesars/create', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'store']);
Route::post('/pizza/littlecaesars/update', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'update']);
Route::post('/pizza/littlecaesars/delete', [App\Http\Controllers\Pizza\LittleCaesarsHrDepartmentController::class, 'destroy']);

// Export routes
Route::get('/pizza/littlecaesars/excel', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'exportToExcel']);
Route::get('/pizza/littlecaesars/data', [App\Http\Controllers\Pizza\ExportLittleCaesarsHrDepartmentController::class, 'getData']);
/************* deposit delivery ************/

Route::post('pizza/deposit-delivery-data', [DepositDeliveryDataController::class, 'create']);
Route::post('/deposit-delivery/update', [App\Http\Controllers\Pizza\DepositDeliveryDataController::class, 'update']);
Route::post('/deposit-delivery/delete', [App\Http\Controllers\Pizza\DepositDeliveryDataController::class, 'destroy']);

// Deposit Delivery Data Export Routes
// Query parameters: start_date, end_date, franchisee_num

    Route::get('/deposit-delivery/get-data/{start_date?}/{end_date?}/{franchisee_num?}', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'getData']);
    Route::get('/deposit-delivery/get-data', [App\Http\Controllers\Pizza\DepositDeliveryController::class, 'getData']);

    // Route::get('/export-final-summary/{start_date?}/{end_date?}/{franchise_store?}', [ExportController::class, 'exportFinalSummary']);
    // Route::get('/final-summary-json/{start_date?}/{end_date?}/{franchise_store?}', [ExportController::class, 'getFinalSummaryJson']);

