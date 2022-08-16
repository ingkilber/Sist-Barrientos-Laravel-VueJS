<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_cash_register') }}</h4>
                    <h4 class="m-0" v-else>{{ trans('lang.add_cash_register') }}</h4>
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
                <div class="form-group col-md-12">
                    <label for="title">{{ trans('lang.title') }}</label>
                    <input v-validate="'required'" name="title" class="form-control" id="title" type="text"
                           v-model="title">
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('title')">{{ errors.first('title') }}</small>
                    </div>
                </div>

                <div class="form-group margin-top col-md-12">
                    <label for="branches">{{ trans('lang.branch') }}</label>
                    <select v-validate="'required'" name="branch" v-model="branch_id" id="branches"
                            class="custom-select">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="branch in branches" :value="branch.id"> {{branch.name}}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('branch')">{{ errors.first('branch') }}</small>
                    </div>
                </div>

                <div class="form-group margin-top col-md-12">
                    <label for="invoices">{{ trans('lang.sales_invoice_template') }}</label>
                    <select v-validate="'required'" name="invoice" v-model="sales_invoice_id" id="invoices"
                            class="custom-select">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="invoice in invoices" :value="invoice.id" v-if="invoice.template_type === 'sales'"> {{invoice.template_title}}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('invoice')">{{ errors.first('invoice') }}</small>
                    </div>
                </div>

                <div class="form-group margin-top col-md-12">
                    <label for="invoices">{{ trans('lang.receiving_invoice_template') }}</label>
                    <select v-validate="'required'" name="invoice" v-model="receiving_invoice_id"
                            class="custom-select">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="invoice in invoices" :value="invoice.id" v-if="invoice.template_type === 'receiving'"> {{invoice.template_title}}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('invoice')">{{ errors.first('invoice') }}</small>
                    </div>
                </div>

                <div class="form-group margin-top col-md-12">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="multi-user" value="1"  name="multi-user" v-model="allowMultiUser" />
                        <label class="custom-control-label" for="multi-user">
                            {{ trans('lang.allow_multi_user') }}
                        </label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save') }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">
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
        extends: axiosGetPost,
        props: ['id', 'modalID'],
        data() {
            return {
                title: '',
                branch_id: '',
                branches: [],
                allowMultiUser: 0,
                invoices: [],
                sales_invoice_id: '',
                receiving_invoice_id: '',
                template_title: '',
                hidePreLoader:false,
            }
        },

        created() {

            if (this.id) {
                this.getCashRegisterData('/cash-register-show/' + this.id);
            }
        },

        mounted() {
            let instance = this;

            //get application setting data
            this.axiosGet('/allBranches',
                function (response) {
                    instance.branches = response.data;
                },
                function (error) {
                },
            );

            //get application setting data
            this.axiosGet('/allInvoice',
                function (response) {
                    instance.invoices = response.data.invoice_template;
                    instance.hidePreLoader = true;
                },
                function (error) {
                    instance.hidePreLoader = true;
                },
            );

            // Enable bootstrap tooltip
            $(document).ready(function() {
                $("body").tooltip({ selector: '[data-toggle=tooltip]' });
            });
        },

        methods: {

            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            title: this.title,
                            branch_id: this.branch_id,
                            sales_invoice_id: this.sales_invoice_id,
                            receiving_invoice_id: this.receiving_invoice_id,
                            allowMultiUser:this.allowMultiUser,
                        };

                        if (this.id) {
                            this.postDataMethod('/cash-register-update/' + this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod('/cash-register-store', this.inputFields);
                        }
                    }
                });
            },
            getCashRegisterData(route) {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet(route,
                    function (response) {
                        instance.title = response.data.title;
                        instance.branch_id = response.data.branch_id;
                        instance.sales_invoice_id = response.data.sales_invoice_id;
                        instance.receiving_invoice_id = response.data.receiving_invoice_id;
                        instance.allowMultiUser = response.data.multiple_access;
                        instance.setPreLoader(true);
                    },
                    function () {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality() {
                $(this.modalID).modal('hide');
                this.$hub.$emit('reloadDataTable');
            },
            postDataCatchFunctionality(error) {
                this.hidePreLoader = false;
                this.showErrorAlert(error.data.message);
            },
        },
    }
</script>