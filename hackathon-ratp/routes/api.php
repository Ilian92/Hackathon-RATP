<?php

use App\Http\Controllers\Api\ComplaintController;
use App\Http\Middleware\ApiTokenMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(ApiTokenMiddleware::class)->group(function () {
    Route::get('/complaints/pending', [ComplaintController::class, 'pending']);
    Route::post('/complaints/severity', [ComplaintController::class, 'storeSeverity']);
});
