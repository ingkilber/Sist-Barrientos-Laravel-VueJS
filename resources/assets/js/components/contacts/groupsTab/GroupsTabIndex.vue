<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.customer_groups') }}</h5>
                </div>
                <div v-if="permission !== 'read_only'" class="main-layout-card-header-contents text-right d-flex justify-content-end">
                    <div class="p-1">
                        <button class="btn btn-primary app-color"  data-toggle="modal" data-target="#group-add-edit-modal" @click="addEditAction('')">{{ trans('lang.add') }}</button>
                    </div>
                    <div class="p-1">
                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                              buttonText="export"
                                              v-on:submit="exportStatus"></common-submit-button>
                    </div>

                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content"  :options="tableOptions" :exportData="exportToVue"
                             exportFileName="group" @resetStatus="resetExportValue"></datatable-component>

        <!-- Modal -->
        <div class="modal fade" id="group-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <group-create-edit class="modal-content" v-if="isActive" :id="selectedItemId" :modalID="modalID"></group-create-edit>
            </div>
        </div>

        <confirmation-modal id="confirm-delete" :message = "'service_deleted_permanently'" :firstButtonName="'yes'" :secondButtonName = "'no'" @confirmationModalButtonAction = "confirmationModalButtonAction"></confirmation-modal>


    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        props:['permission'],
        extends: axiosGetPost,

        data() {
            return{

                tableOptions: {
                    tableName: 'groups',
                    columns: [
                        {title: 'lang.title', key: 'title', type: 'text', sortable: true},
                        {title: 'lang.discount', key: 'discount', type: 'text', sortable: true},
                        {title: 'lang.default', key: 'is_default', type: 'text', sortable: true},
                        ( this.permission !== 'read_only'?{title: 'lang.action', type: 'component',key:'action' ,componentName: 'group-action-component'}:{})
                    ],
                    source: '/groups',
                    percentFormatting : ['discount'],
                    search: false,
                    right_align:'action',
                },
                modalID:'#group-add-edit-modal',
                buttonLoader:false,
                isDisabled:false,
                exportToVue: false,
            }
        },

        mounted(){

            let instance = this;

            this.$hub.$on('viewGroup', function (id) {
                instance.addEditAction(id);
            });

            this.modalCloseAction(this.modalID);

        },

        methods:{

            confirmationModalButtonAction()
            {
                this.deleteDataMethod( '/group/delete/' + this.deleteID,this.deleteIndex);
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
        },
    }

</script>