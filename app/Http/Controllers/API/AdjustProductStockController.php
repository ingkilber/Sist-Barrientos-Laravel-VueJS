<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdjustProductStockType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class AdjustProductStockController extends Controller
{
    public function index()
    {
        return AdjustProductStockType::allData();
    }

    public function getAdjustStockList(Request $request)
    {

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $adjustStockList = AdjustProductStockType::getAdjustStockType($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        return ['datarows' => $adjustStockList['data'], 'count' => $adjustStockList['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $title = $request->title;

        AdjustProductStockType::store([
            'title' => $request->title,
            'created_by' => Auth::user()->id
        ]);

        $response = [
            'message' => ucfirst(strtolower(Lang::get('lang.adjust_stock_type') . ' ' . Lang::get('lang.successfully_added')))
        ];

        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
        ]);

        $data = array();
        $data['title'] = $request->title;
        $data['created_by'] = Auth::user()->id;

        if (AdjustProductStockType::updateData($id, $data)) {
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.adjust_stock_details') . ' ' . Lang::get('lang.successfully_updated')))
            ];

            return response()->json($response, 200);
        }
    }

    public function getData()
    {
        return AdjustProductStockType::index(['id', 'title']);
    }

    public function getAdjustStockDetailsData($id)
    {
        return AdjustProductStockType::getOne($id);
    }

    public function deleteAdjustStockType($id)
    {
        AdjustProductStockType::deleteData($id);

        $response = [
            'message' => ucfirst(strtolower(Lang::get('lang.adjust_stock_type') . ' ' . Lang::get('lang.successfully_deleted')))
        ];

        return response()->json($response, 200);
    }
}
