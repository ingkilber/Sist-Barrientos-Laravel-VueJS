@extends('layouts.app')

@section('title', trans("lang.products"))

@section('content')
     @inject('permission', 'App\Http\Controllers\API\PermissionController')
    <products-index
            products={{$permission->productManagePermission()}}
            product_category={{$permission->productCategoryManagePermission()}}
            product_brand={{$permission->productBrandManagePermission()}}
            product_group={{$permission->productGroupManagePermission()}}
            variant_attributes={{$permission->productVariantManagePermission()}}
            units={{$permission->productUnitManagePermission()}}
            tab_name={{$tab_name}}
            route_name={{$route_name}}
    >
    </products-index>

@endsection
