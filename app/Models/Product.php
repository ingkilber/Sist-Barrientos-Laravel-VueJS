<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder as BuilderAlias;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Libraries\searchHelper;
class Product extends BaseModel

{
    protected $fillable = ['title', 'description', 'category_id', 'brand_id', 'unit_id', 'group_id', 'taxable', 'tax_type', 'tax_id', 'product_type', 'branch_id', 'imageURL', 'created_by'];

    public static function groupId($id)
    {
        return Product::where('group_id', $id)->count();
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }


    /**
     * @param $branchId
     * @param $orderType
     * @param int $inStockOnly
     * @param bool $returnProductId
     * @return Builder
     */
    public static function getProductVariantsQuery($branchId, $orderType, $inStockOnly = 0, $returnProductId = false)
    {
        $price = 'product_variants.purchase_price as price';

        if ($orderType == 'sales') {
            $price = 'product_variants.selling_price  as price';
        }

        $fields = "product_variants.id, product_variants.sku, product_variants.product_id, product_variants.variant_title, product_variants.attribute_values, product_variants.enabled, product_variants.imageURL, product_variants.bar_code, product_variants.re_order, product_variants.purchase_price, product_variants.selling_price, $price, order_items.qty AS availableQuantity";

        if ($returnProductId) {
            $fields = "product_variants.product_id";
        }

        $orderItems = DB::table('order_items')
            ->selectRaw('sum(quantity) as qty, variant_id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.branch_id', '=', $branchId)
            ->groupBy('variant_id');

        return DB::table('product_variants')
            ->selectRaw($fields)
            ->leftJoinSub($orderItems, 'order_items', function (JoinClause $leftJoin) {
                return $leftJoin->on('product_variants.id', '=', 'order_items.variant_id');
            })->where('product_variants.enabled', 1)
            ->when($inStockOnly, function (Builder $builder) {
                $builder->where('order_items.qty', '>', 0);
            });
    }


    public static function getProductVariantsList($branchId, $orderType, $inStockOnly, Collection $products = null)
    {
        return self::getProductVariantsQuery($branchId, $orderType, $inStockOnly)
            ->when($products, function (Builder $builder) use ($products) {
                $builder->whereIn(
                    'product_variants.product_id',
                    $products->pluck('productID')->toArray()
                );
            })->get();
    }

    //Get product for reorder
    public static function getOrderProduct($productId, $variantId, $quantity){
        $quary = Product::join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->select('products.*', 'product_variants.*')
            ->where('product_variants.product_id', '=', $productId)
            ->where('product_variants.id', '=', $variantId);
        return $quary->first();
    }

    public static function getAllProducts($options)
    {

        $fields = $options['fields'];
        $limit = $options['limit'];
        $offset = $options['offset'];
        $searchValue = $options['searchValue'];
        $branchId = $options['branchId'];
        $orderType = $options['orderType'];
        $onlyInStockProducts = $options['onlyInStockProducts'];

        // We have to search the stocks in variant level if we want to show in stock products.
        $inStockProducts = self::getProductVariantsQuery($branchId, $orderType, $onlyInStockProducts, true);

        $selectProducts = 'products.id AS productID, products.title, products.category_id, products.taxable, products.tax_id, products.tax_type, products.imageURL AS productImage, IF( products.taxable, IF(products.tax_id, taxes.percentage, branch_tax.taxPercentage ), 0) AS taxPercentage,
	  (SELECT GROUP_CONCAT(name) FROM product_attributes WHERE FIND_IN_SET(product_attributes.id, attribute_group.product_attribute_ids)) AS attributeName';

        $selectBranches = 'branches.id AS branch_id, If(branches.tax_id, taxes.percentage, (SELECT percentage from taxes WHERE is_default = 1 LIMIT 1)) AS taxPercentage';

        $selectProductAttributeValues = 'product_id, GROUP_CONCAT(attribute_id) as product_attribute_ids';

        $branches = DB::table("branches")
            ->selectRaw($selectBranches)
            ->leftJoin('taxes', 'taxes.id', '=', 'branches.tax_id');

        $productAttributeValues = DB::table('product_attribute_values')
            ->selectRaw($selectProductAttributeValues)
            ->groupBy('product_id');

        return self::query()
            ->selectRaw($selectProducts)
            ->leftJoinSub($branches, 'branch_tax', 'branch_tax.branch_id', '=', DB::raw($branchId))
            ->leftJoin('taxes', 'taxes.id', 'products.tax_id')
            ->leftJoinSub($productAttributeValues, 'attribute_group', 'attribute_group.product_id', '=', 'products.id')
            ->when($onlyInStockProducts, function (BuilderAlias $builder) use ($inStockProducts) {
                $builder->whereIn(
                    'products.id',
                    $inStockProducts
                        ->get()
                        ->pluck('product_id')
                        ->toArray()
                );
            })->when($searchValue, function (BuilderAlias $builder) use ($searchValue) {
                $builder->where('products.title', 'LIKE', "%$searchValue%");
            })->when($limit, function (BuilderAlias $builder) use ($limit, $offset) {
                $builder->limit($limit)
                    ->offset($offset);
            })->orderBy('products.id')
            ->get();

    }


