<template>
    <div class="modal-layout-content">
        <a href="#" class="position-absolute variant-modal-close-btn p-2 close" data-dismiss="modal" aria-label="Close"
           @click.prevent="closeModal">
            <i class="la la-close text-grey"></i>
        </a>
        <div class="row mx-0 modal-content-row">
            <!--Left payment details-->
            <div class="col-12 col-md-6 cart-border-right text-center">
                <div class="horizontal-scroll">
                    <h5 class="text-center mb-4">{{ trans('lang.' + orderType) + ' ' + trans('lang.details') }}</h5>
                    <div class="invoiceLogo text-center">
                        <img v-if="orderType === 'sales'"
                             :src="publicPath+'/uploads/logo/'+ salesInvoiceLogo"
                             width="100" class="img-fluid" alt=""
                        >

                        <img v-else
                             :src="publicPath+'/uploads/logo/'+ purchaseInvoiceLogo"
                             width="100" class="img-fluid" alt="">

                    </div>
                    <div>
                        <div class="text-center header-line-height">
                            <small class='text-center font-weight-bold'>{{ app_name }}</small>
                            <br>
                            <small class='text-center'>{{ dateFormats(finalCart.date) }}</small>
                            <br>
                            <small class='text-center'>
                                {{ trans('lang.' + orderType) + ' ' + trans('lang.receipt') }}
                            </small>
                            <br>
                            <small class="text-left">
                                {{ trans('lang.' + sold_by) }}: {{ user.first_name + " " + user.last_name }}
                            </small>
                            <br>
                            <small v-if="orderType === 'sales'">
                            <span v-if="salesOrReceivingType === 'customer'">
                                <span v-if="finalCart.customer.length === 0">
                                    {{ trans('lang.' + sold_to) }}: {{ trans('lang.walk_in_customer') }}</span>
                                <span v-else>
                                    {{
                                        trans('lang.' + sold_to)
                                    }}: {{ finalCart.customer.first_name + " " + finalCart.customer.last_name }}
                                </span>
                            </span>
                                <span v-else>
                                {{ trans('lang.' + sold_to) }}: {{ finalCart.transferBranchName }}
                            </span>
                            </small>
                            <small v-else>
                                <span v-if="salesOrReceivingType === 'supplier'">
                                    <span v-if="finalCart.customer.length === 0">
                                        {{ trans('lang.received_from') }}: {{ trans('lang.walk_in_supplier') }}
                                    </span>
                                    <span v-else>
                                        {{
                                            trans('lang.received_from')
                                        }} : {{ finalCart.customer.first_name + " " + finalCart.customer.last_name }}
                                    </span>
                                </span>
                                <span v-else>
                                    {{ trans('lang.received_from') }}: {{ finalCart.transferBranchName }}</span>
                            </small>
                            <small class="text-left invoice-show" style="display: none">
                                {{ trans('lang.invoice_id') }}:{{ invoiceID }}
                            </small>
                        </div>
                        <div class="invoice-table">
                            <table class="table product-card-font" style="font-weight:500">
                                <thead class="border-top-0">
                                <tr>
                                    <th class="cart-summary-table text-left" v-if="finalCart.cart.length>1">
                                        {{ trans('lang.items') }}
                                    </th>
                                    <th class="cart-summary-table text-left" v-else>{{ trans('lang.item') }}</th>
                                    <th class="cart-summary-table text-left">{{ trans('lang.qty') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.price') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.discount') }}</th>
                                    <th class="cart-summary-table text-right">{{ trans('lang.total') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="cartItem in finalCart.cart">
                                    <td class="cart-summary-table text-left">
                                        {{ cartItem.productTitle }}
                                        <br>
                                        <span
                                            v-if="cartItem.productTitle != cartItem.variantTitle && cartItem.variantTitle !== 'default_variant' && cartItem.variantTitle != '' && cartItem.orderType !== 'discount'">( {{
                                                cartItem.variantTitle
                                            }} )</span>
                                        <p v-if="cartItem.cartItemNote!='' && cartItem.cartItemNote != null"
                                           class="cart-note pb-0 mb-0">
                                            {{ trans('lang.note') }}: <span>{{ cartItem.cartItemNote }}</span>
                                        </p>
                                    </td>
                                    <td class="cart-summary-table"
                                        v-if="cartItem.orderType !== 'discount' && cartItem.orderType !== 'shipment'">
                                        {{ numberFormatting(cartItem.quantity) }}
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table"
                                        v-if="cartItem.orderType !== 'discount' && cartItem.orderType !== 'shipment'">
                                        {{ numberFormat(cartItem.price) }}
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table" v-if="cartItem.discount >0">
                                        {{ decimalFormat(cartItem.discount) }}%
                                    </td>
                                    <td class="text-right cart-summary-table"
                                        v-else-if="cartItem.orderType !== 'discount' && cartItem.orderType !== 'shipment'">
                                        {{ decimalFormat('0.00') }}%
                                    </td>
                                    <td class="cart-summary-table" v-else></td>
                                    <td class="text-right cart-summary-table">
                                        {{ numberFormat(cartItem.calculatedPrice) }}
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="cart-summary-table font-weight-bold text-left">{{
                                            trans('lang.sub_total')
                                        }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{ numberFormat(finalCart.subTotal) }}
                                    </td>
                                </tr>
                                <tr v-if="finalCart.tax>0">
                                    <td class="cart-summary-table text-left">{{ trans('lang.item_tax') }}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{ numberFormat(finalCart.tax) }}</td>
                                </tr>
                                <tr>
                                    <td class="cart-summary-table font-weight-bold text-left">{{
                                            trans('lang.total')
                                        }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{
                                            numberFormat(finalCart.grandTotal)
                                        }}
                                    </td>
                                </tr>
                                <tr v-if="payment.paymentName" v-for="payment in payments">
                                    <td class="cart-summary-table text-left">{{ payment.paymentName }}</td>
                                    <td class="cart-summary-table">
                                        <small class="font-weight-bold" v-if="payment.paid">{{ trans('lang.paid') }}<br>
                                            {{ payment.paid }}
                                        </small>
                                    </td>
                                    <td class="cart-summary-table text-left" v-if="payment.paymentName">
                                        <small class="font-weight-bold" v-if="payment.exchange">
                                            {{ trans('lang.exchange') }}
                                            <br> {{ payment.exchange }}
                                        </small>
                                    </td>
                                    <td class="cart-summary-table">
                                        {{ numberFormat(payment.paid - payment.exchange) }}
                                    </td>
                                </tr>
                                <tr v-for="(paymentTypes, index) in paymentListData"
                                    :key="`paymentListInvoiceData-${index}`"
                                    v-if="paymentTypes.is_active">
                                    <td class="cart-summary-table text-left">{{ paymentTypes.paymentName }}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{ numberFormat(paymentTypes.paid) }}</td>
                                </tr>
                                <tr v-if="exchangeValue>0">
                                    <td class="cart-summary-table text-left">{{ trans('lang.exchange') }}</td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table ">{{ numberFormat(exchangeValue) }}</td>
                                </tr>
                                <tr v-if="!status.printReceiptView">
                                    <td class="cart-summary-table font-weight-bold text-left" v-if="balance >= 0">
                                        {{ trans('lang.due') }}
                                    </td>
                                    <td class="cart-summary-table font-weight-bold text-left" v-else>{{
                                            trans('lang.change')
                                        }}
                                    </td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="cart-summary-table"></td>
                                    <td class="text-right cart-summary-table">{{ numberFormat(absValue(balance)) }}</td>
                                </tr>
                                <tr v-if="finalCart.cartNote !=''">
                                    <td class="cart-summary-table" colspan="3">
                                        <small>{{ trans('lang.note') }}: {{ finalCart.cartNote }}</small>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <a href="#" v-if="!status.isPaymentDone" @click.prevent="printReceiptBeforeDonePayment()"
                   class="px-2 btn-before-receipt">
                    <i class="la la-print pr-3"></i>
                    {{ trans('lang.print_receipt') }}
                </a>
            </div>

            <div class="col-12 col-md-6" id="js-payment">
                <div v-if="!status.isPaymentDone || invoice_template == ''">
                    <div class="row mx-0 mb-4" id="js-payment-title">
                        <div class="col-6"><h5>{{ trans('lang.total') }}</h5></div>
                        <div class="col-6 text-right payment-amount"><h5> {{ numberFormat(finalCart.grandTotal) }} </h5>
                        </div>
                    </div>
                    <pre-loader v-if="!status.hidePaymentListGetLoader || invoice_template == ''"></pre-loader>
                    <div v-else>
                        <div class="payment-description" id="js-payment-description">
                            <div v-if="salesOrReceivingType !== 'internal-transfer'" class="row mx-0 mb-2"
                                 v-for="(paymentTypes, index) in paymentListData">
                                <div class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 mt-auto">
                                    <label>{{ paymentTypes.paymentName }}</label>
                                </div>
                                <div class="col-4 col-sm-5 col-md-6 col-lg-7 col-xl-7 pl-0">
                                    <label>{{ numberFormat(paymentTypes.paid) }}</label>
                                </div>
                                <div class="col-1 mt-auto p-0 text-right">
                                    <a href="#"
                                       @click.prevent="clearPayment(index,paymentTypes.paymentID,paymentTypes.paid)">
                                        <i class="text-danger la la-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <div v-if="salesOrReceivingType !== 'internal-transfer'" class="form-group row ml-0">
                                <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-form-label"
                                       :for="paid">{{ paymentName }}</label>
                                <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                    <payment-input id="'paid'"
                                                   v-if="paymentGuard"
                                                   :inputValue="decimalFormat(paymentValue)"
                                                   @input="getPaymentAmount">
                                    </payment-input>
                                </div>
                            </div>

                            <div v-if="orderType === 'sales'" class="form-group row ml-0">
                                <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-form-label">
                                    {{ trans('lang.note') }}
                                </label>
                                <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                    <textarea class="form-control"
                                              v-model="salesNote">
                                    </textarea>
                                </div>
                            </div>

                            <div
                                v-if='parseInt(is_shipment_enable) === 1 && orderType ==="sales" && salesOrReceivingType === "customer" && salesOrReturnType==="sales" && is_connected'
                                class="form-group row ml-0">
                                <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 col-form-label pt-0">
                                    {{ trans('lang.shipping') }}
                                </label>
                                <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                    <div class="d-flex align-items-center">
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio"
                                                   name="shipment"
                                                   class="custom-control-input"
                                                   id="shipment-yes"
                                                   checked="checked"
                                                   @click="addShipmentStatus(1)"
                                                   value="1"
                                                   v-model="addShipment"/>
                                            <label class="custom-control-label"
                                                   for="shipment-yes">
                                                {{ trans('lang.yes') }}
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio"
                                                   name="shipment"
                                                   class="custom-control-input"
                                                   id="shipment-no"
                                                   @click="addShipmentStatus(0)"
                                                   value="0"
                                                   v-model="addShipment"/>
                                            <label class="custom-control-label"
                                                   for="shipment-no">
                                                {{ trans('lang.no') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="addShipmentInfo" class="">
                                <div class="form-group row ml-0">
                                    <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 col-form-label">
                                        {{ trans('lang.shipping_area') }}
                                    </label>
                                    <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                        <select v-model="shippingAreaId"
                                                v-validate="'required'"
                                                data-vv-as="shipping area"
                                                id="id"
                                                name="shipping_area"
                                                class="form-control"
                                                @change="setShippingPrice">
                                            <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                                            <option v-for="(row, index) in shippingData" :value="row.id">
                                                {{ row.area }}
                                            </option>
                                        </select>

                                        <div class="heightError">
                                            <small class="text-danger" v-show="errors.has('shipping_area')">
                                                {{ errors.first('shipping_area') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row ml-0">
                                    <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 col-form-label">
                                        {{ trans('lang.price') }}
                                    </label>
                                    <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                        <input v-validate="'required'"
                                               name="price"
                                               type="text"
                                               class="form-control"
                                               v-model="shippingPrice"/>
                                        <div class="heightError">
                                            <small class="text-danger" v-show="errors.has('price')">
                                                {{ errors.first('price') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row ml-0">
                                    <label class="col-4 col-sm-6 col-md-5 col-lg-4 col-xl-4 col-form-label">{{
                                            trans('lang.address')
                                        }}</label>
                                    <div class="col-4 col-sm-6 col-md-7 col-lg-8 col-xl-8 pl-0">
                                        <input v-validate="'required'"
                                               name="address"
                                               type="text"
                                               class="form-control"
                                               v-model="shippingAddress"
                                        />
                                        <div class="heightError">
                                            <small class="text-danger" v-show="errors.has('address')">
                                                {{ errors.first('address') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="payment-action" id="js-payment-action">
                            <div class="row mx-0 mt-2 no-gutters">
                                <div class="col-12">
                                    <hr class="custom-margin">
                                    <div class="payment-group" style="overflow: hidden;">
                                        <span v-for="(paymentTypes, index) in paymentList">
                                           <button
                                               v-if="(salesOrReceivingType === 'customer' || salesOrReceivingType === 'supplier') && paymentTypes.type !== 'credit'"
                                               :id="paymentTypes.id"
                                               class="btn app-color mobile-btn mr-1 mb-1"
                                               :class="{activePayment: paymentName==paymentTypes.name}"
                                               @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button
                                                v-else-if="(salesOrReceivingType === 'customer' || salesOrReceivingType === 'supplier') && !isEmptyObj(finalCart.customer) && (paid >= 0 && noRoundingAmount >=0)"
                                                :id="paymentTypes.id"
                                                class="btn app-color mobile-btn mr-1 mb-1"
                                                :class="{activePayment: paymentName==paymentTypes.name}"
                                                @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                            <button v-else-if="salesOrReceivingType === 'internal'"
                                                    :id="paymentTypes.id"
                                                    class="btn app-color mobile-btn mr-1 mb-1"
                                                    :class="{activePayment: paymentName==paymentTypes.name}"
                                                    @click.prevent="setPayment(paymentTypes.id,paymentTypes.name,paymentTypes.status,paymentTypes.type)">
                                                    {{ paymentTypes.name }}
                                            </button>
                                        </span>
                                    </div>
                                    <hr class="custom-margin">
                                    <span
                                        v-if="(salesOrReceivingType !== 'internal-transfer') && parseInt(balance)===0 || paidAmount == finalCart.grandTotal">
                                        <button class="btn btn-block app-color payment-button"
                                                v-shortkey="donePaymentShortcut"
                                                :disabled="!status.isConnected && offline == 0"
                                                @shortkey="shortcutForDonePayment('donePayment')"
                                                @click.prevent="storeInvoice()">
                                                {{ trans('lang.done_payment') }}
                                        </button>
                                    </span>
                                    <span v-else-if=" salesOrReceivingType === 'internal-transfer'">
                                       <button class="btn btn-block app-color payment-button"
                                               v-shortkey="donePaymentShortcut"
                                               :disabled="!status.isConnected && offline == 0"
                                               @shortkey="shortcutForDonePayment('donePayment')"
                                               @click.prevent="storeInvoice()">
                                                {{ trans('lang.transfer') }}
                                        </button>
                                    </span>
                                    <span v-else>
                                        <button class="btn btn-block app-color payment-button"
                                                :disabled="!status.isConnected && offline == 0"
                                                @click.prevent="storeInvoice()">
                                                {{ trans('lang.add_payment') }}
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mx-0" v-else>
                    <div class="col-12 text-center">
                        <h4>{{ trans('lang.payment_received') }}</h4>
                    </div>
                    <a href="#" @click.prevent="printReceipt()" class="printReceiptButton">
                        <i class="la la-print pr-3"></i>
                        {{ trans('lang.print_receipt') }}
                    </a>

                    <div
                        v-if="finalCart.customer.id && finalCart.customer.phone_number && !status.isSendSms &&  is_connected && parseInt(autoSendSms) === 0"
                        class="col-12 text-center">
                        <h4 v-if="orderType==='sales'">{{ trans('lang.send_sms_customer') }}</h4>
                        <h4 v-else>{{ trans('lang.send_sms_supplier') }}</h4>
                    </div>
                    <a v-if="finalCart.customer.id && finalCart.customer.phone_number && !status.isSendSms &&  is_connected && parseInt(autoSendSms) === 0"
                       href="#" @click.prevent="sendCustomerSms()" class="printReceiptButton">
                        <i class="la la-sms"></i>
                        {{ trans('lang.send_sms') }}
                    </a>
                </div>


            </div>

        </div>

        <invoice :printInvoice="status.printInvoice"
                 :rawHtml="rawHtml"
                 @resetGetInvoice="resetGetInvoice">
        </invoice>

        <print-receipt-component
            :invoice_size="invoice_size"
            :is_template_default="is_template_default"
            :print_invoice_before_done_payment="status.printInvoiceBeforeDonePayment"
            :sales_or_receiving_type="salesOrReceivingType"
            :transfer_branch_name="transferBranchName"
            :payment_list_data="paymentListData"
            :exchange_value="exchangeValue"
            :order_type="orderType"
            :final_cart="finalCart"
            :payments="payments"
            :sales_note="salesNote"
            :sold_to="sold_to"
            :sold_by="sold_by"
            :balance="balance"
            :invoice_template="invoice_template"
            :user="user"
            :logo="orderType === 'sales' ? salesInvoiceLogo : purchaseInvoiceLogo"
            :shipping_area="shippingArea"
            :shipping_address="shippingAddress"
            :add_shipping_info="addShipmentInfo"
            @resetGetInvoiceBeforeDonePayment="resetGetInvoiceBeforeDonePayment">
        </print-receipt-component>
    </div>
</template>

<script>


import axiosGetPost from '../../../helper/axiosGetPostCommon';
import PaymentDetailsMixin from "../mixin/PaymentDetailsMixin";

export default {
    extends: axiosGetPost,
    mixins: [PaymentDetailsMixin],
    props: [
        'selectedCashRegisterID',
        'finalCart',
        'user',
        'orderType',
        'salesOrReceivingType',
        'salesOrReturnType',
        'receiveOrReturnType',
        'orderID',
        'orderIdInternalTransfer',
        'orderIDInternal',
        'sold_to',
        'sold_by',
        'logo',
        'bankOrCardAmount',
        'calculateBank',
        'bankOrCardOptions',
        'donePaymentShortcut',
        'transferBranch',
        'transferBranchName',
        'paymentTypes',
        'autoInvoice',
        'invoice_template',
        'last_invoice_number',
        'invoice_prefix',
        'invoice_suffix',
        'selectedBranchID',
        'is_shipment_enable',
        'invoiceId',
        'is_cash_register_branch',
        'sales_default_invoice_template',
        'receives_default_invoice_template',
        'is_cash_register_used',
        'is_connected',
        'shipping_data',
        'add_shipping',
        'is_template_default',
        'invoice_size',
    ],
}
</script>