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
                        <h5 class="m-0">{{ trans('lang.profit_and_loss') }}</h5>
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
            <!--Export Button end-->
            <datatable-component
                class="main-layout-card-content"
                :options="tableOptions"
                :exportData="exportToVue"
                :exportFileName="trans('lang.profit')"
                @resetStatus="resetExportValue"
                :tab_name="tabName"
                :route_name="routeName"
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
            exportToVue: false,
            isActive: false,
            selectedItemId: "",
            tableOptions: {},
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            tabName: "profit_loss_report",
            routeName: "reports",
            filterColumn: "year",
            columnName: "year",
            columnNameList: ["year", "month", "date", "customer", "invoice_id"],
            hasData: value => {
                return !_.isEmpty(value);
            }
        };
    },
    created() {
        let instance = this;
        this.$root.$on("filter-data-option", filterOption => {
            instance.filterColumn = filterOption;
            let index = instance.columnNameList.findIndex(
                key => key === instance.filterColumn.filterOption
            );
            if (index >= 0) {
                instance.columnName = instance.filterColumn.filterOption;
            }
            instance.getData();
        });
        instance.getData();
    },
    mounted() {},
    methods: {
        getData() {
            let instance = this;
            instance.tableOptions = {
                tableName: "orders",
                columns: [
                    {
                        title: "lang." + instance.columnName,
                        key: instance.columnName,
                        type: "text",
                        source: "reports/sales",
                        uniquefield: "sales_id",
                        sortable: true
                    },
                    {
                        title: "lang.grand_total",
                        key: "grand_total",
                        type: "text",
                        sortable: false
                    },
                    {
                        title: "lang.item_tax",
                        key: "item_tax",
                        type: "text",
                        sortable: false
                    },
                    {
                        title: "lang.profit_amount",
                        key: "profit_amount",
                        type: "text",
                        sortable: false
                    }
                ],
                source: "/profit-loss-report",
                search: false,
                summary: true,
                formatting: ["grand_total", "item_tax", "profit_amount"],
                dateFormatting: ["sales_date"],
                summation: ["grand_total", "item_tax", "profit_amount"],
                sortedBy: "",
                sortedType: "DESC",
                summationKey: instance.columnName,
                right_align: ["grand_total", "item_tax", "profit_amount"],
                filters: [
                    {
                        title: "lang.date_range",
                        key: "date_range",
                        type: "date_range"
                    },
                    {
                        title: "lang.group_by",
                        key: "type",
                        type: "dropdown",
                        options: [
                            {
                                text: "lang.year",
                                value: "year",
                                selected: true
                            },
                            { text: "lang.month", value: "month" },
                            { text: "lang.date", value: "date" },
                            { text: "lang.customer", value: "customer" },
                            { text: "lang.invoice_id", value: "invoice_id" }
                        ]
                    }
                ]
            };
            instance.setPreLoader(true);
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