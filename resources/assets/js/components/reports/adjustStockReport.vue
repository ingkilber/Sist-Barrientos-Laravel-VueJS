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
                        <h5 class="m-0">{{ trans('lang.stock_adjustment') }}</h5>
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
                exportFileName="adjust_stock"
                @resetStatus="resetExportValue"
            ></datatable-component>

            <!-- Modal -->
            <div
                class="modal fade"
                id="due-amount-edit-modal"
                tabindex="-1"
                role="dialog"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                    <cart-due-payment
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :orderType="order_type"
                        :modalID="modalID"
                        :modalTitle="trans('lang.due_total')"
                        @cartItemsToStore="cartItemsToStore"
                    ></cart-due-payment>
                </div>
            </div>
        </span>
    </div>
</template>
<script>
import axiosGetPost from "../../helper/axiosGetPostCommon";
export default {
    props: ["permission"],
    extends: axiosGetPost,
    data() {
        return {
            isActive: false,
            isActiveAttributeModal: false,
            selectedItemId: "",
            order_type: "sales",
            hidePreLoader: false,
            exportToVue: false,
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            tabName: "adjust_stock_report",
            routeName: "reports",
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value);
            }
        };
    },
    created() {
        this.getSalesReport();
    },
    mounted() {
        let instance = this;

        this.modalCloseAction(this.modalID);

        $("#attributes-add-edit-modal").on("hidden.bs.modal", function(e) {
            instance.isActiveAttributeModal = false;
            $("body").addClass("modal-open");
        });

        this.$hub.$on("viewSalesReportEdit", function(rowdata) {
            instance.addEditAction(rowdata);
        });
    },
    methods: {
        getSalesReport() {
            let instance = this;
            instance.axiosGet(
                "/adjustment-report-filter",
                function(response) {
                    if (response.data) {
                        /*Appending cash register static value(All) with dynamic cash register title from db*/
                        let branches = [
                                { text: "All", value: "all", selected: true },
                                ...response.data.branches
                            ],
                            products = [
                                { text: "All", value: "all", selected: true },
                                ...response.data.products
                            ],
                            adjustmentTypes = [
                                { text: "All", value: "all", selected: true },
                                ...response.data.adjustmentTypes
                            ];

                        instance.tableOptions = {
                            tableName: "products",
                            columns: [
                                {
                                    title: "lang.sales_date",
                                    key: "date",
                                    type: "text",
                                    sortable: true
                                },
                                {
                                    title: "lang.product_name",
                                    key: "product_name",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.variant_title",
                                    key: "variant_title",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.adjustment_item",
                                    key: "adjustment_item",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.branch_name",
                                    key: "branch_name",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.adjustment_type",
                                    key: "adjustment_type",
                                    type: "text",
                                    sortable: false
                                },
                                {
                                    title: "lang.sold_by",
                                    key: "created_by",
                                    type: "clickable_link",
                                    source: "user",
                                    uniquefield: "user_id",
                                    sortable: false
                                }
                            ],
                            source: "/adjust-stock-report",
                            summary: false,
                            search: false,
                            sortedBy: "",
                            sortedType: "DESC",
                            formatting: [],
                            dateFormatting: ["date"],
                            right_align: ["adjustment_item"],
                            summation: [],
                            summationKey: [],
                            filters: [
                                {
                                    title: "lang.date_range",
                                    key: "date_range",
                                    type: "date_range"
                                },
                                {
                                    title: "lang.product",
                                    key: "product_name",
                                    type: "dropdown",
                                    languageType: "raw",
                                    options: products
                                },
                                {
                                    title: "lang.branch",
                                    key: "branch_name",
                                    type: "dropdown",
                                    languageType: "raw",
                                    options: branches
                                },
                                {
                                    title: "lang.adjustment_type_label",
                                    key: "adjustment_type",
                                    type: "dropdown",
                                    languageType: "raw",
                                    options: adjustmentTypes
                                }
                            ]
                        };
                    }
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                }
            );
        },
        cartItemsToStore(cartItemsToStore) {
            let instance = this;
            instance.hideSalesReturnsPreLoader = false;
            cartItemsToStore.paymentType = "credit";

            instance.axiosGETorPOST(
                {
                    url: "/save-due-amount",
                    postData: { cartItemsToStore }
                },
                (success, responseData) => {
                    if (success) {
                        //response after then function
                        instance.hideSalesReturnsPreLoader = true;
                        instance.showSuccessAlert(responseData.message);

                        $(`${this.modalID}`).modal("hide");
                        instance.$hub.$emit("reloadDataTable");
                    } else {
                        instance.hideSalesReturnsPreLoader = true;
                        $(`${this.modalID}`).modal("hide");
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
};
</script>