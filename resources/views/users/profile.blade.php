@extends('layouts.app')

@section('title', trans("lang.profile_title"))

@section('content')
    <profile-index :user="{{ Auth::user() }}" :profile="{{ $profile }}"></profile-index>

@endsection
