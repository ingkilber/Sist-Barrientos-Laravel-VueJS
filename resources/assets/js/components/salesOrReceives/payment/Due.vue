<template>
    <div>
        <div v-if="!hasData(tableOptions)">
            <pre-loader/>
        </div>
        <div v-else>
            <div class="main-layout-card-header-with-button">
                <div class="main-layout-card-content-wrapper">
                    <div class="main-layout-card-header-contents">
                        <h5 class="m-0">{{ trans('lang.sales_list') }}</h5>
                    </div>
                </div>
            </div>
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 exportFileName="sales"
                                 @resetStatus="resetExportValue"/>


            <div class="modal fade"
                 id="due-amount-edit-modal"
                 tabindex="-1"
                 role="dialog"
                 aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                    <cart-due-payment class="modal-content"
                                      v-if="isActive"
                                      :rowdata="selectedItemId"
                                      :orderType="order_type"
                                      :modalID="modalID"
                                      :modalTitle="trans('lang.due_total')"
                                      :pre_loader="dueModalPreloader"
                                      @cartItemsToStore="cartItemsToStore"
                                      @emitForIsActive="emitForIsActive"/>
                </div>
            </div>
        </div>
        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete" :message = "'sales_list_deleted_permanently'" :firstButtonName="'yes'" :secondButtonName = "'no'" @confirmationModalButtonAction = "confirmationModalButtonAction"></confirmation-modal>
    </div>
</template>
<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        props: ["branch_id"],
        extends: axiosGetPost,
        data() {
            return {
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: "",
                modalID: "#due-amount-edit-modal",
                order_type: "sales",
                hidePreLoader: false,
                exportToVue: false,
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tableOptions: {},
                dueModalPreloader: true,
                hasData: value => {
                    return !_.isEmpty(value) ? true : false;
                }
            };
        },
        watch: {
            branch_id: function (value) {
                this.$hub.$emit("reloadDataTable");
            }
        },
        created() {
            this.getDue();
        },
        mounted() {
            let instance = this;
            $("#attributes-add-edit-modal").on("hidden.bs.modal", function (e) {
                instance.isActiveAttributeModal = false;
                $("body").addClass("modal-open");
            });

            this.$hub.$on("viewSalesReportEdit", function (rowdata) {
                instance.addEditAction(rowdata);
            });

            $("#branch-or-cash-register-select-modal").on(
                "hidden.bs.modal",
                function (e) {
                    instance.$emit("resetBranchAndCashRegisterModal");
                }
            );
        },
        methods: {
            getDue() {
                let instance = this;
                instance.axiosGet(
                    "/sales-due-filter",
                    function (response) {
                        if (response.data) {
                            /*Appending cash register static value(All) with dynamic cash register title from db*/
                            let customers = [
                                {text: "All", value: "all", selected: true},
                                ...response.data.customers
                            ];
                            instance.tableOptions = {
                                tableName: "products",
                                columns: [
                                    {
                                        title: "lang.invoice_id",
                                        key: "invoice_id",
                                        type: "text",
                                        source: "reports/sales",
                                        sortable: true
                                    },
                                    {
                                        title: "lang.sales_date",
                                        key: "date",
                                        type: "text",
                                        sortable: true
                                    },
                                    {
                                        title: "lang.sales_type_dt",
                                        key: "type",
                                        type: "text",
                                        sortable: true
                                    },
                                    {
                                        title: "lang.sold_by",
                                        key: "created_by",
                                        type: "text",
                                        source: "user",
                                        sortable: true
                                    },
                                    {
                                        title: "lang.sold_to",
                                        key: "customer",
                                        type: "text",
                                        source: "customer",
                                        sortable: true
                                    },
                                    {
                                        title: "lang.status",
                                        key: "payment_status",
                                        type: "text",
                                        sortable: false,
                                    },
                                    {
                                        title: "lang.item_purchased",
                                        key: "item_purchased",
                                        type: "text",
                                        sortable: false
                                    },
                                    {
                                        title: "lang.tax",
                                        key: "tax",
                                        type: "text",
                                        sortable: false
                                    },
                                    {
                                        title: "lang.discount",
                                        key: "discount",
                                        type: "text",
                                        sortable: false
                                    },
                                    {
                                        title: "lang.total",
                                        key: "total",
                                        type: "text",
                                        sortable: false
                                    },
                                    {
                                        title: "lang.due",
                                        key: "due_amount",
                                        type: "text",
                                        sortable: false
                                    },
                                    {
                                        title: "lang.action",
                                        type: "component",
                                        componentName: "sales-list-action-component"
                                    }
                                ],
                                source: "/sales-list-data/" + instance.branch_id,
                                summary: true,
                                search: true,
                                sortedBy: "id",
                                sortedType: "DESC",
                                formatting: [
                                    "total",
                                    "sub_total",
                                    "tax",
                                    "discount",
                                    "due_amount"
                                ],
                                dateFormatting: ["date"],
                                right_align: [
                                    "sub_total",
                                    "item_purchased",
                                    "tax",
                                    "discount",
                                    "total",
                                    "due_amount"
                                ],
                                summation: [
                                    "sub_total",
                                    "item_purchased",
                                    "tax",
                                    "discount",
                                    "total",
                                    "due_amount"
                                ],
                                summationKey: ["invoice_id"],
                                filters: [
                                    {
                                        title: "lang.customer",
                                        key: "customers",
                                        type: "dropdown",
                                        languageType: "raw",
                                        options: customers
                                    },
                                    {title:'lang.payment_type', type:'dropdown',key:'payment_type',options:[
                                            {text: 'lang.all', value: 'all', selected: true},
                                            {text: 'lang.paid', value: 'paid'},
                                            {text: 'lang.due', value: 'due'},
                                        ]
                                    }

                                ]
                            };
                        }
                        instance.setPreLoader(true);
                    },
                    function (response) {
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
                        postData: {cartItemsToStore}
                    },
                    (success, responseData) => {
                        if (success) {
                            //response after then function
                            instance.showSuccessAlert(responseData.message);

                            $(`${this.modalID}`).modal("hide");
                            instance.$hub.$emit("reloadDataTable");
                            instance.hideSalesReturnsPreLoader = true;
                            instance.dueModalPreloader = true;
                        } else {
                            instance.hideSalesReturnsPreLoader = true;
                            instance.dueModalPreloader = true;
                            $(`${this.modalID}`).modal("hide");
                        }
                    }
                );
            },
            emitForIsActive(value) {
                this.isActive = value;
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
            },
            confirmationModalButtonAction()
            {
                this.deleteDataMethod( '/sales/list/delete/' + this.deleteID,this.deleteIndex);
            },
        }
    };
</script>