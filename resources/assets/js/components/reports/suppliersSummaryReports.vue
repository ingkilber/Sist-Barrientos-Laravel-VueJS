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
                        <h5 class="m-0">{{ trans('lang.suppliers_summary') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                        <common-submit-button
                            :buttonLoader="buttonLoader"
                            :isDisabled="isDisabled"
                            :isActiveText="isActiveText"
                            buttonText="export"
                            v-on:submit="exportStatus"
                        ></common-submit-button>
                    </div>
                </div>
            </div>
            <datatable-component
                class="main-layout-card-content"
                :options="tableOptions"
                :exportData="exportToVue"
                :tab_name="tabName"
                :route_name="routeName"
                exportFileName="trans('lang.suppliers_summary')"
                @resetStatus="resetExportValue"
            ></datatable-component>
        </span>
    </div>
</template>

<script>
import axiosGetPost from "../../helper/axiosGetPostCommon";
export default {
    extends: axiosGetPost,
    data() {
        return {
            isActive: false,
            isActiveAttributeModal: false,
            selectedItemId: "",
            modalID: "#due-amount-edit-modal",
            hidePreLoader: false,
            exportToVue: false,
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            tabName: "supplier_summary_report",
            routeName: "reports",
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value);
            }
        };
    },
    created() {
        let instance = this;
        instance.getData();
    },
    methods: {
        getData() {
            let instance = this;
            instance.axiosGet(
                "/branch-list",
                function(response) {
                    if (response.data) {
                        let branches = [
                            { text: "All", value: "all", selected: true },
                            ...response.data
                        ];

                        instance.tableOptions = {
                            tableName: "supplier_table",
                            columns: [
                                {
                                    title: "lang.supplier",
                                    key: "name",
                                    type: "text",
                                    sortable: true
                                },
                                {
                                    title: "lang.total_purchase",
                                    key: "total_purchase",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.total_payment",
                                    key: "total_payment",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.due",
                                    key: "due",
                                    type: "text",
                                    sortable: false
                                }
                            ],
                            source: "/supplier-summary-report",
                            search: true,
                            formatting: [
                                "total_purchase",
                                "total_payment",
                                "due"
                            ],
                            right_align: [
                                "total_purchase",
                                "total_payment",
                                "due"
                            ],
                            summary: true,
                            summationKey: ["filter_key"],
                            filters: [
                                {
                                    title: "lang.branch",
                                    key: "branch",
                                    type: "dropdown",
                                    languageType: "raw",
                                    options: branches
                                }
                            ]
                        };
                    }
                    instance.setPreLoader(true);
                },
                function() {
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
};
</script>