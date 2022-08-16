<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ProductBrand;

class ProductBrandsController extends Controller
{

    public function index()
    {
        return ProductBrand::showProductBrand();
    }

    public function getBrand(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $productBrand = ProductBrand::getProductBrand($columnName, $request->columnSortedBy,$limit,$request->rowOffset,$requestType);

        return ['datarows' => $productBrand['data'], 'count' => $productBrand['count']];
    }

    public function storeBrand(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        if ($productBrand = ProductBrand::store([
            'name' => $request->input('name'),
            'created_by' => Auth::user()->id
        ])) {
            $response = [
                'message' => Lang::get('lang.brand').' '.Lang::get('lang.successfully_saved')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function showBrand($id)
    {
        return ProductBrand::getOne($id);
    }

    public function updateBrand(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $productBrand = ProductBrand::getOne($id);

        if ($productBrand) {
            $productBrand->name = $request->input('name');
            $productBrand->save();
            $response = [
                'message' => Lang::get('lang.brand').' '.Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function deleteBrand($id)
    {
        $used = Product::usedProduct($id);

        if ($used == 0){
            ProductBrand::deleteData($id);
            $response = [
                'message' => Lang::get('lang.brand').' '.Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.brand').' '.Lang::get('lang.in_use').', '.Lang::get('lang.you_can_not_delete_the').' '.strtolower(Lang::get('lang.brand'))
            ];

            return response()->json($response, 200);
        }
    }
}