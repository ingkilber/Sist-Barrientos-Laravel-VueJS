<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.suppliers') }}</h5>
                </div>
                <div v-if="permission !== 'read_only'" class="main-layout-card-header-contents text-right d-flex justify-content-end">
                    <div class="p-1">
                        <button class="btn btn-primary app-color"  data-toggle="modal" data-target="#supplier-add-edit-modal" @click="addEditAction('')">{{ trans('lang.add') }}</button>
                    </div>
                    <div class="p-1">
                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                              buttonText="export"
                                              v-on:submit="exportStatus"></common-submit-button>
                    </div>
                    <div class="p-1">
                        <button class="btn btn-primary app-color"  data-toggle="modal" @click="openModal()">{{ trans('lang.import') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!--need to show circle loader component until data table gets loaded-->
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <datatable-component class="main-layout-card-content" :options="tableOptions" :exportData="exportToVue"
                                 exportFileName="supplier" @resetStatus="resetExportValue"
                                 :tab_name="tabName"
                                 :route_name="routeName"></datatable-component>
        </span>

        <!-- Modal -->
        <div class="modal fade" id="supplier-add-edit-modal"  role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <supplier-create-edit class="modal-content" v-if="isActive" :id="selectedItemId" :modalID="modalID"></supplier-create-edit>
            </div>
        </div>
        <!-- Modal for import-->
        <import-modal v-if="isImportModalActive" @resetModal="resetModal"
                      :importOptions="importOptions"></import-modal>
        <!-- Delete Modal -->
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
                tabName : 'suppliers',
                routeName : 'contacts',
                receivedData: false,
                tableOptions : {

                    tableName: 'supplier',
                    columns: [
                        {title: 'lang.name',                key: 'name',            type: 'clickable_link',     sortable: true,     source:'supplier',   uniquefield:'id'},
                        {title: 'lang.customer_company',    key: 'company',         type: 'text',               sortable: true},
                        {title: 'lang.customer_email',      key: 'email',           type: 'text',               sortable: true},
                        {title: 'lang.phone_number_datatable', key: 'phone_number',    type: 'text',               sortable: true},
                        {title: 'lang.address',             key: 'address',         type: 'text',               sortable: true},
                        ( this.permission !== 'read_only'?{title: 'lang.action',    type: 'component',          key: 'action',      componentName: 'supplier-action-component'}:{})
                    ],
                    source: '/supplier-list',
                    right_align:'action',
                },
                hidePreLoader: false,
                modalID:'#supplier-add-edit-modal',
                hasData: value => {
                    return !_.isEmpty(value) ? true : false
                },
                buttonLoader:false,
                isDisabled:false,
                exportToVue: false,
                isImportModalActive:false,
            }
        },
        created(){

        },

        mounted(){

            let instance = this;

            this.$hub.$on('viewSupplierEdit', function (id) {
                instance.addEditAction(id);
            });

            this.modalCloseAction(this.modalID);

        },

        methods:{
            openModal() {
                this.isImportModalActive = true;
                setTimeout(function () {
                    $('#import-modal').modal('show');
                });

                this.importOptions = {
                    title: 'lang.suppliers',
                    routeToImport: '/import-supplier-contacts',
                    requiredColumns: ['FIRST_NAME', 'LAST_NAME', 'EMAIL', 'COMPANY','PHONE_NUMBER','ADDRESS'],
                    requiredFields: ['FIRST_NAME', 'LAST_NAME', 'EMAIL'],
                    downloadSample: this.publicPath + "/sample/supplier_contacts.xlsx",
                }
            },

            confirmationModalButtonAction()
            {
                this.deleteDataMethod( '/supplier/delete/' + this.deleteID,this.deleteIndex);
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
            resetModal() {
                this.isImportModalActive = false;
            },


        },
    }

</script>