@extends('layouts.app')

@section('title', trans('lang.notification'))

@section('content')
    <all-notification-view :data="{{$data}}" :profile="{{Auth::User()}}"></all-notification-view>
@endsection