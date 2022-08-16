<template>
    <div class="p-3">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>{{ modalTitle }}</h5>
            </div>
            <div class="col-md-5 text-right payment-amount">
                <h5>{{ numberFormat(rowdata.due_amount) }}</h5>
            </div>
            <div class="col-md-1 text-right">
                <a href="#" data-dismiss="modal" aria-label="Close" class="close">
                    <i class="la la-close text-grey"/>
                </a>
            </div>
        </div>

        <div>
            <pre-loader v-if="!hidePaymentListGetLoader"/>
            <div v-else>
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label">{{trans('lang.amount')}}</label>
                    <div class="col-md-8 pl-0">
                        <payment-input id="'paid'"
                                       v-if="paymentGuard"
                                       :inputValue="decimalFormat(rowdata.due_amount)"
                                       @input="getPaymentAmount"/>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-md-4 col-form-label">{{trans('lang.payment_method_label')}}</label>
                    <div class="col-md-8 pl-0">
                        <select class="custom-select"
                                id="exampleFormControlSelect1"
                                v-model="selectedPayment">
                            <option v-for="(payment, index) in paymentList"
                                    :id="payment.id"
                                    v-if="payment.type != 'credit'"
                                    :value="payment.id">
                                {{ payment.name}}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3" v-show="exchangeValue > 0">
                    <label class="col-md-4 col-form-label">{{trans('lang.exchange')}}</label>
                    <div class="col-md-8 pl-0">
                        <span>{{numberFormat(exchangeValue)}}</span>
                    </div>
                </div>
                <div class="row">
                    <hr class="custom-margin"/>

                    <span class="col-md-12 mt-3">
                        <button class="btn btn-block app-color payment-button"
                                :disabled="paid == '' || selectedPayment == null"
                                @click.prevent="storeInvoice()">
                            {{ trans('lang.done_payment') }}
                        </button>


                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    const {unformat} = require("number-currency-format");
    export default {
        extends: axiosGetPost,
        props: ["rowdata", "modalID", "modalTitle", "orderType", "pre_loader"],
        data() {
            return {
                paymentList: [],
                isPaymentDone: false,
                paymentGuard: true,
                payments: [],
                savedPayments: [],
                balance: unformat(this.rowdata.due_amount),
                hidePaymentListGetLoader: false,
                exchangeValue: 0,
                currentOrderId: "",
                paymentListData: [],
                paymentName: "",
                paymentId: "",
                paymentStatus: "",
                paid: "",
                noRoundingAmount: "",
                roundingDifference: 0,
                paymentType: "",
                paymentValue: "",
                paymentOptions: {},

                selectedRowData: this.rowdata,
                selectedModalId: this.modalID,
                selectedPayment: null
            };
        },
        created() {
            this.readPaymentListData();
            this.currentOrderId = this.orderID;
        },
        mounted() {
            let instance = this;
            this.paid = this.balance;
            $("#due-amount-edit-modal").on("hidden.bs.modal", function (e) {
                instance.$emit("emitForIsActive", false);
            });
        },
        methods: {
            readPaymentListData() {
                this.hidePaymentListGetLoader = false;

                let instance = this;

                instance.axiosGETorPOST(
                    {url: "/payment-list"},
                    (success, responseData) => {

                        if (success) {

                            instance.paymentList = responseData;

                            let paymentRow = instance.paymentList.find(function (
                                element
                            ) {
                                return element.is_default == 1;
                            });

                            instance.selectedPayment = paymentRow.id;
                        }
                        instance.hidePaymentListGetLoader = true;
                    }
                );
            },

            getPaymentAmount(value) {
                this.paid = value;
                this.exchangeValue = this.paid - this.balance;
            },

            storeInvoice() {
                let totalDue = unformat(this.selectedRowData.due_amount);
                let currentDue = totalDue - this.paid;
                let creditId = null;
                let selectedPaymentObj = null;
                for (let item of this.paymentList) {
                    if (item.type === "credit") {
                        creditId = item.id;
                    }
                    if (item.id == this.selectedPayment) {
                        selectedPaymentObj = item;
                    }
                }

                if (currentDue > 0) {
                    this.paymentListData.push({
                        paid: currentDue,
                        paymentID: creditId,
                        paymentName: "Credit",
                        paymentType: "credit",
                        exchange: 0,
                        PaymentTime: moment().format("YYYY-MM-DD H:mm"),
                        options: this.paymentOptions
                    });
                }
                this.paymentListData.push({
                    paid: this.paid,
                    paymentID: this.selectedPayment,
                    paymentName: selectedPaymentObj.name,
                    paymentType: selectedPaymentObj.type,
                    exchange: this.exchangeValue > 0 ? this.exchangeValue : 0,
                    PaymentTime: moment().format("YYYY-MM-DD H:mm"),
                    options: this.paymentOptions
                });

                this.hidePaymentListGetLoader = false;

                let cartItemsToStore = {
                    rowData: this.selectedRowData,
                    payments: this.paymentListData
                };

                this.$emit("cartItemsToStore", cartItemsToStore);
            },
            rounding(amount) {
                if (this.paymentStatus === "near_half") {
                    return Math.round(amount * 2).toFixed() / 2;
                } else if (this.paymentStatus === "near_integer") {
                    return Math.round(amount);
                } else {
                    return amount;
                }
            }
        }
    };
</script>
