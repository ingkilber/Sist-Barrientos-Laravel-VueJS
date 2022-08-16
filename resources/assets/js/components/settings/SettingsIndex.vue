<template>
    <div>
        <div class="main-layout-wrapper" v-shortkey="loadSales" @shortkey="globalShortcutMethod()">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.settings')}}</span>
                    </li>
                </ol>
            </nav>
            <div class="container-fluid pr-0 mt-0">
                <div class="row mr-0">
                    <div class="col-sm-12 col-md-12 col-lg-3 col-xl-2 settings-left-card">
                        <div class="main-layout-card">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.settings') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <ul class="list-group list-group-flush" id="settings-list">
                                    <li
                                            class="list-group-item"
                                            :class="{'active-border':isSelectedTab(tab.name)}"
                                            @click.prevent="selectTab(tab)"
                                            v-if="isVisible(tab.name)"
                                            v-for="tab in tabs"
                                    >{{ trans(tab.lang) }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-9 col-xl-10 px-0">
                        <transition name="slide-fade" mode="out-in">
                            <component
                                    v-if="this.componentName"
                                    :permission_key="permissionKey"
                                    :list_data="time_zones"
                                    v-bind:is="this.componentName"
                            ></component>
                        </transition>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from "../../helper/axiosGetPostCommon";

    export default {
        extends: axiosGetPost,
        props: [
            "roles",
            "app_settings",
            "email_settings",
            "email_templates",
            'sms_settings',
            "sms_templates",
            "payment_settings",
            "tax_settings",
            "branches_setting",
            "shipping_area_setting",
            "users",
            "cash_register",
            "invoice_settings",
            "purchase_invoice_settings",
            "invoice_templates",
            "product_settings",
            "adjust_stock_settings",
            "shortcuts_setting",
            "updates_setting",
            "table_setting",
            "tab_name",
            "route_name",
            "time_zones",
            "notification_settings",
            "corn_settings",
            'sales_setting',

        ],
        data() {
            return {
                demo: [],
                selectedTab: null,
                componentName: null,
                permissionKey: null,
                loadSales: [],
                tabs: [
                    {
                        name: "app_settings",
                        lang: "lang.application",
                        component: "application-setting",
                        permission: this.app_settings
                    },
                    {
                        name: "sales_setting",
                        lang: "lang.sales_settings",
                        component: "sales-setting",
                        permission: this.sales_setting
                    },
                    {
                        name: "email_settings",
                        lang: "lang.emails",
                        component: "email-setting",
                        permission: this.email_settings
                    },
                    {
                        name: "email_templates",
                        lang: "lang.email_templates",
                        component: "email-template-list",
                        permission: this.email_templates
                    },
                    {
                        name: "sms_settings",
                        lang: "lang.sms_settings",
                        component: "sms-setting",
                        permission: this.sms_settings
                    },
                    {
                        name: "sms_templates",
                        lang: "lang.sms_templates",
                        component: "sms-template-list",
                        permission: this.sms_templates
                    },
                    {
                        name: "roles",
                        lang: "lang.roles",
                        component: "roles-index",
                        permission: this.roles
                    },
                    {
                        name: "users",
                        lang: "lang.users",
                        component: "user-list",
                        permission: this.users
                    },
                    {
                        name: "tax_settings",
                        lang: "lang.taxes",
                        component: "all-taxes",
                        permission: this.tax_settings
                    },
                    {
                        name: "branches_setting",
                        lang: "lang.branches",
                        component: "branches",
                        permission: this.branches_setting
                    },
                    {
                        name: "shipping_area_setting",
                        lang: "lang.shipping_area",
                        component: "shipping",
                        permission: this.shipping_area_setting
                    },
                    {
                        name: "cash_register",
                        lang: "lang.cash_registers",
                        component: "cash-register",
                        permission: this.cash_register
                    },
                    {
                        name: "table_setting",
                        lang: "lang.restaurant_tables",
                        component: "tables-setting",
                        permission: this.table_setting
                    },
                    {
                        name: "payment_settings",
                        lang: "lang.payment_types",
                        component: "payment-types",
                        permission: this.payment_settings
                    },
                    {
                        name: "invoice_settings",
                        lang: "lang.sales_invoices",
                        component: "invoice-settings",
                        permission: this.invoice_settings
                    },
                    {
                        name: "purchase_invoice_settings",
                        lang: "lang.purchase_invoices",
                        component: "purchase-invoice-settings",
                        permission: this.purchase_invoice_settings
                    },
                    {
                        name: "invoice_templates",
                        lang: "lang.invoice_templates",
                        component: "invoice-template-list",
                        permission: this.invoice_templates
                    },
                    {
                        name: "product_settings",
                        lang: "lang.products",
                        component: "product-settings",
                        permission: this.product_settings
                    },
                    {
                        name: "adjust_stock_settings",
                        lang: "lang.adjust_stock",
                        component: "adjust-stock-setting",
                        permission: this.adjust_stock_settings
                    },
                    {
                        name: "notification_settings",
                        lang: "lang.notification",
                        component: "notification-setting",
                        permission: this.notification_settings
                    },
                    this.lowStockNotifation == "1"
                        ? {
                            name: "corn_settings",
                            lang: "lang.corn_job",
                            component: "corn-job-setting",
                            permission: this.corn_settings
                        }
                        : {},
                    {
                        name: "updates_setting",
                        lang: "lang.updates",
                        component: "updates-setting",
                        permission: this.updates_setting
                    }
                ],
                isVisible: function (tabName) {
                    if (tabName != undefined) {
                        return (
                            this[tabName] != 0 &&
                            (this[tabName] == "manage" ||
                                this[tabName] == "read_only")
                        );
                    } else {
                        return false;
                    }
                },
                isSelectedTab: function (tabName) {
                    return tabName === this.selectedTab;
                }
            };
        },
        methods: {
            selectTab: function (tab) {
                this.selectedTab = tab.name;
                this.componentName = tab.component;
                this.permissionKey = tab.permission;
            },
            initSelectedTab: function () {
                let instance = this;
                this.tabs.forEach(function (tab) {
                    if (!instance.selectedTab && instance.isVisible(tab.name)) {
                        instance.selectTab(tab);
                    }
                });
            }
        },
        mounted() {
            this.initSelectedTab();
            this.loadSales = this.shortCutKeyConversion();

            if (this.tab_name && this.isVisible(this.tab_name)) {
                var instance = this;
                this.tabs.forEach(function (tab) {
                    if (tab.name == instance.tab_name) {
                        instance.selectTab(tab);
                    }
                });
            }
        }
    };
</script>
