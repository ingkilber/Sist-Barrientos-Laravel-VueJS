<?php

namespace App\Http\Controllers\API;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Tax;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class TaxController extends Controller
{

    public function getData(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $taxData = Tax::getTax($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        foreach ($taxData['data'] as $item) {

            if (Product::checkExists('tax_id', $item->id) || Branch::checkExists('tax_id', $item->id)) {
                $item->used = 1;
            }

            $item->is_default = $item->is_default == 1 ? 'Yes' : 'No';
        }

        return ['datarows' => $taxData['data'], 'count' => $taxData['count']];
    }

    public function taxGetDate()
    {
        return Tax::allData();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'percentage' => 'required',
        ]);

        $is_default = $request->is_default;

        $taxItem = [
            'name' => $request->name,
            'percentage' => $request->percentage,
            'is_default' => $is_default,
        ];

        if ($is_default == 1) {
            Tax::updateDefault();
            Tax::store($taxItem);
        } else {

            if (Tax::store($taxItem)) {
                $response = [
                    'message' => Lang::get('lang.tax_item') . ' ' . Lang::get('lang.successfully_added')
                ];

                return response()->json($response, 200);
            }
        }
    }

    public function getRowTax($id)
    {
        $rowTaxData = Tax::getOne($id);

        if (Product::checkExists('tax_id', $rowTaxData->id) || Branch::checkExists('tax_id', $rowTaxData->id) || OrderItems::checkExists('tax_id', $rowTaxData->id)) {
            $rowTaxData->used = 1;
        }

        return $rowTaxData;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $name = $request->name;
        $percentage = $request->percentage;
        $is_default = $request->is_default;

        if ($is_default == 1) {
            Tax::updateDefault();
        }

        $taxItem = array();

        if (!Product::checkExists('tax_id', $id) || Branch::checkExists('tax_id', $id) || OrderItems::checkExists('tax_id', $id)) {
            $taxItem['percentage'] = $percentage;
        }

        $taxItem['name'] = $name;
        $taxItem['is_default'] = $is_default;

        if (Tax::updateData($id, $taxItem)) {
            $default_tax_id = Tax::getDefaultTaxId();

            Branch::setDefaultTax($default_tax_id);

            $response = [
                'message' => Lang::get('lang.tax_item') . ' ' . Lang::get('lang.successfully_updated')

            ];

            return response()->json($response, 200);
        }
    }

    public function deleteTax($id)
    {
        $used = 0;

        if (Product::checkExists('tax_id', $id) || Branch::checkExists('tax_id', $id) || OrderItems::checkExists('tax_id', $id)) {
            $used = 1;
        }

        if ($used == 0) {
            Tax::deleteData($id);
            $response = [
                'message' => Lang::get('lang.tax_item') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        } else {
            $response = [
                'message' => Lang::get('lang.tax_item') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.tax_item'))
            ];

            return response()->json($response, 200);
        }
    }
}
