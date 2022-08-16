<template>
    <div class="side-bar custom-scrollbar">
        <ul class="m-0">
            <li class="side-bar-logo">
                <a :href="publicPath+'/dashboard'" :class="{disabled:!isConnected && offline == 1}">
                    <img :src="publicPath+'/uploads/logo/'+applogo" alt />
                </a>
            </li>
            <li
                :class="{'active-side-bar': currentUrl == publicPath+'/dashboard', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/dashboard'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-desktop"></i>
                    <div>{{ trans('lang.dashboard') }}</div>
                </a>
            </li>
            <li
                v-if="customers == 'manage' || customer_group == 'manage' || suppliers == 'manage' ||customers == 'read_only' || customer_group == 'read_only' || suppliers == 'read_only' "
                :class="{'active-side-bar': currentUrl == publicPath+'/contacts', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/contacts'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-users"></i>
                    <div>{{ trans('lang.contacts') }}</div>
                </a>
            </li>
            <li
                v-if="products=='manage' || product_category == 'manage' || product_brand == 'manage' || product_group== 'manage' || variant_attributes == 'manage' || units=='manage' || products=='read_only' || product_category == 'read_only' || product_brand == 'read_only' || product_group== 'read_only' || variant_attributes == 'read_only' || units=='read_only'"
                :class="{'active-side-bar': currentUrl == publicPath+'/products', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/products'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-share-alt"></i>
                    <div>{{ trans('lang.products') }}</div>
                </a>
            </li>
            <li
                v-if="sales==1"
                :class="{'active-side-bar': currentUrl == publicPath+'/sales', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/sales'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-credit-card"></i>
                    <div>{{ trans('lang.sales') }}</div>
                </a>
            </li>
            <li
                v-if="receives==1"
                :class="{'active-side-bar': currentUrl == publicPath+'/receives', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/receives'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-truck"></i>
                    <div>{{ trans('lang.receives') }}</div>
                </a>
            </li>
            <li
                v-if="isReportActive"
                :class="{'active-side-bar': currentUrl == publicPath+'/reports', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/reports'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-pie-chart"></i>
                    <div>{{ trans('lang.reports') }}</div>
                </a>
            </li>
            <li
                v-if="isSettingActive"
                :class="{'active-side-bar': currentUrl == publicPath+'/settings', 'active-hover': isConnected || offline == 0}"
            >
                <a :href="publicPath+'/settings'" :class="{disabled:!isConnected && offline == 1}">
                    <i class="la la-gear"></i>
                    <div>{{ trans('lang.settings') }}</div>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import axiosGetPost from "../../helper/axiosGetPostCommon";
export default {
    props: [
        "route",
        "sales",
        "receives",
        "products",
        "product_category",
        "product_brand",
        "product_group",
        "units",
        "variant_attributes",

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
        "profit_loss_report",
        "available_tax_report",
        "available_stock_chart",
        "customers_summary_report",
        "suppliers_summary_report",
        "sales_and_purchase_report",
        "adjust_stock_report",

        "customers",
        "customer_group",
        "suppliers",

        "applogo",
        "appsettings",
        "emailsettings",
        "emailtemplate",
        "tax_settings",
        "payment_settings",
        "sales_channels",
        "branches_setting",
        "shipping_area_setting",
        "invoice_settings",
        "purchase_invoice_settings",
        "users",
        "roles",
        "cash_register",

        "table_setting",
        "updates_setting",
        "adjust_stock_settings",
        "notification_settings",
        "product_settings",
        "corn_settings"
    ],
    extends: axiosGetPost,
    data() {
        return {
            servicePermissions: 0,
            currentUrl: "",
            isSettingActive: true,
            isReportActive: true
        };
    },
    mounted() {
        let instance = this;
        instance.currentUrl = window.location.href;
        instance.checkUrl();
        instance.settingMenu();
        instance.reportMenu();
    },
    methods: {
        checkUrl() {
            this.currentUrl = this.currentUrl.split("?")[0];
        },
        settingMenu() {
            this.isSettingActive = this.appsettings != 0 ||
                this.table_setting != 0 ||
                this.updates_setting != 0 ||
                this.adjust_stock_settings != 0 ||
                this.product_settings != 0 ||
                this.emailsettings != 0 ||
                this.emailtemplate != 0 ||
                this.tax_settings != 0 ||
                this.payment_settings != 0 ||
                this.sales_channels != 0 ||
                this.branches_setting != 0 ||
                this.shipping_area_setting != 0 ||
                this.roles != 0 ||
                this.users != 0 ||
                this.cash_register != 0 ||
                this.notification_settings != 0 ||
                this.corn_settings != 0 ||
                this.invoice_settings != 0 ||
                this.purchase_invoice_settings != 0;
        },
        reportMenu() {
            this.isReportActive = this.sales_report == 1 ||
                this.sales_summary_reports == 1 ||
                this.receiving_report == 1 ||
                this.receiving_summary == 1 ||
                this.register_report == 1 ||
                this.inventory_report == 1 ||
                this.payment_report == 1 ||
                this.payment_summary_report == 1 ||
                this.yearly_sales_chart == 1 ||
                this.available_stock_chart == 1 ||
                this.available_tax_report == 1 ||
                this.profit_loss_report == 1 ||
                this.customers_summary_report == 1 ||
                this.suppliers_summary_report == 1 ||
                this.sales_and_purchase_report == 1 ||
                this.adjust_stock_report == 1 ||
                this.sales_details_report == 1;
        }
    }
};
</script>
