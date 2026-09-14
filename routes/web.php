<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RcaCalculatorController;
use Illuminate\Support\Facades\Route;

/**
 * This file is dedicated for where the web routes are registered in the application.
 */

// For RCA calculation and issuance frontend wizard
Route::get('/', function () {
    return view('calculator');
});

/**
 * RCA calculator and Emission APIs.
 */
// Placed in the web.php to inherit session cookies so Auth::check() tracks logged-in users
Route::post('/api/rca/quotes', [RcaCalculatorController::class, 'getQuotes']);
Route::post('/api/rca/issue', [RcaCalculatorController::class, 'issuePolicy']);
Route::get('/api/rca/policies/{id}/document', [RcaCalculatorController::class, 'getPolicyDocument']);
Route::get('/policy/{id}/view-pdf', [RcaCalculatorController::class, 'viewPdfDocument']);

/**
 * User dashboard and account management.
 */
// Accessible only to logged-in users with a verified mail
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile editing, updating and for account deletion endpoints
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Audit trails and logs viewer
 */
// Endpoints for listing all API audit trails and viewing raw request/response payloads
Route::get('/audit-logs', [RcaCalculatorController::class, 'listAuditLogs']);
Route::get('/audit-log/{id}', [RcaCalculatorController::class, 'viewAuditLog']);

// Load authentication workflow routes (e.g. login, register, passwords) from auth.php
require __DIR__.'/auth.php';
