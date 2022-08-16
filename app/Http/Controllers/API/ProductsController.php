<?php

namespace App\Http\Controllers\API;

use App\Libraries\Permissions;
use App\Libraries\ProductData;
use App\Libraries\searchHelper;
use App\Models\Branch;
use App\Models\Order;
use App\Models\ProductUnit;
use App\Models\Setting;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductGroup;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttribute;
use App\Models\OrderItems;
use App\Libraries\AllSettingFormat;
use App\Libraries\imageHandler;
use Milon\Barcode\DNS1D;


class ProductsController extends Controller
{
    public function permissionCheck()
    {
        return new Permissions();
    }

    public function getProduct(Request $request)
    {
        $products = Product::getAllData($request);
        $this->setBarcode($products['data']);

        if (empty($request->reqType)) {
            return ['datarows' => $products['data'], 'count' => $products['count']];
        } else {
            return ['datarows' => $products];
        }
    }

    private function setBarcode($products)
    {
        $collection = collect($products);
        $barcodeManipulation= $collection->map(function ($item) {

            $variants = collect($item->variants);

            $variants->map(function ($barcode){
                $barcode->newBarcode = '<img src="data:image/png;base64,' . (new DNS1D)->getBarcodePNG($barcode->newBarcode, "C39") . '" alt="barcode" />';
            });
        });
        return $barcodeManipulation->all();

    }

    public function showVariant($id)
    {
        return ProductVariant::getFirst('*', 'product_id', $id);
    }

    public function productSupportingData()
    {
        return [
            'brands' => ProductBrand::allData(),
            'categories' => ProductCategory::allData(),
            'groups' => ProductGroup::allData(),
            'taxes' => Tax::allData(),
            'branches' => Branch::allData(),
            'units' => ProductUnit::allData()
        ];
    }

