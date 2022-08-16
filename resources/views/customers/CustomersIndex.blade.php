@inject('permission', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', trans("lang.customers"))

@section('content')

    <customers-page-index
            customers={{$permission->customersManagePermission()}}
            customer_group={{$permission->customerGroupManagePermission()}}
    >

    </customers-page-index>

@endsection

