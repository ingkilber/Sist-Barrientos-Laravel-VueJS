<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductApiController extends Controller
{
    public function index()
    {
        ini_set('memory_limit', '1024MB');
        return Product::with('variants')->get();
    }
}
