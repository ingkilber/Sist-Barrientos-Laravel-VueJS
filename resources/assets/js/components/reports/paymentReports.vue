<template>
    <div>
        <!--need to show pre-loader component until data table gets loaded-->
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
                   <!--Export Button-->
                <div class="main-layout-card-header-with-button">
                    <div class="main-layout-card-content-wrapper">
                        <div class="main-layout-card-header-contents">
                            <h5 class="m-0">{{ trans('lang.payment_report') }}</h5>
                        </div>
                        <div class="main-layout-card-header-contents text-right">
                           <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                                 :isActiveText="isActiveText" buttonText="export"
                                                 v-on:submit="exportStatus"></common-submit-button>
                        </div>
                    </div>
                </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content" :options="tableOptions" :exportData="exportToVue"
                                 :exportFileName="trans('lang.payment')" @resetStatus="resetExportValue"
                                 :tab_name="tabName"
                                 :route_name="routeName"
            ></datatable-component>
        </span>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                exportToVue: false,
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tableOptions: {},
                tabName: 'payment_report',
                routeName: 'reports',
                hasData: value => {
                    return !_.isEmpty(value);
                },
            }
        },
        created() {
            this.getPaymentFilterAttributes();
        },

        mounted() {
            let instance = this;

            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });
        },

        methods: {

            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
            },

            getPaymentFilterAttributes() {

                let instance = this;
                instance.axiosGet('/payment-reports-filter/',
                    function (response) {
                        if (response.data) {

                            let cashRegister = [],
                                paymentMethod = [];

                            /*Appending static value(All) with dynamic Filter options from db*/
                            if (response.data.cashRegister) cashRegister = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.cashRegister];
                            if (response.data.paymentMethod) paymentMethod = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.paymentMethod];

                            instance.tableOptions = {

                                tableName: 'cash_register_logs',
                                columns: [
                                    {title: 'lang.invoice_id', key: 'invoice_id', type: 'text', sortable: true},
                                    {title: 'lang.payment_date', key: 'date', type: 'text', sortable: true},
                                    {title: 'lang.method', key: 'payment_method', type: 'text', sortable: true},
                                    {
                                        title: 'lang.paid_by',
                                        key: 'paid_by',
                                        type: 'clickable_link',
                                        dynamicSource: 'route',
                                        uniquefield: 'paid_id',
                                        sortable: false
                                    },
                                    {title: 'lang.cash_register', key: 'cash_register', type: 'text', sortable: false},
                                    {title: 'lang.amount', key: 'paid', type: 'text', sortable: true},
                                ],
                                source: '/payment-reports',
                                summary: true,
                                summation: ['paid', 'exchange'],
                                summationKey: ['invoice_id'],
                                search: true,
                                sortedBy: 'invoice_id',
                                sortedType: 'DESC',
                                formatting: ['exchange', 'paid'],
                                dateFormatting: ['date'],
                                right_align: ['paid', 'exchange'],

                                filters: [
                                    /*dropdown filter for inventory report (dynamic value from db)*/
                                    {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                    {
                                        title: 'lang.data_table_search_type', key: 'type', type: 'dropdown', options: [
                                            {text: 'lang.all', value: 'all', selected: true},
                                            {text: 'lang.in_in', value: 'sales'},
                                            {text: 'lang.out', value: 'receiving'}
                                        ]
                                    },
                                    {
                                        title: 'lang.cash_register_label',
                                        key: 'cashRegister',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: cashRegister
                                    },
                                    {
                                        title: 'lang.payment_method_label',
                                        key: 'paymentMethod',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: paymentMethod
                                    },

                                ]
                            }
                        }

                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            exportStatus() {
                this.exportToVue = true;
                this.buttonLoader = true;
                this.isDisabled = true;
            },
            resetExportValue(value) {
                this.exportToVue = value;
                this.buttonLoader = false;
                this.isDisabled = false;
            }
        }
    }
</script>