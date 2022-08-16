@extends('layouts.app')

@section('title', trans("lang.user_details"))

@section('content')

<user-details :user="{{ $userDetails }}"
              tab_name = "{{$tab_name}}"
              route_name = "{{$route_name}}"></user-details>

@endsection
