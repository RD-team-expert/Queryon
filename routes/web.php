<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Pizza\ApprovalController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Approvals Import
Route::get('/approvals/import', [ApprovalController::class, 'showImportForm'])->name('approvals.import.form');
Route::post('/approvals/import', [ApprovalController::class, 'import'])->name('approvals.import');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
