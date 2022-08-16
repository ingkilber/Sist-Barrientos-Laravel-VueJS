<template>
    <div class="modal-content">
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h4 class="m-0"></h4>
                    </div>
                    <div class="col-2 text-right pr-0">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <span aria-hidden="true"><i class="la la-close"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <form class="form-row margin-top">
                <div class="form-group col-md-6">
                    <label for="">{{ trans('lang.amount') }}</label>
                    <payment-input v-validate="'required'" name="paying_amount" data-vv-as="amount" id="'payingAmount'" :inputValue="paymentValue" @input = getAmount></payment-input>
                    <!-- <input v-validate="'required'" name="paying_amount" data-vv-as="amount" v-model="amount" class="form-control" id="payingAmount" type="text"> -->
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('paying_amount')">
                            {{ errors.first('paying_amount') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="cardNumber">{{ trans('lang.card_number') }}</label>
                    <input type="text" v-validate="'required'" v-model="cardNumber" name="card_number" data-vv-as="card number" class="form-control" id="cardNumber">
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('card_number')">
                            {{ errors.first('card_number') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="cardHolderName">{{ trans('lang.card_holder_name') }}</label>
                    <input type="text" v-validate="'required'" name="card_holder_name" data-vv-as="holder name" v-model="cardHolder" class="form-control" id="cardHolderName">
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('card_holder_name')">
                            {{ errors.first('card_holder_name') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="cardTransactionNumber">{{ trans('lang.card_transaction_no') }}</label>
                    <input type="text" v-validate="'required'" v-model="transactionNo" name="card_transaction_number" data-vv-as="transaction no" class="form-control" id="cardTransactionNumber">
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('card_transaction_number')">
                            {{ errors.first('card_transaction_number') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="cardType">{{ trans('lang.card_type') }}</label>
                    <select v-validate="'required'" v-model="cardType" name="card_type" data-vv-as="type" class="custom-select"
                            id="cardType">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option value="visa">{{ trans('lang.visa_card') }}</option>
                        <option value="master-card">{{ trans('lang.master_card') }}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('card_type')">
                            {{ errors.first('card_type') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="month">{{ trans('lang.month') }}</label>
                    <select v-validate="'required'" v-model="month" name="transaction_month" data-vv-as="month" class="custom-select" id="month">
                        <option value="" disabled selected>{{ trans('lang.choose_month') }}</option>
                        <option value="january">{{ trans('lang.january') }}</option>
                        <option value="february">{{ trans('lang.february') }}</option>
                        <option value="march">{{ trans('lang.march') }}</option>
                        <option value="april">{{ trans('lang.april') }}</option>
                        <option value="may">{{ trans('lang.may') }}</option>
                        <option value="june">{{ trans('lang.june') }}</option>
                        <option value="july">{{ trans('lang.july') }}</option>
                        <option value="august">{{ trans('lang.august') }}</option>
                        <option value="september">{{ trans('lang.september') }}</option>
                        <option value="october">{{ trans('lang.october') }}</option>
                        <option value="november">{{ trans('lang.november') }}</option>
                        <option value="december">{{ trans('lang.december') }}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('transaction_month')">
                            {{ errors.first('transaction_month') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="year">{{ trans('lang.year') }}</label>
                    <input type="text" v-validate="'required'" v-model="year" name="transaction_year" data-vv-as="year" class="form-control" id="year">
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('transaction_year')">
                            {{ errors.first('transaction_year') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="securityCode">{{ trans('lang.security_code') }}</label>
                    <input v-validate="'required'" name="security_code" v-model="securityCode" data-vv-as="security code" class="form-control" id="securityCode">
                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('security_code')">
                            {{ errors.first('security_code') }}
                        </small>
                    </div>
                </div>
                
                <div class="form-group col-12">
                    <button type="button" class="btn btn-primary app-color mobile-btn" v-if="paid > amount" @click="donePayment">
                        {{ trans('lang.add_payment') }}
                    </button>
                    <button type="button" class="btn btn-primary app-color mobile-btn" v-else @click="donePayment">
                        {{ trans('lang.done_payment') }}
                    </button>
                    <button type="button" class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal">
                        {{ trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: ['paid'],
        extends: axiosGetPost,
        data() {
            return {
                amount:'',
                cardNumber: '',
                cardHolder: '',
                transactionNo: '',
                cardType: '',
                month: '',
                year: '',
                securityCode: '',
                options: {},
                paymentValue:''
            }
        },
        mounted(){
            this.amount = this.paid;
            this.paymentValue = this.paid;
        },
        methods: {
            getAmount(amount){
                this.amount = amount;
            },
            donePayment() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.options = {
                            amount: this.amount,
                            cardNumber: this.cardNumber,
                            cardHolder: this.cardHolder,
                            transactionNo: this.transactionNo,
                            cardType: this.cardType,
                            month: this.month,
                            year: this.year,
                            securityCode: this.securityCode
                        };
                        $('#card-payment-modal').modal('hide');
                        this.$emit('cardPayment',this.amount,this.options);
                    }
                });
            }

        },
    }

</script>