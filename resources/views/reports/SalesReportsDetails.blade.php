@extends('layouts.app')

@section('title', trans("lang.reports"))

@section('content')
    @inject('permission', 'App\Http\Controllers\API\PermissionController')
    <sales-reports-details :id="{{ $id}}"
                           :order_type ="'sales'"
                           tab_name = "{{$tabName}}"
                           route_name="{{$route_name}}" ></sales-reports-details>
@endsection