@inject('perm', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')
@section('title', 'Sales')
@section('content')

    <sales-or-receives-component :user="{{ Auth::user() }}"
                                 :total_branch="{{$totalBranch}}"
                                 :order_type="'sales'"
                                 :sold_to="'sold_to'"
                                 :sold_by="'sold_by'"
                                 current_branch="{{$currentBranch}}"
                                 current_cash_register="{{$currentCashRegister}}"
                                 addcustomer="{{$perm->customersManagePermission()}}"
                                 manage_price="{{$perm->salesPriceManagePermission()}}"
                                 manage_sales={{$perm->salesManagePermission()}}
                                 sales_return_status="{{$salesReturnStatus}}"
                                 sales_receiving_type="{{$salesType}}"
                                 all_branch="{{$branches}}"
                                 auto_invoice="{{$autoInvoice}}"
                                 payment_types="{{$paymentTypes}}"
                                 customer="{{$customer}}"
                                 customer_group="{{$customerGroup}}"
                                 product="{{$product}}"
                                 app_name="{{$appName}}"
                                 invoice_prefix="{{$invoicePrefix}}"
                                 invoice_suffix="{{$invoiceSuffix}}"
                                 last_invoice="{{$lastInvoiceNum}}"
                                 is_branch_selected="{{$isBranchSelected}}"
                                 hold_orders="{{$holdOrders}}"
                                 default_sales_invoice_template="{{$defaultInvoiceTemplateForSales}}"
                                 booked_tables="{{json_encode($bookedTables)}}"
                                 restaurant_tables = "{{$restaurantTables}}">
    </sales-or-receives-component>

@endsection
