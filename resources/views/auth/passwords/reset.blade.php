@extends('layouts.app')

@section('title', trans('lang.reset_password'))

@section('content')

    <reset-password token="{{ $token }}" email="{{ $email }}" ></reset-password>

@endsection