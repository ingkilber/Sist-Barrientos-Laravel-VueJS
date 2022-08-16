<?php

namespace App\Models;

use DB;
use App\Http\Controllers\API\PermissionController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Libraries\AllSettingFormat;
use App\Models\OrderItems;
use App\Models\Payments;

class Order extends BaseModel
{
    protected $fillable = [
        'date',
        'order_type',
        'sales_note',
        'sub_total',
        'total_tax',
        'all_discount',
        'total',
        'due_amount', 'type', 'profit', 'status', 'branch_id', 'transfer_branch_id', 'table_id', 'created_by', 'returned_invoice', 'return_type', 'customer_id', 'supplier_id', 'invoice_id', 'created_at'];

    protected $casts = [
        'sub_total' => 'float',
        'total_tax' => 'float',
        'all_discount' => 'float',
        'total' => 'float',
        'due_amount' => 'float',
        'profit' => 'float',
        'branch_id' => 'int',
        'transfer_branch_id' => 'int',
        'table_id' => 'int',
        'created_by' => 'int',
        'customer_id' => 'int',
        'supplier_id' => 'int',
    ];

    // Update Order_type
    public static function updateOrderType($invoiceReturnId, $returnType, $orderType)
    {
        DB::table('orders')
            ->where('invoice_id', $invoiceReturnId)->where('order_type', $orderType)
            ->update(['return_type' => $returnType]);
    }

