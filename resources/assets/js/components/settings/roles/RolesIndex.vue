<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.roles') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button v-if="permission_key == 'manage'" class="btn btn-primary app-color"  data-toggle="modal" data-target="#role-add-edit-modal" @click.prevent="addEditAction('')">
                        {{ trans('lang.add') }}
                    </button>
                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>

        <!-- Modal -->
        <div class="modal fade" id="role-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <roles-details class="modal-content" v-if="isActive" :id="selectedItemId" :modalID="modalID"></roles-details>
            </div>
        </div>
        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete" :message = "'role_deleted_permanently'" :firstButtonName="'yes'" :secondButtonName = "'no'" @confirmationModalButtonAction = "confirmationModalButtonAction"></confirmation-modal>

    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {

                tableOptions: {
                    tableName: 'roles',
                    columns: [
                        {title: 'lang.title', key: 'title', type: 'text', sortable: true, },
                        ( this.permission_key == 'manage' ? {title: 'lang.action', type: 'component', key:'action' ,componentName: 'roles-action-component'} : {})
                    ],
                    source: '/roles-list',
                    right_align:'action',
                },
                modalID:'#role-add-edit-modal',
            }
        },

        mounted(){

            let instance = this;

            instance.$hub.$on('roleAddEdit', function (id) {
                instance.addEditAction(id);
            });

            instance.modalCloseAction(instance.modalID);

        },
        methods: {

            confirmationModalButtonAction()
            {
                let instance=this;
                instance.deleteDataMethod( '/delete-role/' + instance.deleteID,instance.deleteIndex);
            },
        },
    }
</script>