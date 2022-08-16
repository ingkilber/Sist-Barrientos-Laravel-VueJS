<?php

namespace App\Models;

use App\Models\OrderItems;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class CashRegister extends BaseModel
{
    protected $fillable = ['title', 'branch_id', 'sales_invoice_id', 'receiving_invoice_id', 'created_by'];

    protected $casts = [
        'branch_id' => 'int',
        'sales_invoice_id' => 'int',
        'receiving_invoice_id' => 'int',
        'created_by' => 'int',
    ];

    public static function getRegisters($columnName, $columnSortedBy, $limit, $offset)
    {
        $count = CashRegister::count();
        $data = CashRegister::join('branches', 'cash_registers.branch_id', '=', 'branches.id')
            ->select('cash_registers.*', 'branches.name as branch_name')
            ->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();

        return ['data' => $data, 'count' => $count];
    }

    public static function getTotals($id)
    {
        return CashRegister::where('branch_id', $id)->count();
    }

    public static function getList($branchId)
    {
        return CashRegister::select('id', 'title', 'branch_id as branchID', 'sales_invoice_id', 'receiving_invoice_id', 'multiple_access')->where('branch_id', $branchId)->get();
    }

    public static function getCashRegisters($id)
    {
        return CashRegister::select('id', 'title', 'branch_id as branchID', 'sales_invoice_id', 'receiving_invoice_id')->where('id', $id)->first();
    }

    public static function getCurrentCashRegister()
    {
        $currentBranch = Setting::getSettingValue('current_branch');
        $cashRegister = null;
        if (isset($currentBranch)) {
            $cashRegister = CashRegister::select('*')->where('branch_id', $currentBranch->setting_value)->first();
        }
        return $cashRegister;
    }

    public static function registerSaleInfo($columnName, $columnSortedBy, $limit, $offset, $id)
    {
        $todaydate = date('Y-m-d');

        $data = Order::leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->join('payments', 'orders.id', 'payments.order_id')
            ->join('cash_registers', 'payments.cash_register_id', 'cash_registers.id')
            ->join('cash_register_logs', 'cash_registers.id', 'cash_register_logs.cash_register_id')
            ->select('orders.*', 'cash_register_logs.status',
                DB::raw("CONCAT(customers.first_name,' ',customers.last_name)  AS customer"),
                DB::raw("customers.id as customer_id")
            )
            ->groupBy('payments.order_id')
            ->where('orders.date', $todaydate)
            ->where('orders.branch_id', $id)
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.status', '=', 'done')
            ->where('orders.created_by', Auth::user()->id)
            ->where('cash_register_logs.status', '=', 'open');
        if (empty($requestType)) {
            $count = $data->get()->count();
            $allData = $data->get();
            $data = $data->orderBy($columnName, $columnSortedBy)->take($limit)->skip($offset)->get();
            return ['data' => $data, 'allData' => $allData, 'count' => $count];
        } else {
            return $data->orderBy($columnName, $columnSortedBy)->get();
        }
    }

    public static function cashRegisterInfo($id)
    {
        $user_id = Auth::user()->id;
        $log = CashRegisterLog::where('opened_by', $user_id)->where('status', 'open')->sum('opening_amount');


        $openingTime = CashRegisterLog::leftJoin('cash_registers', 'cash_registers.id', 'cash_register_logs.cash_register_id')
            ->where('cash_register_logs.opened_by', $user_id)
            ->where('cash_register_logs.status', 'open')
            ->where('cash_registers.branch_id', $id)->first();

        $query = Order::join('payments', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('payment_types', 'payment_types.id', 'payments.payment_method')
            ->join('cash_registers', 'cash_registers.id', 'payments.cash_register_id')
            ->join('cash_register_logs', 'cash_register_logs.cash_register_id', 'cash_registers.id')
            ->join('users', 'users.id', '=', 'orders.created_by')
            ->join('branches', 'branches.id', '=', 'orders.branch_id')
            ->select('orders.*')
            ->select('payment_types.*')
            ->where('orders.status', '=', 'done')
            ->where('cash_register_logs.user_id', Auth::user()->id)
            ->where('cash_register_logs.status', '=', 'open')
            ->where('cash_register_logs.opening_time', '>=', Carbon::parse($openingTime->opening_time));

        $totalSales = $query->where('orders.order_type', '=', 'sales')->sum('orders.sub_total');

        $totalCashSales = $query->where('orders.order_type', '=', 'sales')
            ->where('payment_types.type', '=', 'cash')->sum('payments.paid');

        //$totalPurchase = $query->where('orders.order_type', 'receiving')->sum('orders.sub_total');

        //$totalCashPurchase = $query->where('orders.order_type', 'receiving')->where('payments.payment_method', 'cash')->sum('orders.sub_total');


        return [
            'opening_amount' => $log,
            'total_sales' => $totalSales,
            'total_cash_sale' => $totalCashSales,
            //  'total_purchase' => $totalPurchase,
            // 'total_cash_purchase' => $totalCashPurchase
        ];
    }
}
