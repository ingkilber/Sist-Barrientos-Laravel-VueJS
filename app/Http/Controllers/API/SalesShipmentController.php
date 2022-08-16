<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingInformation;
use App\Libraries\searchHelper;
use Illuminate\Support\Facades\Lang;

class SalesShipmentController extends Controller
{
    // sales shipment list

    public function salesListShipment(Request $request,$id)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;

        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $shipments = ShippingInformation::salesShipmentList($id, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);
        foreach ($shipments['data'] as $shipment) {
            if ($shipment->status == 'pending') {
                $shipment->status = Lang::get('lang.pending');
            } else if ($shipment->status == 'packet'){
                $shipment->status = Lang::get('lang.packet');
            } else if ($shipment->status == 'on the way'){
                $shipment->status = Lang::get('lang.on_the_way');
            } else if ($shipment->status == 'delivered'){
                $shipment->status = Lang::get('lang.delivered');
            } else {
                $shipment->status = Lang::get('lang.cancelled');
            }
        }
        return ['datarows' => $shipments['data'], 'count' => $shipments['count']];
    }

    public function setShippingStatus($id,$status)
    {
        ShippingInformation::where('id', $id)->update(['status' => $status]);
        $response = [
            'message' => Lang::get('lang.shipment_status_has_ben_update_successfully')
        ];
        return response()->json($response, 200);
    }
}
