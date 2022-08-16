@extends('layouts.app')

@section('title', trans("lang.settings"))
@php
    $timezones = timezone_identifiers_list();
@endphp
@section('content')
    @inject('perm', 'App\Http\Controllers\API\PermissionController')
    <setting-index app_settings={{$perm->appsManagePermission()}}
            email_settings={{$perm->emailsManagePermission()}}
            email_templates={{$perm->emailTemplateManagePermission()}}
            sms_settings={{$perm->smsSettingPermission()}}
            sms_templates={{$perm->smsTemplateManagePermission()}}
            payment_settings={{$perm->paymentManagePermission()}}
            tax_settings={{$perm->taxSettingManagePermission()}}
            branches_setting={{$perm->branchManagePermission()}}
            shipping_area_setting={{$perm->shippingAreaPermission()}}
            roles={{$perm->rolesManagePermission()}}
            users={{$perm->userManagePermission()}}
            cash_register={{$perm->cashRegistersManagePermission()}}
            invoice_settings={{$perm->InvoiceSettingsPermission()}}
            purchase_invoice_settings={{$perm->purchaseInvoiceSettingPermission()}}
            invoice_templates={{$perm->InvoiceTemplateSettingsPermission()}}
            product_settings={{$perm->productSettingsPermission()}}
            adjust_stock_settings={{$perm->adjustStockSettingsPermission()}}
            notification_settings={{$perm->notificationSettingsPermission()}}
            corn_settings={{$perm->cornJobSettingsPermission()}}
            updates_setting={{$perm->updateSettingPermission()}}
            table_setting={{$perm->tablesSettingsPermission()}}
            sales_setting={{$perm->salesSettingsPermission()}}
            time_zones={{json_encode($timezones)}}
            tab_name={{$tab_name}}
            route_name={{$route_name}}>
    </setting-index>

@endsection
