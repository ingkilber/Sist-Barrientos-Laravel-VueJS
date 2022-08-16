<?php

use App\Http\Controllers\API\InviteController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthController;

Route::get('/', [LoginController::class, 'showLoginForm'])
    ->name('login');
Route::get('/login', [LoginController::class, 'showLoginForm']);
Route::post('/', [LoginController::class, 'login']);
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm']);
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm']);
Route::post('/recover', [AuthController::class, 'recover']);
Route::post('/password/reset/{token}', [ResetPasswordController::class, 'reset']);
Route::get('/verify/{token}', [RegisterController::class, 'verifyUser']);
Route::get('accept/{token}', [InviteController::class, 'accept']);
Route::get('register/{token}', [RegisterController::class, 'regForm']);
Route::post('register/{token}', [InviteController::class, 'invitedRegistration']);