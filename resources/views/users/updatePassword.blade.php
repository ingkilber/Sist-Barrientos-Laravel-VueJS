@extends('layouts.app')

@section('title', trans("lang.account_update"))

@section('content')

    <change-password :user="{{ $user }}"></change-password>

@endsection
