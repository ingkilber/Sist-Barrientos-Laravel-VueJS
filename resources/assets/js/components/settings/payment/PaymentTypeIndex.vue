<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.payment_types') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button
                        v-if="permission_key == 'manage'"
                        class="btn btn-primary app-color"
                        data-toggle="modal"
                        data-target="#payment-add-edit-modal"
                        @click.prevent="addEditAction('')"
                    >{{ trans('lang.add') }}</button>
                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>

        <!-- Modal -->
        <div
            class="modal fade"
            id="payment-add-edit-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <payment-type-details
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :allPaymentTypes="allPaymentTypes"
                    :modalID="modalID"
                ></payment-type-details>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal
            id="confirm-delete"
            :message="'this_payment_method_deleted_permanently'"
            :firstButtonName="'yes'"
            :secondButtonName="'no'"
            @confirmationModalButtonAction="confirmationModalButtonAction"
        ></confirmation-modal>
    </div>
</template>
<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

export default {
    extends: axiosGetPost,
    props: ["permission_key"],
    data() {
        return {
            allPaymentTypes: [],
            tableOptions: {
                tableName: "payment_types",
                columns: [
                    {
                        title: "lang.type",
                        key: "name",
                        type: "text",
                        sortable: true
                    },
                    (this.permission_key == "manage"
                        ? {
                              title: "lang.action",
                              type: "component",
                              key: "action",
                              componentName: "payment-action-component"
                          }
                        : {})
                ],
                source: "/payment-list",
                search: false,
                right_align: "action"
            },
            modalID: "#payment-add-edit-modal"
        };
    },

    mounted() {
        let instance = this;

        instance.$hub.$on("dataRowsForTable", function(datarows) {
            instance.allPaymentTypes = datarows;
        });

        this.$hub.$on("paymentAddEdit", function(id) {
            instance.addEditAction(id);
        });

        this.modalCloseAction(this.modalID);
    },

    methods: {
        confirmationModalButtonAction() {
            this.deleteDataMethod(
                "/delete-payment/" + this.deleteID,
                this.deleteIndex
            );
        }
    }
};
</script>