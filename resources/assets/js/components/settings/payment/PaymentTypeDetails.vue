<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_type') }}</h4>
                    <h4 class="m-0" v-else>{{ trans('lang.add_type') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row" v-else>
                <span v-if="alertMessage.length>0" class="alertBranch">
                    <div class="alert alert-warning alertBranch" role="alert">
                        {{alertMessage}}
                    </div>
                </span>
                <div class="form-group col-md-12">
                    <label for="type-name">{{ trans('lang.name') }}</label>
                    <input class="form-control" v-validate="'required'" id="type-name" name="paymentTypeName"
                           data-vv-as="payment type name" type="text" v-model="paymentTypeName">
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('paymentTypeName')">{{
                            errors.first('paymentTypeName') }}
                        </small>
                    </div>
                </div>
                <div class="form-group margin-top col-md-12">
                    <label>{{ trans('lang.round') }}</label>
                    <select class="custom-select" v-validate="'required'" name="round" v-model="round" id="round">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option value="no_round">{{ trans('lang.no_round') }}</option>
                        <option value="near_integer">{{ trans('lang.near_integer') }}</option>
                        <option value="near_half">{{ trans('lang.near_half') }}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('round')">{{ errors.first('round') }}</small>
                    </div>
                </div>
                <div  class="form-group  col-md-5 margin-top">
                    <label>{{ trans('lang.is_default') }}</label><br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" name="isDefault" class="custom-control-input" id="default" value="1"
                               v-model="isDefault">
                        <label class="custom-control-label" for="default">{{ trans('lang.yes') }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline" v-if="isDefault !== 1">
                        <input type="radio" name="isDefault" class="custom-control-input" id="not_default" value="0"
                               v-model="isDefault">
                        <label class="custom-control-label" for="not_default">{{ trans('lang.no') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">{{ trans('lang.save')
                        }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{
                        trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: ['id', 'modalID', 'allPaymentTypes'],
        extends: axiosGetPost,
        data() {
            return {
                paymentTypeName: '',
                isDefault: 0,
                paymentType: '',
                alertMessage: '',
                round: '',
            }
        },

        created() {

            if (this.id) {
                this.getPaymentData('/payment-details/' + this.id);
            }
        },

        methods: {


            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            paymentTypeName: this.paymentTypeName,
                            pIsDefault: this.isDefault,
                            round: this.round,
                        };
                        if (this.id) {
                            this.postDataMethod('/edit-payment/' + this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod('/add-payment', this.inputFields);
                        }
                    }
                });
            },
            getPaymentData(route) {
                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.paymentTypeName = response.data.name;
                        instance.paymentType = response.data.type;
                        instance.isDefault = response.data.is_default;
                        instance.round = response.data.status;
                        if (!(instance.paymentType === 'card' || instance.paymentType === 'bank')) {
                        }
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response) {
                $(this.modalID).modal('hide');
                this.$hub.$emit('reloadDataTable');

            },
            postDataCatchFunctionality(error) {

                this.showErrorAlert(error.data.message);
            },

        },
    }
</script>