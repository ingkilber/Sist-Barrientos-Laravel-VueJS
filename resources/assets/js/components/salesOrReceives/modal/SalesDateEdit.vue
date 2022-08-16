<template id="sales-date-edit">
    <div class="p-3">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>{{ modalTitle }}</h5>
            </div>
            <div class="col-md-5 text-right payment-amount">
            </div>
            <div class="col-md-1 text-right">
                <a href="#" data-dismiss="modal" aria-label="Close" class="close">
                    <i class="la la-close text-grey"/>
                </a>
            </div>
        </div>
        <div>
            <pre-loader v-if="!hideEditSalesDatePreloader"/>
            <div v-else>
                <div>
                    <div class="row mb-3">
                        <label class="col-md-4 col-form-label">{{trans('lang.sales_date')}}</label>
                        <div class="col-md-8 pl-0">
                            <datepicker id="sales-date" v-model="selectedDate"
                                        :format="datePickerDateFormatter(dateFormat)"
                                        :bootstrap-styling="true"></datepicker>
                        </div>
                    </div>
                    <div class="row">
                        <hr class="custom-margin"/>

                        <span class="col-md-12 mt-3">
                        <button class="btn btn-block app-color payment-button"
                                @click.prevent="updateSalesDate()">
                            {{ trans('lang.save') }}
                        </button>
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";
    import Datepicker from 'vuejs-datepicker';

    export default {
        extends: axiosGetPost,
        props: ["rowData", "modalIdForSalesDateEdit", "modalTitle", "orderType", "pre_loader"],
        components: {
            Datepicker
        },
        data() {
            return {
                date: null,
                selectedDate: '',
            };
        },
        created() {
            this.selectedDate = this.rowData.date_original;
            this.hideEditSalesDatePreloader = this.pre_loader;
            this.hidePaymentListGetLoader = true;
        },
        mounted() {
            let instance = this;

            $("#date-edit-modal").on("hidden.bs.modal", function (e) {
                instance.$emit("emitForIsActiveEdit", false);
            });
        },

        watch: {
            pre_loader: function (value) {
                this.hideEditSalesDatePreloader = value;
            }
        },

        methods: {
            updateSalesDate() {
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result) {

                        this.inputFields = {
                            editedSalesDate: moment(this.selectedDate, this.dateFormat).format('YYYY-MM-DD'),
                        };
                        if (this.rowData.id) {
                            this.postDataMethod('/sales/date/update/' + this.rowData.id, this.inputFields);
                        }
                    }
                });

            },
            postDataThenFunctionality() {
                $(this.modalIdForSalesDateEdit).modal('hide');
            },
            postDataCatchFunctionality() {
                $(this.modalIdForSalesDateEdit).modal('hide');
            }
        }
    };
</script>
