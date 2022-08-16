<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.cash_registers') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <button v-if="permission_key == 'manage'" class="btn btn-primary app-color"  data-toggle="modal" data-target="#cash-register-add-edit-modal" @click.prevent="addEditAction('')">
                        {{ trans('lang.add') }}
                    </button>
                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>

        <!-- Modal -->
        <div class="modal fade" id="cash-register-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <cash-register-add-edit class="modal-content" v-if="isActive" :id="selectedItemId" :modalID="modalID"></cash-register-add-edit>
            </div>
        </div>
        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete" :message = "'cash_register_deleted_permanently'" :firstButtonName="'yes'" :secondButtonName = "'no'" @confirmationModalButtonAction = "confirmationModalButtonAction"></confirmation-modal>

    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return{

                tableOptions: {
                    tableName: 'cash_registers',
                    columns: [
                        {title: 'lang.title', key: 'title', type: 'text', sortable: true},
                        {title: 'lang.branch', key: 'branch_name', type: 'text', sortable: true},
                        (this.permission_key == 'manage' ? {title: 'lang.action', type: 'component',key:'action' ,componentName: 'cash-register-action-component'} : {} )
                    ],
                    source: '/cash-registers',
                    search: false,
                    right_align:'action'
                },
                modalID:'#cash-register-add-edit-modal',
            }
        },
        mounted(){

            let instance = this;

            this.$hub.$on('cashRegisterAddEdit', function (id) {
                instance.addEditAction(id);
            });

            this.modalCloseAction(this.modalID);

        },

        methods:{

            confirmationModalButtonAction()
            {
                this.deleteDataMethod( '/delete-register/' + this.deleteID,this.deleteIndex);
            },
        },
    }
</script>