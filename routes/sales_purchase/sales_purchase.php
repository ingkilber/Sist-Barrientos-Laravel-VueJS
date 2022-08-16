<?php

use App\Http\Controllers\API\AdjustProductStockController;
use App\Http\Controllers\API\CashRegisterController;
use App\Http\Controllers\API\RestaurantTableController;
use App\Http\Controllers\API\Sales\SaleApiController;
use App\Http\Controllers\API\SalesController;
use App\Http\Controllers\API\SalesShipmentController;

Route::get('sales', [SalesController::class, 'salesView']);
Route::post('sales-product', [SalesController::class, 'getProductNew']);
Route::get('get-sales-product/{limit}/{offset}', [SaleApiController::class, 'index']);
Route::post('get-return-orders', [SalesController::class, 'getReturnProduct']);
Route::post('sales-returns-type-set', [SalesController::class, 'setSalesReturnsType']);
Route::post('receive-type-set', [SalesController::class, 'setPurchaseReturnsType']);
Route::post('/sales-list-data/{id}', [SalesController::class, 'salesListGetData']);
Route::post('/sales/list/delete/{id}', [SalesController::class, 'saleListDelete']);
Route::post('/sales/date/update/{id}', [SalesController::class, 'saleListUpdate']);

//sales shipment list
Route::post('/sales-shipment-data/{id}', [SalesShipmentController::class, 'salesListShipment']);
Route::post('/shipping-order-status/{id}/{status}', [SalesShipmentController::class, 'setShippingStatus']);
Route::post('sales-receiving-type-set', [SalesController::class, 'setSalesReceivingType']);
Route::post('/store', [SalesController::class, 'salesStore'])
    ->middleware('permissions:can_manage_sales');
Route::post('/continue-sale', [SalesController::class, 'salesStore'])
    ->middleware('permissions:can_manage_sales');
Route::post('/purchase-store', [SalesController::class, 'salesStore'])
    ->middleware('permissions:can_manage_receives');
Route::post('/continue-purchase', [SalesController::class, 'salesStore'])
    ->middleware('permissions:can_manage_receives');
Route::post('/continue-sale-payments', [SalesController::class, 'getPaymentsAndDetails']);
Route::get('/get-hold-orders', [SalesController::class, 'getHoldOrder']);
Route::post('customers-list', [SalesController::class, 'customerList']);
Route::post('sales-branch-set', [SalesController::class, 'setBranch']);
Route::post('sales-cancel', [SalesController::class, 'salesCancel']);
Route::get('/receives', [SalesController::class, 'purchaseView']);
Route::get('/get-register-amount/{id}', [SalesController::class, 'getRegisterAmount']);
Route::post('/save-due-amount', [SalesController::class, 'saveDueAmount']);
Route::post('/offline-sales', [SalesController::class, 'offlineSalesStore']);

// Customer sales sms
Route::post('/customer-send-sms', [SalesController::class, 'customerSendSms']);

// Restaurant
Route::post('get-table-list', [RestaurantTableController::class, 'getTableList']);
Route::get('/tables', [RestaurantTableController::class, 'index']);
Route::post('/addTable', [RestaurantTableController::class, 'store']);
Route::post('/editTable/{id}', [RestaurantTableController::class, 'update']);
Route::get('/edit-table/{id}', [RestaurantTableController::class, 'getRowTable']);
Route::post('/delete-table/{id}', [RestaurantTableController::class, 'deleteTable']);

// Adjust Stock
Route::get('/adjust-stock-list', [AdjustProductStockController::class, 'getData']);
Route::post('/adjust-stock-list', [AdjustProductStockController::class, 'getAdjustStockList']);
Route::post('/add-adjust-stock', [AdjustProductStockController::class, 'store'])
    ->middleware('permissions:can_manage_adjust_stock');
Route::post('/edit-adjust-stock/{id}', [AdjustProductStockController::class, 'update'])
    ->middleware('permissions:can_manage_adjust_stock');
Route::post('/delete-adjust-stock/{id}', [AdjustProductStockController::class, 'deleteAdjustStockType'])
    ->middleware('permissions:can_manage_adjust_stock');
Route::get('/adjust-stock-details/{id}', [AdjustProductStockController::class, 'getAdjustStockDetailsData']);

//sales register
Route::get('/cash-registers', [CashRegisterController::class, 'getCashRegisterList']);
Route::post('cash-registers', [CashRegisterController::class, 'index']);
Route::post('cash-register-store', [CashRegisterController::class, 'store'])
    ->middleware('permissions:can_manage_cash_registers');
Route::get('cash-register-show/{id}', [CashRegisterController::class, 'show']);
Route::post('cash-register-update/{id}', [CashRegisterController::class, 'update'])
    ->middleware('permissions:can_manage_cash_registers');
Route::post('delete-register/{id}', [CashRegisterController::class, 'deleteCashRegister'])
    ->middleware('permissions:can_manage_cash_registers');
Route::post('cash-register-open-close', [CashRegisterController::class, 'cashRegisterLogs']);
Route::post('/register-sales-info/{id}', [CashRegisterController::class, 'registerSalesInfo']);
Route::get('/cash-register-total-sales-balance/{id}', [CashRegisterController::class, 'cashRegisterInfo']);