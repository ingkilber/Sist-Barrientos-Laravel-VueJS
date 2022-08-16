<?php

namespace App\Http\Controllers\API;

use App\Libraries\ProductData;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;

class ProductAttributesController extends Controller
{
    public function getAttribute(ProductData $data)
    {
        $productAttribute = ProductAttribute::showAttribute();
        $productSupportingData = $data->productSupportingData();

        $product = new ProductsController();
        $allSku = $product->getAllSku();
        $allBarCode = $product->getAllBarcode();

        return ['productAttribute' => $productAttribute, 'productSupportingData' => $productSupportingData, 'getAllBarcode' => $allBarCode, 'getAllSku' => $allSku];
    }

    public  function getProductAttributeList()
    {
        return ProductAttribute::showAttribute();
    }

    public function getAttributeList(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;

        $productAttribute = ProductAttribute::getProductAttribute($columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        return ['datarows' => $productAttribute['data'], 'count' => $productAttribute['count']];
    }

    public function storeAttribute(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        if ($productAttribute = ProductAttribute::store([
            'name' => $request->input('name'),
            'created_by' => Auth::user()->id,
        ])) {
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.product_attributes') . ' ' . Lang::get('lang.successfully_saved')))
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function showAttribute($id)
    {
        return ProductAttribute::getOne($id);
    }

    public function updateAttribute(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        $productAttribute = ProductAttribute::getOne($id);

        if ($productAttribute) {
            $productAttribute->name = $request->input('name');
            $productAttribute->save();

            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.product_attributes') . ' ' . Lang::get('lang.successfully_updated')))
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function deleteAttribute($id)
    {
        $productAttribute = ProductAttribute::getOne($id);
        $used = ProductAttributeValue::attributeCount($productAttribute->id);

        if ($used == 0) {
            ProductAttribute::deleteData($id);
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.product_attributes') . ' ' . Lang::get('lang.successfully_deleted')))
            ];

            return response()->json($response, 201);
        } else {
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.product_attributes') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . Lang::get('lang.product_attributes')))
            ];

            return response()->json($response, 200);
        }
    }
}
