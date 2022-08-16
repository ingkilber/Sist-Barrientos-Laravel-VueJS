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
                        <h5 class="m-0">{{ trans('lang.sales_and_purchase') }}</h5>
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
                                 :exportFileName="trans('lang.sales_and_purchase')"
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
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                modalID: '#due-amount-edit-modal',
                hidePreLoader: false,
                exportToVue: false,
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tabName: 'sales_and_purchase_report',
                routeName: 'reports',
                tableOptions: {},
                hasData: value => {
                    return !_.isEmpty(value);
                }
            }
        },
        created() {
            let instance = this;
            instance.getData();
        },
        mounted() {
        },
        methods: {
            getData() {
                let instance = this;
                instance.axiosGet('/branch-list',
                    function (response) {
                        if (response.data) {
                            let branches = [{text: 'All', value: 'all', selected: true}, ...response.data];

                            instance.tableOptions = {
                                tableName: 'sales_and_purchase_table',
                                columns: [
                                    {title: 'lang.type', key: 'type', type: 'text', sortable: true},
                                    {title: 'lang.total', key: 'total', type: 'text', sortable: false},
                                ],
                                source: '/sales-and-purchase-report',
                                formatting: ['total'],
                                right_align: ['total'],
                                filters: [
                                    {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
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
                    function (error) {
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
        },
    }
</script>