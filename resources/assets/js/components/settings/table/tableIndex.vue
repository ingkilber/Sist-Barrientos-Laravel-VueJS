<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.restaurant_tables') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button
                        v-if="permission_key == 'manage'"
                        class="btn btn-primary app-color"
                        data-toggle="modal"
                        data-target="#restaurant-table-add-edit-modal"
                        @click.prevent="addEditAction('')"
                    >{{ trans('lang.add') }}</button>
                </div>
            </div>
        </div>

        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <div class="main-layout-card-content">
                <datatable-component :options="tableOptions"></datatable-component>
            </div>
        </span>

        <!-- Modal -->
        <div
            class="modal fade"
            id="restaurant-table-add-edit-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <tables-details
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :modalID="modalID"
                ></tables-details>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal
            id="confirm-delete"
            :message="'table_deleted_permanently'"
            :firstButtonName="'yes'"
            :secondButtonName="'no'"
            @confirmationModalButtonAction="confirmationModalButtonAction"
        ></confirmation-modal>
    </div>
</template>

<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

export default {
    props: ["file", "permission_key"],
    extends: axiosGetPost,
    data() {
        return {
            modalID: "#restaurant-table-add-edit-modal",
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value) ? true : false;
            }
        };
    },
    mounted() {
        let instance = this;
        instance.getData();

        this.$hub.$on("tableAddEdit", function(id) {
            instance.addEditAction(id);
        });

        this.modalCloseAction(this.modalID);
    },
    methods: {
        getActiveAttributeModal(isActive) {
            this.isActiveAttributeModal = isActive;
        },
        getData() {
            let instance = this;
            instance.axiosGet(
                "/restaurant-branch-list",
                function(response) {
                    if (response.data) {
                        let branches = [
                            { text: "All", value: "all", selected: true },
                            ...response.data
                        ];
                        instance.tableOptions = {
                            tableName: "sales-summery",
                            columns: [
                                {
                                    title: "lang.table_name",
                                    key: "name",
                                    type: "text",
                                    sortable: true
                                },
                                {
                                    title: "lang.branch",
                                    key: "branchName",
                                    type: "text",
                                    sortable: true
                                },
                                (
                                    instance.permission_key == 'manage' ?
                                    {title: 'lang.action', type: 'component', key:'action', componentName: 'restaurant-table-action-component'} : {}
                                )
                            ],
                            search: true,
                            right_align: "action",
                            source: "/get-table-list",
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

                function(error) {
                    instance.setPreLoader(true);
                }
            );
        },
        confirmationModalButtonAction() {
            this.deleteDataMethod(
                "/delete-table/" + this.deleteID,
                this.deleteIndex
            );
        }
    }
};
</script>