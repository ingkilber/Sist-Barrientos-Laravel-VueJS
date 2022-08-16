@extends('layouts.app')

@section('title', trans('lang.dashboard'))

@section('content')
    @inject('permission', 'App\Http\Controllers\API\PermissionController')
    <dashboard see_profit_permission={{$permission->checkProfitPermission()}}></dashboard>

@endsection