<?php

// Email template Route
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\CornJobLogController;
use App\Http\Controllers\API\EmailTemplateController;
use App\Http\Controllers\API\InviteController;
use App\Http\Controllers\API\InvoiceTemplateController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RoleAssignController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\SettingController;
use App\Http\Controllers\API\ShippingAreaController;
use App\Http\Controllers\API\SmsTemplateController;
use App\Http\Controllers\API\TaxController;
use App\Http\Controllers\API\UpdateController;
use App\Http\Controllers\API\UserController;

Route::post('template-list', [EmailTemplateController::class, 'index']);
Route::get('/get-template-content/{id}', [EmailTemplateController::class, 'show'])->middleware('permissions:can_edit_email_template');
Route::post('/set-custom-content/{id}', [EmailTemplateController::class, 'update'])->middleware('permissions:can_edit_email_template');
Route::get('/knowDefaultRowSettings', [SettingController::class, 'knowDefaultRowSettings']);

//Sms template Route
Route::post('sms-template-list', [SmsTemplateController::class, 'index']);
Route::get('/get-sms-template-content/{id}', [SmsTemplateController::class, 'show'])->middleware('permissions:can_edit_sms_template');
Route::post('/set-sms-custom-content/{id}', [SmsTemplateController::class, 'update'])->middleware('permissions:can_edit_sms_template');

// Email Setting Route
Route::get('/email-setting', [SettingController::class, 'emailSettingForm']);
Route::post('/email-setting', [SettingController::class, 'emailSettingSave'])
    ->middleware('permissions:can_edit_email_setting');
Route::get('/email-setting-data', [SettingController::class, 'emailSettingData']);

// Sms Setting Route
Route::get('/get-sms-data', [SettingController::class, 'getSmsData']);
Route::post('/sms-setting-update', [SettingController::class, 'smsSettingUpdate'])
    ->middleware('permissions:can_edit_sms_settings');

// App Setting Route
Route::post('/basic-setting', [SettingController::class, 'basicSettingSave'])
    ->middleware('permissions:can_edit_application_setting');
Route::get('/basic-setting-data', [SettingController::class, 'basicSettingData']);
Route::get('timezone', [ProfileController::class, 'getTimezone']);

// Invoice Settings Route
Route::post('invoice-setting-save', [SettingController::class, 'invoiceSettingsSave'])
    ->middleware('permissions:can_manage_invoice_setting');

Route::get('invoice-settings', [SettingController::class, 'getInvoiceSettings'])
    ->middleware('permissions:can_manage_invoice_setting');
Route::get('invoice-setting-data', [SettingController::class, 'invoiceSettingData']);
Route::get('purchase-invoice-setting-data', [SettingController::class, 'purchaseInvoiceSettingData']);
Route::post('purchase-invoice-setting-save',[SettingController::class, 'purchaseInvoiceSettingsSave'])
    ->middleware('permissions:can_manage_invoice_purchase_invoice_setting');

// Invoice Template
Route::post('invoice-templates', [InvoiceTemplateController::class, 'index']);
Route::get('/get-invoice-template/{id}', [InvoiceTemplateController::class, 'show']);
Route::get('/get-invoice-edit-data/{id}', [InvoiceTemplateController::class, 'getInvoiceEditData']);
Route::post('/save-invoice-template/{id}', [InvoiceTemplateController::class, 'update']);
Route::post('/add-invoice-template', [InvoiceTemplateController::class, 'store']);
Route::get('/allInvoice', [InvoiceTemplateController::class, 'getAllInvoiceTemplate']);

// Invite Route
Route::post('/invite', [InviteController::class, 'process'])
    ->middleware('permissions:can_manage_users');
Route::get('/all-role-id', [InviteController::class, 'getRoleId']);

// Role Route
Route::post('roles-list', [RoleController::class, 'getRolesList']);
Route::get('/role-title', [RoleController::class, 'allData']);
Route::post('/add-role', [RoleController::class, 'store'])
    ->middleware('permissions:can_manage_roles');
Route::post('/add-role/{id}', [RoleController::class, 'update'])
    ->middleware('permissions:can_manage_roles');
Route::get('/role-permissions/{id}', [RoleController::class, 'getRolePermissions']);
Route::get('/cashRegisterSalesBalance/', [RoleController::class, 'getRoleWithout']);
Route::post('/delete-role/{id}', [RoleController::class, 'delete'])
    ->middleware('permissions:can_manage_roles');
Route::get('roles', [RoleController::class, 'index']);

//Product setting route
Route::get('/product-setting', [SettingController::class, 'productSetting']);
Route::post('/product-setting-save', [SettingController::class, 'productSettingSave']);

//Selling setting route
Route::get('/sales-setting', [SettingController::class, 'salesSetting']);
Route::post('/sales-setting-save', [SettingController::class, 'salesSettingSave']);

//Notification Settings route should remove
Route::get('/notification-setting', [SettingController::class, 'notificationSetting']);
Route::post('/notification-setting-save', [SettingController::class, 'notificationSettingSave']);
Route::post('/low-stock-notification-setting-save', [SettingController::class, 'lowStockNotificationSettingSave']);
Route::get('/corn-log-last-obj', [CornJobLogController::class, 'getLastElement']);

