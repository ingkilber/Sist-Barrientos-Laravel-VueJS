<?php

//customer
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\CustomerGroupController;
use App\Http\Controllers\API\SupplierController;

Route::post('/customer-list', [CustomerController::class, 'getCustomerList']);
Route::post('import-customer-contacts', [CustomerController::class, 'importCustomers']);
Route::post('/customer/store', [CustomerController::class, 'store'])
    ->middleware('permissions:can_manage_customers');
Route::post('/customer/{id}', [CustomerController::class, 'updateCustomer'])
    ->middleware('permissions:can_manage_customers');
Route::post('/delete/{id}', [CustomerController::class, 'destroy'])
    ->middleware('permissions:can_manage_customers');
Route::post('/customer/delete/{id}', [CustomerController::class, 'deleteCustomer'])
    ->middleware('permissions:can_manage_customers');
Route::get('/customer-data/{id}', [CustomerController::class, 'getCustomerData']);
Route::post('/update-customer-avatar/{id}', [CustomerController::class, 'updateAvatar']);

//supplier
Route::post('import-supplier-contacts', [SupplierController::class, 'importSuppliers']);
Route::post('supplier/store', [SupplierController::class, 'store']);
Route::post('/supplier-list', [SupplierController::class, 'getSupplierData']);
Route::get('/supplier-edit/{id}', [SupplierController::class, 'getData']);
Route::get('/supplier/{id}', [SupplierController::class, 'getSupplierDetails']);
Route::post('supplier/{id}', [SupplierController::class, 'editSupplierData']);
Route::post('supplier/delete/{id}', [SupplierController::class, 'deleteSupplier']);
Route::post('supplier-delivery-report/{id}', [SupplierController::class, 'getSupplierDeliveryRecords']);
Route::post('/update-supplier-avatar/{id}', [SupplierController::class, 'updateAvatar']);

// groups
Route::get('/groups', [CustomerGroupController::class, 'index']);
Route::post('/groups', [CustomerGroupController::class, 'getGroups']);
Route::post('/group/store', [CustomerGroupController::class, 'store'])
    ->middleware('permissions:can_manage_customer_groups');
Route::get('/groups/{id}', [CustomerGroupController::class, 'show']);
Route::post('/group/delete/{id}', [CustomerGroupController::class, 'destroy'])
    ->middleware('permissions:can_manage_customer_groups');
Route::post('/group/{id}', [CustomerGroupController::class, 'update'])
    ->middleware('permissions:can_manage_customer_groups');
Route::get('/customer-groups', [CustomerGroupController::class, 'getCustomerGroups']);