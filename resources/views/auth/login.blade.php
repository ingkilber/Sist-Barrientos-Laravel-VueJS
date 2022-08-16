@extends('layouts.app')

@section('title', trans('lang.login'))

@section('content')

    <login-form checkemail="{{$email}}" checkpass="{{$password}}"></login-form>

@endsection 