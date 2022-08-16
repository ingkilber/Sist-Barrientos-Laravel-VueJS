@inject('permission', 'App\Http\Controllers\API\PermissionController')
@inject('appLogo', 'App\Http\Controllers\API\SettingController')

<?php
$data = "$_SERVER[REQUEST_URI]";
?>

<side-bar route={{$data}}
        sales={{$permission->salesManagePermission()}}
        receives={{$permission->receivesManagePermission()}}
        products={{$permission->productManagePermission()}}
        product_category={{$permission->productCategoryManagePermission()}}
        product_brand={{$permission->productBrandManagePermission()}}
        product_group={{$permission->productGroupManagePermission()}}
        units={{$permission->productUnitManagePermission()}}
        variant_attributes={{$permission->productVariantManagePermission()}}
        sales_report={{$permission->salesReportPermission()}}
        sales_details_report={{$permission->salesDetailsReportPermission()}}
        sales_summary_reports={{$permission->salesSummaryReportPermission()}}
        receiving_report={{$permission->receivingReportPermission()}}
        receiving_summary={{$permission->receivingSummaryReportPermission()}}
        register_report={{$permission->registerReportPermission()}}
        payment_report={{$permission->paymentReportPermission()}}
        payment_summary_report={{$permission->paymentSummaryReportPermission()}}
        yearly_sales_chart={{$permission->yearlySalesChartReportPermission()}}
        available_stock_chart={{$permission->availableStockReportPermission()}}
        available_tax_report={{$permission->availableTaxReportPermission()}}
        profit_loss_report={{$permission->profitLossReportPermission()}}
        customers_summary_report={{$permission->customerSummaryReportPermission()}}
        suppliers_summary_report={{$permission->supplierSummaryReportPermission()}}
        sales_and_purchase_report={{$permission->salesAndPurchaseReportPermission()}}
        adjust_stock_report={{$permission->adjustStockPermission()}}
        inventory_report={{$permission->inventoryReportPermission()}}

        customers={{$permission->customersManagePermission()}}
        customer_group={{$permission->customerGroupManagePermission()}}
        suppliers={{$permission->suppliersManagePermission()}}

        applogo={{ $appLogo->getAppLogo() }}
        emailsettings={{ $permission->emailsManagePermission() }}

        appsettings={{ $permission->appsManagePermission() }}
        emailtemplate={{ $permission->emailTemplateManagePermission () }}
        sms_settings={{$permission->smsSettingPermission()}}
        tax_settings={{$permission->taxSettingManagePermission()}}
        payment_settings={{$permission->paymentManagePermission()}}
        sales_channels={{$permission->salesChannelManagePermission()}}
        branches_setting={{$permission->branchManagePermission()}}
        shipping_area_setting={{$permission->shippingAreaPermission()}}
        invoice_settings={{$permission->InvoiceSettingsPermission()}}
        purchase_invoice_settings={{$permission->purchaseInvoiceSettingPermission()}}
        users={{$permission->userManagePermission()}}
        roles={{$permission->rolesManagePermission()}}
        cash_register={{$permission->cashRegistersManagePermission()}}

        table_setting={{$permission->tablesSettingsPermission()}}
        updates_setting={{$permission->updateSettingPermission()}}
        adjust_stock_settings={{$permission->adjustStockSettingsPermission()}}
        notification_settings={{$permission->notificationSettingsPermission()}}
        corn_settings={{$permission->cornJobSettingsPermission()}}
        product_settings={{$permission->productSettingsPermission()}}>
</side-bar>