    public function storeProduct(Request $request)
    {
        $name = $request->name;
        $description = $request->description;
        $category = $request->category;
        $brand = $request->brand;
        $group = $request->group;
        $unit = $request->unit;
        $taxID = $request->taxID;
        $image = $request->image;
        $branch = $request->branch;
        $fileNameToStore = 'no_image.png';
        if ($request->type == 0) {
            $product_type = 'standard';
        } else {
            $product_type = 'variant';
        }
        $created_by = Auth::user()->id;
        $imageHandler = new imageHandler;
        if ($file = $image) {
            $fileNameToStore = $imageHandler->imageUpload($image, 'product_', 'uploads/products/');
        }
        $productData = [
            'title' => $name,
            'description' => $description,
            'category_id' => $category,
            'brand_id' => $brand,
            'group_id' => $group,
            'unit_id' => $unit,
            'product_type' => $product_type,
            'imageURL' => $fileNameToStore,
            'created_by' => $created_by
        ];
        if ($taxID == 'no-tax') {
            $productData['taxable'] = 0;
        } else if ($taxID == 'default-tax') {
            $productData['taxable'] = 1;
            $productData['tax_type'] = 'default';
        } else {
            $productData['taxable'] = 1;
            $productData['tax_type'] = 'custom';
            $productData['tax_id'] = $taxID;
        }
        $productId = Product::store($productData);
        $this->variantInsertion($request, $productId, $branch);
        $response = [
            'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_added')
        ];
        return response()->json($response, 201);
    }

    private function variantInsertion($request, $productId, $branch)
    {

        $allSetting = new AllSettingFormat;
        $lastInvoiceNumber = Setting::getSettingValue('purchase_last_invoice_number')->setting_value;
        $invoiceFixes = $allSetting->getInvoiceFixes();

        $lastproductid = $productId->id;
        $variant = $request->variant;
        $sallingPrice = $request->sallingPrice ? $request->sallingPrice : 0;
        $receivingPrice = $request->receivingPrice ? $request->receivingPrice : 0;
        $sku = $request->sku;
        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;
        $reorder = $request->reorder;
        $barcode = $request->barcode;
        $quantity = $request->quantity;
        $enabled = $request->enabled;
        $variantDetails = $request->variantDetails;
        $chipValue = $request->chipValues;
        $imageHandler = new imageHandler;
        $variantInsertValue = [];

        if (!empty($variantDetails)) {
            $variantAttributeInsertValue = $this->variantAttributeInsertValues($chipValue, $productId->id);
            ProductAttributeValue::insertData($variantAttributeInsertValue);

            foreach ($variantDetails as $item) {
                $data = $item['imageURL'] ? $item['imageURL'] : false;
                if ($data != null) {
                    $fileNameToStore2 = $imageHandler->imageUpload($item['imageURL'], 'product_', 'uploads/products/');
                } else {
                    $fileNameToStore2 = 'no_image.png';
                }
                if (!isset($item['sku'])) {
                    $skuValue = null;
                } else {
                    $skuValue = $skuPrefix . $item['sku'];
                }
                array_push($variantInsertValue, [
                    'product_id' => $productId->id,
                    'variant_title' => $item['variant'],
                    'attribute_values' => $item['variant'],
                    're_order' => $item['reOrder'],
                    'purchase_price' => $item['purchasePrice'] ? $item['purchasePrice'] : 0,
                    'selling_price' => $item['sellingPrice'] ? $item['sellingPrice'] : 0,
                    'enabled' => $item['enabled'],
                    'imageURL' => $fileNameToStore2,
                    'bar_code' => $item['barcode'],
                    'sku' => $item['sku']
                ]);


            };

             ProductVariant::insertData($variantInsertValue);
             $productVariants = ProductVariant::allProductVariantById($lastproductid);
             $productVariant = [];
             foreach ($productVariants as $productVariantId)
             {
                 array_push($productVariant, $productVariantId->id);
             }

            $this->productVariantOrderItem($branch,$lastproductid, $productVariant, $variantDetails, $allSetting, $lastInvoiceNumber, $invoiceFixes); // product order item table

        } else {
            if ($sku != null) {
                $sku = $skuPrefix . $sku;
            }

            $storableData = ['product_id' => $productId->id,
                'variant_title' => 'default_variant',
                'attribute_values' => 'default_variant',
                'purchase_price' => $receivingPrice,
                'selling_price' => $sallingPrice,
                'sku' => $sku,
                're_order' => $reorder,
                'bar_code' => $barcode,
                'enabled' => $enabled
            ];
            $productVariantlast_id = ProductVariant::store($storableData);

            $productVariantlast_id = $productVariantlast_id->id;
            $this->productOrder($branch, $receivingPrice, $quantity, $lastproductid, $productVariantlast_id, $allSetting, $lastInvoiceNumber, $invoiceFixes); // product order table
        }
    }
    // Order Table
    private function productOrder($branch, $receivingPrice, $quantit, $lastproductid, $productVariantlast_id, $allSetting, $lastInvoiceNumber, $invoiceFixes)
    {
        if (!empty($branch))
        {
          return Order::productStore($branch, $receivingPrice, $quantit, $lastproductid, $productVariantlast_id, $allSetting, $lastInvoiceNumber, $invoiceFixes);
        }
    }

    // Order Item
    private function productVariantOrderItem($branch, $lastproductid, $varientsId, $variantDetails, $allSetting, $lastInvoiceNumber, $invoiceFixes)
    {
        if (!empty($branch))
        {
            return Order::productVariantOrderItem($branch, $lastproductid, $varientsId, $variantDetails, $allSetting, $lastInvoiceNumber, $invoiceFixes);
        }
    }

    private function variantAttributeInsertValues($data, $id)
    {

        $chipValue = $data;
        $variantAttributeInsertValue = [];
        for ($i = 0; $i < sizeof($chipValue); $i++) {
            $attributeValue = '';
            if ($chipValue[$i] != null) {
                for ($j = 0; $j < sizeof($chipValue[$i]); $j++) {
                    $attributeValue = $attributeValue . ',' . $chipValue[$i][$j];
                }
                array_push($variantAttributeInsertValue, ['product_id' => $id, 'attribute_id' => $i, 'values' => $attributeValue]);
            }
        }
        return $variantAttributeInsertValue;
    }

    public static function getAllBarcode($productId = 0)
    {
        $barcodeList = [];
        $allBarcode = ProductVariant::getSkuBarcode('bar_code', $productId);

        foreach ($allBarcode as $bar_code) {
            array_push($barcodeList, $bar_code->bar_code);
        }

        return ['allBarcode' => $barcodeList];
    }

    public static function getAllSku($productId = 0)
    {
        $skuList = [];
        $allSku = ProductVariant::getSkuBarcode('sku', $productId);

        foreach ($allSku as $sku) {
            array_push($skuList, $sku->sku);
        }
        return ['allSku' => $skuList];
    }

    public function getProductEditData($id, ProductData $data)
    {
        $productDetails = Product::getFirst('*', 'id', $id);
        $variantDetails = ProductVariant::getVariantByProductId($id);
        $productSupportingData = $data->productSupportingData();
        $defaultReorder = Setting::getSettingValue('re_order')->setting_value;

        if ($productDetails->product_type == 'variant') {
            $variantAttributes = ProductAttributeValue::getById($id);
            $variantData = array();

            foreach ($variantAttributes as $variant) {
                $variantData[$variant->id] = explode(',', $variant->values);
                array_shift($variantData[$variant->id]);
            }

            $productAttributes = ProductAttribute::index(['id', 'name']);
            foreach ($variantDetails as $variant) {
                $variant['isImageChanged'] = false;
                $variant['isBarcodeExsist'] = false;
                $variant['isSkuExsist'] = false;
                $variant['variantReceivePriceMoreThan'] = false;
                $variant['variantSalePriceMoreThan'] = false;
                if ($variant['reOrder'] == null) $variant['reOrder'] = $defaultReorder;
            }

            return ['productDetails' => $productDetails, 'variantDetails' => $variantDetails, 'variantData' => $variantData, 'AllAttributesProduct' => $productAttributes, 'productSupportingData' => $productSupportingData, 'defaultReorder' => $defaultReorder, 'getAllBarcode' => $this->getAllBarcode($id), 'getAllSku' => $this->getAllSku($id)];
        } else return ['productDetails' => $productDetails, 'variantDetails' => $variantDetails, 'productSupportingData' => $productSupportingData, 'getAllBarcode' => $this->getAllBarcode($id), 'getAllSku' => $this->getAllSku($id)];
    }

    public function editProduct(Request $request, $id)
    {
        $hasDuplicate = [];
        $imageHandler = new imageHandler;

        if ($request->type == 0) $productType = 'standard';
        else $productType = 'variant';

        $data = array();
        $data['title'] = $request->name;
        $data['description'] = $request->description;
        $data['category_id'] = $request->category;
        $data['brand_id'] = $request->brand;
        $data['group_id'] = $request->group;
        $data['unit_id'] = $request->unit;
        $data['product_type'] = $productType;
        $data['created_by'] = Auth::user()->id;
        $variantDetails = $request->variantDetails;

        if ($request->taxID == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($request->taxID == 'default-tax') {
            $data['taxable'] = 1;
            $data['tax_type'] = 'default';
        } else {
            $data['taxable'] = 1;
            $data['tax_type'] = 'custom';
            $data['tax_id'] = $request->taxID;
        }

        if (empty($request->image)) Product::updateData($id, $data);
        else {
            $fileNameToStore = $imageHandler->imageUpload($request->image, 'product_', 'uploads/products/');
            $data['imageURL'] = $fileNameToStore;
            Product::updateData($id, $data);
        }

        if ($request->type == 0) {
            $variantData['purchase_price'] = $request->receivingPrice ? $request->receivingPrice : 0;
            $variantData['selling_price'] = $request->sallingPrice ? $request->sallingPrice : 0;
            $variantData['sku'] = $request->sku;
            $variantData['bar_code'] = $request->barcode;
            $variantData['re_order'] = $request->reorder;

            ProductVariant::editData('product_id', $id, $variantData);
        } else {

            $hasDuplicate = $this->updateProductVariant($variantDetails, $request, $id);

            ProductAttributeValue::deleteRecord('product_id', $id);

            $variantAttributeInsertValue = $this->variantAttributeInsertValues($request->chipValues, $id);

            ProductAttributeValue::insertData($variantAttributeInsertValue);
        }
        if ($hasDuplicate != null && (array_key_exists("duplicateSku", $hasDuplicate) || array_key_exists("duplicateBarcode", $hasDuplicate))) {
            $response = [
                'duplicateData' => $hasDuplicate
            ];
            return response()->json($response, 400);
        } else {
            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 200);
        }
    }

    private function updateProductVariant($variantDetails, $request, $id)
    {
        $duplicateData = [];
        $isDuplicate = false;
        $imageHandler = new imageHandler;
        $duplicateSku = [];
        $duplicateBarcode = [];
        foreach ($variantDetails as $index => $item) {

            if (!empty($request->sku[$index])) {
                $skuExists = ProductVariant::ifExists('sku', $request->sku[$index]);
                if ($skuExists > 1) {
                    $isDuplicate = true;
                    array_push($duplicateSku, $request->sku[$index]);
                }
                $duplicateData['duplicateSku'] = $duplicateSku;
            }

            if (!empty($request->barcode[$index])) {
                $barcodeExists = ProductVariant::ifExists('bar_code', $request->barcode[$index]);
                if ($barcodeExists > 1) {
                    $isDuplicate = true;
                    array_push($duplicateBarcode, $request->barcode[$index]);
                }
                $duplicateData['duplicateBarcode'] = $duplicateBarcode;
            }
            if (!$isDuplicate) {

                $variantInsertValue = [
                    'product_id' => $id,
                    'variant_title' => $item['variant'],
                    'attribute_values' => $item['variant'],
                    're_order' => $item['reOrder'],
                    'purchase_price' => $item['purchasePrice'] ? $item['purchasePrice'] : 0,
                    'selling_price' => $item['sellingPrice'] ? $item['sellingPrice'] : 0,
                    'enabled' => $item['enabled'],
                    'bar_code' => $item['barcode'],
                ];
                if (!$item['enabled']) {
                    if ($item['purchasePrice'] == null) {
                        $variantInsertValue['purchase_price'] = 0;
                    }
                    if ($item['sellingPrice'] == null) {
                        $variantInsertValue['selling_price'] = 0;
                    }
                }

                if ($item['isImageChanged'] && isset($item['imageURL']) && $item['imageURL'] != null && $item['imageURL'] != 'no_image.png') {
                    $variantInsertValue['imageURL'] = $imageHandler->imageUpload($item['imageURL'], 'product_', 'uploads/products/');
                }

                if (isset($item['sku']) && $item['sku'] != null && $item['sku'] != '') {
                    $variantInsertValue['sku'] = $item['sku'];
                }

                if ($item['id'] != null) {
                    ProductVariant::updateData($item['id'], $variantInsertValue);
                } else {
                    ProductVariant::insertData($variantInsertValue);
                }
            } else return $duplicateData;
        }
    }

    public function deleteProduct($id)
    {
        if (OrderItems::checkExists('product_id', $id)) {
            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.product'))
            ];

            return response()->json($response, 200);
        } else {
            Product::deleteData($id);
            ProductVariant::deleteRecord('product_id', $id);

            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 201);
        }
    }

    public function getProductDetails($id)
    {
        $productDetails = Product::detailsById($id);

        if ($productDetails->tax_type = 'custom') {
            $productDetails->tax_type = Lang::get('lang.customs');
        }

        if ($productDetails->product_type == 'standard') {
            $productDetails->temp_product_type = Lang::get('lang.standard');
            $variant = ProductVariant::getFirst('*', 'product_id', $id);
            $allSettingFormat = new AllSettingFormat;
            $variant->price = $allSettingFormat->getCurrency($variant->price);

            return ['productDetails' => $productDetails, 'variant' => $variant];
        } else {
            $productDetails->temp_product_type = Lang::get('lang.variant');
            return ['productDetails' => $productDetails];
        }
    }

    public function getVariantDetails($id)
    {
        $variant = ProductVariant::getAll('*', 'product_id', $id);
        $count = ProductVariant::countRecord('product_id', $id);

        $allSettingFormat = new AllSettingFormat;
        foreach ($variant as $data) {
            $data->price = $allSettingFormat->getCurrency($data->price);
        }
        return ['datarows' => $variant, 'count' => $count];
    }

    public function importProduct(Request $request)
    {
        $row = 0;
        $invalidData = [];
        $created_by = Auth::user()->id;
        $invalidDataCollection = [];
        $errorPreviewData = [];

        // Validate with correct column name
        $isValid = $this->importFileValidation($request->importData, $request->requiredColumns);

        $defaultReorder = Setting::getSettingValue('re_order')->setting_value;


        if ($isValid == true) {
            foreach ($request->importData as $index => $product) {
                if ($product['RE-ORDER'] == null) {
                    $product['RE-ORDER'] = $defaultReorder;
                }

                $productData = [
                    'title' => $product['NAME'],
                    'category_id' => $product['CATEGORY'],
                    'brand_id' => $product['BRAND'],
                    'group_id' => $product['GROUP'],
                    'name' => $product['UNIT'],
                    'short_name' => $product['UNIT_SHORT_NAME'],
                    'product_type' => strtolower($product['PRODUCT_TYPE']),
                    'variant_title' => $product['VARIANT_NAME'],
                    'attribute_value' => $product['VARIANT_VALUE'],
                    'variant_details' => $product['VARIANT_DETAIL'],
                    'sku' => $product['SKU'],
                    'bar_code' => $product['BARCODE'],
                    're_order' => $product['RE-ORDER'],
                    'purchase_price' => $product['PURCHASE-PRICE'],
                    'selling_price' => $product['SELLING-PRICE'],
                    'imageURL' => 'no_image.png',
                    'created_by' => $created_by
                ];

                if (strtolower($product['PRODUCT_TYPE']) == 'standard') {
                    $rowData = $this->importStandardProduct($productData, $index, $request->fill_able);
                    if (gettype($rowData) == "array") array_push($invalidData, $rowData);
                    else $row++;
                } elseif (strtolower($product['PRODUCT_TYPE']) == 'variant') {
                    $rowData = $this->importVariantProduct($productData, $index);
                    if (gettype($rowData) == "array") array_push($invalidData, $rowData);
                    else $row++;
                } else {
                    $errorData['index'] = $index;
                    if ($product['NAME'] == null) {
                        $errorData['emptyData'] = $product['NAME'];
                    }
                    $errorData['productType'] = $product['PRODUCT_TYPE'];
                    if (ProductVariant::ifExists('product_variants.sku', $productData['sku'])) $errorData['invalidSku'] = $productData['sku'];
                    if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) $errorData['invalidBarcode'] = $productData['bar_code'];
                    array_push($invalidData, $errorData);
                }
            }
        }
        if (sizeof($request->importData) == $row) {

            $response = [
                'message' => Lang::get('lang.product') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];
            return response()->json($response, 201);
        } else {

            $showInvalidData = $request->importData;
            foreach ($showInvalidData as $index => $product) {
                foreach ($invalidData as $item) {
                    if ($item['index'] == $index) {
                        $excelErrorData = [];
                        if (array_key_exists("invalidSku", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'SKU' . ': ' . $item['invalidSku'] . ' ');
                        if (array_key_exists("invalidBarcode", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'BARCODE' . ': ' . $item['invalidBarcode']);
                        if (array_key_exists("invalidTitle", $item)) array_push($excelErrorData, Lang::get('lang.duplicate_in') . ' ' . 'TITLE' . ': ' . $item['invalidTitle']);
                        if (array_key_exists("invalidData", $item)) array_push($excelErrorData, Lang::get('lang.invalid_data') . ' ' . 'INVALID DATA' . ': ' . $item['invalidData']);
                        if (array_key_exists("emptyData", $item)) array_push($excelErrorData, Lang::get('lang.field_must_not_be_empty'));
                        if (array_key_exists("productType", $item)) array_push($excelErrorData, Lang::get('lang.product_type_must_be_either_standard_or_variant'));
                        $product['INVALID_DATA'] = implode(" ", $excelErrorData);
                    }
                }
                array_push($invalidDataCollection, $product);
            }

            foreach ($invalidData as $data) {
                if (array_key_exists("invalidSku", $data)) array_push($errorPreviewData, $data['invalidSku']);
                if (array_key_exists("invalidBarcode", $data)) array_push($errorPreviewData, $data['invalidBarcode']);
                if (array_key_exists("invalidTitle", $data)) array_push($errorPreviewData, $data['invalidTitle']);
                if (array_key_exists("productType", $data)) array_push($errorPreviewData, $data['productType']);
            }


            $columnTitles = $request->requiredColumns;
            array_push($columnTitles, 'INVALID_DATA');
            $response = [
                'message' => Lang::get('lang.invalid_data_download_file_to_see_the_error'),
                'excelInvalidDatas' => $invalidDataCollection,
                'requiredColumns' => $columnTitles,
                'errorPreviewData' => $errorPreviewData
            ];
            return response()->json($response, 400);
        }
    }

    private function importFileValidation($productImportedData, $correctColumnNames)
    {
        //column key name must have the same as demo file columns name
        $data = $productImportedData[0];
        $inCorrectColumn[] = "";
        foreach ($correctColumnNames as $correctColumnName) {
            if (isset($data[$correctColumnName])) return $isValid = true;
            else {
                foreach ($correctColumnNames as $column) {
                    if (!isset($data[$column])) array_push($inCorrectColumn, $column);
                }
                return ['inCorrectColumn' => $inCorrectColumn];
            }
        }
    }


    private  function validationForOpeningStockColumns($productImportedData, $correctColumnNames)
    {
        $inCorrectColumn[] = "";
        foreach(array_keys($productImportedData[0]) as $columnName){
            if(!in_array($columnName, $correctColumnNames)) {
                array_push($inCorrectColumn, $columnName);
                return ['inCorrectColumn' => $inCorrectColumn];
            } else return true;

        }
    }

    private function importStandardProduct($productData, $index, $fillAble)
    {
        $errorData = [];
        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;

        $count = ProductVariant::productAlreadyExisted($productData['sku'], $productData['bar_code'], $productData['title'], $productData['product_type']);
        if ($count == 0 && !empty($productData['title'])) {
            $data = $this->insertProductAttributeForImport($productData);
            $variantData = array(
                'product_id' => $data['productId'],
                'variant_title' => 'default_variant',
                'attribute_values' => 'default_variant',
                'purchase_price' => $productData['purchase_price'],
                'selling_price' => $productData['selling_price'],
                'enabled' => 1,
                'bar_code' => $productData['bar_code'],
                're_order' => $productData['re_order']
            );
            if (empty($productData['sku'])) {
                $variantData['sku'] = null;
            } else $variantData['sku'] = $skuPrefix . $productData['sku'];
            ProductVariant::store($variantData);
            return true;
        } else {

            $errorData['index'] = $index;

            if ($productData['sku'] != null) {
                if (ProductVariant::ifExists('product_variants.sku', $productData['sku'])) $errorData['invalidSku'] = $productData['sku'];
            }
            if ($productData['bar_code'] != null) {
                if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) $errorData['invalidBarcode'] = $productData['bar_code'];
            }

            if (Product::ifExists('products.title', $productData['title'])) $errorData['invalidTitle'] = $productData['title'];
            foreach ($fillAble as $field) {
                if ($productData[$field] == null) $errorData['emptyData'] = $productData[$field];
            }
            return $errorData;
        }
    }

    private function importVariantProduct($productData, $index)
    {
        $attributes = explode(",", $productData['variant_title']);
        $createdBy = Auth::user()->id;
        foreach ($attributes as $attribute) {
            $checkAttributeExists = ProductAttribute::where('name', $attribute)->exists();
            if (!$checkAttributeExists) {
                ProductAttribute::insertData(['name' => $attribute, 'created_by' => $createdBy]);
            }
        }
        $checkVariant = Product::getAllDetails($productData);
        if ($checkVariant == 1) {
            $data = Product::getVariantId($productData);
            $productId = $data['id'];
        } else {
            $data = $this->insertProductAttributeForImport($productData);
            $productId = $data['productId'];
        }
        $errorData = [];
        $attributeName = explode(",", $productData['variant_title']);
        $attributeValue = explode(",", $productData['attribute_value']);

        $attributeData = [];
        array_push($attributeData, null);
        foreach ($attributeValue as $attribute) {
            array_push($attributeData, array(ucfirst($attribute)));
        }
        $variantAttributeInsertValue = $this->variantAttributeInsertValues($attributeData, $productId);

        ProductAttributeValue::insertData($variantAttributeInsertValue);
        $variantAttributeInsertValue = [];
        $attributeItems = [];

        foreach ($attributeName as $item) {
            $id = ProductAttribute::getIdOfExisted('name', $item);
            if (isset($id)) {
                array_push($attributeItems, $id->id);
            }
        }
        foreach ($attributeItems as $item) {
            for ($i = 0; $i < sizeof($attributeName); $i++) {
                array_push($variantAttributeInsertValue, ['product_id' => $data['productId'], 'attribute_id' => $item, 'values' => $attributeValue[$i]]);
            }
        }

        $skuPrefix = Setting::getSettingValue('sku_prefix')->setting_value;

        $variantData = array(
            'sku' => $skuPrefix . $productData['sku'],
            'product_id' => $productId,
            'variant_title' => ucfirst($productData['attribute_value']),
            'attribute_values' => ucfirst($productData['attribute_value']),
            'variant_details' => $productData['variant_details'],
            'purchase_price' => $productData['purchase_price'],
            'selling_price' => $productData['selling_price'],
            'enabled' => 1,
            'bar_code' => $productData['bar_code'],
            're_order' => $productData['re_order']
        );

        if (empty($productData['sku'])) {
            $variantData['sku'] = null;
        } else $variantData['sku'] = $skuPrefix . $productData['sku'];

        $count = ProductVariant::productAlreadyExisted($productData['sku'], $productData['bar_code'], $productData['title'], $productData['product_type']);

        if ($count == 0) {
            ProductVariant::store($variantData);
            return true;
        } else {

            $errorData['index'] = $index;
            if ($productData['sku'] != null) {
                if (ProductVariant::ifExists('sku', $productData['sku'])) {
                    $errorData['invalidSku'] = $productData['sku'];
                }
            }
            if ($productData['bar_code'] != null) {
                if (ProductVariant::ifExists('product_variants.bar_code', $productData['bar_code'])) {
                    $errorData['invalidBarcode'] = $productData['bar_code'];
                }
            }

            if ($productData['sku'] == null && $productData['bar_code'] == null) {
                return true;
            } else {
                return $errorData;
            }
        }
    }

    private function insertProductAttributeForImport($data)
    {
        $existedCategory = ProductCategory::getIdOfExisted('name', $data['category_id']);
        if (isset($existedCategory)) $categoryId = $existedCategory->id;
        else $categoryId = ProductCategory::getInsertedId(['name' => $data['category_id'], 'created_by' => $data['created_by']]);
        $data['category_id'] = $categoryId;

        $existedBrand = ProductBrand::getIdOfExisted('name', $data['brand_id']);
        if (isset($existedBrand)) $brandId = $existedBrand->id;
        else $brandId = ProductBrand::getInsertedId(['name' => $data['brand_id'], 'created_by' => $data['created_by']]);
        $data['brand_id'] = $brandId;

        $existedGroup = ProductGroup::getIdOfExisted('name', $data['group_id']);
        if (isset($existedGroup)) $groupId = $existedGroup->id;
        else $groupId = ProductGroup::getInsertedId(['name' => $data['group_id'], 'created_by' => $data['created_by']]);
        $data['group_id'] = $groupId;

        $existedUnit = ProductUnit::idOfExisted('*', ['name', 'short_name'], [$data['name'], $data['short_name']]);
        if (isset($existedUnit)) $unitId = $existedUnit->id;
        else $unitId = ProductUnit::getInsertedId(['name' => $data['name'], 'short_name' => $data['short_name'], 'created_by' => $data['created_by']]);
        $data['unit_id'] = $unitId;
        $data['taxable'] = 0;
        $data['tax_type'] = 'custom';
        unset($data['variant_title'], $data['purchase_price'], $data['selling_price'], $data['attribute_value'], $data['variant_details'], $data['sku'], $data['bar_code'], $data['re_order'], $data['name'], $data['short_name']);

        return ['productId' => Product::getInsertedId($data)];
    }

    public function importOpeningStock(Request $request)
    {
        $createdBy = Auth::user()->id;
        $check = 0;
        $invalidData = [];
        $errorPreviewData = [];
        $invalidDataCollection = [];
        $productType = '';
        $errorData =[];

        // Validate with correct column name
        $isValid = $this->validationForOpeningStockColumns($request->importData, $request->requiredColumns);

        if ($isValid == "true") {
            foreach ($request->importData as $index => $item) {

                $count = ProductVariant::alreadyExisted($item['SKU'], $item['BARCODE'], $item['TITLE'], $productType);
                if ($count > 0) {
                    $data = ProductVariant::getProductIdForStock($item);
                    if($data == null){
                        $error['index'] = $index;
                        if (!ProductVariant::ifExists('product_variants.sku', $item['SKU'])) $error['invalidSku'] = $item['SKU'];
                        if (!ProductVariant::ifExists('product_variants.bar_code', $item['BARCODE'])) $error['invalidBarcode'] = $item['BARCODE'];
                        if (!Product::ifExists('products.title', $item['TITLE'])) $error['invalidTitle'] = $item['TITLE'];
                        array_push($invalidData, $error);
                    }else{
                        $check++;
                        $orderId = Order::getInsertedId([
                            'date' => now(),
                            'order_type' => 'adjustment',
                            'type' => 'stock',
                            'status' => 'done',
                            'created_by' => $createdBy,
                        ]);

                        $productId = $data['productId'];
                        $variantId = $data['variantId'];
                        $purchasePrice = $data['purchase_price'];
                        if($item['QUANTITY'] == null){
                            $item['QUANTITY'] = 0;
                        }

                        OrderItems::insertData(
                            [
                                'product_id' => $productId,
                                'variant_id' => $variantId,
                                'quantity' => $item['QUANTITY'],
                                'price' => $purchasePrice,
                                'order_id' => $orderId,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        );
                    }
                } else {
                    $errorData['index'] = $index;
                    if (!ProductVariant::ifExists('product_variants.sku', $item['SKU'])) $errorData['invalidSku'] = $item['SKU'];
                    if (!ProductVariant::ifExists('product_variants.bar_code', $item['BARCODE'])) $errorData['invalidBarcode'] = $item['BARCODE'];
                    if (!Product::ifExists('products.title', $item['TITLE'])) $errorData['invalidTitle'] = $item['TITLE'];
                    array_push($invalidData, $errorData);
                }
            }
        }
        if (sizeof($request->importData) == $check) {
            $response = [
                'message' => Lang::get('lang.opening_stock') . ' ' . Lang::get('lang.successfully_imported_from_your_file')
            ];
            return response()->json($response, 201);
        } else {
            $showInvalidData = $request->importData;
            foreach ($showInvalidData as $index => $product) {
                foreach ($invalidData as $item) {
                    if ($item['index'] == $index) {
                        if (array_key_exists("invalidSku", $item) && array_key_exists("invalidBarcode", $item) && array_key_exists("invalidTitle", $item)) {

                            $product['INVALID_DATA'] = Lang::get('lang.non_existing_product');
                        } else {
                            $excelErrorData = [];
                            if (array_key_exists("invalidSku", $item)) array_push($excelErrorData, Lang::get('lang.sku') . ': ' . $item['invalidSku'] . ",");
                            if (array_key_exists("invalidBarcode", $item)) array_push($excelErrorData, Lang::get('lang.barcode') . ': ' . $item['invalidBarcode'] . ",");
                            if (array_key_exists("invalidTitle", $item)) array_push($excelErrorData, Lang::get('lang.title') . ': ' . $item['invalidTitle'] . ",");
                            array_push($excelErrorData, Lang::get('lang.does_not_match_with_any_product'));
                            $product['INVALID_DATA'] = implode(" ", $excelErrorData);
                        }
                    }
                }
                array_push($invalidDataCollection, $product);
            }
            foreach ($invalidData as $data) {
                if (array_key_exists("invalidSku", $data)) {
                    array_push($errorPreviewData, $data['invalidSku']);
                }
                if (array_key_exists("invalidBarcode", $data)) {
                    array_push($errorPreviewData, $data['invalidBarcode']);
                }
                if (array_key_exists("invalidTitle", $data)) {
                    array_push($errorPreviewData, $data['invalidTitle']);
                }
            }
            $columnTitles = $request->requiredColumns;
            array_push($columnTitles, 'INVALID_DATA');
            $response = [
                'message' => Lang::get('lang.invalid_data_download_file_to_see_the_error'),
                'excelInvalidDatas' => $invalidDataCollection,
                'requiredColumns' => $columnTitles,
                'errorPreviewData' => $errorPreviewData
            ];

            return response()->json($response, 400);
        }
    }

    public function getSupportingData()
    {
        $category = ProductCategory::index(['name as text', 'id as value']);
        $group = ProductGroup::index(['name as text', 'id as value']);
        $brand = ProductBrand::index(['name as text', 'id as value']);
        $variant = ProductVariant::index(['sku as sku', 'bar_code as bar_code', 'product_id as product_id', 'attribute_values as attribute_values']);
        return ['category' => $category, 'group' => $group, 'brand' => $brand, 'variant' => $variant];
    }

    public function adjustStockData(Request $request)
    {

        $branchId = $request->branchId;
        $data = $request->data;

        $createdBy = Auth::user()->id;
        $lastInvoiceNumber = Setting::getSettingValue('last_invoice_number')->setting_value;
        $allSettings = new AllSettingFormat;
        $invoiceFixes = $allSettings->getInvoiceFixes();
        $invoiceId = $invoiceFixes['prefix'] . $lastInvoiceNumber . $invoiceFixes['suffix'];
        $orderItems = [];
        $subTotalAmount = 0;
        $totalTax = 0;
        foreach ($data as $item) {
            
            $productVariantId = $item['product'];
            $variantProduct = Product::detailsByVariant($productVariantId);

            $itemSubTotal = ($variantProduct->price * $item['stockQuantity']);
            $subTotalAmount += $itemSubTotal;
            if ($variantProduct->tax_percentage != null && isset($itemSubTotal)) {
                $totalTax = ($itemSubTotal * $variantProduct->tax_percentage) / 100;
            }
        }
        $orderData = [
            'date' => date('Y-m-d'),
            'order_type' => 'adjustment',
            'sub_total' => $subTotalAmount,
            'total_tax' => $totalTax,
            'total' => $subTotalAmount,
            'type' => 'internal',
            'status' => 'done',
            'branch_id' => $branchId,
            'created_by' => $createdBy,
            'invoice_id' => $invoiceId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $orderLastId = Order::store($orderData);
        $lastInvoiceNumber += 1;
        if ($lastInvoiceNumber) Setting::updateSetting('last_invoice_number', $lastInvoiceNumber);

        foreach ($data as $item) {
            $productVariantId = $item['product'];
            $variantProduct = Product::detailsByVariant($productVariantId);
            $variantSubTotal = $variantProduct->price * $item['stockQuantity'];
            array_push(
                $orderItems,
                [
                    'product_id' => $variantProduct['id'],
                    'variant_id' => $productVariantId,
                    'type' => 'adjustment',
                    'quantity' => $item['stockQuantity'],
                    'price' => $variantProduct->price,
                    'sub_total' => $variantSubTotal,
                    'tax_id' => $variantProduct->tax_id,
                    'order_id' => $orderLastId->id,
                    'adjust_stock_type_id' => $item['stockType']
                ]
            );
        }        
        $insertOrderItems = OrderItems::insertData($orderItems);
        if ($insertOrderItems) {
            return [
                'message' => Lang::get('lang.adjust_stock_successfully'),
                'success' => true,
                'status' => 201
            ];
        }
        return [
            'message' => Lang::get('lang.something_wrong'),
            'success' => false,
            'status' => 403
        ];
    }

    public function getLowStockProduct($branchId)
    {
        $data = Product::getLowStockProductList($branchId);
        return [
            'data' => $data,
            'count' => count($data)
        ];
    }

    public function searchProductForStockAdjust(Request $request)
    {
        $search = $request->search;
        $search = searchHelper::inputSearch($search);
        $data = Product::getProductForStockAdjust($search);
        
        if(count($data) > 0){
            foreach($data as $key => $item){

                if ($item->variant_title == "default_variant") $item->variant_title = "standard product";
        
                $item->title = $item->title ." ( " . $item->variant_title ." )";
            }
        };
        return $data;
    }
}