    public static function getAllProductByLimit($data)
    {
        $fields = $data['fields'];
        $limit = $data['limit'];
        $offset = $data['offset'];
        $searchValue = $data['searchValue'];
        $results = null;
        $query = Product::select($fields);
        $count = $query->get()->count();
        $allProducts = $query->get();
        if (isset($searchValue)) {
            $results = $query->where('title', 'LIKE', '%' . $searchValue . '%')->get();
        } else {
            if ($limit != null) {
                $results = $query->offset($offset)->limit($limit)->get();
            } else {
                $results = $query->get();
            }
        }
        return [
            'products' => $results,
            'allProducts' => $allProducts,
            'count' => $count
        ];
    }

    public static function getAllData($request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;

        $offset = $request->rowOffset;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $columnSortedBy = $request->columnSortedBy;
        $filtersData = $request->filtersData;
        $requestType = $request->reqType;

        if ($columnName == 'group_name') $columnName = 'products.group_id';
        else if ($columnName == 'brand_name') $columnName = 'products.brand_id';
        else if ($columnName == 'category_name') $columnName = 'products.category_id';
        else if ($columnName == 'title') $columnName = 'products.title';
        else $columnName = 'products.id';

        $query = Product::query()->leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->select(
                'products.*',
                'product_groups.name as group_name',
                'product_categories.name as category_name',
                'product_brands.name as brand_name',
                DB::raw('sum(order_items.quantity) as product_quantity')
            );

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "group") {
                    $query->where('products.group_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "brand") {
                    $query->where('products.brand_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "category") {
                    $query->where('products.category_id', $singleFilter['value']);
                }
            }
        }

        if ($searchValue) {
            $query->where('products.title', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_categories.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_brands.name', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_groups.name', 'LIKE', '%' . $searchValue . '%');
        }

