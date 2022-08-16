<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ProductCategory;

class ProductCategoriesController extends Controller
{

    public function index()
    {
        return ProductCategory::showCategory();
    }

    public function getCategory(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $productCategory = ProductCategory::getProductCategory($columnName, $request->columnSortedBy,$limit,$request->rowOffset,$requestType);

        return ['datarows' => $productCategory['data'], 'count' => $productCategory['count']];
    }


    public function storeCategory(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $storeCategory = [
            'name' => $request->input('name'),
            'created_by' => Auth::user()->id
        ];

        if ($productCategory = ProductCategory::store($storeCategory)) {
            $response = [
                'message' => Lang::get('lang.category') . ' ' . Lang::get('lang.successfully_saved')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function showCategory($id)
    {
        return ProductCategory::getOne($id);
    }

    public function updateCategory(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $productCategory = ProductCategory::getOne($id);

        $categoryName = ['name' => $request->input('name')];

        if ($productCategory) {
            ProductCategory::updateData($id, $categoryName);
            $response = [
                'message' => Lang::get('lang.category') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function deleteCategory($id)
    {
        $used = Product::categoryIdUsed($id);

        if ($used == 0) {
            ProductCategory::deleteData($id);
            $response = [
                'message' => Lang::get('lang.category') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.category') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.category'))
            ];

            return response()->json($response, 200);
        }
    }
}
