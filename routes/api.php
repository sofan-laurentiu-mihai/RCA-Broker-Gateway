<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RcaCalculatorController;

/**
 * Endpoint : POST /api/rca/calculate
 * The action: Submits the customer and vehicle data payload to fetch comparasion quotes
 * from the Life is Hard API.
 */
Route::post('/rca/calculate', [RcaCalculatorController::class, 'getQuotes']);

/**
 * Endpoint: POST /api/rca/issue
 * The action: Dispatches the policy issuance request for the selected quotation ID.
 */
Route::post('/rca/issue', [RcaCalculatorController::class, 'issuePolicy']);

/**
 * Endpoint: GET /api/rca/policy/{id}/document
 * Action: Fetches policy document details or external download URLs for an issued policy record.
 */
Route::get('/rca/policy/{id}/document', [RcaCalculatorController::class, 'getPolicyDocument']);
