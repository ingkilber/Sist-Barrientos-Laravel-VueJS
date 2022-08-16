<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ProductGroup;

class ProductGroupsController extends Controller
{

    public function getGroup()
    {
        return $productGroup = ProductGroup::productGroupOrder();
    }

    public function getAllGroup(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $productGroup = ProductGroup::getProductGroup($columnName, $request->columnSortedBy,$limit,$request->rowOffset,$requestType);

        return ['datarows' => $productGroup['data'], 'count' => $productGroup['count']];
    }

    public function storeGroup(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        if ($productGroup = ProductGroup::store([
            'name' => $request->input('name'),
            'created_by' => Auth::user()->id
        ])) {
            $response = [
                'message' => Lang::get('lang.group') . ' ' . Lang::get('lang.successfully_added')
            ];

            return response()->json($response, 201);
        }
        $response = [
            'message' => Lang::get('lang.getting_problems')
        ];

        return response()->json($response, 404);
    }

    public function showGroup($id)
    {
        return $productGroup = ProductGroup::getOne($id);
    }

    public function updateGroup(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $productGroup = ProductGroup::getOne($id);

        if ($productGroup) {
            $productGroup->name = $request->input('name');
            $productGroup->save();

            $response = [
                'message' => Lang::get('lang.group') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        }
        $response = [
            'message' => Lang::get('lang.getting_problems')
        ];

        return response()->json($response, 404);
    }

    public function deleteGroup($id)
    {
        $productGroup = ProductGroup::getOne($id);

        $used = Product::groupId($productGroup->id);

        if ($used == 0) {
            ProductGroup::deleteData($id);
            $response = [
                'message' => Lang::get('lang.group') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.group') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.group'))
            ];

            return response()->json($response, 200);
        }
    }
}
