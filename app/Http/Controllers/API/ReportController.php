<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Libraries\searchHelper;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\ProductVariant;
use App\Models\ShippingInformation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdjustProductStockType;
use Illuminate\Support\Facades\DB;
use App\Models\CustomUser;
use Illuminate\Support\Facades\Lang;

class ReportController extends Controller
{

    private function compare($a, $b)
    {
        return strcmp($a->sub_total, $b->sub_total);
    }

    public function salesReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $sales = OrderItems::salesItems($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {
            $salesData = $sales['data'];
        } else {
            $salesData = $sales;
        }

        if (empty($requestType)) {
            $salesItems = $this->calculateSales($salesData);

            $arrayCount = $salesItems['count'];
            $totalCount = count($sales['allData']);
            $salesData[$arrayCount] = [
                'invoice_id' => Lang::get('lang.total'),
                'item_purchased' => $salesItems['netItem'],
                'tax' => $salesItems['netTax'],
                'discount' => $salesItems['discount'],
                'total' => $salesItems['netTotal'],
                'due_amount' => $salesItems['netDueAmount']
            ];

            $grandCalculation = $this->calculateSales($sales['allData']);

            $salesData[$arrayCount + 1] = [
                'invoice_id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandCalculation['netItem'],
                'tax' => $grandCalculation['netTax'],
                'discount' => $grandCalculation['discount'],
                'total' => $grandCalculation['netTotal'],
                'due_amount' => $salesItems['netDueAmount']
            ];
            return ['datarows' => $salesData, 'count' => $totalCount];
        } else {
            $this->calculateSales($salesData);
            return ['datarows' => $salesData];
        }
    }

