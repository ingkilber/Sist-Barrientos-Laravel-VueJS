@inject('permission', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', trans("lang.contacts"))

@section('content')

   <contacts-page-index
                customers={{$permission->customersManagePermission()}}
                suppliers={{$permission->suppliersManagePermission()}}
                customer_group={{$permission->customerGroupManagePermission()}}
                tab_name={{$tab_name}}
                route_name={{$route_name}}
    >
    </contacts-page-index>

@endsection