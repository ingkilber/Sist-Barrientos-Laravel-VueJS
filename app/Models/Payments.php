<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\PermissionController;

class Payments extends BaseModel
{
    protected $fillable = ['date', 'exchange', 'paid', 'payment_method', 'options', 'order_id', 'cash_register_id', 'is_active'];


    public static function paymentReportList($filtersData, $searchValue, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $perm = new PermissionController();
        $permission = $perm->checkPaymentPermission();

        $query = Payments::select('payments.id', 'payments.order_id', 'payments.date', 'payments.paid', 'orders.invoice_id as invoice_id', 'payments.exchange', 'payment_types.name as payment_method', 'orders.customer_id', 'orders.order_type', 'orders.created_by', 'cash_registers.title as cash_register',
            DB::raw('(CASE WHEN orders.order_type = "sales" THEN concat(customers.first_name," ", customers.last_name) ELSE concat(users.first_name," ", users.last_name) END) as paid_by'),
            DB::raw('(CASE WHEN orders.order_type = "sales" THEN orders.customer_id ELSE orders.created_by END) as paid_id'))
            ->leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->leftJoin('cash_registers', 'cash_registers.id', '=', 'payments.cash_register_id')
            ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('users', 'users.id', '=', 'orders.created_by')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.status', '=', 'done');

        if ($permission == 'personal') {
            $query->where('orders.created_by', Auth::user()->id);
        }

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $query->where('orders.order_type', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "cashRegister") {
                    $query->where('payments.cash_register_id', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "paymentMethod") {
                    $query->where('payments.payment_method', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $query->whereBetween('payments.date', [$singleFilter['value'][0]['start'], $singleFilter['value'][0]['end']]);
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where('cash_registers.title', 'LIKE', '%' . $searchValue . '%')->orWhere('orders.invoice_id', 'LIKE', '%' . $searchValue . '%');
        }

        if (empty($requestType)) {
            $allData = $query->get();
            $count = $query->count();
            $data = $query->orderBy('payments.id', $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {

            $data = $query->orderBy('payments.' . $columnName, $columnSortedBy)->get();
            return ['data' => $data];
        }
    }

    public static function paymentSummary($filtersData, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $query = Payments::leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', '=', 'done');
        $id = "orders.id";

        $filter_key = 'payments.date as filter_key';

        if (empty($filtersData)) {
            $query->groupBy('payments.date');

        } else {

            foreach ($filtersData as $singleFilter) {

                if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "date") {
                    $query->groupBy('payments.date');

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "customer") {
                    $filter_key = DB::raw('concat(customers.first_name," ", customers.last_name) as filter_key');
                    $id = "customers.id as id";
                    $query->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
                        ->groupBy('orders.customer_id');

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "employee") {
                    $filter_key = DB::raw('concat(users.first_name," ", users.last_name) as filter_key');
                    $id = "users.id as id";
                    $query->leftJoin('users', 'orders.created_by', '=', 'users.id')
                        ->whereNotNull('orders.created_by')
                        ->groupBy('orders.created_by');

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "sales") {
                    $query->where('orders.order_type', 'sales');

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "receiving") {
                    $query->where('orders.order_type', 'receiving');

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "paymentMethod") {
                    $query->where('payments.payment_method', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                    $query->whereBetween('payments.date', [$starts, $ends]);
                }
            }
        }
        $query = $query->select($filter_key, $id, DB::raw('sum(payments.paid) as total'),
            DB::raw('sum(payments.exchange) as totalChange'), DB::raw('GROUP_CONCAT(DISTINCT(payment_types.name)) as method'), DB::raw('GROUP_CONCAT(DISTINCT(orders.order_type)) as type'));

        if (empty($requestType)) {
            $count = $query->get()->count();
            $allData = $query->get();
            $data = $query->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

            return ['data' => $data, 'allData' => $allData, 'count' => $count];

        } else {

            return $query->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function yearFilter($salesFilterData, $year, $currentMonth)
    {
        $duration = $salesFilterData['duration'];
        $query = Payments::join('orders', 'orders.id', 'payments.order_id')
            ->where('orders.order_type', 'sales');

        $user = $salesFilterData['user'];

        if (!empty($user)) {
            $query->where('orders.created_by', $user);
        }

        $branch = $salesFilterData['branch'];

        if (!empty($branch)) {
            $query->where('orders.branch_id', $branch);
        }

        if ($duration == 'last_year') {
            $query->groupBy(DB::raw('month(payments.date)'))
                ->where(DB::raw('year(payments.date)'), $year - 1);
            $selectData = DB::raw('month(payments.date) as month');

        } elseif ($duration == 'this_month') {
            $query->groupBy(DB::raw('day(payments.date)'))
                ->where(DB::raw('month(payments.date)'), $currentMonth);
            $selectData = DB::raw('day(payments.date) as day');

        } elseif ($duration == 'last_month') {
            $query->groupBy(DB::raw('day(payments.date)'))
                ->where(DB::raw('month(payments.date)'), $currentMonth - 1);
            $selectData = DB::raw('day(payments.date) as day');

        } else {
            $query->groupBy(DB::raw('month(payments.date)'))
                ->where(DB::raw('year(payments.date)'), $year);
            $selectData = DB::raw('month(payments.date) as month');
        }

        return $query->select(DB::raw('sum(payments.paid) as sales'), $selectData)->get();

    }

    public static function getTotals($id)
    {
        return Payments::where('payment_method', $id)->count();
    }

    public static function paymentDetails($id)
    {
        return Payments::join('payment_types', 'payments.payment_method', 'payment_types.id')
            ->select('payment_types.name',
                DB::raw("sum(payments.paid) as paid")
            )
            ->where('payments.order_id', $id)
            ->groupBy('payments.order_id')
            ->groupBy('payments.payment_method')
            ->get();
    }

    public static function getPaymentInfo($id, $start, $end)
    {
        if ($end == null) {
            $totalSales = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'sales')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->where('payments.created_at', '>=', $start)->where('payments.payment_method', 1)->sum('payments.paid');
            $totalReceiving = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'receiving')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->where('payments.created_at', '>=', $start)->where('payments.payment_method', 1)->sum('payments.paid');
        } else {
            $totalSales = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'sales')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->whereBetween('payments.created_at', [$start, $end])->where('payments.payment_method', 1)->sum('payments.paid');
            $totalReceiving = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'receiving')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->whereBetween('payments.created_at', [$start, $end])->where('payments.payment_method', 1)->sum('payments.paid');
        }

        return ['sales' => $totalSales, 'receiving' => $totalReceiving];
    }

    public static function destroyByOrderAndType($orederId, $paymentType)
    {
        return Payments::leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->where('payments.order_id', $orederId)->where('payment_types.type', $paymentType)->delete();
    }

    public static function getAllInfoByPaymentType($paymentType)
    {

        $data = Payments::leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->select('payments.*', 'payment_types.id as p_type_id', 'payment_types.name as p_type_name', 'payment_types.type as p_type_type')
            ->where('payment_types.type', $paymentType)
            ->get();
        return $data;

    }

    public static function getPaymentDetails($orderId)
    {
        return Payments::leftJoin('payment_types', 'payment_types.id', '=', 'payments.payment_method')
            ->where('payments.order_id', $orderId)
            ->where('is_active', 1)
            ->select('payment_types.name', 'payments.paid')->get();
    }

    public static function getExchange($orderId)
    {
        return Payments::where('order_id', $orderId)->first();
    }
}
