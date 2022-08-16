<!--suppress ALL -->
<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.customers') }}</h5>
                </div>
                <div v-if="permission !== 'read_only'"
                     class="main-layout-card-header-contents text-right d-flex justify-content-end">
                    <div class="p-1">
                        <button class="btn btn-primary app-color" data-toggle="modal"
                                data-target="#customer-add-edit-modal" @click="addEditAction('')">{{ trans('lang.add')
                            }}
                        </button>
                    </div>
                    <div class="p-1">
                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                              buttonText="export"
                                              v-on:submit="exportStatus"></common-submit-button>
                    </div>
                    <div class="p-1">
                        <button class="btn btn-primary app-color" data-toggle="modal" @click="openModal()">{{
                            trans('lang.import') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--need to show circle loader component until data table gets loaded-->
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 exportFileName="customer"
                                 :tab_name="tabName"
                                 :route_name="routeName"
                                 @resetStatus="resetExportValue"></datatable-component>
        </span>

        <!-- Modal -->
        <div class="modal fade" id="customer-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <customer-create-edit class="modal-content" v-if="isActive" :id="selectedItemId"
                                      :modalID="modalID"></customer-create-edit>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete" :message="'service_deleted_permanently'" :firstButtonName="'yes'"
                            :secondButtonName="'no'"
                            @confirmationModalButtonAction="confirmationModalButtonAction"></confirmation-modal>
        <!-- Modal for import-->
        <import-modal v-if="isImportModalActive" @resetModal="resetModal"
                      :importOptions="importOptions"></import-modal>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        props: ['permission'],
        extends: axiosGetPost,

        data() {
            return {
                receivedData: false,
                tableOptions: {},
                hidePreLoader: false,
                modalID: '#customer-add-edit-modal',
                hasData: value => {
                    return !_.isEmpty(value) ? true : false
                },
                buttonLoader: false,
                isDisabled: false,
                exportToVue: false,
                isImportModalActive: false,
                tabName : 'customers',
                routeName : 'contacts',
            }
        },
        created() {
            this.getCustomerGroups();
        },
        mounted() {

            let instance = this;

            this.$hub.$on('viewCustomerEdit', function (id) {
                instance.addEditAction(id);
            });

            this.modalCloseAction(this.modalID);
        },
        methods: {
            openModal() {
                this.isImportModalActive = true;
                setTimeout(function () {
                    $('#import-modal').modal('show');
                });
                this.importOptions = {
                    title: 'lang.customers',
                    routeToImport: '/import-customer-contacts',
                    requiredColumns: ['FIRST_NAME', 'LAST_NAME', 'EMAIL', 'COMPANY', 'PHONE_NUMBER', 'ADDRESS', 'CUSTOMER_GROUP',],
                    requiredFields: ['FIRST_NAME', 'LAST_NAME'],
                    downloadSample: this.publicPath + "/sample/customer_contacts.xlsx",
                }
            },
            confirmationModalButtonAction() {
                this.deleteDataMethod('/customer/delete/' + this.deleteID, this.deleteIndex);
            },
            getCustomerGroups() {
                let instance = this;
                this.axiosGet('/customer-groups',
                    function (response) {
                        if (response.data) {
                            let customerGroups = [];
                            /*Appending static value(All) with dynamic Filter options from db*/
                            if (response.data.customerGroups) customerGroups = [{
                                text: 'All',
                                value: 'all',
                                selected: true
                            }, ...response.data.customerGroups];

                            instance.tableOptions = {

                                tableName: 'customer',
                                columns: [
                                    {
                                        title: 'lang.name',
                                        key: 'full_name',
                                        type: 'clickable_link',
                                        sortable: true,
                                        source: 'customer',
                                        uniquefield: 'id'
                                    },
                                    {title: 'lang.customer_company', key: 'company', type: 'text', sortable: true},
                                    {title: 'lang.customer_email', key: 'email', type: 'text', sortable: true},
                                    {title: 'lang.phone_number_datatable', key: 'phone_number', type: 'text', sortable: true},

                                    {
                                        title: 'lang.customer_group_dt',
                                        key: 'customer_group_title',
                                        type: 'text',
                                        sortable: false
                                    },
                                    (instance.permission !== 'read_only' ? {
                                        title: 'lang.action',
                                        type: 'component',
                                        componentName: 'customer-action-component'
                                    } : {})
                                ],
                                source: '/customer-list',
                                search: true,
                                filters: [
                                    {
                                        title: 'lang.customer_group',
                                        key: 'customerGroups',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: customerGroups
                                    },
                                ]
                            }
                        }
                    },
                    function (error) {
                        
                    }
                );
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