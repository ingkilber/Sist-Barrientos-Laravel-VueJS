<?php

namespace App;

namespace App\Models;

class ShippingInformation extends BaseModel
{
    protected $fillable = ['shipping_area_id', 'price', 'shipping_address', 'order_id', 'branch_id', 'status'];

    public static function salesShipmentList($id, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = ShippingInformation::
        join('shipping_areas', 'shipping_information.shipping_area_id', 'shipping_areas.id')
            ->join('branches', 'shipping_information.branch_id', '=', 'branches.id')
            ->join('orders', 'shipping_information.order_id', '=', 'orders.id')
            ->select('shipping_information.*', 'branches.name as branch_name', 'shipping_areas.area as area_name', 'orders.invoice_id')
            ->where('shipping_information.branch_id', $id);

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('shipping_information.price', 'LIKE', '%' . $searchValue . '%');
                $query->Orwhere('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
            });
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }

    }

    public static function shipmentReports($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = ShippingInformation::
        join('shipping_areas', 'shipping_information.shipping_area_id', 'shipping_areas.id')
            ->join('branches', 'shipping_information.branch_id', '=', 'branches.id')
            ->join('orders', 'shipping_information.order_id', '=', 'orders.id')
            ->select('shipping_information.*', 'branches.name as branch_name', 'shipping_areas.area as area_name', 'orders.invoice_id');

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {

                    $query->where('shipping_information.status', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {

                    $query->where('branches.id', $singleFilter['value']);
                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('shipping_information.price', 'LIKE', '%' . $searchValue . '%');
                $query->Orwhere('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
            });
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }

    }

    public static function orderShipment($orderId)
    {
        return Order::query()
            ->join('order_items', 'order_items.order_id', 'orders.id')
            ->join('shipping_information', 'shipping_information.order_id','orders.id' )
            ->leftJoin('shipping_areas', 'shipping_information.shipping_area_id','shipping_information.id' )
            ->select('orders.date', 'order_items.type',  'order_items.type', 'shipping_information.shipping_address', 'shipping_information.price')
            ->where('order_items.type', 'shipment')
            ->where('shipping_information.order_id', $orderId)->first();
    }
}
