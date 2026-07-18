<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
    Route::put('/me', [\App\Http\Controllers\AuthController::class, 'updateProfile']);
    Route::put('/me/password', [\App\Http\Controllers\AuthController::class, 'updatePassword']);

    // Rotas de Leads
    Route::apiResource('leads', \App\Http\Controllers\LeadController::class)
        ->parameters([
            "leads" => "uuid"
        ]);

    // Rotas de Companies
    Route::apiResource('companies', \App\Http\Controllers\CompanyController::class)
        ->parameters([
            "companies" => "uuid"
        ]);

    // Rotas de Contacts
    Route::apiResource('contacts', \App\Http\Controllers\ContactController::class)
        ->parameters([
            "contacts" => "uuid"
        ]);
});
