<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.users') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button v-if="permission_key == 'manage'"
                        class="btn btn-primary app-color"
                        data-toggle="modal"
                        data-target="#user-invite-modal"
                        @click.prevent="addEditAction('')"
                    >{{ trans('lang.add') }}</button>
                </div>
            </div>
        </div>

        <datatable-component
            class="main-layout-card-content"
            :options="tableOptions"
            :tab_name="tabName"
            :route_name="routeName"
        ></datatable-component>

        <!-- Modal -->
        <div
            class="modal fade"
            id="user-invite-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <invite-user
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :modalID="modalID"
                ></invite-user>
            </div>
        </div>

        <!-- Modal -->
        <div
            class="modal fade"
            id="confirm-admin-enable-disable"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <admin-confirmation-modal
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :confirmModalID="confirmModalID"
                    :message="'be_carefull_make_new_admin'"
                ></admin-confirmation-modal>
            </div>
        </div>
    </div>
</template>

<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

export default {
    extends: axiosGetPost,
    props: ["permission_key"],
    data() {
        return {
            tabName: "users",
            routeName: "settings",
            tableOptions: {
                tableName: "users",
                columns: [
                    {
                        title: "lang.name",
                        key: "full_name",
                        type: "clickable_link",
                        source: "user",
                        uniquefield: "id",
                        sortable: true
                    },
                    {
                        title: "lang.emails",
                        key: "email",
                        type: "text",
                        sortable: true
                    },
                    {
                        title: "lang.role",
                        key: "title",
                        type: "text",
                        sortable: true
                    },
                    (
                        this.permission_key === 'manage' ?
                        {
                            title: "lang.action",
                            type: "component",
                            key: "action",
                            componentName: "userlist-action-component",
                            modifier: function(value) {
                                return !value;
                            }
                        } : {}
                    )
                ],
                source: "/users-list",
                search: true,
                right_align: "action"
            },

            modalID: "#user-invite-modal",
            confirmModalID: "#confirm-admin-enable-disable"
        };
    },
    mounted() {
        let instance = this;

        this.$hub.$on("changeUserRole", function(id) {
            instance.addEditAction(id);
        });

        this.modalCloseAction(this.modalID);
        this.modalCloseAction(this.confirmModalID);

        this.$hub.$on("disableEnableUser", function(id, status) {
            instance.disableEnableUser(id, status);
        });

        this.$hub.$on("makeNewAdmin", function(id) {
            instance.addEditAction(id);
        });
    },
    methods: {
        disableEnableUser(id, status) {
            this.deleteActionPreLoader(false);
            this.postDataMethod("/enable-disable-user/" + id, {
                status: status
            });
        },
        postDataThenFunctionality(response) {
            let instance = this;
            instance.$hub.$emit("reloadDataTable");
        },
        postDataCatchFunctionality(error) {
            this.showErrorAlert(error.data.message);
        },
        confirmationModalButtonAction() {
            this.updateData(
                "make-admin-user/" + this.deleteID,
                this.updateIndex
            );
        }
    }
};
</script>
