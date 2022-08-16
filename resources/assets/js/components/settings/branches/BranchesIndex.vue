<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.branches') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button
                        class="btn btn-primary app-color"
                        v-if="permission_key == 'manage'"
                        data-toggle="modal"
                        data-target="#branch-add-edit-modal"
                        @click.prevent="addEditAction('')"
                    >{{ trans('lang.add') }}</button>
                </div>
            </div>
        </div>

        <div class="main-layout-card-content">
            <datatable-component :options="tableOptions"></datatable-component>
        </div>

        <!-- Modal -->
        <div
            class="modal fade"
            id="branch-add-edit-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <branche-details
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :modalID="modalID"
                ></branche-details>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal
            id="confirm-delete"
            :message="'branch_deleted_permanently'"
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
            tableOptions: {
                tableName: "branches",
                columns: [
                    {
                        title: "lang.name",
                        key: "name",
                        type: "text",
                        sortable: true
                    },
                    {
                        title: "lang.branch_type_dt",
                        key: "branch_type",
                        type: "branch_type"
                    },
                    {
                        title: "lang.branch_manager_up",
                        key: "branch_manager",
                        type: "text"
                    },
                    { title: "lang.tax", key: "tax", type: "text" },
                    this.permission_key === "manage"
                        ? {
                              title: "lang.action",
                              type: "component",
                              key: "action",
                              componentName: "branch-action-component"
                          }
                        : {}
                ],
                source: "/branches",
                search: false,
                right_align: "action"
            },
            modalID: "#branch-add-edit-modal"
        };
    },

    mounted() {
        let instance = this;

        this.$hub.$on("branchAddEdit", function(id) {
            instance.addEditAction(id);
        });

        this.modalCloseAction(this.modalID);
    },

    methods: {
        confirmationModalButtonAction(response) {
            this.deleteDataMethod(
                "/delete-branch/" + this.deleteID,
                this.deleteIndex
            );
        }
    }
};
</script>