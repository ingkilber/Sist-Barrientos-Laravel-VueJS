<?php

namespace App\Http\Controllers\API;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\ShippingInformation;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\AdjustProductStockType;

class BranchController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();

        if ($authUser->is_admin == 1) {
            $data = Branch::index('*');
        } else {
            $branchId = $authUser->branch_id;
            $data = Branch::getBranch($branchId);
        }

        return $data;
    }

    public function getAllBranches()
    {
        return Branch::allData();
    }

    public function getRowBranch($id)
    {
        return Branch::getOne($id);
    }

    public function getBranchList(Request $request)
    {
        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $branch = Branch::getBranchList($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        foreach ($branch['data'] as $rowBranch) {

            $isUsedInOrderBranch = Order::where('branch_id', $rowBranch->id)->exists();
            $isUsedInOrderTransferBranch = Order::where('transfer_branch_id', $rowBranch->id)->exists();
            $isUsedInProduct = Product::where('branch_id', $rowBranch->id)->exists();
            $isUsedInRestaurantTables = RestaurantTable::where('branch_id', $rowBranch->id)->exists();
            $isUsedInShipping = ShippingInformation::where('branch_id', $rowBranch->id)->exists();

            if ($isUsedInOrderBranch || $isUsedInOrderTransferBranch || $isUsedInProduct || $isUsedInRestaurantTables || $isUsedInShipping) {
                $rowBranch->used = true;
            } else {
                $rowBranch->used = false;
            }
        }

        return ['datarows' => $branch['data'], 'count' => $branch['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'branchType' => 'required',
            'tax_id' => 'required',
            'isCashRegisterUser' => 'required',
            'isEnableShipment' => 'required',
        ]);

        $tax_id = $request->tax_id;
        $data = array();
        $data['name'] = $request->name;
        $data['branch_type'] = $request->branchType;
        $data['is_cash_register'] = $request->isCashRegisterUser;
        $data['is_shipment'] = $request->isEnableShipment;
        $data['user_id'] = $request->user_id;
        $data['created_by'] = Auth::user()->id;

        if ($tax_id == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($tax_id == 'default-tax') {
            $data['taxable'] = 1;
            $data['is_default'] = 1;
            $taxRowCount = Tax::getTotals();

            if ($taxRowCount == 0) {
                $response = [
                    'message' => Lang::get('lang.no_tax_added_default')
                ];

                return response()->json($response, 404);
            } else {
                $tax = Tax::getId();
                $data['tax_id'] = $tax;
            }
        } else {
            $data['taxable'] = 1;
            $data['is_default'] = 0;
            $data['tax_id'] = $tax_id;
        }

        $branch_id = Branch::getInsertedId($data);
        if ($branch_id) {
            CashRegister::store([
                "title" => 'Main Cash Register',
                "branch_id" => $branch_id,
                "sales_invoice_id" => 2,
                "receiving_invoice_id" => 4,
                "created_by" => Auth::user()->id
            ]);
            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_added')
            ];

            return response()->json($response, 200);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'branchType' => 'required',
            'tax_id' => 'required',
            'isCashRegisterUser' => 'required',
            'isEnableShipment' => 'required',
        ]);

        $tax_id = $request->tax_id;
        $data = array();
        $data['name'] = $request->name;
        $data['branch_type'] = $request->branchType;
        $data['is_cash_register'] = $request->isCashRegisterUser;
        $data['is_shipment'] = $request->isEnableShipment;
        $data['user_id'] = $request->user_id;
        $data['created_by'] = Auth::user()->id;

        if ($tax_id == 'no-tax') {
            $data['taxable'] = 0;
        } elseif ($tax_id == 'default-tax') {
            $data['taxable'] = 1;
            $data['is_default'] = 1;
            $taxRowCount = Tax::getTotals();

            if ($taxRowCount == 0) {
                $response = [
                    'message' => Lang::get('lang.no_tax_added_default')
                ];

                return response()->json($response, 404);
            } else {
                $tax = Tax::getId();
                $data['tax_id'] = $tax;
            }
        } else {
            $data['taxable'] = 1;
            $data['is_default'] = 0;
            $data['tax_id'] = $tax_id;
        }
        Branch::updateData($id, $data);

        $response = [
            'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_updated')
        ];

        return response()->json($response, 200);
    }

    public function deleteBranch($id)
    {
        $isUsedInOrderBranch = Order::where('branch_id', $id)->exists();
        $isUsedInOrderTransferBranch = Order::where('transfer_branch_id', $id)->exists();
        $isUsedInProduct = Product::where('branch_id', $id)->exists();
        $isUsedInRestaurantTables = RestaurantTable::where('branch_id', $id)->exists();
        $isUsedInShipping = ShippingInformation::where('branch_id', $id)->exists();

        $branchCount = Branch::countData();

        if ($branchCount == 1) {
            $response = [
                'message' => Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.branch'))
            ];

            return response()->json($response, 200);

        } elseif ($isUsedInOrderBranch || $isUsedInOrderTransferBranch || $isUsedInProduct || $isUsedInRestaurantTables || $isUsedInShipping) {
            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.branch'))
            ];

            return response()->json($response, 200);

        } else {
            Branch::deleteData($id);
            CashRegister::where('branch_id', $id)->delete();

            $response = [
                'message' => Lang::get('lang.branch') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        }
    }

    public function branchList()
    {
        return Branch::index(['name as text', 'id as value']);
    }

    public function restaurantBranchList()
    {
        return Branch::query()->select(['name as text', 'id as value'])->where('branch_type', 'restaurant')->get();
    }

    public function getBranchAndAdjustType()
    {
        $authUser = Auth::user();
        $branches = [];
        $adjustMentTypes = [];
        if ($authUser->is_admin == 1) {
            $branches = Branch::index('*');
        } else {
            $branchId = $authUser->branch_id;
            $branches = Branch::getBranch($branchId);
        }
        $adjustMentTypes = AdjustProductStockType::index('*');
        return [
            'branches' => $branches,
            'adjustMentTypes' => $adjustMentTypes
        ];
    }
}
