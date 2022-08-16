<template>
    <div style="display: none">
        <div id="printMe" v-html="rawHtml"/>
    </div>

</template>

<script>

import axiosGetPost from "../../../helper/axiosGetPostCommon";
import AppFunction from "../../../js/AppFuntion";

export default {
    extends: axiosGetPost,

    props: [
        "add_shipping_info",
        "balance",
        "exchange_value",
        "final_cart",
        "invoiceId",
        "invoice_template",
        "logo",
        "order_type",
        "payment_list_data",
        "payments",
        "print_invoice_before_done_payment",
        "sales_note",
        "sales_or_receiving_type",
        "shipping_address",
        "shipping_area",
        "sold_by",
        "sold_to",
        "transfer_branch_name",
        "user",
        "is_template_default",
        "invoice_size"
    ],

    data() {
        return {
            rawHtml: '',
            shipmentAmount: '',
            printOptions: {
                printable: 'printMe',
                type: 'html',
                maxWidth: '',
                scanStyles: false,
                css: [
                    AppFunction.getAppUrl('css/app.css')
                ],
            },
        };
    },

    watch: {
        print_invoice_before_done_payment: function (newVal) {
            if (newVal) {
                this.printReceiptBeforeDonePayment();
            }
        },
        final_cart: function (newVal) {
            if (newVal) {
                this.generate();
            }
        },
        shipping_address: 'generate',
        exchange_value: 'generate',
        sales_note: 'generate',
        payment_list_data: 'generate',
    },
    created() {
        this.generate();
    },
    methods: {

        printReceiptBeforeDonePayment() {
            this.$emit("resetGetInvoiceBeforeDonePayment", false);
            this.generate();
            this.print()
        },
        print() {
            //ref: https://printjs.crabbly.com/#documentation
            printJS(this.printOptions)
        },
        generate() {

            let orderProducts = '', paymentDetails = '';

            orderProducts = this.invoice_size === 'small' ? this.orderProductForSmallInvoice() : this.orderProductForLargeInvoice();
            this.rawHtml = this.invoice_template.replace('{item_details}', orderProducts)

            paymentDetails = this.generatePaymentInInvoice();
            this.rawHtml = this.rawHtml.replace('{payment_details}', paymentDetails);

            this.replaceShippingAddress();

            this.invoiceIndividuals();
        },

        replaceShippingAddress() {
            this.rawHtml = this.rawHtml.replace('{shipment_address}', '');
        },
        invoiceIndividuals() {

            let customer = this.getCustomerOrSupplierInfo();

            let obj = {
                '{app_logo}': this.getLogo(),
                '{app_name}': this.app_name,
                '{date}': this.dateFormats(this.date),
                '{time}': this.dateFormatsWithTime(this.date),
                '{invoice_id}': "",
                '{employee_name}': this.user.first_name + " " + this.user.last_name,
                '{customer_name}': customer.customerName,
                '{supplier_name}': customer.customerName,
                '{phone_number}': customer.customerPhone ? customer.customerPhone : '',
                '{address}': customer.customerAddress ? customer.customerAddress : '',
                '{tin}': customer.tinNumber ? customer.tinNumber : '',
                '{note}': this.sales_note ? this.sales_note : "",
                '{table_name}': "",
                '{sub_total}': this.numberFormat(this.final_cart.subTotal),
                '{tax}': this.numberFormat(this.final_cart.tax),
                '{total}': this.numberFormat(this.final_cart.grandTotal),
                '{discount}': this.numberFormat(this.final_cart.discount),
                '{exchange}': this.payment_list_data > 0 ? this.numberFormat(this.exchange_value) : this.numberFormat(0),
                '{barcode}': "",
                '{shipment_amount}': this.getShipmentAmount(),
            };

            for (let [key, value] of Object.entries(obj)) {
                this.rawHtml = this.rawHtml.replace(key, value);
            }
        },
        getShipmentAmount() {
            return this.add_shipping_info ? this.numberFormat(this.shipmentAmount) + '</td></tr>' : 'N/A';
        },
        getCustomerOrSupplierInfo() {
            let info = {};
            if (this.order_type === "sales") {
                if (this.sales_or_receiving_type === "customer") {
                    if (this.final_cart.customer.length === 0) {
                        info.customerName = this.trans("lang.walk_in_customer");
                    } else {
                        info.customerName = `${this.final_cart.customer.first_name} ${this.final_cart.customer.last_name}`;
                        info.customerPhone = this.final_cart.customer.phone_number ? this.final_cart.customer.phone_number : '';
                        info.customerAddress = this.final_cart.customer.address ? this.final_cart.customer.address : '';
                        info.tinNumber = this.final_cart.customer.tin_number ? this.final_cart.customer.tin_number : '';
                    }
                } else {
                    info.customerName = this.final_cart.transferBranchName;
                }
            } else {
                if (this.sales_or_receiving_type === "supplier") {
                    info.customerName = this.final_cart.customer.length === 0
                        ? this.trans("lang.walk_in_supplier")
                        : `${this.final_cart.customer.first_name} ${this.final_cart.customer.last_name}`;
                    info.tinNumber = this.final_cart.customer.tin_number ? this.final_cart.customer.tin_number : '';
                } else {
                    info.customerName = this.final_cart.transferBranchName;
                }
            }
            return info;
        },
        getLogo() {

            let logo = this.order_type === 'sales' ?  this.salesInvoiceLogo : this.purchaseInvoiceLogo;

            return this.rawHtml = this.rawHtml.replace('{logo_source}', this.publicPath + "/uploads/logo/" + logo);
        },

        pad(d) {
            return (d < 10) ? '0' + d.toString() : d.toString();
        },
        orderProductForSmallInvoice() {

            let orderProducts = '', orderProduct = {};

            this.final_cart.cart.forEach((element, i) => {
                let tempDiscount = typeof element.discount === 'number' ? this.decimalFormat(element.discount) : this.numberFormat(this.parseToFloat(0));

                orderProduct.productName = this.generateProductTitle(element, i);

                if (element.orderType !== 'shipment') {
                    orderProduct.price = '<td class="w-25">' + this.numberFormat(element.price) + '</td>';
                    orderProduct.qty = '<td class="text-right">' + parseInt(this.numberFormatting(element.quantity)) + '</td>';
                    orderProduct.discount = '<td class="text-right">' + tempDiscount + '</td>';
                    orderProduct.total = '<td class="text-right">' + this.numberFormat(element.calculatedPrice) + '</td>';

                    orderProducts = orderProducts + '<tr>' + orderProduct.productName + '</tr>' + '<tr>' + orderProduct.price + orderProduct.qty + orderProduct.discount + orderProduct.total + '</tr>';

                } else {
                    this.shipmentAmount = this.add_shipping_info ? this.numberFormat(element.calculatedPrice) : 'N/A'

                }
            });

            return orderProducts;
        },
        orderProductForLargeInvoice() {

            let orderProducts = '', orderProduct = {};

            this.final_cart.cart.forEach((element, i) => {
                let tempDiscount = typeof element.discount === 'number' ? this.decimalFormat(element.discount) : this.numberFormat(this.parseToFloat(0));

                orderProduct.productName = this.generateProductTitle(element, i);

                if (element.orderType !== 'shipment') {
                    orderProduct.price = '<td class="w-25">' + this.numberFormat(element.price) + '</td>';
                    orderProduct.qty = '<td class="text-right">' + parseInt(this.numberFormatting(element.quantity)) + '</td>';
                    orderProduct.discount = '<td class="text-right">' + tempDiscount + '</td>';
                    orderProduct.total = '<td class="text-right">' + this.numberFormat(element.calculatedPrice) + '</td>';

                    orderProducts = orderProducts + '<tr>' + orderProduct.productName + orderProduct.qty + orderProduct.price + orderProduct.discount + orderProduct.total + '</tr>';

                } else {
                    this.shipmentAmount = this.add_shipping_info ? this.numberFormat(element.calculatedPrice) : 'N/A'
                }
            });

            return orderProducts;
        },
        generateProductTitle(element, i) {
            let serial = '', customTdTag = '';

            if (element.orderType !== 'shipment') {
                serial = this.pad(parseInt(i) + 1) + '. ';
            }

            customTdTag = this.invoice_size === 'small' ? '<td colspan="4">' : '<td>';

            return element.variantTitle !== 'default_variant' && element.orderType !== 'shipment'
                ? customTdTag + serial + element.productTitle + ' ' + '(' + element.variantTitle + ')' + '</td>'
                : customTdTag + serial + element.productTitle + '</td>';

        },
        generatePaymentInInvoice() {

            let outputs = '', output = {};

            if (this.payment_list_data){
                if (this.payment_list_data.length > 0) {
                    for (let item of this.payment_list_data) {
                        if (item.is_active) {

                            output.paymentName = '<th>' + item.productTitle + '</th>';

                            output.paid = '<td class="font-weight-bold" colspan="4">' + this.numberFormat(item.paid) + '</td>';

                            outputs = outputs + '<tr>' + output.paymentName + output.paid + '</tr>';
                        }
                    }
                }
            }
            return outputs;

        },
    }
};
</script>