    public function allSalesDetails(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = $request->searchValue;
        $requestType = $request->reqType;

        $sales = OrderItems::getAllSalesDetails($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {
            $salesData = $sales['data'];
        } else {
            $salesData = $sales;
        }

        if (empty($requestType)) {
            $salesItems = $this->calculateSalesDetails($salesData);

            $arrayCount = $salesItems['count'];
            $totalCount = count($sales['allData']);
            $salesData[$arrayCount] = [
                'invoice_id' => Lang::get('lang.total'),
                'sub_total' => $salesItems['sub_total'],
                'quantity' => $salesItems['quantity'],
            ];

            $grandCalculation = $this->calculateSalesDetails($sales['allData']);

            $salesData[$arrayCount + 1] = [
                'invoice_id' => Lang::get('lang.grand_total'),
                'sub_total' => $grandCalculation['sub_total'],
                'quantity' => $grandCalculation['quantity'],
            ];
            return ['datarows' => $salesData, 'count' => $totalCount];
        } else {
            $this->calculateSales($salesData);
            return ['datarows' => $salesData];
        }

    }

    public function calculateSalesDetails($salesData)
    {

        $netTotal = 0;
        $netItem = 0;
        $arrayCount = 0;

        foreach ($salesData as $rowData) {
            $netTotal += $rowData->sub_total;
            $netItem += $rowData->quantity;
            $arrayCount++;
        }

        return [
            'sub_total' => $netTotal,
            'quantity' => $netItem,
            'count' => $arrayCount,
        ];
    }

    public function calculateSales($salesData)
    {
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;
        $netDueAmount = 0;

        foreach ($salesData as $rowData) {
            if ($rowData->type == 'customer') {
                $rowData->type = Lang::get('lang.customer');
            } else if ($rowData->type == 'returns'){
                $rowData->type = Lang::get('lang.returns');
            } else if ($rowData->type == 'internal-transfer'){
                $rowData->type = Lang::get('lang.internal_transfer');
            } else {
                $rowData->type = Lang::get('lang.internal_sales');
                $rowData->customer = $rowData->transfer_branch_name;
            }
            if ($rowData->customer == '') $rowData->customer = Lang::get('lang.walk_in_customer');
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $netDueAmount += $rowData->due_amount;
            $arrayCount++;
        }

        return [
            'netTotal' => $netTotal,
            'netTax' => $netTax,
            'netItem' => $netItem,
            'discount' => $netDiscount,
            'count' => $arrayCount,
            'netDueAmount' => $netDueAmount
        ];
    }

    public function salesSummaryReport(Request $request)
    {
        $filterKey = 'product_brands.name as filter_key';
        $groupBy = 'products.brand_id';
        $joinTable = 'product_brands';
        $joinColumn1 = 'product_brands.id';
        $joinColumn2 = 'products.brand_id';
        $branchId = 0;
        $dateFormat = false;
        $requestType = $request->reqType;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $columnName = 'product_brands.name';
        $columnSortedBy = $request->columnSortedBy;

        if (empty($filtersData)) {
            $summary = OrderItems::salesSummary($filterKey, $limit, $request->rowOffset, $groupBy, $requestType, $columnName, $columnSortedBy);
        } else {
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $filter = $singleFilter['value'];
                    if ($filter == 'brand') {
                        $filterKey = 'product_brands.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_brands';
                        $joinColumn1 = 'product_brands.id';
                        $joinColumn2 = 'products.brand_id';
                        $columnName = 'product_brands.name';
                    } else if ($filter == 'category') {
                        $filterKey = 'product_categories.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_categories';
                        $joinColumn1 = 'product_categories.id';
                        $joinColumn2 = 'products.category_id';
                        $columnName = 'product_categories.name';
                    } else if ($filter == 'group') {
                        $filterKey = 'product_groups.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_groups';
                        $joinColumn1 = 'product_groups.id';
                        $joinColumn2 = 'products.group_id';
                        $columnName = 'product_groups.name';
                    } else if ($filter == 'customer') {
                        $groupBy = 'customers.first_name';
                        $filterKey = DB::raw('concat(customers.first_name," ",customers.last_name) as filter_key');
                        $joinTable = 'customers';
                        $joinColumn1 = 'customers.id';
                        $joinColumn2 = 'orders.customer_id';
                        $columnName = 'customers.first_name';
                    } else if ($filter == 'employee') {
                        $filterKey = DB::raw('concat(users.first_name," ",users.last_name) as filter_key');
                        $groupBy = 'orders.created_by';
                        $joinTable = 'users';
                        $joinColumn1 = 'users.id';
                        $joinColumn2 = 'orders.created_by';
                        $columnName = 'users.first_name';
                    } else if ($filter == 'product') {
                        $filterKey = DB::raw('concat(title,if(variant_title="default_variant","",concat("(",product_variants.variant_title,")"))) as filter_key');
                        $groupBy = 'order_items.variant_id';
                        $joinTable = 'product_variants';
                        $joinColumn1 = 'product_variants.id';
                        $joinColumn2 = 'order_items.variant_id';
                        $columnName = 'products.title';
                    } else if ($filter == 'date') {
                        $filterKey = 'orders.date as filter_key';
                        $groupBy = 'orders.date';
                        $dateFormat = true;
                        $columnName = 'orders.date';
                    }
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $branchId = $singleFilter['value'];
                }
            }

            $starts = 0;
            $ends = 0;
            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                }
            }

            $summary = OrderItems::salesSummaryTypeFilter($filterKey, $limit, $request->rowOffset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy);
        }

        if (empty($requestType)) {
            $summaryData = $summary['data'];
        } else {
            $summaryData = $summary;
        }

        foreach ($summaryData as $rowData) {
            if ($rowData->filter_key == '') $rowData->filter_key = Lang::get('lang.walk_in_customer');
            if ($dateFormat) {
                $allSettingFormat = new AllSettingFormat;
                $rowData->filter_key = $allSettingFormat->getDate($rowData->filter_key);
            }
        }
        if (empty($requestType)) {
            $totalCount = $summary['count'];
            $salesSummary = $this->calculateSalesSummary($summaryData);
            $arrayCount = $salesSummary['count'];
            $summaryData[$arrayCount] = ['filter_key' => Lang::get('lang.total'), 'item_purchased' => $salesSummary['netItem'], 'discount' => $salesSummary['discount'], 'sub_total' => $salesSummary['netSubTotal'], 'tax' => $salesSummary['netTax'], 'total' => $salesSummary['netTotal']];
            $grandCalculation = $this->calculateSalesSummary($summary['allData']);
            $summaryData[$arrayCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'item_purchased' => $grandCalculation['netItem'], 'discount' => $salesSummary['discount'], 'sub_total' => $grandCalculation['netSubTotal'], 'tax' => $grandCalculation['netTax'], 'total' => $grandCalculation['netTotal']];

            return ['datarows' => $summaryData, 'count' => $totalCount];
        } else {

            $this->calculateSalesSummary($summaryData);
            return ['datarows' => $summaryData];
        }
    }

    public function calculateSalesSummary($salesSummary)
    {

        $netSubTotal = 0;
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;

        foreach ($salesSummary as $rowData) {

            $rowData->total = $rowData->sub_total + $rowData->tax - $rowData->discount;
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $arrayCount++;
            $netSubTotal += $rowData->sub_total;
        }

        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'netItem' => $netItem, 'discount' => $netDiscount, 'netSubTotal' => $netSubTotal, 'count' => $arrayCount];
    }


    public function getSalesDetails(Request $request, $id)
    {
        $count = 0;
        $details = OrderItems::getOrderDetails($id);
        foreach ($details as $item) {
            if ($item->title == 'Discount') {
                $item->price = null;
                $item->quantity = null;
                $item->discount = null;
            } else {
                $item->discount = $this->formateDiscount($item->discount);
                $item->price = $this->formateDiscount($item->price);
            }
            $count++;
        }

        $orders = Order::getsOrders($id);

        $details[$count++] = ['title' => Lang::get('lang.sub_total'), 'total' => $orders->sub_total];
        $details[$count++] = ['title' => Lang::get('lang.tax'), 'total' => $orders->total_tax];
        $details[$count++] = ['title' => Lang::get('lang.total'), 'total' => $orders->total];

        $payments = Payments::paymentDetails($id);

        foreach ($payments as $payment) {
            $details[$count++] = ['title' => $payment->name, 'total' => $payment->paid];
        }
        return ['datarows' => $details, 'count' => 0];
    }

    public function formateDiscount($discount)
    {
        $allSettingFormat = new AllSettingFormat;
        return $allSettingFormat->getCurrencySeparator($discount);
    }

    public function calculateOrderDetails($orderDetails)
    {
        $totalQuantity = 0;
        $subTotal = 0;
        $totalTax = 0;
        $allTotal = 0;
        $count = 0;
        foreach ($orderDetails as $item) {

            if ($item->tax != null) {
                $taxAmount = $item->calculatingTax;
            } else {
                $taxAmount = 0;
            }
            $tax = ($item->subtotal * $taxAmount) / 100;

            if ($item->discount_type == "%") {
                $discount = ($item->subTotal * $item->discount) / 100;
            } else {
                $discount = $item->discount;
            }
            $item->total = ($item->subtotal - $discount) + $taxAmount;
            $count++;
            $totalQuantity = $totalQuantity + $item->quantity;
            $subTotal = $subTotal + $item->subtotal;
            $totalTax = $totalTax + $taxAmount;
            $allTotal = $allTotal + $item->total;
        }
        return ['totalQuantity' => $totalQuantity, 'subTotal' => $subTotal, 'totalTax' => $totalTax, 'allTotal' => $allTotal, 'count' => $count];
    }

    public function purchaseReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;
        $requestType = $request->reqType;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $receiving = OrderItems::receivingItems($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        if (empty($requestType)) {
            $receivingItems = $receiving['data'];
            $totalCalculation = $this->calculateReceivings($receiving['data']);
            $arrayCount = $totalCalculation['count'];
            $totalCount = count($receiving['allData']);
            $receivingItems[$arrayCount] = ['invoice_id' => Lang::get('lang.total'), 'item_purchased' => $totalCalculation['netItem'], 'total' => $totalCalculation['total'], 'due_amount' => $totalCalculation['netDue']];

            $grandCalculation = $this->calculateReceivings($receiving['allData']);

            $receivingItems[$arrayCount + 1] = ['invoice_id' => Lang::get('lang.grand_total'), 'item_purchased' => $grandCalculation['netItem'], 'total' => $grandCalculation['total'], 'due_amount' => $totalCalculation['netDue']];

            return ['datarows' => $receivingItems, 'count' => $totalCount];
        } else {

            $this->calculateReceivings($receiving);
            return ['datarows' => $receiving];
        }
    }

    public function calculateReceivings($receivingData)
    {
        $netTotal = 0;
        $netItem = 0;
        $netDue = 0;
        $arrayCount = 0;

        foreach ($receivingData as $rowData) {

            if ($rowData->type == 'supplier') {
                $rowData->type = Lang::get('lang.supplier');
            } else if ($rowData->type == 'internal-transfer') {
                $rowData->type = Lang::get('lang.internal_transfer');
            } else {
                $rowData->type = Lang::get('lang.internal_receivings');
            }

            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDue += $rowData->due_amount;
            $arrayCount++;
        }

        return ['total' => $netTotal, 'netItem' => $netItem, 'netDue' => $netDue, 'count' => $arrayCount];
    }

    public function receivingSummary(Request $request)
    {
        $filterKey = 'product_brands.name as filter_key';
        $groupBy = 'products.brand_id';
        $joinTable = 'product_brands';
        $joinColumn1 = 'product_brands.id';
        $joinColumn2 = 'products.brand_id';
        $branchId = 0;
        $dateFormat = false;
        $requestType = $request->reqType;

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $columnName = 'product_brands.name';
        $columnSortedBy = $request->columnSortedBy;

        if (empty($filtersData)) {
            $summaryData = OrderItems::receiveSummary($filterKey, $limit, $request->rowOffset, $groupBy, $requestType, $columnName, $columnSortedBy);
        } else {

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "type") {
                    $filter = $singleFilter['value'];

                    if ($filter == 'brand') {
                        $filterKey = 'product_brands.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_brands';
                        $joinColumn1 = 'product_brands.id';
                        $joinColumn2 = 'products.brand_id';
                        $columnName = 'product_brands.name';
                    }

                    if ($filter == 'category') {
                        $filterKey = 'product_categories.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_categories';
                        $joinColumn1 = 'product_categories.id';
                        $joinColumn2 = 'products.category_id';
                        $columnName = 'product_categories.name';
                    }

                    if ($filter == 'group') {
                        $filterKey = 'product_groups.name as filter_key';
                        $groupBy = 'products.' . $filter . '_id';
                        $joinTable = 'product_groups';
                        $joinColumn1 = 'product_groups.id';
                        $joinColumn2 = 'products.group_id';
                        $columnName = 'product_groups.name';
                    }

                    if ($filter == 'supplier') {
                        $filterKey = DB::raw('concat(suppliers.first_name," ",suppliers.last_name) as filter_key');
                        $groupBy = 'suppliers.first_name';
                        $joinTable = 'suppliers';
                        $joinColumn1 = 'suppliers.id';
                        $joinColumn2 = 'orders.supplier_id';
                        $columnName = 'suppliers.first_name';
                    }

                    if ($filter == 'employee') {
                        $filterKey = DB::raw('concat(users.first_name," ",users.last_name) as filter_key');
                        $groupBy = 'orders.created_by';
                        $joinTable = 'users';
                        $joinColumn1 = 'users.id';
                        $joinColumn2 = 'orders.created_by';
                        $columnName = 'users.first_name';
                    }

                    if ($filter == 'product') {
                        $filterKey = DB::raw('concat(title,if(variant_title="default_variant","",concat("(",product_variants.variant_title,")"))) as filter_key');
                        $groupBy = 'order_items.variant_id';
                        $joinTable = 'product_variants';
                        $joinColumn1 = 'product_variants.id';
                        $joinColumn2 = 'order_items.variant_id';
                        $columnName = 'products.title';
                    }

                    if ($filter == 'date') {
                        $filterKey = 'orders.date as filter_key';
                        $groupBy = 'orders.date';
                        $dateFormat = true;
                        $columnName = 'orders.date';
                    }
                } else if (array_key_exists('key', $singleFilter) && $singleFilter['key'] == "branch") {
                    $branchId = $singleFilter['value'];
                }
            }

            $starts = 0;
            $ends = 0;

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('filterKey', $singleFilter) && $singleFilter['filterKey'] == "date_range") {
                    $starts = $singleFilter['value'][0]['start'];
                    $ends = $singleFilter['value'][0]['end'];
                }
            }

            $summaryData = OrderItems::receiveSummaryFilter($filterKey, $limit, $request->rowOffset, $joinTable, $joinColumn1, $joinColumn2, $groupBy, $singleFilter, $branchId, $starts, $ends, $requestType, $columnName, $columnSortedBy);
        }

        if (empty($requestType)) {
            $receiveSummary = $summaryData['data'];
        } else {
            $receiveSummary = $summaryData;
        }

        foreach ($receiveSummary as $rowQuery) {
            if ($rowQuery->filter_key == '') $rowQuery->filter_key = Lang::get('lang.walk_in_supplier');

            if ($dateFormat) {
                $allSettingFormat = new AllSettingFormat;
                $rowQuery->filter_key = $allSettingFormat->getDate($rowQuery->filter_key);
            }
        }

        if (empty($requestType)) {

            $totalCalculation = $this->calculateReceivingSummary($receiveSummary);
            $arrayCount = $totalCalculation['count'];
            $totalCount = $summaryData['count'];
            $receiveSummary[$arrayCount] = ['filter_key' => Lang::get('lang.total'), 'item_receive' => $totalCalculation['netItem'], 'total' => $totalCalculation['total']];
            $grandCalculation = $this->calculateReceivingSummary($summaryData['allData']);
            $receiveSummary[$arrayCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'item_receive' => $grandCalculation['netItem'], 'total' => $grandCalculation['total']];

            return ['datarows' => $receiveSummary, 'count' => $totalCount];
        } else {
            $this->calculateReceivingSummary($receiveSummary);
            return ['datarows' => $receiveSummary];
        }
    }

    public function calculateReceivingSummary($receivingData)
    {

        $netTotal = 0;
        $netItem = 0;
        $arrayCount = 0;

        foreach ($receivingData as $rowData) {
            $rowData->total = $rowData->sub_total + $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_receive;
            $arrayCount++;
        }

        return ['total' => $netTotal, 'netItem' => $netItem, 'count' => $arrayCount];
    }


    public function registerLogReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $registerLogData = CashRegisterLog::registerLogFilter($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);
        if (empty($requestType)) {

            $registerLogs = $registerLogData['data'];
        } else {
            $registerLogs = $registerLogData;
        }

        foreach ($registerLogs as $registerLog) {

            $paymentInfo = Payments::getPaymentInfo($registerLog->cash_register_id, $registerLog->opening_time, $registerLog->closing_time);

            $registerLog->cash_receives = $paymentInfo['receiving'];
            $registerLog->cash_sales = $paymentInfo['sales'];


            if ($registerLog->difference == null) {
                $registerLog->difference = '';
            }

            if ($registerLog->closing_amount == null) {
                $registerLog->closing_amount = '';
            }

            if ($registerLog->closed_by) {
                $registerLog->difference = (float) $registerLog->opening_amount + (float) $registerLog->cash_sales - (float) $registerLog->cash_receives - (float) $registerLog->closing_amount;
                $registerLog->closed_user = CustomUser::userInfo($registerLog->closed_by);
            }

            if ($registerLog->status == 'closed') {
                $registerLog->status = Lang::get('lang.closed');
            } else {
                $registerLog->status = Lang::get('lang.open');
                $registerLog->closing_amount = '';
            }
        }
        if (empty($requestType)) {
            $totalCount = $registerLogData['count'];

            return ['datarows' => $registerLogs, 'count' => $totalCount];
        } else {
            return ['datarows' => $registerLogData];
        }
    }

    public function getCashRegisterFilterData()
    {

        $cashRegisters = CashRegister::index(['title as text', 'id as value']);
        $user = CustomUser::getAll([DB::raw('concat(first_name," ",last_name) as text'), 'id as value'], 'user_type', 'staff');
        $branch = Branch::index(['name as text', 'id as value']);

        return ['user' => $user, 'branch' => $branch, 'cashRegisters' => $cashRegisters];
    }

    public function inventoryReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $offset = $request->rowOffset;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $inventory = ProductVariant::inventoryReports($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $offset, $requestType);
        $inventories = $inventory['data'];
        foreach ($inventories as $item) {

            if ($item->variantTitle == 'default_variant') {
                $item->variantTitle = '';
            }
        }
        if (empty($requestType)) {
            $inventories = $inventory['data'];
        } else {
            $inventories = $inventory;
        }

        if (empty($requestType)) {
            $inventoryItems = $this->calculateInventory($inventories);

            $arrayCount = $inventoryItems['count'];
            $totalCount = count($inventory['allData']);
            $inventories[$arrayCount] = [
                'id' => Lang::get('lang.total'),
                'purchase_price' => $inventoryItems['netPurchasePrice'],
                'selling_price' => $inventoryItems['netSellingPrice'],
                'inventory' => $inventoryItems['netInventory'],
            ];

            $grandCalculation = $this->calculateInventory($inventory['allData']);

            $inventories[$arrayCount + 1] = [
                'id' => Lang::get('lang.grand_total'),
                'purchase_price' => $grandCalculation['netPurchasePrice'],
                'selling_price' => $grandCalculation['netSellingPrice'],
                'inventory' => $grandCalculation['netInventory'],
            ];
            return ['datarows' => $inventories, 'count' => $totalCount];
        } else {
            $this->calculateInventory($inventories);
            return ['datarows' => $inventories];
        }

    }

    public function calculateInventory($inventories){
        $netPurchasePrice = 0;
        $netSellingPrice = 0;
        $arrayCount = 0;
        $netInventory = 0;

        foreach ($inventories as $rowData) {
            if ($rowData->type == 'customer') {
                $rowData->type = Lang::get('lang.customer');
            } else {
                $rowData->type = Lang::get('lang.internal_sales');
                $rowData->customer = $rowData->transfer_branch_name;
            }
            if ($rowData->customer == '') $rowData->customer = Lang::get('lang.walk_in_customer');
            $netPurchasePrice += $rowData->purchase_price;
            $netSellingPrice += $rowData->selling_price;
            $netInventory += $rowData->inventory;
            $arrayCount++;
        }

        return [
            'netPurchasePrice' => $netPurchasePrice,
            'netSellingPrice' => $netSellingPrice,
            'netInventory' => $netInventory,
            'count' => $arrayCount,
        ];
    }

    public function inventoryReportsFilter()
    {
        $branchName = Branch::index(['name as text', 'id as value']);
        $brandName = ProductBrand::index(['name as text', 'id as value']);
        $categoryName = ProductCategory::index(['name as text', 'id as value']);
        $groupName = ProductGroup::index(['name as text', 'id as value']);

        return ['branchName' => $branchName, 'brandName' => $brandName, 'categoryName' => $categoryName, 'groupName' => $groupName];
    }

    public function paymentReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;

        $payments = Payments::paymentReportList($filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        foreach ($payments['data'] as $payment) {

            if ($payment->paid_by == null) {
                $payment->paid_by = Lang::get('lang.walk_in_customer');
            }

            if ($payment->order_type == 'sales') {
                $payment->route = 'customer';
            } else {
                $payment->route = 'user';
            }
        }

        if (empty($requestType)) {
            $totalCount = $payments['count'];
            $paymentData = $payments['data'];
            $totalCalculation = $this->calculatePayment($paymentData);
            $rowCount = $totalCalculation['count'];

            $paymentData[$rowCount] = ["invoice_id" => Lang::get('lang.total'), "paid" => $totalCalculation['totalPaid'], 'change' => $totalCalculation['totalChange']];

            $grandCalculation = $this->calculatePayment($payments['allData']);

            $paymentData[$rowCount + 1] = ["invoice_id" => Lang::get('lang.grand_total'), "paid" => $grandCalculation['totalPaid'], 'change' => $grandCalculation['totalChange']];

            return ['datarows' => $paymentData, 'count' => $totalCount];
        } else {
            return ['datarows' => $payments['data']];
        }
    }

    public function calculatePayment($paymentData)
    {
        $totalPaid = 0;
        $totalChange = 0;
        $rowCount = 0;

        foreach ($paymentData as $rowPayment) {
            $totalPaid += $rowPayment->paid;
            $totalChange += $rowPayment->change;
            $rowCount++;
        }

        return ['totalPaid' => $totalPaid, 'totalChange' => $totalChange, 'count' => $rowCount];
    }

    public function paymentSummary(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $total = 0;
        $requestType = $request->reqType;

        $paymentSummary = Payments::paymentSummary($filtersData, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        if (empty($requestType)) {
            $totalCount = $paymentSummary['count'];
            $payment = $paymentSummary['data'];
            $dateFormat = false;

            foreach ($filtersData as $singleFilter) {
                if (array_key_exists('key', $singleFilter) && $singleFilter['value'] == "date") {
                    $dateFormat = true;
                }
            }

            if (empty($filtersData) || $dateFormat) {

                foreach ($payment as $SinglePayment) {
                    $allSettingFormat = new AllSettingFormat;
                    $SinglePayment->filter_key = $allSettingFormat->getDate($SinglePayment->filter_key);
                }
            } else {
                foreach ($payment as $SinglePayment) {
                    if ($SinglePayment->filter_key == null) $SinglePayment->filter_key = Lang::get('lang.walk_in_customer');
                }
            }
            $calculateTotal = $this->calculatePaymentSummary($payment);
            $rowCount = $calculateTotal['count'];
            $payment[$rowCount] = ['filter_key' => Lang::get('lang.total'), 'total' => $calculateTotal['total']];

            $calculateGrandTotal = $this->calculatePaymentSummary($paymentSummary['allData']);

            $payment[$rowCount + 1] = ['filter_key' => Lang::get('lang.grand_total'), 'total' => $calculateGrandTotal['total']];

            return ['datarows' => $payment, 'count' => $totalCount];
        } else {
            $this->calculatePaymentSummary($paymentSummary);
            return ['datarows' => $paymentSummary];
        }
    }

    public function calculatePaymentSummary($paymentSummary)
    {
        $total = 0;
        $count = 0;

        foreach ($paymentSummary as $value) {

            $typeArray = explode(',', $value->type);
            for ($i = 0; $i < count($typeArray); $i++) {

                if ($typeArray[$i] == 'sales') {
                    $typeArray[$i] = Lang::get('lang.sales');
                } else {
                    $typeArray[$i] = Lang::get('lang.receiving');
                }
            }

            $value->type = implode(',', $typeArray);

            $total = $total + $value->total;
            $count++;
        }

        return ['total' => $total, 'count' => $count];
    }

    public function paymentReportFilter()
    {
        $cashRegister = CashRegister::index(['title as text', 'id as value']);
        $paymentMethod = PaymentType::index(['name as text', 'id as value']);

        return ['cashRegister' => $cashRegister, 'paymentMethod' => $paymentMethod];
    }

    public function paymentSummaryReportFilter()
    {
        $paymentMethod = PaymentType::index(['name as text', 'id as value']);

        return ['paymentMethod' => $paymentMethod];
    }

    public function customerPurchaseReport(Request $request, $id)
    {

        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;

        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $customerDetails = Order::customerDetails($id, $filtersData, $searchValue, $columnName, $request->columnSortedBy, $limit, $request->rowOffset, $requestType);

        $customerPurchase = $customerDetails['data'];

        $total = $this->calculateCustomerPurchase($customerPurchase);

        if (empty($requestType)) {
            $count = $total['count'];
            $customerPurchase[$count] = [
                'id' => Lang::get('lang.total'),
                'item_purchased' => $total['item_purchased'],
                'sub_total' => $total['subTotal'],
                'tax' => $total['tax'],
                'discount' => $total['discount'],
                'total' => $total['total'],
                'due_amount' => $total['due_amount']
            ];

            $grandTotal = $this->calculateCustomerPurchase($customerDetails['allData']);
            $customerPurchase[$count + 1] = [
                'id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandTotal['item_purchased'],
                'sub_total' => $grandTotal['subTotal'],
                'tax' => $grandTotal['tax'],
                'discount' => $grandTotal['discount'],
                'total' => $grandTotal['total'],
                'due_amount' => $grandTotal['due_amount']
            ];
        }

        return ['datarows' => $customerPurchase, 'count' => $customerDetails['count']];
    }

    public function calculateCustomerPurchase($purchaseRecord)
    {
        $netTotal = 0;
        $netSubTotal = 0;
        $netTax = 0;
        $netDue = 0;
        $discount = 0;
        $count = 0;
        $item = 0;

        foreach ($purchaseRecord as $rowData) {
            $netTax += $rowData->tax;
            $netSubTotal += $rowData->sub_total;
            $netTotal += $rowData->total;
            $discount += $rowData->discount;
            $item += $rowData->item_purchased;
            $netDue += $rowData->due_amount;
            $count++;
        }
        return ['item_purchased' => $item, 'subTotal' => $netSubTotal, 'tax' => $netTax, 'discount' => $discount, 'total' => $netTotal, 'count' => $count, 'due_amount' => $netDue];
    }

    public function getOrdersDetails(Request $request, $orderId)
    {
        $branchId = Order::select('branch_id', 'invoice_id')->find($orderId);
        $cashRegisterId = Branch::getCashRegisterID($branchId->branch_id);
        $transferBranchName = '';

        $orderDetails = Order::orderDetails($orderId, $cashRegisterId);

        if ($orderDetails->transfer_branch_id != null) {
            $transferBranchName= Branch::getOne($orderDetails->transfer_branch_id)->name;
        }
        $invoiceTemplate = new InvoiceTemplateController();
        $templateData = $invoiceTemplate->getInvoiceTemplateToPrint($orderId, $orderDetails->sales_or_receiving_type, $transferBranchName, $cashRegisterId, $orderDetails->order_type, 'receipt');

        return [
            'templateData' => $templateData,
            'invoiceId' => $branchId->invoice_id
        ];
    }

    public function yearlySalesChart(Request $request)
    {
        $salesFilterData = $request->filterData;
        $year = date("Y");
        $currentMonth = date("m");

        if (!isset($salesFilterData['duration'])) {
            $salesFilterData['duration'] = 'this_year';
        }

        $duration = $salesFilterData['duration'];

        $salesChart = Payments::yearFilter($salesFilterData, $year, $currentMonth);

        if ($duration == 'last_month' || $duration == 'this_month') {

            if ($duration == 'this_month') {
                $days = date("t");
            } else {
                $days = date("t", mktime(0, 0, 0, date("n") - 1));
            }

            return ['salesChartData' => $salesChart, "salesFilterData" => $salesFilterData, "days" => $days];
        } else {
            $salesChartData = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

            foreach ($salesChart as $data) {
                $salesChartData[$data->month - 1] = $data->sales;
            }
        }

        return ['salesChartData' => $salesChartData, "salesFilterData" => $salesFilterData];
    }

    public function availableStockChart()
    {
        return Product::availableStock();
    }

    public function getBranchAndUser()
    {
        $user = CustomUser::getAll('*', 'user_type', 'staff');
        $branch = Branch::allData();

        return ['user' => $user, 'branch' => $branch];
    }

    public function taxReports(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue =  searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;

        $tax = Order::taxReports($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) {

            $taxData = $tax['data'];
            $totalCount = $tax['count'];

            $taxItems = $this->calculateTax($taxData);
            $arrayCount = $taxItems['count'];

            $taxData[$arrayCount] = ['invoice_id' => Lang::get('lang.total'), 'total' => $taxItems['netTotal'], 'tax' => $taxItems['netTax']];

            $grandCalculation = $this->calculateTax($tax['allData']);

            $taxData[$arrayCount + 1] = ['invoice_id' => Lang::get('lang.grand_total'), 'total' => $grandCalculation['netTotal'], 'tax' => $grandCalculation['netTax']];

            return ['datarows' => $taxData, 'count' => $totalCount];
        } else {
            $this->calculateTax($tax);
            return ['datarows' => $tax];
        }
    }

    public function calculateTax($data)
    {
        $netTotal = 0;
        $netTax = 0;
        $arrayCount = 0;
        foreach ($data as $rowData) {
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $arrayCount++;

            if ($rowData->order_type == 'sales') {
                $rowData->order_type = Lang::get('lang.sales');
            } else {
                $rowData->order_type = Lang::get('lang.receiving');
            }
        }

        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'count' => $arrayCount];
    }

    public function profitLossReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $profitFilter = null;
        $profit = Order::getProfit($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);
        if (empty($requestType)) {
            $profitData = $profit['data'];
            $profitFilter = $profit['filter'];
        } else {
            $profitData = $profit;
        }
        if (empty($requestType)) {
            $totalCount = $profit['count'];

            $totalProfit = $this->calculateProfit($profitData, $profitFilter);
            $profitData = $totalProfit['data'];
            $rowCount = $totalProfit['count'];
            $profitData[$rowCount] = [$profitFilter => Lang::get('lang.total'), "grand_total" => $totalProfit['netTotal'], 'item_tax' => $totalProfit['netTax'], 'profit_amount' => $totalProfit['netProfit']];

            $grandProfit = $this->calculateProfit($profit['allData'], null);
            $profitData[$rowCount + 1] = [$profitFilter => Lang::get('lang.grand_total'), "grand_total" => $grandProfit['netTotal'], 'item_tax' => $grandProfit['netTax'], 'profit_amount' => $grandProfit['netProfit']];
            return ['datarows' => $profitData, 'count' => $totalCount];
        } else {
            return ['datarows' => $profitData];
        }
    }

    public function calculateProfit($data, $filter)
    {
        $netTotal = 0;
        $netTax = 0;
        $netProfit = 0;
        $arrayCount = 0;
        foreach ($data as $rowData) {
            if ($filter != null) {
                if ($filter == 'customer' && $rowData->customer == null) $rowData->customer = Lang::get('lang.walk_in_customer');
                else if ($filter == 'month') $rowData->month = $this->getMonthName($rowData->month);
            }
            $netTax += $rowData->item_tax;
            $netTotal += $rowData->grand_total;
            $netProfit += $rowData->profit_amount;
            $arrayCount++;
        }
        return ['netTotal' => $netTotal, 'netTax' => $netTax, 'netProfit' => $netProfit, 'count' => $arrayCount, 'data' => $data];
    }

    function getMonthName($monthNumber)
    {
        return date("F", mktime(0, 0, 0, $monthNumber, 1));
    }

    public function getSalesReportFilterData()
    {
        $brands = ProductBrand::index(['name as text', 'id as value']);
        $categories = ProductCategory::index(['name as text', 'id as value']);
        $groups = ProductGroup::index(['name as text', 'id as value']);
        $customers = Customer::query()->select([DB::raw('concat(first_name," ",last_name) as text'), 'id as value'])->get();
        $employee = CustomUser::getAll([DB::raw('concat(first_name," ",last_name) as text'), 'id as value'], 'user_type', 'staff');

        return ['brands' => $brands, 'categories' => $categories, 'groups' => $groups, 'customers' => $customers, 'employee' => $employee];
    }

    public function purchaseReportFilter()
    {

        $suppliers = Supplier::index([DB::raw("CONCAT(suppliers.first_name,' ',suppliers.last_name)  AS text"), 'id as value']);

        return ['suppliers' => $suppliers];
    }

    /* Sales Details Filter Start */
    public function getSalesDetailsFilterData(){
        $brands = ProductBrand::index(['name as text', 'id as value']);
        $categories = ProductCategory::index(['name as text', 'id as value']);
        $groups = ProductGroup::index(['name as text', 'id as value']);
        $allInvoiceId = Order::getAllInvoiceId();

        return ['brands' => $brands, 'categories' => $categories, 'groups' => $groups, 'allInvoiceId'=>$allInvoiceId];
    }

    public function getCustomerDueFilterData()
    {
        $customers = Customer::query()->select([DB::raw('concat(first_name," ",last_name) as text'), 'id as value'])->get();
        return ['customers' => $customers];
    }

    public function getCustomerReportFilterData()
    {
        $customerGroup = CustomerGroup::index(['title as text', 'id as value']);
        $branchList = Branch::index(['name as text', 'id as value']);

        return ['customerGroup' => $customerGroup, 'branchList' => $branchList];
    }

    public function calculateCustomerSummary($salesData)
    {
        $netTotalPayment = 0;
        $netTotalReturn = 0;
        $netTotalSales = 0;
        $netDueAmount = 0;
        $arrayCount = 0;
        foreach ($salesData as $rowData) {
            if ($rowData->name == null) $rowData->name = Lang::get('lang.walk_in_customer');
            $netTotalPayment += $rowData->total_payment;
            $netTotalReturn += $rowData->total_return;
            $netTotalSales += $rowData->total_sales;
            $netDueAmount += $rowData->due;
            $arrayCount++;
        }
        return [
            'netTotalPayment' => $netTotalPayment,
            'netTotalReturn' => $netTotalReturn,
            'netTotalSales' => $netTotalSales,
            'netDueAmount' => $netDueAmount,
            'count' => $arrayCount
        ];
    }

    public function customerSummaryReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $requestType = $request->requestType;
        $searchValue = searchHelper::inputSearch($request->searchValue);

        $customerReport = Order::customerReport($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);
        $customerData = [];
        if (empty($requestType)) {
            $customerData = $customerReport['data'];
        } else {
            $customerData = $customerReport;
        }

        if (empty($requestType)) {
            $customerDataSum = $this->calculateCustomerSummary($customerData);

            $arrayCount = $customerDataSum['count'];
            $totalCount = count($customerReport['allData']);
            $customerData[$arrayCount] = [
                'name' => Lang::get('lang.total'),
                'total_payment' => $customerDataSum['netTotalPayment'],
                'total_return' => $customerDataSum['netTotalReturn'],
                'total_sales' => $customerDataSum['netTotalSales'],
                'due' => $customerDataSum['netDueAmount'],
            ];

            $grandCalculation = $this->calculateCustomerSummary($customerReport['allData']);

            $customerData[$arrayCount + 1] = [
                'name' => Lang::get('lang.grand_total'),
                'total_payment' => $grandCalculation['netTotalPayment'],
                'total_return' => $grandCalculation['netTotalReturn'],
                'total_sales' => $grandCalculation['netTotalSales'],
                'due' => $grandCalculation['netDueAmount'],
            ];

            return ['datarows' => $customerData, 'count' => $totalCount];
        } else {
            $this->calculateCustomerSummary($customerData);

            return ['datarows' => $customerData];
        }
    }

    public function supplierSummaryReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $requestType = $request->requestType;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $supplierReport = Order::supplierReport($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);
        $supplierData = [];

        if (empty($requestType)) $supplierData = $supplierReport['data'];
        else $supplierData = $supplierReport;

        if (empty($requestType)) {
            $supplierDataSum = $this->calculateSupplierSummary($supplierData);
            $arrayCount = $supplierDataSum['count'];
            $totalCount = count($supplierReport['allData']);
            $supplierData[$arrayCount] = [
                'name' => Lang::get('lang.total'),
                'total_payment' => $supplierDataSum['netTotalPayment'],
                'total_purchase' => $supplierDataSum['netTotalPurchase'],
                'due' => $supplierDataSum['netDueAmount'],
            ];
            $grandCalculation = $this->calculateSupplierSummary($supplierReport['allData']);

            $supplierData[$arrayCount + 1] = [
                'name' => Lang::get('lang.grand_total'),
                'total_payment' => $grandCalculation['netTotalPayment'],
                'total_purchase' => $grandCalculation['netTotalPurchase'],
                'due' => $grandCalculation['netDueAmount'],
            ];
            return ['datarows' => $supplierData, 'count' => $totalCount];
        } else {
            $this->calculateSupplierSummary($supplierData);
            return ['datarows' => $supplierData];
        }
    }

    public function calculateSupplierSummary($salesData)
    {
        $netTotalPayment = 0;
        $netTotalPurchase = 0;
        $netDueAmount = 0;
        $arrayCount = 0;
        foreach ($salesData as $rowData) {
            if ($rowData->name == null) $rowData->name = Lang::get('lang.walk_in_supplier');
            $netTotalPayment += $rowData->total_payment;
            $netTotalPurchase += $rowData->total_purchase;
            $netDueAmount += $rowData->due;
            $arrayCount++;
        }
        return [
            'netTotalPayment' => $netTotalPayment,
            'netTotalPurchase' => $netTotalPurchase,
            'netDueAmount' => $netDueAmount,
            'count' => $arrayCount
        ];
    }

    public function salesAndPurchaseReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $requestType = $request->requestType;

        $totalSales = Order::totalSalesAmount($filtersData, $request->searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);
        return ['datarows' => $totalSales['data']];
    }

    public function adjustStockReport(Request $request)
    {
        if ($request->rowLimit) $limit = $request->rowLimit;
        if ($request->columnKey) $columnName = $request->columnKey;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $columnSortedBy = $request->columnSortedBy;
        $rowOffset = $request->rowOffset;

        $adjustmentItems = OrderItems::adjustmentItems($filtersData, $searchValue, $columnSortedBy, $limit, $rowOffset, $columnName, $requestType);

        if (empty($requestType)) {
            $totalCount = count($adjustmentItems['allData']);
            $adjustmentItemData = $this->calculateAdjustStock($adjustmentItems['data']);
            return ['datarows' => $adjustmentItemData['data'], 'count' => $totalCount];
        } else {
            $adjustmentItemData = $this->calculateAdjustStock($adjustmentItems);
            return ['datarows' => $adjustmentItemData['data']];
        }
    }

    public function calculateAdjustStock($data)
    {
        $arrayCount = 0;
        foreach ($data as $rowData) {
            if ($rowData->variant_title == 'default_variant') $rowData->variant_title = Lang::get('lang.default_variant');
            $arrayCount++;
        }
        return [
            'count' => $arrayCount,
            'data' => $data
        ];
    }

    public function getAdjustmentReportFilterData()
    {
        $branches = Branch::index(['name as text', 'id as value']);
        $products = Product::index(['title as text', 'id as value']);
        $adjustmentTypes = AdjustProductStockType::index(['title as text', 'id as value']);
        return ['branches' => $branches, 'products' => $products, 'adjustmentTypes' => $adjustmentTypes];
    }

    public function shipmentReport(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue =  searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;

        $shipments = ShippingInformation::shipmentReports($filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        foreach ($shipments['data'] as $shipment) {
            if ($shipment->status == 'pending') {
                $shipment->status = Lang::get('lang.pending');
            } else if ($shipment->status == 'packet'){
                $shipment->status = Lang::get('lang.packet');
            } else if ($shipment->status == 'on the way'){
                $shipment->status = Lang::get('lang.on_the_way');
            } else if ($shipment->status == 'delivered'){
                $shipment->status = Lang::get('lang.delivered');
            } else {
                $shipment->status = Lang::get('lang.cancelled');
            }
        }

        return ['datarows' => $shipments['data'], 'count' => $shipments['count']];
    }
}