        if (empty($requestType)) {
            $count = $query->get()->count();

            $products = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            $products = Product::getProductVariants($products);

            return ['data' => $products, 'count' => $count];
        } else {
            $products = $query->get();
            $products = Product::getProductVariants($products);
            return $products;
        }
    }


    public static function getProductVariants($products)
    {
        foreach ($products as $product) {
            if ($product->product_quantity == null) {
                $product->product_quantity = 0;
            }
            $data = ProductVariant::getVariant($product->id);
            $product->purchase_price = $data['purchase_price'];
            $product->selling_price = $data['selling_price'];
            $product->variants = $data['variant'];
        }
        return $products;
    }

    public static function detailsById($id)
    {
        return Product::query()->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->leftJoin('product_units', 'product_units.id', '=', 'products.unit_id')
            ->leftJoin('users', 'users.id', '=', 'products.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'products.tax_id')
            ->select('products.id', 'products.title', 'products.description','products.taxable', 'products.tax_type', 'products.product_type', 'products.imageURL', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name', 'product_units.name as unit_name', 'taxes.name as tax_name', 'taxes.percentage', 'users.first_name', 'users.last_name')
            ->where('products.id', $id)
            ->first();
    }

    public static function availableStock()
    {
        return Product::join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('order_items', 'order_items.variant_id', '=', 'product_variants.id')
            ->groupBy('order_items.variant_id')
            ->select('products.id as prod_id', 'products.title as prod_title', 'product_variants.attribute_values as attributes', DB::raw('sum(quantity) as qtty'), 'product_variants.id as variant_id')
            ->get();
    }

    public static function categoryIdUsed($id)
    {
        return Product::where('category_id', $id)->count();
    }

    public static function usedProduct($id)
    {
        return Product::where('brand_id', $id)->count();
    }

    public static function getProducts($searchValue = '')
    {
        return Product::leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->select('products.id as productID', 'products.title', 'products.taxable', 'products.tax_type', 'products.tax_id', 'products.imageURL as productImage', 'product_variants.purchase_price as purchasePrice', 'product_variants.selling_price as sellingPrice', 'products.branch_id', 'product_groups.name as group_name', 'product_categories.name as category_name', 'product_brands.name as brand_name', 'product_variants.sku', 'product_variants.bar_code')
            ->where('products.title', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_variants.sku', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_variants.bar_code', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_categories.name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_brands.name', 'LIKE', '%' . $searchValue . '%')
            ->orWhere('product_groups.name', 'LIKE', '%' . $searchValue . '%')
            ->groupBy('productID')
            ->get();
    }

    public static function insertProduct($productName, $category_id, $brand_id, $group_id, $unit_id, $product_type, $created_by)
    {
        return Product::insertGetId(
            [
                'title' => $productName,
                'category_id' => $category_id,
                'brand_id' => $brand_id,
                'group_id' => $group_id,
                'unit_id' => $unit_id,
                'product_type' => $product_type,
                'created_by' => $created_by
            ]
        );
    }

    public static function ifExists($field, $data)
    {
        if ($data == null) $data = '';

        return ProductVariant::select('product_variants.product_id as productId', 'product_variants.id as variantId', 'product_variants.purchase_price as purchase_price', 'product_variants.selling_price as selling_price', 'products.title as title')
            ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
            ->where($field, '=', $data)
            ->exists();
    }

    public static function getAllDetails($productData)
    {
        return Product::leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->select('products.id', 'products.title', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name')
            ->where('products.product_type', 'variant')
            ->where('product_categories.name', $productData['category_id'])
            ->where('product_brands.name', $productData['brand_id'])
            ->where('product_groups.name', $productData['group_id'])
            ->count();
    }

    public static function getVariantId($productData)
    {
        return Product::leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->select('products.id', 'products.title', 'product_categories.name as cat_name', 'product_brands.name as brand_name', 'product_groups.name as group_name')
            ->where('products.product_type', 'variant')
            ->where('product_categories.name', $productData['category_id'])
            ->where('product_brands.name', $productData['brand_id'])
            ->where('product_groups.name', $productData['group_id'])
            ->first();
    }

    public static function detailsByVariant($id)
    {
        return Product::query()->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->leftJoin('product_groups', 'product_groups.id', '=', 'products.group_id')
            ->leftJoin('product_units', 'product_units.id', '=', 'products.unit_id')
            ->leftJoin('users', 'users.id', '=', 'products.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'products.tax_id')
            ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.title',
                'products.taxable',
                'products.tax_type',
                'products.product_type',
                'product_categories.name as cat_name',
                'products.tax_id as tax_id',
                'product_brands.name as brand_name',
                'product_groups.name as group_name',
                'product_units.name as unit_name',
                'taxes.name as tax_name',
                'taxes.percentage as tax_percentage',
                'users.first_name',
                'users.last_name',
                'product_variants.purchase_price as price'
            )
            ->where('product_variants.id', $id)
            ->first();
    }

    public static function getLowStockProductList($branchId)
    {
        $data = ProductVariant::query()->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('product_groups', 'products.group_id', '=', 'product_groups.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('product_brands', 'products.brand_id', '=', 'product_brands.id')
            ->leftJoin('order_items', 'product_variants.id', '=', 'order_items.variant_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->groupBy('product_variants.id')
            ->select(
                'product_variants.id as variant_id',
                'order_items.order_id as order_id',
                'products.title as product_name',
                'product_variants.variant_title as variant_title',
                'product_variants.re_order as re_order',
                'product_variants.isNotify as isNotify',
                DB::raw('sum(order_items.quantity) as product_quantity')
            )
            ->where('product_variants.enabled', '=', 1)
            ->where('orders.branch_id', '=', $branchId)
            ->get();
        if (isset($data) && !empty($data)) {
            return self::getLowStockData($data, $branchId);
        }
        return [];
    }

    public static function getLowStockData($data, $branchId)
    {
        $newDataArr = [];
        $reOrder = Setting::getSettingValue('re_order')->setting_value;
        foreach ($data as $item) {
            if ($item->re_order == null) $item->re_order = $reOrder;
            if ($item->isNotify != null && $item->product_quantity <= $reOrder) {
                $notifyArr = explode(",", $item->isNotify);
                if (!in_array($branchId, $notifyArr)) {
                    array_push($newDataArr, $item);
                    // notify array store
                    array_push($notifyArr, $branchId);
                    $newNotifyArr = implode(",", $notifyArr);
                    ProductVariant::upDateIsNotify($item->variant_id, $newNotifyArr);
                }
            } else if ($item->isNotify == null && $item->product_quantity <= $reOrder) {
                array_push($newDataArr, $item);
                ProductVariant::upDateIsNotify($item->variant_id, $branchId);
            }
        }
        return $newDataArr;
    }

    public static function getProductForStockAdjust($searchValue)
    {
        $query = Product::query()->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
                ->select('products.id as productID', 'product_variants.id as id', 'products.title','product_variants.variant_title','product_variants.sku','product_variants.bar_code');
        
        if ($searchValue){
            return $query->where('products.title', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_variants.sku', 'LIKE', '%' . $searchValue . '%')
                ->orWhere('product_variants.bar_code', 'LIKE', '%' . $searchValue . '%')
                ->get();
        }
        
        return $query->get();
    }
}
