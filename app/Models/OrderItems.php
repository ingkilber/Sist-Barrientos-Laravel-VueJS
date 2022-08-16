<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\PermissionController;
use Illuminate\Support\Facades\DB;

class OrderItems extends BaseModel
{
    protected $fillable = ['product_id', 'variant_id', 'quantity', 'price', 'discount', 'tax_id', 'order_id', 'adjust_stock_type_id', 'type', 'sub_total'];

    protected $casts = [
        'discount' => 'float',
        'price' => 'float',
        'sub_total' => 'float',
        'product_id' => 'int',
        'variant_id' => 'int',
        'tax_id' => 'int',
        'order_id' => 'int',
        'adjust_stock_type_id' => 'int',
    ];
    //All return product Information
    public static function getOrderItemsDetails($id)
    {
        $query = OrderItems::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'orders.invoice_id as invoiceReturnId',
                'price',
                'order_items.discount',
                'order_items.product_id as productID',
                'order_items.type as orderType',
                'order_items.tax_id as taxID',
                DB::raw('orders.sub_total as original_sold_sub_total'),
                DB::raw('(CASE WHEN order_items.type = "discount" THEN 0 ELSE (CASE WHEN order_items.quantity > 0 THEN ((-1)*order_items.quantity) ELSE order_items.quantity END) END) as quantity'),
                'order_items.variant_id as variantID',
                'order_items.note as cartItemNote'
            )
            ->where('order_items.order_id', '=', $id);
        return $query->get();
    }

    public static function monthlySale($year)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $monthlySale = Order::query()->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.type', '=', 'customer')
            ->select(DB::raw('sum(orders.total) as sales'), DB::raw('month(orders.date) as month'));

        if ($salesPermission != 'manage') {
            $monthlySale->where('users.id', $userId);
        }

        return $monthlySale->get();
    }

    public static function monthlyReceive($year)
    {
        $perm = new PermissionController();
        $receivePermission = $perm->checkReceivingPermission();
        $userId = Auth()->user()->id;

        $monthlyReceive = Order::query()->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'receiving')
            ->select(DB::raw('sum(orders.total) as receive'), DB::raw('month(orders.date) as month'));

        if ($receivePermission != 'manage') {

            return $monthlyReceive->where('users.id', $userId)->get();
        }

        return $monthlyReceive->get();
    }

    public static function monthlyProfit($year)
    {
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();
        $userId = Auth()->user()->id;

        $monthlyProfit = Order::query()->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->select(DB::raw('sum(orders.profit) as profit'), DB::raw('month(orders.date) as month'));

        if ($profitPermission != 'manage') {

            return $monthlyProfit->where('users.id', $userId)->get();
        }

        return $monthlyProfit->get();
    }

    public static function salesItems($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkSalesPermission();

        $query = OrderItems::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('branches', 'branches.id', 'orders.transfer_branch_id')
            ->select(
                'products.title',
                'orders.id',
                'branches.name as transfer_branch_name',
                'orders.date',
                'orders.type',
                'orders.sub_total',
                'orders.total_tax as tax',
                'orders.total',
                'orders.invoice_id',
                'orders.due_amount',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by"),
                DB::raw("users.id as user_id"),
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer"),
                DB::raw("customers.id as customer_id"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))+ 
                (select abs(order_items.sub_total) from order_items where order_items.type ='discount' and order_items.order_id = orders.id)) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_purchased')
            )
            ->where('orders.order_type', '!=', 'receiving')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->groupBy('order_items.order_id');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }


        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "sales_type") {

                    if ($singleFilter['value'] == 'returns') {
                        $query->where('orders.total', '<', 0);
                    } else if ($singleFilter['value'] == 'internal-transfer') {
                        $query->where('orders.type', $singleFilter['value']);
                    } else {
                        $query->where('orders.type', $singleFilter['value']);
                    }
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {
                    if ($singleFilter['value'] == 'paid') {
                        $query->where('orders.due_amount', '=', 0);
                    } elseif ($singleFilter['value'] == 'due') {
                        $query->where('orders.due_amount', '>', 0);
                    }
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "brands") {
                    $query->where('products.brand_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "categories") {

                    $query->where('products.category_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "groups") {

                    $query->where('products.group_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "customers") {

                    $query->where('customers.id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "employee") {

                    $query->where('users.id', $singleFilter['value']);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('customers.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('customers.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
            });
        }

        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function salesSummary($filterKey, $limit, $offset, $groupBy, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::query()->select(
            'order_items.tax_id',
            DB::raw('abs(sum(order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)) as sub_total'),
            DB::raw('abs(sum((order_items.quantity*order_items.price -(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            DB::raw("(sum((abs(order_items.quantity)*order_items.price)* order_items.discount)/100) AS discount"),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
            $filterKey
        )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->where('orders.order_type', 'sales')
            ->where('orders.status', 'done')
            ->groupBy($groupBy)
            ->orderBy($columnName, $columnSortedBy);

        $allData = $query->get();
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $allData;
        }
    }

    public static function salesSummaryTypeFilter($filterKey, $limit, $offset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::query()->select(
            'order_items.tax_id',
            $joinColumn1,
            DB::raw('abs(sum(order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)) as sub_total'),
            DB::raw('abs(sum((order_items.quantity*order_items.price -(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            DB::raw("(sum((abs(order_items.quantity)*order_items.price)* order_items.discount)/100) AS discount"),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
            $filterKey
        )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin($joinTable, $joinColumn1, '=', $joinColumn2)
            ->where('orders.order_type', 'sales');

        if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
            $query->whereBetween('orders.date', [$starts, $ends]);
        }

        if ($branchId > 0) {
            $query->where('orders.branch_id', $branchId);
        }

        $query->groupBy($groupBy)->orderBy($columnName, $columnSortedBy);
        $allData = $query->get();
        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $allData;
        }
    }

    public static function orderType($id)
    {
        return OrderItems::getFirst('*', 'id', $id);
    }

    public static function getOrderDetails($id, $invoice = false)
    {
        if ($invoice) $subTotal = "order_items.sub_total";
        else $subTotal = DB::raw('abs(order_items.sub_total) as total');

        return OrderItems::query()->leftjoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('order_items.order_id', '=', $id)
            ->select(
                'order_items.price',
                'order_items.type',
                $subTotal,
                DB::raw('(CASE WHEN order_items.product_id = 0
                THEN  "Discount" ELSE concat(title,if(variant_title="default_variant"," ",concat("(",product_variants.variant_title,")"))) END) as title'),
                DB::raw('abs(order_items.quantity) as quantity'),
                'order_items.discount'
            )->get();
    }


    public static function getAllSalesDetails($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkSalesPermission();

        $query = OrderItems::query()->leftjoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('product_variants', 'order_items.variant_id', '=', 'product_variants.id')
            ->select(
                'products.brand_id',
                'products.category_id',
                'products.group_id',
                'order_items.sub_total',
                'orders.invoice_id',
                'order_items.type',
                DB::raw('(CASE WHEN order_items.product_id = 0
                THEN (CASE WHEN order_items.type = "shipment" THEN "Shipment" ELSE "Discount" END) ELSE concat(title,if(variant_title="default_variant"," ",concat("(",product_variants.variant_title,")"))) END) as title'),
                DB::raw('(CASE WHEN order_items.type = "discount" THEN 0 ELSE (CASE WHEN order_items.quantity > 0 THEN ((-1)*order_items.quantity) ELSE abs(order_items.quantity) END) END) as quantity'),
                'order_items.discount'
            )
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done');

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "brands") {
                    $query->where('products.brand_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "sales_type") {
                    if ($singleFilter['value'] == 'returns') {
                        $query->where('orders.total', '<', 0);
                    } else if ($singleFilter['value'] == 'internal-transfer') {
                        $query->where('orders.type', $singleFilter['value']);
                    } else $query->where('orders.type', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "categories") {

                    $query->where('products.category_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "groups") {

                    $query->where('products.group_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "allInvoiceId") {

                    $query->where('orders.invoice_id', $singleFilter['value']);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->Where('orders.invoice_id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('order_items.price', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('title', 'LIKE', '%' . $searchValue . '%');
            });
        }

        $columnName = 'orders.invoice_id';
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function receivingItems($filtersData, $searchValue, $columnName, $request, $limit, $offset, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkReceivingPermission();

        $query = OrderItems::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'orders.supplier_id')
            ->select(
                'orders.id',
                'orders.date',
                'orders.type',
                'orders.total',
                'orders.invoice_id',
                'orders.due_amount',
                DB::raw("suppliers.id as supplier_id"),
                DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_purchased'),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS full_name"),
                DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS supplier_name"),
                DB::raw("users.id as user_id")
            )
            ->where('orders.order_type', '=', 'receiving')
            ->groupBy('order_items.order_id');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {

                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "receive_type") {
                    if ($singleFilter['value'] == 'returns') {
                        $query->where('orders.total', '<', 0);
                    } else if ($singleFilter['value'] == 'internal-transfer') {
                        $query->where('orders.type', $singleFilter['value']);
                    } else $query->where('orders.type', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {

                    $query->whereBetween('orders.date', [$singleFilter['value'][0]['start'], $singleFilter['value'][0]['end']]);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {

                    if ($singleFilter['value'] == 'paid') {
                        $query->where('orders.due_amount', '=', 0);
                    } elseif ($singleFilter['value'] == 'due') {
                        $query->where('orders.due_amount', '>', 0);
                    }
                }
                else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "supplier" && $singleFilter['value'] != "all") {
                    $query->where('suppliers.id', '=', $singleFilter['value']);
                }

            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%');
            });
        }

        $allData = $query->orderBy($columnName, $request)->get();


        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $allData;
        }
    }

    public static function receiveSummary($filterKey, $limit, $offset, $groupBy, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::query()->select(
            'order_items.tax_id',
            DB::raw('sum(order_items.sub_total) as sub_total'),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_receive'),
            DB::raw('abs(sum((order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            $filterKey
        )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_brands', 'product_brands.id', '=', 'products.brand_id')
            ->where('orders.order_type', 'receiving')
            ->groupBy($groupBy)
            ->orderBy($columnName, $columnSortedBy);

        $allData = $query->get();

        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $allData;
        }
    }

    public static function receiveSummaryFilter($filterKey, $limit, $offset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy)
    {
        $query = OrderItems::query()->select(
            'order_items.tax_id',
            $joinColumn1,
            DB::raw('sum(order_items.sub_total) as sub_total'),
            DB::raw('CONVERT(abs(sum(order_items.quantity)),SIGNED INTEGER) as item_receive'),
            DB::raw('abs(sum((order_items.quantity*order_items.price-(order_items.quantity*order_items.price*order_items.discount)/100)*taxes.percentage/100)) as tax'),
            $filterKey
        )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin($joinTable, $joinColumn1, '=', $joinColumn2)
            ->where('orders.order_type', 'receiving');

        if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
            $query->whereBetween('orders.date', [$starts, $ends]);
        }

        if ($branchId > 0) {
            $query->where('orders.branch_id', $branchId);
        }
        $query->groupBy($groupBy)->orderBy($columnName, $columnSortedBy);
        $allData = $query->get();

        if (empty($requestType)) {
            $count = $query->get()->count();
            $data = $query->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $allData;
        }
    }

    public static function availableQuantity($variantId)
    {
        return OrderItems::query()->where('variant_id', $variantId)->sum('quantity');
    }

    public static function checkAvailableQuantity($variantId)
    {
        return OrderItems::query()->where('variant_id', $variantId)->sum('quantity');
    }

    public static function productQuantity($variantId)
    {
        return OrderItems::query()->where('variant_id', $variantId)->select(DB::raw('CONVERT(abs(sum(quantity)),SIGNED INTEGER) as quantity'))->first();

    }

    public static function userSalesRecord($year, $id)
    {
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $monthlySale = Order::query()->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->groupBy(DB::raw('month(orders.date)'))
            ->whereBetween('orders.date', array($year . '-01-01', $year . '-12-31'))
            ->where('orders.status', 'done')
            ->where('orders.order_type', 'sales')
            ->where('orders.created_by', $id)
            ->select(DB::raw('sum(orders.total) as sales'), DB::raw('month(orders.date) as month'));

        if ($salesPermission != 'manage') {
            $monthlySale->where('users.id', $id);
        }

        return $monthlySale->get();
    }

    public static function itemsForInvoice($orderId)
    {
        return OrderItems::query()->leftjoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->select('product_variants.variant_title', 'products.title', 'order_items.price', 'order_items.type', 'order_items.discount', 'order_items.sub_total', 'order_items.note', DB::raw('abs(order_items.quantity) as quantity'))
            ->where('order_id', $orderId)->where('type', '!=', 'discount')
            ->get();
    }

    public static function getDiscountAmount($orderId)
    {
        return OrderItems::query()->select(DB::raw('abs(sub_total) as overAllDiscount'))->where('type', 'discount')->where('order_id', $orderId)->first();
    }

    public static function adjustmentItems($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkSalesPermission();

        $query = OrderItems::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('branches', 'branches.id', 'orders.branch_id')
            ->leftJoin('adjust_product_stock_types', 'adjust_product_stock_types.id', 'order_items.adjust_stock_type_id')
            ->select(
                'orders.date as date',
                'products.title as product_name',
                'product_variants.variant_title as variant_title',
                'branches.name as branch_name',
                'order_items.quantity as adjustment_item',
                'adjust_product_stock_types.title as adjustment_type',
                DB::raw('sum(order_items.quantity) as adjustment_item'),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by")
            )
            ->where('orders.order_type', '=', 'adjustment')
            ->where('orders.status', '=', 'done')
            ->groupBy('order_items.product_id')
            ->groupBy('orders.branch_id')
            ->groupBy('order_items.variant_id')
            ->groupBy('order_items.adjust_stock_type_id');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "product_name") {
                    $query->where('order_items.product_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch_name") {
                    $query->where('orders.branch_id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "adjustment_type") {
                    $query->where('order_items.adjust_stock_type_id', $singleFilter['value']);
                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                }
            }
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function salesListItems($id, $filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = OrderItems::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('branches', 'branches.id', 'orders.transfer_branch_id')
            ->select(
                'orders.id',
                'branches.name as transfer_branch_name',
                'orders.date',
                'orders.type',
                'orders.sub_total',
                'orders.total_tax as tax',
                'orders.total',
                'orders.invoice_id',
                'orders.due_amount',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by"),
                DB::raw("users.id as user_id"),
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer"),
                DB::raw("customers.id as customer_id"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))+ 
                (select abs(order_items.sub_total) from order_items where order_items.type ='discount' and order_items.order_id = orders.id)) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_purchased')
            )
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')
            ->where('orders.branch_id', '=', $id)
            ->groupBy('order_items.order_id');

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "customers") {
                    $query->where('customers.id', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {
                    if ($singleFilter['value'] == 'paid') {
                        $query->where('orders.due_amount', '=', 0);
                    } elseif ($singleFilter['value'] == 'due') {
                        $query->where('orders.due_amount', '>', 0);
                    }
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
            });
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }
}
