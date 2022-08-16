<?php

namespace App\Http\Controllers\API;

use App\Libraries\AllSettingFormat;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\OrderItems;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\PermissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class CashRegisterController extends Controller
{
    public function index(Request $request)
    {

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $cashRegisters = CashRegister::getRegisters($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        foreach ($cashRegisters['data'] as $item) {
            if (CashRegisterLog::checkExists('cash_register_id', $item->id)) {
                $item->used = 1;
            }
        }

        return ['datarows' => $cashRegisters['data'], 'count' => $cashRegisters['count']];
    }

    public function getCashRegisterList()
    {
        $allSetting = new AllSettingFormat;
        $currentBranch = $allSetting->getCurrentBranch();
        $permission = new PermissionController;
        $closeOtherCashRegisters = $permission->closeOthersCashRegisters();
        $currentUser = Auth::user();

        $data = CashRegister::getList($currentBranch->id);
        foreach ($data as $rowData) {
            $status = CashRegisterLog::getLogs($rowData->id);

            if ($status) {
                $rowData['status'] = $status->status;
                $rowData['opening_amount'] = $status->opening_amount;

                if ($status->status == 'open') {
                    $rowData['register_status'] = Lang::get('lang.opened_by') . ' ' . $status->opened_by;

                } else {
                    $rowData['register_status'] = '';
                }

                $rowData->userID = explode(',', $status->user_id);

            } else {
                $rowData->status = 'closed';
            }

            if (isset($status->open_user_id) && $currentUser->id == $status->open_user_id) {
                $rowData['permision'] = 1;
                $rowData['open_user_id'] = $status->open_user_id;

            } else {
                $rowData['permision'] = $closeOtherCashRegisters;
                $rowData['open_user_id'] = 0;
            }
        }

        return $data;
    }

    public function cashRegisterLogs(Request $request)
    {
        $userID = Auth::user('id')->id;
        $cashRegisterId = $request->id;
        $openingAmount = $request->openingAmount;
        $openingTime = $request->openingTime;
        $status = $request->status;
        $closingAmount = $request->closingAmount;
        $closingTime = $request->closingTime;
        $note = $request->note;

        if ($status == 'open') {
            CashRegisterLog::store([
                'user_id' => $userID,
                'cash_register_id' => $cashRegisterId,
                'opening_amount' => $openingAmount,
                'status' => $status,
                'opening_time' => $openingTime,
                'opened_by' => $userID,
            ]);
        }

        if ($status == 'enroll') {
            CashRegisterLog::updateRegisterLog($cashRegisterId, [
                'user_id' => CashRegisterLog::getEnrollId('user_id', 'cash_register_id', $cashRegisterId, 'open')->user_id . ',' . $userID
            ]);
        }

        if ($status == 'closed') {
            CashRegisterLog::updateRegisterLog($cashRegisterId, [
                'closing_amount' => $closingAmount,
                'closing_time' => $closingTime,
                'closed_by' => $userID,
                'note' => $note,
                'status' => $status,
            ]);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'branch_id' => 'required',
            'sales_invoice_id' => 'required',
            'receiving_invoice_id' => 'required',
            'allowMultiUser' => 'required',
        ]);

        $title = $request->title;
        $branch = $request->branch_id;
        $sales_invoice = $request->sales_invoice_id;
        $receiving_invoice = $request->receiving_invoice_id;
        $allowMultiUser = $request->allowMultiUser;
        if ($allowMultiUser) $allowMultiUser = 1;
        $created_by = Auth::user()->id;

        if (CashRegister::store([
            "title" => $title,
            "branch_id" => $branch,
            "sales_invoice_id" => $sales_invoice,
            "receiving_invoice_id" => $receiving_invoice,
            "multiple_access" => $allowMultiUser,
            "created_by" => $created_by
        ])) {
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.cash_register') . ' ' . Lang::get('lang.successfully_saved')))
            ];
            return response()->json($response, 201);
        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];

            return response()->json($response, 404);
        }
    }

    public function show($id)
    {
        return CashRegister::getOne($id);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'branch_id' => 'required',
            'sales_invoice_id' => 'required',
            'receiving_invoice_id' => 'required',
            'allowMultiUser' => 'required',
        ]);

        $allowMultiUser = $request->allowMultiUser;
        if ($allowMultiUser) $allowMultiUser = 1;
        $cashRegister = CashRegister::getOne($id);

        if ($cashRegister) {
            CashRegister::updateData($id, ['title' => $request->title, 'branch_id' => $request->branch_id, 'sales_invoice_id' => $request->sales_invoice_id, 'receiving_invoice_id' => $request->receiving_invoice_id, 'multiple_access' => $allowMultiUser]);
            $response = [
                'message' => ucfirst(strtolower(Lang::get('lang.cash_register') . ' ' . Lang::get('lang.successfully_updated')))
            ];
            return response()->json($response, 201);

        } else {
            $response = [
                'message' => Lang::get('lang.getting_problems')
            ];
            return response()->json($response, 404);
        }
    }

    public function deleteCashRegister($id)
    {
        $used = CashRegisterLog::getTotals($id);
        if ($used == 0) {
            CashRegister::deleteData($id);
            $response = [

                'message' => ucfirst(strtolower(Lang::get('lang.cash_register') . ' ' . Lang::get('lang.successfully_deleted')))
            ];

            return response()->json($response, 200);
        } else {
            $response = [

                'message' => ucfirst(strtolower(Lang::get('lang.cash_register') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . Lang::get('lang.cash_register')))
            ];

            return response()->json($response, 200);
        }
    }

    public function registerSalesInfo(Request $request, $id)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $offset = $request->rowOffset;
        $registerInfoSale = CashRegister::registerSaleInfo($columnName, $request->columnSortedBy, $limit, $offset, $id);

        foreach ($registerInfoSale['data'] as $datarow) {
            if ($datarow->customer == null) $datarow->customer = Lang::get('lang.walk_in_customer');
        }

        return ['datarows' => $registerInfoSale['data'], 'count' =>$registerInfoSale['count']];
    }

    public function cashRegisterInfo($id)
    {
        return CashRegister::cashRegisterInfo($id);
    }
}
