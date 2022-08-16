<template>
    <div>
        <div class="main-layout-wrapper" v-shortkey="loadSales" @shortkey="globalShortcutMethod()">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.reports')}}</span>
                    </li>
                </ol>
            </nav>
            <div class="main-layout-card">
                <div class="custom-tabs">
                    <ul class="nav nav-tabs">
                        <li
                            class="nav-item d-flex justify-content-center"
                            :class="{'active':isSelectedTab(tab.name)}"
                            @click.prevent="selectTab(tab.name, tab.component)"
                            v-if="isVisible(tab.name)"
                            v-for="tab in tabs"
                        >
                            <a
                                class="nav-link"
                                href="#customers"
                                @click.prevent="isActive = 1"
                            >{{ trans(tab.lang) }}</a>
                        </li>
                    </ul>
                </div>
                <transition name="slide-fade" mode="out-in">
                    <component v-if="this.componentName" v-bind:is="this.componentName"></component>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
import axiosGetPost from "../../helper/axiosGetPostCommon";
export default {
    extends: axiosGetPost,
    props: [
        "sales_report",
        "sales_details_report",
        "sales_summary_reports",
        "receiving_report",
        "receiving_summary",
        "register_report",
        "inventory_report",
        "payment_report",
        "payment_summary_report",
        "yearly_sales_chart",
        "available_stock_chart",
        "available_tax_report",
        "profit_loss_report",
        "customers_summary_report",
        "suppliers_summary_report",
        "sales_and_purchase_report",
        "adjust_stock_report",
        "shipment_report",
        "tab_name",
        "route_name"
    ],

    data() {
        return {
            selectedTab: null,
            componentName: null,
            loadSales: [],
            tabs: [
                {
                    name: "sales_report",
                    lang: "lang.sales",
                    component: "sales-reports"
                },
                {
                    name: "sales_details_report",
                    lang: "lang.sales_details",
                    component: "sales-details"
                },
                {
                    name: "sales_summary_reports",
                    lang: "lang.sales_summary",
                    component: "sales-summary-reports"
                },
                {
                    name: "receiving_report",
                    lang: "lang.receives",
                    component: "receiving-reports"
                },
                {
                    name: "receiving_summary",
                    lang: "lang.receives_summary",
                    component: "receiving-summary-reports"
                },
                {
                    name: "register_report",
                    lang: "lang.register_logs",
                    component: "cash-register-log-reports"
                },
                {
                    name: "inventory_report",
                    lang: "lang.inventories",
                    component: "inventory-reports"
                },
                {
                    name: "payment_report",
                    lang: "lang.payment_report",
                    component: "payment-reports"
                },
                {
                    name: "payment_summary_report",
                    lang: "lang.payment_summary_report",
                    component: "payment-summary-reports"
                },
                {
                    name: "yearly_sales_chart",
                    lang: "lang.sales_statistics",
                    component: "yearly-sales-chart"
                },
                {
                    name: "available_tax_report",
                    lang: "lang.tax",
                    component: "available-tax-report"
                },
                {
                    name: "profit_loss_report",
                    lang: "lang.profit_and_loss",
                    component: "profit-loss-report"
                },
                {
                    name: "customers_summary_report",
                    lang: "lang.customers_summary",
                    component: "customers-summary-reports"
                },
                {
                    name: "suppliers_summary_report",
                    lang: "lang.suppliers_summary",
                    component: "suppliers-summary-reports"
                },
                {
                    name: "sales_and_purchase_report",
                    lang: "lang.sales_and_purchase",
                    component: "sales-and-purchase-reports"
                },
                {
                    name: "adjust_stock_report",
                    lang: "lang.stock_adjustment",
                    component: "adjust-stock-reports"
                },
                {
                    name: "shipment_report",
                    lang: "lang.shipment_report",
                    component: "shipment-reports"
                }
            ],
            isVisible: function(tabName) {
                return this[tabName] == "1";
            },
            isSelectedTab: function(tabName) {
                return tabName === this.selectedTab;
            }
        };
    },
    methods: {
        selectTab: function(tabName, componentName) {
            this.selectedTab = tabName;
            this.componentName = componentName;
        },
        initSelectedTab: function() {
            var instance = this;

            this.tabs.forEach(function(tab) {
                if (!instance.selectedTab && instance.isVisible(tab.name)) {
                    instance.selectTab(tab.name, tab.component);
                }
            });
        }
    },
    mounted() {
        this.initSelectedTab();
        this.loadSales = this.shortCutKeyConversion();

        if (this.tab_name) {
            var instance = this;
            this.tabs.forEach(function(tab) {
                if (tab.name == instance.tab_name) {
                    instance.selectTab(tab.name, tab.component);
                }
            });
        }
    }
};
</script>