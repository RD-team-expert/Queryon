<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckSecretHeader;

use App\Http\Controllers\ExportEMPDataController;
use App\Http\Controllers\ExportRDODataController;
use App\Http\Controllers\Export_Late_Early_Controller;
use App\Http\Controllers\ExportCapsDataController;


use App\Http\Controllers\CapsController;
use App\Http\Controllers\EmployeesDataController;
use App\Http\Controllers\RDO_Data_Controller;
use App\Http\Controllers\LateEarlyController;

use App\Http\Controllers\ClockInOutController;






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
Route::get('/rdo_data/excel', [ExportRDODataController::class, 'exportToExcel'])
->middleware(CheckSecretHeader::class);
// Return data as JSON
Route::get('/rdo_data/data', [ExportRDODataController::class, 'getData'])
->middleware(CheckSecretHeader::class);
// end point to excel
Route::get('/rdo_data/export', [ExportRDODataController::class, 'export'])
->middleware(CheckSecretHeader::class);


//********** Late_Early Data Form **************//
Route::post('/store-late-early', [LateEarlyController::class, 'store']);
Route::post('/update-late-early', [LateEarlyController::class, 'update']);
Route::post('/delete-late-early', [LateEarlyController::class, 'destroy']);


//get data
// Route to export Late_Early data as CSV for Excel
Route::get('/export-late-early/excel', [Export_Late_Early_Controller::class, 'exportToExcel'])
->middleware(CheckSecretHeader::class);

// Route to return all Late_Early data as JSON
Route::get('/export-late-early/data', [Export_Late_Early_Controller::class, 'getData'])
->middleware(CheckSecretHeader::class);

// Route to export Late_Early data as downloadable CSV
Route::get('/export-late-early', [Export_Late_Early_Controller::class, 'export'])
->middleware(CheckSecretHeader::class);


//********** Caps Data Form **************//

Route::post('/caps/create', [CapsController::class, 'store']);
Route::post('/caps/update', [CapsController::class, 'update']);
Route::post('/caps/destroy', [CapsController::class, 'destroy']);

//get data
// Route to export Caps data as CSV for Excel
Route::get('/export-caps-data/excel', [ExportCapsDataController::class, 'exportToExcel'])
->middleware(CheckSecretHeader::class);
// Route to return all Caps data as JSON
Route::get('/export-caps-data/data', [ExportCapsDataController::class, 'getData'])
->middleware(CheckSecretHeader::class);
// Route to export Caps data as downloadable CSV
Route::get('/export-caps-data/export', [ExportCapsDataController::class, 'export'])
->middleware(CheckSecretHeader::class);

//********clock in out excel data */



// Webhook route to handle incoming JSON data
Route::post('/webhook', [ClockInOutController::class, 'Index']);

// Route to delete records by Entry_Number
Route::post('/clock-in-out/delete-by-entry', [ClockInOutController::class, 'deleteByEntryNumber']);

Route::post('/clock-in-out/update-by-entry', [ClockInOutController::class, 'updateByEntryNumber']);