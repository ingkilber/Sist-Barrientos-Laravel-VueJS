@inject('perm', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', 'Purchase')

@section('content')


    <sales-or-receives-component :user="{{ Auth::user() }}"
                                 :total_branch="{{$totalBranch}}"
                                 :order_type="'receiving'"
                                 :sold_to="'receiving_to'"
                                 :sold_by="'receiving_by'"
                                 manage_receives={{$perm->receivesManagePermission()}}
                                 current_branch="{{$currentBranch}}"
                                 current_cash_register="{{$currentCashRegister}}"
                                 sales_receiving_type="{{$receivingType}}"
                                 purchase_return_status="{{$purchaseReturnStatus}}"
                                 all_branch="{{$branches}}"
                                 auto_invoice="{{$autoInvoice}}"
                                 payment_types="{{$paymentTypes}}"
                                 supplier="{{$supplier}}"
                                 product="{{$product}}"
                                 app_name="{{$appName}}"
                                 invoice_prefix="{{$invoicePrefix}}"
                                 invoice_suffix="{{$invoiceSuffix}}"
                                 last_invoice="{{$lastInvoiceNum}}"
                                 is_branch_selected="{{$isBranchSelected}}"
                                 default_receive_invoice_template="{{$defaultInvoiceTemplateForReceives}}">

    </sales-or-receives-component>

@endsection