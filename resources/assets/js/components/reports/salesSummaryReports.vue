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
                    <h5 class="m-0">{{ trans('lang.sales_summary') }}</h5>
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
                                 :options="tableOptions" :exportData="exportToVue"
                                 :exportFileName="trans('lang.sales_summary')"
                                 :tab_name="tabName"
                                 :route_name="routeName"
                                 @resetStatus="resetExportValue"></datatable-component>
        </span>
    </div>
</template>

<script>

import axiosGetPost from '../../helper/axiosGetPostCommon';

export default {
    props: ["file"],

    extends: axiosGetPost,

    data() {
        return {
            exportToVue: false,
            isActive: false,
            isActiveAttributeModal: false,
            selectedItemId: '',
            tableOptions: {},
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            filterColumn: 'brand',
            columnName: 'brand',
            tabName: 'sales_summary_reports',
            routeName: 'reports',
            columnNameList: ['brand', 'category', 'group', 'product', 'customer', 'employee', 'date'],
            hasData: value => {
                return !_.isEmpty(value)
            },
        }
    },
    created() {
        let instance = this;
        this.$root.$on('filter-data-option', filterOption => {
            instance.filterColumn = filterOption;
            let index = instance.columnNameList.findIndex(key => key === instance.filterColumn.filterOption);

            if (index >= 0) {
                instance.columnName = instance.filterColumn.filterOption;
            }
            instance.getData();
        });
        instance.getData();
    },
    mounted() {
        let instance = this;
        this.modalCloseAction(this.modalID);
        $('#attributes-add-edit-modal').on('hidden.bs.modal', function () {
            instance.isActiveAttributeModal = false;
            $('body').addClass('modal-open');
        });
    },
    methods: {
        getActiveAttributeModal(isActive) {
            this.isActiveAttributeModal = isActive;
        },
        getData() {
            let instance = this;
            instance.axiosGet('/branch-list',
                function (response) {

                    if (response.data) {
                        let branches = [{text: 'All', value: 'all', selected: true}, ...response.data];
                        instance.tableOptions = {
                            tableName: 'sales-summery',
                            columns: [
                                (instance.columnName !== 'customer' && instance.columnName !== 'employee' ? {
                                        title: 'lang.' + instance.columnName,
                                        key: 'filter_key',
                                        type: 'text',
                                        sortable: true
                                    }
                                    : {
                                        title: 'lang.' + instance.columnName,
                                        key: 'filter_key',
                                        type: 'clickable_link',
                                        sortable: true,
                                        uniquefield: 'id',
                                        source: (instance.columnName == 'customer' ? 'customer' : 'user')
                                    }),
                                {title: 'lang.item_sold', key: 'item_purchased', type: 'text', sortable: false},
                                {title: 'lang.sub_total', key: 'sub_total', type: 'text', sortable: false},
                                {title: 'lang.tax', key: 'tax', type: 'text', sortable: false},
                                {title: 'lang.discount', key: 'discount', type: 'text', sortable: false},
                                {title: 'lang.total', key: 'total', type: 'text', sortable: false},
                            ],
                            source: '/sales-summary-report',
                            summary: true,
                            formatting: ['sub_total', 'tax', 'discount', 'total'],
                            summation: ['sub_total', 'item_purchased', 'discount', 'tax', 'total'],
                            summationKey: ['filter_key'],
                            right_align: ['item_purchased', 'sub_total', 'tax', 'discount', 'total'],
                            filters: [
                                {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                {
                                    title: 'lang.group_by', key: 'type', type: 'dropdown', options: [
                                        {text: 'lang.brand', value: 'brand', selected: true},
                                        {text: 'lang.category', value: 'category'},
                                        {text: 'lang.group', value: 'group'},
                                        {text: 'lang.product', value: 'product'},
                                        {text: 'lang.customer', value: 'customer'},
                                        {text: 'lang.employee', value: 'employee'},
                                        {text: 'lang.date', value: 'date'},
                                    ]
                                },
                                {
                                    title: 'lang.branch',
                                    key: 'branch',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: branches
                                },
                            ]
                        }
                    }

                    instance.setPreLoader(true);
                },

                function () {
                    instance.setPreLoader(true);
                }
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