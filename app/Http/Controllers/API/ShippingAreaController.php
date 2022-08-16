<?php

namespace App\Http\Controllers\API;

use App\Models\ShippingArea;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class ShippingAreaController extends Controller
{
    public function areaListGetData(Request $request)
    {

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $ShippingData = ShippingArea::getShippingData($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        return ['datarows' => $ShippingData['data'], 'count' => $ShippingData['count']];
    }

    public function areaGetData()
    {
        $response = [
            'shippingData' => ShippingArea::query()->get(['id', 'area', 'price']),
        ];
        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'area' => 'required',
            'price' => 'required',
        ]);


        $shippingItem = [
            'area' => $request->area,
            'price' => $request->price,
        ];


        if (ShippingArea::store($shippingItem)) {
            $response = [
                'message' => Lang::get('lang.shipping_item') . ' ' . Lang::get('lang.successfully_added')
            ];

            return response()->json($response, 200);
        }
    }

    public function deleteShippingInfo($id)
    {
        ShippingArea::deleteData($id);
        $response = [
            'message' => Lang::get('lang.shipping_item') . ' ' . Lang::get('lang.successfully_deleted')
        ];

        return response()->json($response, 200);
    }

    public function getRowShipping($id)
    {
        return ShippingArea::getOne($id);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'area' => 'required',
            'price' => 'required',
        ]);

        $area = $request->area;
        $price = $request->price;

        $shippingItem = array();


        $shippingItem['area'] = $area;
        $shippingItem['price'] = $price;

        ShippingArea::updateData($id, $shippingItem);

        $response = [
            'message' => Lang::get('lang.shipping_item') . ' ' . Lang::get('lang.successfully_updated')
        ];

        return response()->json($response, 200);
    }
}
