<template>
    <div>
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <!--Export Button-->
            <div class="main-layout-card-header-with-button">
                <div class="main-layout-card-content-wrapper">
                    <div class="main-layout-card-header-contents">
                        <h5 class="m-0">{{ trans('lang.sales') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                              :isActiveText="isActiveText" buttonText="export"
                                              v-on:submit="exportStatus"></common-submit-button>
                    </div>
                </div>
            </div>
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 :tab_name="tabName"
                                 :route_name="routeName"
                                 exportFileName="sales" @resetStatus="resetExportValue"></datatable-component>

         </span>
    </div>
</template>
<script>
import axiosGetPost from '../../helper/axiosGetPostCommon';

export default {
    props: ['permission'],
    extends: axiosGetPost,
    data() {
        return {
            isActive: false,
            isActiveAttributeModal: false,
            selectedItemId: '',
            modalID: '#due-amount-edit-modal',
            order_type: 'sales',
            hidePreLoader: false,
            exportToVue: false,
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            tabName: 'sales_report',
            routeName: 'reports',
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value)
            }
        }
    },
    created() {
        this.getSalesReport();
    },
    mounted() {

        let instance = this;

        this.modalCloseAction(this.modalID);

        $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
            instance.isActiveAttributeModal = false;
            $('body').addClass('modal-open');
        });

        this.$hub.$on('viewSalesReportEdit', function (rowdata) {
            instance.addEditAction(rowdata);
        });
    },
    methods: {
        getSalesReport() {
            let instance = this;
            instance.axiosGet('/sales-report-filter',
                function (response) {
                    if (response.data) {
                        /*Appending cash register static value(All) with dynamic cash register title from db*/
                        let brands = [{text: 'All', value: 'all', selected: true}, ...response.data.brands],
                            categories = [{text: 'All', value: 'all', selected: true}, ...response.data.categories],
                            groups = [{text: 'All', value: 'all', selected: true}, ...response.data.groups],
                            customers = [{text: 'All', value: 'all', selected: true}, ...response.data.customers],
                            employee = [{text: 'All', value: 'all', selected: true}, ...response.data.employee];
                        instance.tableOptions = {

                            tableName: 'products',
                            columns: [
                                {
                                    title: 'lang.invoice_id',
                                    key: 'invoice_id',
                                    type: 'clickable_link',
                                    source: 'reports/sales',
                                    uniquefield: 'id',
                                    sortable: false
                                },
                                {title: 'lang.sales_date', key: 'date', type: 'text', sortable: true},
                                {title: 'lang.sales_type_dt', key: 'type', type: 'text', sortable: true},
                                {
                                    title: 'lang.sold_by',
                                    key: 'created_by',
                                    type: 'clickable_link',
                                    source: 'user',
                                    uniquefield: 'user_id',
                                    sortable: true
                                },
                                {
                                    title: 'lang.sold_to',
                                    key: 'customer',
                                    type: 'clickable_link',
                                    source: 'customer',
                                    uniquefield: 'customer_id',
                                    sortable: true
                                },
                                {title: 'lang.item_purchased', key: 'item_purchased', type: 'text', sortable: false},
                                {title: 'lang.tax', key: 'tax', type: 'text', sortable: false},
                                {title: 'lang.discount', key: 'discount', type: 'text', sortable: false},
                                {title: 'lang.total', key: 'total', type: 'text', sortable: false},
                                {title: 'lang.due', key: 'due_amount', type: 'text', sortable: false},
                            ],
                            source: '/sales-report',
                            summary: true,
                            search: true,
                            sortedBy: 'id',
                            sortedType: 'DESC',
                            formatting: ['total', 'sub_total', 'tax', 'discount', 'due_amount'],
                            dateFormatting: ['date'],
                            right_align: ['sub_total', 'item_purchased', 'tax', 'discount', 'total', 'due_amount'],
                            summation: ['sub_total', 'item_purchased', 'tax', 'discount', 'total', "due_amount"],
                            summationKey: ['invoice_id'],
                            filters: [
                                {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                {
                                    title: 'lang.sales_type', type: 'dropdown', key: 'sales_type', options: [
                                        {text: 'lang.all', value: 'all', selected: true},
                                        {text: 'lang.sold_to_customer', value: 'customer'},
                                        {text: 'lang.internal_sales', value: 'internal'},
                                        {text: 'lang.internal_transfer', value: 'internal-transfer'},
                                        {text: 'lang.sales_returns', value: 'returns'},
                                    ]
                                },
                                {
                                    title: 'lang.payment_type', type: 'dropdown', key: 'payment_type', options: [
                                        {text: 'lang.all', value: 'all', selected: true},
                                        {text: 'lang.paid', value: 'paid'},
                                        {text: 'lang.due', value: 'due'},
                                    ]
                                },
                                {
                                    title: 'lang.category',
                                    key: 'categories',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: categories
                                },
                                {
                                    title: 'lang.brand',
                                    key: 'brands',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: brands
                                },
                                {
                                    title: 'lang.group',
                                    key: 'groups',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: groups
                                },
                                {
                                    title: 'lang.customer',
                                    key: 'customers',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: customers
                                },
                                {
                                    title: 'lang.employee',
                                    key: 'employee',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: employee
                                },
                            ]
                        }
                    }
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },
        cartItemsToStore(cartItemsToStore) {
            let instance = this;
            instance.hideSalesReturnsPreLoader = false;
            cartItemsToStore.paymentType = 'credit';

            instance.axiosGETorPOST(
                {
                    url: '/save-due-amount',
                    postData: {cartItemsToStore},
                },
                (success, responseData) => {

                    if (success) {
                        instance.hideSalesReturnsPreLoader = true;
                        instance.showSuccessAlert(responseData.message);

                        $(`${this.modalID}`).modal('hide');
                        instance.$hub.$emit('reloadDataTable');

                    } else {
                        instance.hideSalesReturnsPreLoader = true;
                        $(`${this.modalID}`).modal('hide');
                    }
                }
            );
        },
        getActiveAttributeModal(isActive) {
            this.isActiveAttributeModal = isActive;
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