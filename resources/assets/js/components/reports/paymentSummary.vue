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
                            <h5 class="m-0">{{ trans('lang.payment_summary_report') }}</h5>
                        </div>
                        <div class="main-layout-card-header-contents text-right">
                             <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                                   :isActiveText="isActiveText" buttonText="export"
                                                   v-on:submit="exportStatus"></common-submit-button>
                        </div>
                    </div>
                </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 :exportFileName="trans('lang.payment_summary')"
                                 :tab_name="tabName"
                                 :route_name="routeName"
                                 @resetStatus="resetExportValue"></datatable-component>
        </span>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                exportToVue:false,
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tabName : 'payment_summary_report',
                routeName : 'reports',
                tableOptions: {},
                columnName: 'date',
                hasData: value => {
                    return !_.isEmpty(value);
                },
            }
        },
        created() {
            let instance = this;
            this.$root.$on('filter-data-option', filterOption => {
                instance.filterColumn = filterOption;
                if(instance.filterColumn.filterOption==='date' || instance.filterColumn.filterOption==="customer"||instance.filterColumn.filterOption==="employee"){
                    instance.columnName = instance.filterColumn.filterOption;
                }
                instance.getPaymentSummaryFilterAttributes();
            });

            this.getPaymentSummaryFilterAttributes();
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
            getPaymentSummaryFilterAttributes() {
                let instance = this;
                instance.axiosGet('/payment-summery-reports-filter/',
                    function (response) {
                        if (response.data) {

                            let paymentMethod = [];

                            /*Appending static value(All) with dynamic Filter options from db*/
                            if (response.data.paymentMethod) paymentMethod = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.paymentMethod];

                            instance.tableOptions = {

                                tableName: 'payment_table',
                                columns: [
                                    (instance.columnName !== 'customer' && instance.columnName !== 'employee'? {title: 'lang.' + instance.columnName, key: 'filter_key', type: 'text', sortable: true}
                                        :{title: 'lang.' + instance.columnName, key: 'filter_key',type:'clickable_link', sortable: true, uniquefield:'id',source: (instance.columnName === 'customer'?'customer':'user')}),
                                    {title: 'lang.type', key: 'type', type: 'text', sortable: true},
                                    {title: 'lang.payment_method', key: 'method', type: 'text', sortable: true},
                                    {title: 'lang.total', key: 'total', type: 'text', sortable: true},

                                ],
                                source: '/payment-summary-reports',
                                summary: true,
                                formatting : ['total'],
                                summation: ['total'],
                                summationKey: ['filter_key'],
                                right_align: 'total',
                                search: false,
                                sortedBy:'filter_key',
                                sortedType:'DESC',
                                filters: [
                                    {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                    {
                                        title: 'lang.group_by', key: 'type', type: 'dropdown', options: [
                                            {text: 'lang.date', value: 'date'},
                                            {text: 'lang.customer', value: 'customer'},
                                            {text: 'lang.employee', value: 'employee'},
                                        ]
                                    },
                                    {
                                        title: 'lang.type', key: 'status', type: 'dropdown', options: [
                                            {text: 'lang.all', value: 'all', selected: true},
                                            {text: 'lang.sales', value: 'sales'},
                                            {text: 'lang.receiving', value: 'receiving'}
                                        ]
                                    },
                                    /*dropdown filter for inventory report (dynamic value from db)*/
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