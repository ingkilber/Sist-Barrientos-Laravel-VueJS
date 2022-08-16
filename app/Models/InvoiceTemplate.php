<?php

namespace App\Models;


use App\Http\Controllers\API\InvoiceTemplateController;

class InvoiceTemplate extends BaseModel
{
    protected $fillable = ['template_type', 'is_default_template', 'invoice_size', 'template_title', 'default_content', 'custom_content'];

    public static function getInvoiceTemplate($request)
    {
        return InvoiceTemplate::query()->orderBy($request->columnKey, $request->columnSortedBy)->get();
    }

    public static function getInvoiceTemplateToPrint($cashRegisterId, $orderType)
    {
        $cashRegister = CashRegister::query()->find($cashRegisterId);

        $invoice = $orderType == 'sales'
            ? InvoiceTemplate::query()->find($cashRegister->sales_invoice_id)
            : InvoiceTemplate::query()->find($cashRegister->receiving_invoice_id);

        return $invoice->custom_content != ''
            ? ['content' => $invoice->custom_content, 'invoice_size' => $invoice->invoice_size]
            : ['content' => $invoice->default_content, 'invoice_size' => $invoice->invoice_size];

    }

    public static function getInvoiceTemplateForNoCashReg($orderType)
    {
        $invoice = InvoiceTemplate::query()
            ->select('*')
            ->where('is_default_template', 1)
            ->where('template_type', $orderType)
            ->first();

        return ($invoice->custom_content != '')
            ? ['content' => $invoice->custom_content, 'invoice_size' => $invoice->invoice_size]
            : ['content' => $invoice->default_content, 'invoice_size' => $invoice->invoice_size];
    }

    public static function updateDefaultInvoiceTemplate($type)
    {
        InvoiceTemplate::where('is_default_template', 1)
            ->where('template_type', $type)
            ->update(['is_default_template' => 0]);
    }

    public static function getDefaultTemplate()
    {
        $salesInvoiceTemplateData = InvoiceTemplate::where('is_default_template', 1)->where('template_type', 'sales')->first();
        $receiveInvoiceTemplateData = InvoiceTemplate::where('is_default_template', 1)->where('template_type', 'receiving')->first();

        //Sales
        if ($salesInvoiceTemplateData->custom_content != '') {
            $salesInvoiceTemplateData->invoice_template = $salesInvoiceTemplateData->custom_content;
        } else {
            $salesInvoiceTemplateData->invoice_template = $salesInvoiceTemplateData->default_content;
        }
        //Receives
        if ($receiveInvoiceTemplateData->custom_content != '') {
            $receiveInvoiceTemplateData->invoice_template = $receiveInvoiceTemplateData->custom_content;
        } else {
            $receiveInvoiceTemplateData->invoice_template = $receiveInvoiceTemplateData->default_content;
        }
        return ['sales_invoice' => $salesInvoiceTemplateData, 'receive_invoice' => $receiveInvoiceTemplateData];

    }
}
