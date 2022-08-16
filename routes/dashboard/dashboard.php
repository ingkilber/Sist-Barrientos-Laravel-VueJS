<?php

use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ProfileController;

Route::get('/dashboard-data', [DashboardController::class, 'getAllData']);
Route::get('/dashBoard', [DashboardController::class, 'getData']);
Route::get('user-profile', [ProfileController::class, 'index']);
Route::post('profile/{id}', [ProfileController::class, 'update']);
Route::post('/update-password/{id}', [ProfileController::class, 'updatePassword']);
Route::get('getDateFormat', [ProfileController::class, 'dateFormat']);