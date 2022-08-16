@extends('layouts.app')

@section('title', trans("lang.supplier_details"))

@section('content')

    <supplier-details :supplier="{{ $supplierDetails }}"
                      tab_name="{{$tab_name}}"
                      route_name="{{$route_name}}"
    ></supplier-details>

@endsection