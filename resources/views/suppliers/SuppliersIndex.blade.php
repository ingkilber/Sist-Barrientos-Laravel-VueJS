@inject('permission', 'App\Http\Controllers\API\PermissionController')
@extends('layouts.app')

@section('title', trans("lang.suppliers"))

@section('content')

    <suppliers-page-index
            suppliers={{$permission->suppliersManagePermission()}}
    >
    </suppliers-page-index>

@endsection