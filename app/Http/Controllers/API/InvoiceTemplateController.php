<?php

namespace App\Http\Controllers\API;

use App\Http\Traits\InvoiceTemplateTrait;
use App\Models\InvoiceTemplate;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;

class InvoiceTemplateController extends Controller
{
    use InvoiceTemplateTrait;


    public function index(Request $request)
    {
        $data = InvoiceTemplate::getInvoiceTemplate($request);
        $totalCount = InvoiceTemplate::countData();

        return ['datarows' => $data, 'count' => $totalCount];
    }

    public function getAllInvoiceTemplate()
    {
        return ['invoice_template' => InvoiceTemplate::allData(), 'offline_mode' => Setting::getOneSetting('offline_mode')->setting_value];
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required'
        ]);

        if ($request->input('is_default_template') == 1) {
            InvoiceTemplate::updateDefaultInvoiceTemplate($request->input('template_type'));
        }

        InvoiceTemplate::query()->create([
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'is_default_template' => $request->input('is_default_template'),
            'invoice_size' => $request->input('invoice_size'),
            'custom_content' => $request->input('content'),
        ]);
    }

    public function show($id)
    {
        $invoiceTemplate = InvoiceTemplate::getOne($id);

        if ($invoiceTemplate->custom_content != '') {
            return [
                'content' => $invoiceTemplate->custom_content,
                'is_default' => $invoiceTemplate->is_default_template,
                'invoice_size' => $invoiceTemplate->invoice_size
            ];
        }
        else {
            return [
                'content' => $invoiceTemplate->default_content,
                'is_default' =>  $invoiceTemplate->is_default_template,
                'invoice_size' => $invoiceTemplate->invoice_size
            ];
        }
    }

    public function getInvoiceEditData($id)
    {
        $invoiceTemplate = InvoiceTemplate::getOne($id);
        return [
            "template_title" => $invoiceTemplate->template_title,
            "template_type" => $invoiceTemplate->template_type,
            "is_default_template" => $invoiceTemplate->is_default_template,
            "invoice_size" => $invoiceTemplate->invoice_size,
            "content" => $invoiceTemplate->custom_content != '' ? $invoiceTemplate->custom_content : $invoiceTemplate->default_content,
            "isReStoreShow" => $invoiceTemplate->custom_content != '',
        ];
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'title' => 'required',
            'template_type' => 'required',
            'invoice_size' => 'required',
        ]);

        if ($request->input('is_default_template') == 1) {
            InvoiceTemplate::updateDefaultInvoiceTemplate($request->input('template_type'));
        }

        $success = InvoiceTemplate::updateData($id, [
            'template_title' => $request->input('title'),
            'template_type' => $request->input('template_type'),
            'invoice_size' => $request->input('invoice_size'),
            'is_default_template' => $request->input('is_default_template'),
            'custom_content' => $request->input('content')
        ]);

        $msg = Lang::get('lang.invoice_setting_saved_successfully');
        $status = 200;

        if (!$success) {

            $msg = Lang::get('lang.error_during_update');
            $status = 404;
        }

        return response()->json(['message' => $msg], $status);
    }

    public function getInvoiceTemplateToPrint($orderId, $salesOrReceivingType, $transferBranchName, $cashRegisterId, $orderType, $from)
    {
        $invoiceTemplateInfo = $this->getTemplate($cashRegisterId,$orderType);
        $invoiceTemplateSize = $invoiceTemplateInfo['invoice_size'];
        $template = $invoiceTemplateInfo['content'];
        $template = $this->replaceItemDetails($orderId, $template, $invoiceTemplateSize);
        $template = $this->replacePaymentDetails($orderId,$template,$invoiceTemplateSize);
        $template = $this->getInvoiceLogo($from, $template, $orderType);
        $template = $this->replaceSpecificInfo($template, $orderId, $orderType, $cashRegisterId, $salesOrReceivingType, $transferBranchName);

        return (['data' => $template, 'invoice_size' => $invoiceTemplateSize]);
    }
}