// Notification Route
Route::get('notify', [NotificationController::class, 'index']);
Route::post('/up-notify/{id}', [NotificationController::class, 'update']);
Route::get('count', [NotificationController::class, 'count']);
Route::post('count-up/{id}', [NotificationController::class, 'countUp']);
Route::get('booking/{id}', [NotificationController::class, 'singleView']);
Route::get('notification', [NotificationController::class, 'allNoti']);
Route::get('notifications', [NotificationController::class, 'reorder']);
Route::post('/role-assign/{id}', [RoleAssignController::class, 'update']);
Route::get('/get-user/{id}', [UserController::class, 'getRowUser']);
Route::post('/enable-disable-user/{id}', [UserController::class, 'enableUser']);
Route::post('/make-admin-user/{id}', [UserController::class, 'newAdminUser']);

//tax
Route::post('/tax-list', [TaxController::class, 'getData']);
Route::get('/tax-list', [TaxController::class, 'taxGetDate']);
Route::post('/add-tax', [TaxController::class, 'store'])
    ->middleware('permissions:can_manage_tax_settings');
Route::post('/edit-tax/{id}', [TaxController::class, 'update'])
    ->middleware('permissions:can_manage_tax_settings');
Route::get('/edit-tax/{id}', [TaxController::class, 'getRowTax']);
Route::post('/delete-tax/{id}', [TaxController::class, 'deleteTax'])
    ->middleware('permissions:can_manage_tax_settings');
Route::post('/edit-tax/{id}', [UserController::class, 'editTax']);

//Shipping area
Route::post('/area-list', [ShippingAreaController::class, 'areaListGetData']);
Route::get('/get-areal-list', [ShippingAreaController::class, 'areaGetData']);
Route::post('/add-shipping-area', [ShippingAreaController::class, 'store'])
    ->middleware('permissions:can_manage_shipping_area');
Route::post('/delete-shipping-area/{id}', [ShippingAreaController::class, 'deleteShippingInfo'])
    ->middleware('permissions:can_see_shipping_area');
Route::get('/edit-shipping-area/{id}', [ShippingAreaController::class, 'getRowShipping']);
Route::post('/edit-shipping-area/{id}', [ShippingAreaController::class, 'update'])
    ->middleware('permissions:can_manage_shipping_area');

//branches
Route::get('/allBranches', [BranchController::class, 'getAllBranches']);
Route::get('/branches', [BranchController::class, 'index']);
Route::post('/branches', [BranchController::class, 'getBranchList']);
Route::get('/branch-list', [BranchController::class, 'branchList']);
Route::get('/restaurant-branch-list', [BranchController::class, 'restaurantBranchList']);
Route::post('/add-branch', [BranchController::class, 'store'])
    ->middleware('permissions:can_manage_branches');
Route::post('/edit-branch/{id}', [BranchController::class, 'update'])
    ->middleware('permissions:can_manage_branches');
Route::get('/edit-branch/{id}', [BranchController::class, 'getRowBranch']);
Route::post('/delete-branch/{id}', [BranchController::class, 'deleteBranch'])
    ->middleware('permissions:can_manage_branches');
Route::get('/branches-and-adjust-type', [BranchController::class, 'getBranchAndAdjustType']);

//branch settings
Route::get('/branch-settings-support-data', [SettingController::class, 'getDataForBranchSettings']);

//payment
Route::get('/payment-list', [PaymentController::class, 'getData']);
Route::post('/payment-list', [PaymentController::class, 'getPaymentList']);
Route::post('/add-payment', [PaymentController::class, 'store'])
    ->middleware('permissions:can_manage_payment_settings');
Route::post('/edit-payment/{id}', [PaymentController::class, 'update'])
    ->middleware('permissions:can_manage_payment_settings');
Route::post('/delete-payment/{id}', [PaymentController::class, 'deletePaymentMethod'])
    ->middleware('permissions:can_manage_payment_settings');
Route::get('/payment-details/{id}', [PaymentController::class, 'getPaymentDetailsData']);
Route::get('/invoice-logo', [PaymentController::class, 'getInvoiceLogo']);
Route::get('/get-auto-invoice', [PaymentController::class, 'getAutoInvoice']);

//users
Route::post('/users-list', [UserController::class, 'getUsersList']);
Route::get('/user/{id}', [UserController::class, 'userDetail']);
Route::get('/userChartData/{id}', [UserController::class, 'getUser']);

//keyboard shortcuts
Route::post('shortcuts', [SettingController::class, 'storeKeyboardShortcutSettings']);
Route::get('shortcut-setting-data/{id}', [SettingController::class, 'getShortcutSettings']);

// Updates Route
Route::get('/gain-update', [UpdateController::class, 'applicationVersion']);
Route::get('/update-version-list', [UpdateController::class, 'versionUpdateList']);
Route::post('/install-new-version/{version}', [UpdateController::class, 'updateAction']);
Route::get('/update-list', [UpdateController::class, 'updateAppUrl']);
Route::get('/curl_get_contents', [UpdateController::class, 'curl_get_contents']);
Route::get('/nexInstallVersion', [UpdateController::class, 'nexInstallVersion']);
Route::get('/check-purchase-key', [UpdateController::class, 'checkPurchaseKey']);
Route::post('/purchase-key-save', [SettingController::class, 'purchaseKeySave']);