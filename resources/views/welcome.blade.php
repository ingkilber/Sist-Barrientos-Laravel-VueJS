@extends('layouts.app')

@section('title', trans('lang.login'))

@section('content')

    <login-form appurl="{{ $appurl }}"></login-form>

@endsection