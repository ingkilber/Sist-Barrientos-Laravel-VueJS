<?php

namespace App\Http\Controllers\API;

use App\Models\Payments;
use App\Models\PaymentType;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Lang;

class PaymentController extends Controller
{
    public function index()
    {
        return PaymentType::allData();
    }

    public function getPaymentList(Request $request)
    {

        if ($request->columnKey) $columnName = $request->columnKey;

        if ($request->rowLimit) $limit = $request->rowLimit;

        $payments = PaymentType::getPaymentType($columnName, $request->columnSortedBy, $limit, $request->rowOffset);

        foreach ($payments['data'] as $payment) {

            if (Payments::checkExists('payment_method', $payment->id) || $payment->type == 'cash' || $payment->type == 'card' || $payment->type == 'bank' ) {
                $payment->used = 1;
            }
        }

        return ['datarows' => $payments['data'], 'count' => $payments['count']];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'paymentTypeName' => 'required',
            'pIsDefault' => 'required',
            'round' => 'required',
        ]);

        $is_default = $request->pIsDefault;
        $round = $request->round;

        if ($is_default == 1) {
            PaymentType::updatePaymentType();
        }

        PaymentType::store([
                'name' => $request->paymentTypeName,
                'type' => 'custom',
                'status' => $round,
                'is_default' => $is_default,
                'created_by' => Auth::user()->id
        ]);

        $response = [
            'message' => Lang::get('lang.payment_type') . ' ' . Lang::get('lang.successfully_added')
        ];

        return response()->json($response, 200);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'paymentTypeName' => 'required',
            'pIsDefault' => 'required',
        ]);

        $is_default = $request->pIsDefault;

        if ($is_default == 1) {
            PaymentType::updatePaymentType();
        }

        $paymentType = PaymentType::getOne($id);
        $data = array();
        $data['name'] = $request->paymentTypeName;
        $data['status'] = $request -> round;
        $data['is_default'] = $is_default;
        $data['created_by'] = Auth::user()->id;
        

        if (PaymentType::updateData($id, $data)) {
            $response = [
                'message' => Lang::get('lang.payment_type') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 200);
        }
    }

    public function getData()
    {
        return PaymentType::index(['id', 'name', 'type','status','is_default']);
    }

    public function getPaymentDetailsData($id)
    {
        return PaymentType::getOne($id);
    }

    public function getInvoiceLogo()
    {
        $invoiceLogo = Setting::getSettingValue('invoiceLogo');

        return ['logo' => $invoiceLogo];
    }

    public function getAutoInvoice()
    {
        $autoInvoice = Setting::getSettingValue('auto_generate_invoice');

        return ['autoInvoice' => $autoInvoice];
    }

    public function deletePaymentMethod($id)
    {
        $used = Payments::getTotals($id);

        if ($used == 0) {
            PaymentType::deleteData($id);
            $response = [
                'message' => Lang::get('lang.payment_type') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);

        } else {
            $response = [
                'message' => Lang::get('lang.payment_type') . ' ' . Lang::get('lang.in_use') . ', ' . Lang::get('lang.you_can_not_delete_the') . ' ' . strtolower(Lang::get('lang.payment_type'))
            ];

            return response()->json($response, 200);
        }
    }
}
