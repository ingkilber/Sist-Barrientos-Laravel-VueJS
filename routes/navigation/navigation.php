<?php

use App\Http\Controllers\View\NavigationController;


Route::get('/dashboard', [NavigationController::class, 'dashboardView'])->name('dashboard');
Route::get('profile-view', [NavigationController::class, 'profileView']);
Route::get('/settings', [NavigationController::class, 'settingsView']);
Route::get('/invite', [NavigationController::class, 'inviteView']);
Route::get('/products', [NavigationController::class, 'productView']);
Route::get('/details/{id}', [NavigationController::class, 'productDetailsView']);
Route::get('/contacts', [NavigationController::class, 'contactView']);
Route::get('/customers', [NavigationController::class, 'customerView']);
Route::get('/suppliers', [NavigationController::class, 'supplierView']);
Route::get('/customer/{id}', [NavigationController::class, 'customerDetailsView']);
Route::get('reports', [NavigationController::class, 'reportView']);
Route::get('reports/sales/{id}', [NavigationController::class, 'salesReportsDetailsView']);
Route::get('reports/receiving/{id}', [NavigationController::class, 'purchaseReportsDetailsView']);
Route::get('/products/details/{id}', [NavigationController::class, 'productDetailsView']);