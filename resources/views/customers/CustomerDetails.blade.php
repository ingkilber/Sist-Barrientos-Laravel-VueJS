@extends('layouts.app')

@section('title', trans("lang.customer_details"))

@section('content')

   <customer-details :customer="{{ $customerDetails }}"
                     tab_name = "{{$tab_name}}"
                     route_name = "{{$route_name}}"
   ></customer-details>

@endsection