    public static function supplierQuery($id)
    {
        return OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->leftJoin('branches', 'branches.id', '=', 'orders.branch_id')
            ->select(
                'orders.id',
                'orders.date',
                'branches.name as received_branch',
                'orders.sub_total',
                'orders.total_tax as tax',
                'orders.due_amount',
                'orders.total',
                'users.id as user_id',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS sold_by"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS received_by"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))+ 
                (select abs(order_items.sub_total) from order_items where order_items.type ='discount' and order_items.order_id = orders.id)) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_received')
            )
            ->where('orders.supplier_id', '=', $id)
            ->groupBy('order_items.order_id');
    }

    //Sales Details Invoice Id
    public static function getAllInvoiceId()
    {
        return $query = Order::select('invoice_id as text', 'invoice_id as value')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')->get();
    }

    public static function supplierRecords($columnName, $columnSortedBy, $limit, $offset, $id, $searchValue, $requestType)
    {
        $supplierRecords = Order::supplierQuery($id);

        if ($searchValue) {
            $supplierRecords = $supplierRecords->where('orders.id', '=', $searchValue);
        }

        $count = $supplierRecords->get()->count();

        if (empty($requestType)) {
            $allData = $supplierRecords->get();
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->get();

            return ['data' => $data, 'count' => $count];
        }
    }

    public static function supplierRecordsByDate($columnName, $limit, $offset, $searchValue, $id, $columnSortedBy, $filtersData, $requestType)
    {
        $supplierRecords = Order::supplierQuery($id);

        if ($searchValue) {
            $query = $supplierRecords->where('orders.id', '=', $searchValue);
        }

        foreach ($filtersData as $singleFilter) {

            if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                $starts = $singleFilter['value'][0]['start'];
                $ends = $singleFilter['value'][0]['end'];
                $query = $supplierRecords->whereBetween('orders.date', [$starts, $ends]);
            } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {
                if ($singleFilter['value'] == 'paid') {
                    $query = $supplierRecords->where('orders.due_amount', '=', 0);
                } elseif ($singleFilter['value'] == 'due') {
                    $query = $supplierRecords->where('orders.due_amount', '>', 0);
                }
            }
        }

        $count = $supplierRecords->get()->count();

        if (empty($requestType)) {
            $allData = $supplierRecords->get();
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            $data = $supplierRecords->orderBy($columnName, $columnSortedBy)->get();

            return ['data' => $data, 'count' => $count];
        }
    }

    public static function customerDetails($id, $filtersData, $searchValue, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $query = OrderItems::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('taxes', 'taxes.id', '=', 'order_items.tax_id')
            ->select(
                'orders.id',
                'orders.date',
                'orders.sub_total',
                'orders.total_tax as tax',
                'orders.total',
                'orders.due_amount',
                'users.id as user_id',
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS sold_by"),
                DB::raw("((sum(((abs(order_items.quantity)*order_items.price)* order_items.discount)/100))
                + abs(SUM(CASE WHEN order_items.type = 'discount' THEN order_items.sub_total ELSE 0 END)) ) AS discount"),
                DB::raw('CONVERT(abs(SUM(CASE WHEN order_items.type = "discount" THEN 0 ELSE order_items.quantity END)),SIGNED INTEGER) as item_purchased')
            )
            ->where('orders.customer_id', '=', $id)
            ->groupBy('order_items.order_id');

        foreach ($filtersData as $singleFilter) {

            if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                $starts = $singleFilter['value'][0]['start'];
                $ends = $singleFilter['value'][0]['end'];
                $query->whereBetween('orders.date', [$starts, $ends]);
            } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "payment_type" && $singleFilter['value'] != "all") {
                if ($singleFilter['value'] == 'paid') {
                    $query->where('orders.due_amount', '=', 0);
                } elseif ($singleFilter['value'] == 'due') {
                    $query->where('orders.due_amount', '>', 0);
                }
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('orders.id', 'LIKE', '%' . $searchValue . '%');
            });
        }

        $count = $query->get()->count();

        if (empty($requestType)) {
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            $data = $query->orderBy($columnName, $columnSortedBy)->get();
            return ['data' => $data, 'count' => $count];
        }
    }

    public static function orderDetails($id, $cashRegister)
    {
        $joinTable = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('branches', 'branches.id', '=', 'orders.branch_id');
        if ($cashRegister == null) {
            $data = $joinTable->where('orders.id', '=', $id)
                ->select(
                    'orders.id',
                    'orders.date',
                    'orders.invoice_id',
                    'orders.sub_total',
                    'orders.all_discount',
                    'orders.total',
                    'orders.total_tax',
                    'orders.customer_id',
                    'orders.supplier_id',
                    'orders.created_at',
                    'orders.transfer_branch_id',
                    'orders.table_id',
                    'payments.paid',
                    'payments.exchange as change',
                    'payment_types.name as method',
                    'users.first_name',
                    'users.last_name',
                    'branches.name as branch_name',
                    'branches.id as branch_id',
                    'orders.type as sales_or_receiving_type',
                    'orders.order_type as order_type'
                )->first();
            $data->cash_register_id = $cashRegister;
            return $data;
        } else {
            return $joinTable
                ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                ->where('orders.id', '=', $id)
                ->select(
                    'orders.id',
                    'orders.date',
                    'orders.invoice_id',
                    'orders.sub_total',
                    'orders.all_discount',
                    'orders.total',
                    'orders.total_tax',
                    'orders.customer_id',
                    'orders.supplier_id',
                    'orders.created_at',
                    'orders.transfer_branch_id',
                    'orders.table_id',
                    'payments.paid',
                    'payments.exchange as change',
                    'payment_types.name as method',
                    'users.first_name',
                    'users.last_name',
                    'cash_registers.title as cash_register_name',
                    'cash_registers.id as cash_register_id',
                    'branches.name as branch_name',
                    'orders.type as sales_or_receiving_type',
                    'branches.id as branch_id',
                    'orders.order_type as order_type'
                )
                ->first();
        }
    }

    public static function holdOrder()
    {
        return Order::select('created_by as createdBy', 'customer_id as customer', 'created_at as date', 'id as orderID', 'type as salesOrReceivingType')->where('status', 'hold')->get();
    }

    public static function userSales($id, $Month, $date)
    {
        return Order::join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->select('orders.date', DB::raw('abs(sum(order_items.price*order_items.quantity)) as sales'))
            ->where('orders.created_by', '=', $id)
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')
            ->whereBetween('orders.date', [$Month, $date])->get();
    }


    public static function salesInvoice($date)
    {
        return Order::select('invoice_id')->where('order_type', '=', 'sales')->where('date', '=', $date)->count();
    }

    public static function todayTotalTax($date)
    {
        return Order::where('order_type', '=', 'sales')->where('date', '=', $date)->sum('total_tax');
    }

    public static function getsOrders($id)
    {
        return Order::select('orders.id', 'orders.sub_total', 'orders.total_tax', 'orders.total')
            ->where('id', $id)
            ->first();
    }

    public static function taxReports($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = Order::join('branches', 'orders.branch_id', '=', 'branches.id')
            ->select('orders.id', 'orders.date', 'orders.order_type', 'orders.total_tax as tax', 'branches.name', 'orders.total', 'orders.invoice_id as invoice_id');

        if (!empty($filtersData)) {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {

                    $query->where('orders.order_type', $singleFilter['value']);
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
                $query->where('orders.id', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('branches.name', 'LIKE', '%' . $searchValue . '%');
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

    public static function getSevenDaysProfit()
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $date = carbon::today()->toDateString();
        $prev = carbon::today()->subDays(7)->toDateString();

        $sevenDayProfit = Order::select('orders.date', DB::raw('sum(orders.profit) as profit'))
            ->leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.order_type', 'sales')
            ->where('orders.status', 'done')
            ->whereBetween('orders.date', [$prev, $date])
            ->groupBy('orders.date');

        if ($profitPermission != 'manage') {
            return $sevenDayProfit->where('users.id', $userId)->get();
        }
        return $sevenDayProfit->get();
    }

    public static function todaysSale($today)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();

        $todaySales = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.type', '=', 'customer')
            ->whereDate('orders.date', '=', $today);
        if ($salesPermission != 'manage') {
            return $todaySales->where('users.id', $userId)->sum('orders.total');
        }
        return $todaySales->sum('orders.total');
    }

    public static function monthlySold($date)
    {
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();
        $userId = Auth()->user()->id;
        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.type', '=', 'customer')
            ->whereDate('orders.date', '>=', $date);
        if ($salesPermission != 'manage') {
            return $monthlySale->where('users.id', $userId)->sum('orders.total');
        }
        return $monthlySale->sum('orders.total');
    }

    public static function totalSold()
    {
        $perm = new PermissionController();
        $salesPermission = $perm->checkSalesPermission();
        $userId = Auth()->user()->id;
        $monthlySale = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.type', '=', 'customer');
        if ($salesPermission != 'manage') {
            return $monthlySale->where('users.id', $userId)->sum('orders.total');
        }
        return $monthlySale->sum('orders.total');
    }

    public static function todayProfit($today)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '=', $today);
        if ($profitPermission != 'manage') {
            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }
        return $todayProfit->sum('orders.profit');
    }

    public static function monthlyProfit($date)
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales')
            ->whereDate('orders.date', '>=', $date);
        if ($profitPermission != 'manage') {
            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }
        return $todayProfit->sum('orders.profit');
    }

    public static function totalProfit()
    {
        $userId = Auth()->user()->id;
        $perm = new PermissionController();
        $profitPermission = $perm->checkProfitPermission();

        $todayProfit = Order::leftjoin('users', 'users.id', '=', 'orders.created_by')
            ->where('orders.status', '=', 'done')
            ->where('orders.order_type', '=', 'sales');
        if ($profitPermission != 'manage') {

            return $todayProfit->where('users.id', $userId)->sum('orders.profit');
        }

        return $todayProfit->sum('orders.profit');
    }

    public static function searchOrders($id, $orderType)
    {

        return Order::where(function ($query) use ($id) {
            $query->where('id', 'LIKE', '%' . $id . '%')
                ->orWhere('invoice_id', 'LIKE', '%' . $id . '%');
        })->where('order_type', $orderType)
            ->where('total', '>', 0)
            ->where('status', 'done')
            ->where(function ($q) {
                $q->where('return_type', '=', null)
                    ->orWhere('return_type', '=', 'partial');
            })
            ->select('created_by as createdBy', 'return_type', 'invoice_id', 'all_discount', 'customer_id as customer', 'created_at as date', 'id as orderID', 'type as salesOrReceivingType')->get();
    }

    //Get data for for invoice return
    public static function getOrderInformation($invoiceId, $orderType)
    {
        $query = Order::join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->select('orders.id', 'orders.profit', 'order_items.product_id', 'order_items.variant_id', 'order_items.quantity', 'products.title as productTitle', 'product_variants.variant_title as variantTitle')
            ->where('orders.invoice_id', '=', $invoiceId)
            ->where('orders.order_type', '=', $orderType)
            ->get();
        return $query;
    }


    //Get return product information
    public static function getReturnProduct($invoiceId, $orderType)
    {
        return Order::query()->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->select(
                'orders.id',
                'order_items.product_id',
                'order_items.variant_id',
                DB::raw('(CASE WHEN order_items.type = "discount" THEN 0 ELSE (CASE WHEN order_items.quantity < 0 THEN ((-1)*order_items.quantity) ELSE order_items.quantity END) END) as quantity'),
                'products.title as productTitle',
                'product_variants.variant_title as variantTitle')
            ->where('orders.returned_invoice', '=', $invoiceId)
            ->where('orders.order_type', '=', $orderType)
            ->where('orders.status', '=', 'done')
            ->get();
    }

    public static function getProfit($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkProfitPermission();
        $columns = [
            'item_tax' => DB::raw('sum(orders.total_tax) as item_tax'),
            'grand_total' => DB::raw('sum(orders.total) as grand_total'),
            'profit_amount' => DB::raw('sum(orders.profit) as profit_amount'),
            'date' => 'orders.date as date',
            'customer_id' => 'orders.customer_id as customer_id',
            'invoice_id' => 'orders.invoice_id as invoice_id',
            'year' => DB::raw('YEAR(orders.date) year'),
            'month' => DB::raw('MONTH(orders.date) month'),
            'customer' => DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer"),
        ];
        $filter = 'year';
        $groupByKey = 'year';
        $dateRangeStart = '';
        $dateRangeEnd = '';
        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $filter = $singleFilter['value'];
                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $dateRangeStart = $singleFilter['value'][0]['start'];
                    $dateRangeEnd = $singleFilter['value'][0]['end'];
                }
            }
        }
        if ($filter == 'customer') $groupByKey = 'customer_id';
        else $groupByKey = $filter;
        $query = Order::select(
            $columns[$filter],
            $columns['item_tax'],
            $columns['grand_total'],
            $columns['profit_amount']
        )->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.status', 'done')
            ->where('orders.order_type', 'sales')
            ->groupBy($groupByKey);
        if (isset($dateRangeStart) && $dateRangeStart != '' && isset($dateRangeEnd) && $dateRangeEnd != '') {
            $query->where('orders.date', '>=', $dateRangeStart)
                ->where('orders.date', '<=', $dateRangeEnd);
        }

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            // $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            $data = $query->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count, 'filter' => $filter];
        } else {
            return $query->get();
        }
    }

    public static function getInvoiceData($id, $cashRegister)
    {
        $joinTable = Order::join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->join('branches', 'branches.id', '=', 'orders.branch_id');
        if ($cashRegister == null) {
            return $joinTable->where('orders.id', '=', $id)
                ->select(
                    'orders.id',
                    'orders.date',
                    'orders.invoice_id',
                    'orders.sub_total',
                    'orders.total',
                    'payments.paid',
                    'payments.exchange as change',
                    'payment_types.name as method',
                    'branches.name as branch_name',
                    'customers.first_name',
                    'customers.last_name',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by_name"),
                    DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                )
                ->first();
        } else {
            return $joinTable->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                ->where('orders.id', '=', $id)
                ->select(
                    'orders.id',
                    'orders.date',
                    'orders.invoice_id',
                    'orders.sub_total',
                    'orders.total',
                    'payments.paid',
                    'payments.exchange as change',
                    'payment_types.name as method',
                    'cash_registers.title as cash_register_name',
                    'branches.name as branch_name',
                    'customers.first_name',
                    'customers.last_name',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS created_by_name"),
                    DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                )
                ->first();
        }
    }

    public static function getHoldOrders()
    {
        return Order::leftJoin('branches', 'branches.id', 'orders.transfer_branch_id')
            ->leftJoin('restaurant_tables', 'restaurant_tables.id', 'orders.table_id')
            ->select(
                'orders.created_by as createdBy',
                'orders.branch_id as branchId',
                'orders.status as status',
                'orders.all_discount',
                'orders.customer_id as customer',
                'orders.created_at as date',
                'branches.id as transfer_branch_id',
                'branches.name as transfer_branch_name',
                'orders.id as orderID',
                'orders.type as salesOrReceivingType',
                'orders.total_tax as tax',
                'orders.profit as profit',
                'orders.order_type as orderType',
                'orders.sub_total as subTotal',
                'orders.total as grandTotal',
                'orders.invoice_id as invoice_id',
                'restaurant_tables.id as tableId',
                'restaurant_tables.name as tableName'
            )
            ->where('orders.status', 'hold')
            ->get();
    }

    public static function getOrderDetailsForInvoice($orderId, $orderType, $cashRegisterId)
    {
        $joinTable = Order::join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('branches', 'branches.id', '=', 'orders.branch_id');
        if ($orderType == 'sales') {

            $jointTable2 = $joinTable->leftJoin('customers', 'customers.id', '=', 'orders.customer_id');

            $table_id = Order::getOne($orderId);
            if ($table_id->table_id != null) {
                $jointTable2 = $joinTable->join('restaurant_tables', 'restaurant_tables.id', '=', 'orders.table_id');
            }

            //without cash register
            if ($cashRegisterId == null) {

                return $jointTable2
                    ->where('orders.id', '=', $orderId)
                    ->select(
                        'orders.id',
                        'orders.date',
                        'orders.sales_note',
                        'orders.total_tax',
                        'orders.invoice_id',
                        'orders.sub_total',
                        'orders.total',
                        'orders.due_amount',
                        'payments.paid',
                        DB::raw('abs(sum(payments.exchange)) as exchange'),
                        'payment_types.name as method',
                        'orders.created_at',
                        'branches.name as branch_name',
                        'customers.first_name',
                        'customers.last_name',
                        'customers.phone_number',
                        'customers.address',
                        'customers.tin_number',
                        DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                        DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                    )
                    ->first();
            } else {

                if ($table_id->table_id != null) {
                    return $jointTable2
                        ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                        ->where('orders.id', '=', $orderId)
                        ->select(
                            'orders.id',
                            'orders.date',
                            'orders.sales_note',
                            'orders.total_tax',
                            'orders.invoice_id',
                            'orders.sub_total',
                            'orders.total',
                            'orders.due_amount',
                            'orders.table_id',
                            'restaurant_tables.name as table_name',
                            'payments.paid',
                            DB::raw('abs(sum(payments.exchange)) as exchange'),
                            'payment_types.name as method',
                            'orders.created_at',
                            'cash_registers.title as cash_register_name',
                            'branches.name as branch_name',
                            'customers.first_name',
                            'customers.last_name',
                            'customers.phone_number',
                            'customers.address',
                            'customers.tin_number',
                            DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                            DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                        )
                        ->first();
                } else {
                    return $jointTable2
                        ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                        ->where('orders.id', '=', $orderId)
                        ->select(
                            'orders.id',
                            'orders.date',
                            'orders.sales_note',
                            'orders.total_tax',
                            'orders.invoice_id',
                            'orders.sub_total',
                            'orders.total',
                            'orders.due_amount',
                            'orders.table_id',
                            'payments.paid',
                            DB::raw('abs(sum(payments.exchange)) as exchange'),
                            'payment_types.name as method',
                            'orders.created_at',
                            'cash_registers.title as cash_register_name',
                            'branches.name as branch_name',
                            'customers.first_name',
                            'customers.last_name',
                            'customers.phone_number',
                            'customers.address',
                            'customers.tin_number',
                            DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                            DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer_name")
                        )
                        ->first();
                }
            }
        } else {
            $jointTable2 = $joinTable->leftJoin('suppliers', 'suppliers.id', '=', 'orders.supplier_id');
            //without cash register
            if ($cashRegisterId == null) {
                return $jointTable2
                    ->where('orders.id', '=', $orderId)
                    ->select(
                        'orders.id',
                        'orders.date',
                        'orders.invoice_id',
                        'orders.sub_total',
                        'orders.total',
                        'orders.due_amount',
                        'orders.created_at',
                        'payments.paid',
                        DB::raw('abs(sum(payments.exchange)) as exchange'),
                        'payment_types.name as method',
                        'branches.name as branch_name',
                        'suppliers.first_name',
                        'suppliers.last_name',
                        'suppliers.tin_number',
                        DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                        DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS supplier_name")
                    )
                    ->first();
            } else {
                return $jointTable2
                    ->join('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
                    ->where('orders.id', '=', $orderId)
                    ->select(
                        'orders.id',
                        'orders.date',
                        'orders.invoice_id',
                        'orders.sub_total',
                        'orders.total',
                        'orders.due_amount',
                        'orders.created_at',
                        'payments.paid',
                        DB::raw('abs(sum(payments.exchange)) as exchange'),
                        'payment_types.name as method',
                        'cash_registers.title as cash_register_name',
                        'branches.name as branch_name',
                        'suppliers.first_name',
                        'suppliers.last_name',
                        'suppliers.tin_number',
                        DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS employee_name"),
                        DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS supplier_name")
                    )
                    ->first();
            }
        }
    }

    public static function getBookedTables()
    {
        $booked_table = Order::distinct()
            ->select('table_id')
            ->where('status', 'hold')
            ->get();

        return $booked_table
            ->pluck('table_id');
    }

    public static function customerReport($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = Order::where('orders.order_type', 'sales')
            ->leftJoin('customers', 'customers.id', 'orders.customer_id')
            ->where('orders.type', 'customer')
            ->where('orders.status', '=', 'done')
            ->groupBy('orders.customer_id')
            ->select('orders.customer_id',
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS name"),
                DB::raw('abs(sum(orders.due_amount)) as due')
            );
        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "customerGroup") {

                    $query->where('customers.customer_group', $singleFilter['value']);
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $query->where('orders.branch_id', $singleFilter['value']);
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('customers.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('customers.last_name', 'LIKE', '%' . $searchValue . '%');
            });
        }
        $count = null;
        $allData = null;
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
        } else {
            $data = $query->orderBy($columnName, $columnSortedBy)->get();
        }
        $data = Order::getTotalByType($data);
        $allData = Order::getTotalByType($allData);

        if (empty($requestType)) return ['data' => $data, 'allData' => $allData, 'count' => $count];
        else return $data;
    }

    public static function getTotalByType($data)
    {
        foreach ($data as $item) {
            $total_sales = Order::select(DB::raw('abs(sum(total)) as total_sales'))->where('status', 'done')->where('orders.type', 'customer')->where('customer_id', $item->customer_id)->where('total', '>', 0)->first();
            $item->total_sales = $total_sales->total_sales ? $total_sales->total_sales : 0;
            $total_return = Order::select(DB::raw('abs(sum(total)) as total_return'))->where('status', 'done')->where('orders.type', 'customer')->where('customer_id', $item->customer_id)->where('total', '<', 0)->first();
            $item->total_return = $total_return->total_return ? $total_return->total_return : 0;
            $total_payment = Order::select(DB::raw('abs(sum(total - due_amount)) as total_payment'))->where('status', 'done')->where('orders.type', 'customer')->where('customer_id', $item->customer_id)->where('total', '>', 0)->first();
            $item->total_payment = $total_payment->total_payment ? $total_payment->total_payment : 0;
        };
        return $data;
    }

    public static function supplierReport($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $query = Order::where('orders.order_type', 'receiving')
            ->leftJoin('suppliers', 'suppliers.id', 'orders.supplier_id')
            ->where('orders.type', 'supplier')
            ->groupBy('orders.supplier_id')
            ->select('orders.supplier_id',
                DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS name"),
                DB::raw('abs(sum(orders.due_amount)) as due'),
                DB::raw('abs(sum(orders.total)) as total_purchase'),
                DB::raw('abs(sum(total - due_amount)) as total_payment')
            );

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $query->where('orders.branch_id', $singleFilter['value']);
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('suppliers.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('suppliers.last_name', 'LIKE', '%' . $searchValue . '%');
            });
        }
        $count = null;
        $allData = null;
        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function totalSalesAmount($filtersData, $searchValue, $columnSortedBy, $limit, $offset, $columnName, $requestType)
    {
        $total_return_query = Order::select(DB::raw('sum(total) as total_return'))->where('order_type', 'sales')->where('total', '<', 0)->where('status', '=', 'done');
        $salesQuery = Order::select('orders.order_type',
            DB::raw('abs(sum(orders.due_amount)) as due'),
            DB::raw('abs(sum(orders.total_tax))  as tax'),
            DB::raw('sum(total) as total')

        )->where('order_type', 'sales')->where('status', 'done')->where('total', '>', 0);

        $total_purchase_return_query = Order::select(DB::raw('sum(total) as total_return_purchase'))->where('order_type', 'receiving')->where('total', '<', 0)->where('status', '=', 'done');
        $purchaseQuery = Order::select('orders.order_type',
            DB::raw('abs(sum(orders.due_amount)) as due'),
            DB::raw('abs(sum(orders.total_tax))  as tax'),
            DB::raw('sum(total) as total')
        )->where('order_type', 'receiving')->where('total', '>', 0);

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $total_return_query->where('orders.branch_id', $singleFilter['value']);
                    $salesQuery->where('orders.branch_id', $singleFilter['value']);
                    $purchaseQuery->where('orders.branch_id', $singleFilter['value']);
                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $total_return_query->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);

                    $salesQuery->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);

                    $purchaseQuery->where('orders.date', '>=', $singleFilter['value'][0]['start'])
                        ->where('orders.date', '<=', $singleFilter['value'][0]['end']);
                }
            }
        }
        $total_return = $total_return_query->first();
        $total_purchas_return = $total_purchase_return_query->first();
        $sales = $salesQuery->first();
        $purchase = $purchaseQuery->first();

        $totalReturnAmount = $total_return->total_return ? $total_return->total_return : 0;
        $totalPurchaseAmount = $total_purchas_return->total_return_purchase ? $total_purchas_return->total_return_purchase : 0;


        $data = [
            [
                'type' => Lang::get('lang.sales'),
                'total' => $sales->total
            ],
            [
                'type' => Lang::get('lang.sales_return'),
                'total' => $totalReturnAmount
            ],
            [
                'type' => Lang::get('lang.sales_due'),
                'total' => $sales->due
            ],
            [
                'type' => Lang::get('lang.sales_tax'),
                'total' => $sales->tax
            ],
            [
                'type' => Lang::get('lang.purchase'),
                'total' => $purchase->total
            ],
            [
                'type' => Lang::get('lang.purchase_return'),
                'total' => $totalPurchaseAmount
            ],
            [
                'type' => Lang::get('lang.purchase_due'),
                'total' => $purchase->due
            ],
            [
                'type' => Lang::get('lang.purchase_tax'),
                'total' => $purchase->tax
            ],
        ];
        return ['data' => $data];
    }

    public static function salesListDelete($id)
    {
        Order::where('id', $id)->update(['status' => 'delete']);

    }


    // Product Store

    public static function productStore($branch, $receivingPrice, $quantity, $lastproductid, $productVariantlast_id, $allSetting, $lastInvoiceNumber, $invoiceFixes)
    {
        $isCashRegister = Branch::select('is_cash_register')->where('id', $branch)->first()->value('is_cash_register');
        if ($isCashRegister == 1) {
            $cashRegister = CashRegister::select('id')->where('branch_id', $branch)->first()->value('id');
        } else {
            $cashRegister = null;
        }

        $orderlastid = Order::create([
            'date' => date('Y-m-d'),
            'branch_id' => $branch,
            'sub_total' => $receivingPrice * $quantity,
            'total' => $receivingPrice * $quantity,
            'type' => 'supplier',
            'order_type' => 'receiving',
            'status' => 'done',
            'created_by' => Auth()->user()->id,
        ]);


        Order::updateData($orderlastid->id, ['invoice_id' => $invoiceFixes['purchasePrefix'] . $lastInvoiceNumber . $invoiceFixes['purchaseSuffix']]);
        $lastInvoiceNumber += 1;

        $lastUpdatedInvoice = Setting::where('setting_name', 'purchase_last_invoice_number')->first()->setting_value;
        if ($lastInvoiceNumber > $lastUpdatedInvoice) {
            Setting::updateSetting('purchase_last_invoice_number', $lastInvoiceNumber);
        }

        // Payment

        Payments::create([
            'date' => date('Y-m-d'),
            'paid' => $receivingPrice * $quantity,
            'payment_method' => 1,
            'order_id' => $orderlastid->id,
            'cash_register_id' => $cashRegister,
        ]);

        OrderItems::create([
            'product_id' => $lastproductid,
            'variant_id' => $productVariantlast_id,
            'type' => 'receiving',
            'quantity' => $quantity,
            'price' => $receivingPrice,
            'sub_total' => $receivingPrice * $quantity,
            'order_id' => $orderlastid->id,
        ]);;
    }

    public static function productVariantOrderItem($branch, $lastproductid, $productVariant, $variantDetails, $allSetting, $lastInvoiceNumber, $invoiceFixes)
    {

        $isCashRegister = Branch::select('is_cash_register')->where('id', $branch)->first()->value('is_cash_register');
        if ($isCashRegister == 1) {
            $cashRegister = CashRegister::select('id')->where('branch_id', $branch)->first()->value('id');
        } else {
            $cashRegister = null;
        }

        $variantOrdersInsertValue = [];
        $variantOrdersInsertValueTotal = [];

        // bad codding start
        foreach ($variantDetails as $value) {
            array_push($variantOrdersInsertValueTotal, [

                'sub_total' => $value['purchasePrice'] * $value['qty'],
            ]);
        }


        $total = 0;
        foreach ($variantOrdersInsertValueTotal as $subtotal) {
            $total += $subtotal['sub_total'];
        }

        $orderlastid = Order::create([
            'date' => date('Y-m-d'),
            'branch_id' => $branch,
            'sub_total' => $total,
            'total' => $total,
            'type' => 'supplier',
            'order_type' => 'receiving',
            'status' => 'done',
            'created_by' => Auth()->user()->id,
        ]);

        Order::updateData($orderlastid->id, ['invoice_id' => $invoiceFixes['purchasePrefix'] . $lastInvoiceNumber . $invoiceFixes['purchaseSuffix']]);
        $lastInvoiceNumber += 1;

        $lastUpdatedInvoice = Setting::where('setting_name', 'purchase_last_invoice_number')->first()->setting_value;
        if ($lastInvoiceNumber > $lastUpdatedInvoice) {
            Setting::updateSetting('purchase_last_invoice_number', $lastInvoiceNumber);
        }

        // Payment

        Payments::create([
            'date' => date('Y-m-d'),
            'paid' => $total,
            'payment_method' => 1,
            'order_id' => $orderlastid->id,
            'cash_register_id' => $cashRegister,
        ]);

        foreach ($variantDetails as $key => $item) {
            array_push($variantOrdersInsertValue, [
                'product_id' => $lastproductid,
                'type' => 'receiving',
                'quantity' => $item['qty'],
                'price' => $item['purchasePrice'],
                'sub_total' => $item['purchasePrice'] * $item['qty'],
                'variant_id' => $productVariant[$key],
                'order_id' => $orderlastid->id,
            ]);
        }

        DB::table('order_items')->insert($variantOrdersInsertValue);
    }

    public static function getReturnProductProfit($returnInvoice)
    {
        return Order::select(DB::raw('abs(sum(orders.profit)) as profit'))->where('orders.returned_invoice', $returnInvoice)->where('orders.order_type', 'sales')->where('orders.status', '=', 'done')->first();
    }

}
