<template>
    <div class="modal-content">
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h4 class="m-0">{{ trans('lang.bank_transfer_details') }}</h4>
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
                    <label>{{ trans('lang.amount') }}</label>
                    <payment-input v-validate="'required'" name="paying_amount" data-vv-as="amount" id="'payingAmount'" :inputValue="paymentValue" @input = getAmount></payment-input>

                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('paying_amount')">
                            {{ errors.first('paying_amount') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="accountNumber">{{ trans('lang.bank_account_number') }}</label>

                    <input v-validate="'required'" name="account_number" v-model="accountNum" data-vv-as="account no" class="form-control" id="accountNumber" type="text">

                    <div class="heightError">
                        <small class="text-danger" v-if="errors.has('account_number')">
                            {{ errors.first('account_number') }}
                        </small>
                    </div>
                </div>
                <div class="form-group col-12">
                    <button type="button" class="btn btn-primary app-color mobile-btn" v-if="paid > amount" @click.prevent="donePayment">
                        {{ trans('lang.add_payment') }}
                    </button>
                    <button type="button" class="btn btn-primary app-color mobile-btn" v-else @click.prevent="donePayment">
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
                amount: '',
                accountNum: '',
                options: {},
                paymentValue:'',
            }
        },
        mounted() {
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
                            accountNum: this.accountNum,
                        };
                        $('#bank-transfer-modal').modal('hide');
                        this.$emit('bankPayment', this.amount, this.options);
                    }
                });
            },
        },
    }
</script>