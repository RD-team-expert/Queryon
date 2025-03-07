<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckSecretHeader;

use App\Http\Controllers\ExportEMPDataController;
use App\Http\Controllers\ExportRDODataController;
use App\Http\Controllers\Export_Late_Early_Controller;

use App\Http\Controllers\EmployeesDataController;
use App\Http\Controllers\RDO_Data_Controller;
use App\Http\Controllers\LateEarlyController;



//********** Employees Data Form **************//
//create
Route::post('/employees-data', [EmployeesDataController::class, 'create']);
//update
Route::post('/employees-data/update', [EmployeesDataController::class, 'update']);
//delete
Route::post('/employees-data/delete', [EmployeesDataController::class, 'destroy']);

//Get data
//Export data as CSV
Route::get('/export-to-excel', [ExportEMPDataController::class, 'exportToExcel'])
->middleware(CheckSecretHeader::class);
// Return data as JSON
Route::get('/get-data', [ExportEMPDataController::class, 'getData'])
->middleware(CheckSecretHeader::class);
// end point to excel
Route::get('/export', [ExportEMPDataController::class, 'export'])
->middleware(CheckSecretHeader::class);

//********** RDO Data Form **************//
//create
Route::post('/rdo_data/create', [RDO_Data_Controller::class, 'create']);
//update
Route::post('/rdo_data/update', [RDO_Data_Controller::class, 'update']);
//delete
Route::post('/rdo_data/destroy', [RDO_Data_Controller::class, 'destroy']);

//Get data
//Export data as CSV
Route::get('/rdo_data/excel', [ExportRDODataController::class, 'exportToExcel']);
// Return data as JSON
Route::get('/rdo_data/data', [ExportRDODataController::class, 'getData']);
// end point to excel
Route::get('/rdo_data/export', [ExportRDODataController::class, 'export']);


//********** Late_Early Data Form **************//
Route::post('/store-late-early', [LateEarlyController::class, 'store']);
Route::post('/update-late-early', [LateEarlyController::class, 'update']);
Route::post('/delete-late-early', [LateEarlyController::class, 'destroy']);



// Route to export Late_Early data as CSV for Excel
Route::get('/export-late-early/excel', [Export_Late_Early_Controller::class, 'exportToExcel']);

// Route to return all Late_Early data as JSON
Route::get('/export-late-early/data', [Export_Late_Early_Controller::class, 'getData']);

// Route to export Late_Early data as downloadable CSV
Route::get('/export-late-early', [Export_Late_Early_Controller::class, 'export']);
