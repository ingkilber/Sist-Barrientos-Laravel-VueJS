@extends('layouts.app')

@section('title', trans("lang.products"))

@section('content')
    @inject('permission', 'App\Http\Controllers\API\PermissionController')
    <product-details
            :id="{{ $id}}"
            tab_name="{{$tab_name}}"
            route_name="{{$route_name}}">
    </product-details>
@endsection