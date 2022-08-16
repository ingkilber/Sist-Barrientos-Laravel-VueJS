@include('layouts.include.head')
<body>

@inject('appConfig', 'App\Http\Controllers\Controller')
@php
    $appDetails = config('gain');
@endphp

<div id="app">

    @if(Auth::user())
        @include('layouts.include.navbar')
        @include('layouts.include.sidebar')
    @endif
    <main id="app">

        @yield('content')

    </main>
</div>
<script>
    window.appConfig = {
        appUrl: "<?= $appConfig->appUrl ?>",
        app_name: "<?= $appConfig->app_name ?>",
        publicPath: "<?= $appConfig->publicPath ?>",
        dateFormat: "<?= Config::get('dateFormat') ?>",
        offline: "<?= Config::get('offline_mode') ?>",
        defaultRowSetting: "<?= Config::get('max_row_per_table') ?>",
        currencySymbol: "<?= Config::get('currency_symbol') ?>",
        currencyFormat: "<?= Config::get('currency_format') ?>",
        thousandSeparator: "<?= Config::get('thousand_separator') ?>",
        decimalSeparator: "<?= Config::get('decimal_separator') ?>",
        numDec: "<?= Config::get('number_of_decimal') ?>",
        timeFormat: "<?= Config::get('time_format') ?>",
        timezone : "<?= Config::get('time_zone') ?>",
        appLogo : "<?= Config::get('app_logo') ?>",
        shortcutStatus: "<?= Config::get('overAllShortcutStatus') ?>",
        lowStockNotifation: "<?= Config::get('low_stock_notification') ?>",
        outOfStockProduct: "<?= Config::get('out_of_stock_products') ?>",
        appVersion: "<?= $appDetails['app_version'] ?>",
        salesListDelete: "<?= Config::get('sales_list_delete_option') ?>",
        salesListEdit: "<?= Config::get('sales_list_edit_option') ?>",
        autoSendSms: "<?= Config::get('sms_recive_to_customer') ?>",
        salesInvoiceLogo: "<?= Config::get('invoiceLogo') ?>",
        purchaseInvoiceLogo: "<?= Config::get('purchase_invoiceLogo') ?>",

        shortcutKeyInfo: {
            loadSalesPage: {
                shortCutKey: "<?= Config::get('loadSalesPage') ?>",
                status: "<?= Config::get('loadSalesPage_status') ?>",
            },
            productSearchShortcut: {
                shortCutKey: "<?= Config::get('productSearch') ?>",
                status: "<?= Config::get('productSearch_status') ?>",
            },
            holdCardShortcut: {
                shortCutKey: "<?= Config::get('holdCard') ?>",
                status: "<?= Config::get('holdCard_status') ?>",
            },
            payShortcut: {
                shortCutKey: "<?= Config::get('pay') ?>",
                status: "<?= Config::get('pay_status') ?>",
            },
            addCustomerShortcut: {
                shortCutKey: "<?= Config::get('addCustomer') ?>",
                status: "<?= Config::get('addCustomer_status') ?>",
            },
            cancelCardShortcut: {
                shortCutKey: "<?= Config::get('cancelCarditem') ?>",
                status: "<?= Config::get('cancelCarditem_status') ?>",
            },
            donePayment: {
                shortCutKey: "<?= Config::get('donePayment1') ?>",
                status: "<?= Config::get('donePayment1_status') ?>",
            }
        }
    }
</script>

@include('layouts.include.footer')