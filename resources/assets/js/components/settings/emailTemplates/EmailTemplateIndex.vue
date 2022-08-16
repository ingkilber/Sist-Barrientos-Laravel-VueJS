<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.email_templates') }}</h5>
                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>

        <!-- Modal -->
        <div
            class="modal fade"
            id="email-template-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <email-template-list-details
                    class="modal-content"
                    v-if="isActive"
                    :id="selectedItemId"
                    :modalID="modalID"
                    :name="template_name"
                ></email-template-list-details>
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
            templateId: "",
            template_name: "",
            tableOptions: {
                tableName: "email_templates",
                columns: [
                    {
                        title: "lang.title",
                        key: "template_subject",
                        type: "language",
                        sortable: true
                    },
                    (this.permission_key === "manage"
                        ? {
                            title: "lang.action",
                            type: "component",
                            key: "action",
                            componentName: "email-template-action-component"
                        }
                        : {})
                ],
                source: "/template-list",
                right_align: "action"
            },
            modalID: "#email-template-modal"
        };
    },
    mounted() {
        let instance = this;
        instance.$hub.$on("viewTemplateEdit", function (id, title) {
            instance.addEditAction(id);
            instance.template_name = title;
        });

        instance.modalCloseAction(this.modalID);
    }
};
</script>