@extends('layouts.app')

@section('title', trans('lang.pos_notification'))

@section('content')
    <noti-single-view :data="{{$data}}"></noti-single-view>
@endsection