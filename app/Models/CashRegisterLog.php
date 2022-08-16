<?php

namespace App\Models;

use DB;
use Carbon\Carbon;

class CashRegisterLog extends BaseModel
{
    protected $fillable = [
        'user_id',
        'cash_register_id',
        'opening_amount',
        'closing_amount',
        'status',
        'opening_time',
        'closing_time',
        'opened_by',
        'closed_by',
        'note',
    ];

    public static function cashRegister($today)
    {
        return CashRegisterLog::whereDate('closing_time', '=', $today)
            ->where('status', '=', 'closed')->sum(DB::raw('closing_amount-opening_amount'));
    }

    public static function getRegisterAmount($id)
    {
        $log = CashRegisterLog::where('cash_register_id', $id)->where('status', 'open')->first();
        $start = $log->opening_time;

        $totalSales = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'sales')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->where('payments.created_at','>=',$start)->where('payments.payment_method', 1)->sum('payments.paid');
        $totalReceiving = Payments::join('orders', 'payments.order_id', 'orders.id')->where('orders.order_type', 'receiving')->where('orders.status', 'done')->where('payments.cash_register_id', $id)->where('payments.created_at','>=',$start)->where('payments.payment_method', 1)->sum('payments.paid');

        return  $log->opening_amount + $totalSales - $totalReceiving;


    }

    public static function registerLogFilter($filtersData, $searchValue, $columnName, $columnSortedBy, $limit, $offset, $requestType)
    {
        $query = CashRegisterLog::join('users', 'cash_register_logs.user_id', '=', 'users.id')
            ->join('cash_registers', 'cash_register_logs.cash_register_id', '=', 'cash_registers.id')
            ->select('cash_register_logs.*',
                'cash_registers.branch_id',
                'cash_registers.title',
                'cash_register_logs.opening_amount',
                'cash_register_logs.closing_amount',
                'cash_register_logs.closed_by as closed_user',
                DB::raw(" cash_register_logs.closing_amount AS cash_register_closing_amount"),
                DB::raw(" cash_register_logs.opening_amount AS cash_register_opening_amount"),
                DB::raw("CONCAT(users.first_name,' ',users.last_name)  AS opened_by"),
                DB::raw('abs(cash_register_logs.opening_amount - cash_register_logs.closing_amount) as difference'));

        if (!empty($filtersData)) {
            foreach ($filtersData as $singleFilter) {

                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == 'status') {
                    $query->where('cash_register_logs.status', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "cashRegisterName") {
                    $query->where('cash_register_logs.cash_register_id', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "employee") {
                    $query->where('cash_register_logs.opened_by', $singleFilter['value']);

                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $query->where('cash_registers.branch_id', $singleFilter['value']);

                } else if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                    $query->whereBetween(DB::raw('date(opening_time)'), [$starts, $ends]);
                }
            }
        }
        if (!empty($searchValue)) {
            $query->where(function ($query) use ($searchValue) {
                $query->where('cash_registers.title', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.first_name', 'LIKE', '%' . $searchValue . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $searchValue . '%');
            });
        }
        $query = $query->orderBy($columnName, $columnSortedBy);


        if (empty($requestType)) {
            $count = $query->count();
            $data = $query->take($limit)->skip($offset)->get();

            return ['data' => $data, 'count' => $count];

        } else {
            return $query->get();
        }

    }

    public static function getLogs($id)
    {
        return CashRegisterLog::join('users', 'users.id', 'opened_by')->select('status', 'user_id', DB::raw("concat(users.first_name,' ', users.last_name) as opened_by"), 'opened_by as open_user_id', 'opening_amount')->where('cash_register_logs.id', CashRegisterLog::where('cash_register_id', $id)->max('cash_register_logs.id'))->first();
    }

    public static function updateRegisterLog($cashRegisterId, $data)
    {
        return CashRegisterLog::where('cash_register_id', $cashRegisterId)->where('status', 'open')->update($data);
    }

    public static function getTotals($id)
    {
        return CashRegisterLog::where('cash_register_id', $id)->count();
    }

    public static function getRegistersLog($currentBranchID, $userID)
    {
        return CashRegisterLog::select('cash_register_logs.cash_register_id')->
        leftJoin('cash_registers', 'cash_registers.id', '=', 'cash_register_logs.cash_register_id')->
        where('cash_registers.branch_id', $currentBranchID)->
        where('cash_register_logs.status', 'open')->
        whereRaw('FIND_IN_SET(' . $userID . ',cash_register_logs.user_id)')->first();
    }

}
