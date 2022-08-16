<?php

namespace App\Http\Controllers\API;

use App\Models\CashRegisterLog;
use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Promise\all;
use Carbon\Carbon;
use App\Libraries\AllSettingFormat;
use App\Models\OrderItems;
use App\Models\Payments;


class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAllData()
    {
        $basicData = $this->getBasicData();
        $barChartData = $this->barChartData();
        $lineChartData = $this->lineChartData();

        return ['basicData' => $basicData, 'barChartData' => $barChartData, 'lineChartData' => $lineChartData];
    }

    public function getBasicData()
    {
        $today = Carbon::today()->toDateString();
        $date = Carbon::today()->subDays(30);
        $date = date('Y-m-d', strtotime($date));

        $data = array();

        $data['todaySales'] = Order::todaysSale($today);

        $data['monthlySale'] = Order::monthlySold($date);

        $data['totalSale'] = Order::totalSold();

        $data['totalReturn'] = 0;

        $data['todayProfit'] = Order::todayProfit($today);

        $data['monthlyProfit'] = Order::monthlyProfit($date);

        $data['totalProfit'] = Order::totalProfit();

        return $data;
    }

    public function barChartData()
    {
        $year = date("Y");

        $monthlySale = OrderItems::monthlySale($year);
        $monthlyArraySale = $this->manipulateBarChart($monthlySale, 'sales');
        $monthlyReceive = OrderItems::monthlyReceive($year);
        $monthlyArrayReceive = $this->manipulateBarChart($monthlyReceive, 'receive');

        $monthlyProfit = OrderItems::monthlyProfit($year);
        $monthlyArrayProfit = $this->manipulateBarChart($monthlyProfit, 'profit');

        return ['receiving' => $monthlyArrayReceive, 'sales' => $monthlyArraySale, 'profit' => $monthlyArrayProfit];
    }

    public function manipulateBarChart($chartData, $key)
    {

        $dataArray = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

        foreach ($chartData as $data) {

            $dataArray[$data->month - 1] = $data[$key];
        }

        return $dataArray;
    }

    public function lineChartData()
    {
        $profit = array();
        $days = array();

        $sevenDaysProfit = Order::getSevenDaysProfit();

        foreach ($sevenDaysProfit as $dailyProfit) {
            $date = $dailyProfit->date;
            $day = date("D", strtotime($date));
            array_push($profit, $dailyProfit->profit);
            array_push($days, $day);
        }

        return ['days' => $days, 'profit' => $profit];
    }
}