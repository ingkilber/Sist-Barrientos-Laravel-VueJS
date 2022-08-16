<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.product_units') }}</h5>
                </div>
                <div v-if="permission !== 'read_only'" class="main-layout-card-header-contents text-right d-flex justify-content-end">
                    <div class="p-1">
                        <button class="btn btn-primary app-color"  data-toggle="modal" data-target="#unit-add-edit-modal" @click.prevent="addEditAction('')">
                            {{ trans('lang.add') }}
                        </button>
                    </div>
                    <div class="p-1">
                        <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                              buttonText="export"
                                              v-on:submit="exportStatus"></common-submit-button>
                    </div>
                </div>
            </div>
        </div>

        <datatable-component class="main-layout-card-content" :options="tableOptions" :exportData="exportToVue"
                             exportFileName="unit" @resetStatus="resetExportValue"></datatable-component>

        <!-- Modal -->
        <div class="modal fade" id="unit-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <product-units-add-edit-form class="modal-content" v-if="isActive" :id="selectedItemId"addEditAction :modalOptions="modalOptions"></product-units-add-edit-form>
            </div>
        </div>

        <!-- Delete Modal -->
        <confirmation-modal id="confirm-delete"  :message = "'unit_deleted_permanently'" :firstButtonName="'yes'" :secondButtonName = "'no'" @confirmationModalButtonAction = "confirmationModalButtonAction"></confirmation-modal>
    </div>
</template>
<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    var sourceURL = '/products/unit';

    export default {
        props:['permission'],
        extends: axiosGetPost,
        data() {
            return {

                tableOptions: {
                    tableName: 'groups',
                    columns: [
                        {title: 'lang.name', key: 'name', type: 'text', sortable: true},
                        {title: 'lang.short_name', key: 'short_name', type: 'text', sortable: true},
                        ( this.permission !== 'read_only'?{title: 'lang.action', type: 'component',key:'action', componentName: 'product-unit-action-component'}:{})
                    ],
                    source: '/products/units',
                    search: false,
                    right_align:'action',
                },

                modalOptions: {
                    modalID: '#unit-add-edit-modal',
                    addLang: 'lang.add_new_unit',
                    editLang: 'lang.edit_unit',
                    getDataURL:  sourceURL,
                    postDataWithIDURL:  sourceURL,
                    postDataWithoutIDURL:  sourceURL+'/store',
                },
                buttonLoader:false,
                isDisabled:false,
                exportToVue: false,
            }
        },

        mounted(){

            let instance = this;

            this.$hub.$on('attributeAddEdit', function (id) {

                instance.addEditAction(id);
            });

            this.modalCloseAction(this.modalOptions.modalID);
        },

        methods: {

            confirmationModalButtonAction(response)
            {
                this.deleteDataMethod( sourceURL+'/delete/' + this.deleteID,this.deleteIndex);
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
        }
    }

</script>