<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class ProductUnitsController extends Controller
{

    public function getUnit(Request $request)
    {
        $productUnit = ProductUnit::getUnitData($request);

        return ['datarows' => $productUnit['data'], 'count' => $productUnit['count']];
    }

    public function store(Request $request)
    {
        if ($productUnit = ProductUnit::store([
            'name' => $request->input('name'),
            'short_name' => $request->input('shortname'),
            'created_by' => Auth::user()->id
        ])) {
            $response = [
                'message' => Lang::get('lang.unit') . ' ' . Lang::get('lang.successfully_saved')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
       return ProductUnit::getOne($id);
    }

    public function update(Request $request, $id)
    {
        $productUnit = ProductUnit::getOne($id);

        if ($productUnit) {
            $unitData = array(
                'name' => $request->input('name'),
                'short_name' => $request->input('shortname')
            );

            ProductUnit::updateData($id, $unitData);

            $response = [
                'message' => Lang::get('lang.unit') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function delete($id)
    {
        $used = Product::countRecord('unit_id', $id);

        if ($used == 0) {
            ProductUnit::deleteData($id);
            $response = [
                'message' => Lang::get('lang.unit') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.unit').' '.Lang::get('lang.in_use').', '.Lang::get('lang.you_can_not_delete_the').' '.strtolower(Lang::get('lang.unit'))

            ];

            return response()->json($response, 200);
        }
    }
}
