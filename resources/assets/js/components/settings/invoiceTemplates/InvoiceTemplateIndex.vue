<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.invoice_templates') }}</h5>
                </div>

                <div class="main-layout-card-header-contents text-right">
                    <button v-if="permission_key === 'manage'" class="btn btn-primary app-color" data-toggle="modal"
                            data-target="#invoice-template-add-edit-modal" @click.prevent="addEditAction('')">
                        {{ trans('lang.add') }}
                    </button>
                </div>
            </div>
        </div>


        <datatable-component class="main-layout-card-content" :options="tableOptions"></datatable-component>

        <!-- Modal -->
        <div class="modal fade" id="invoice-template-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <invoice-add-edit class="modal-content" v-if="isActive"
                                  :id="selectedItemId"
                                  :modalID="modalID"
                                  :all_invoice_templates="all_invoice_templates"
                >

                </invoice-add-edit>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {
                all_invoice_templates:'',
                templateId: '',
                tableOptions: {
                    tableName: 'invoice_templates',
                    columns: [
                        {title: 'lang.title', key: 'template_title', type: 'language', sortable: true},
                        (
                            this.permission_key === 'manage' ?
                            {title: 'lang.action', key: 'action', type: 'component', componentName: 'invoice-template-action-component'} : {}
                        )
                    ],
                    source: '/invoice-templates',
                    right_align: 'action'
                },
                modalID: '#invoice-template-add-edit-modal',
            }
        },
        mounted() {
            let instance = this;
            instance.$hub.$on('viewTemplateEdit', function (id) {
                instance.addEditAction(id);
                $("#invoice-template-add-edit-modal").modal()
            });

            $("#invoice-template-add-edit-modal").on('hidden.bs.modal', function (e) {
                instance.isActive = false;
            });
        },
        created(){
            this.getAllInvoices('/allInvoice')
        },
        methods:{
            getAllInvoices(route){
                let instance = this;
                this.axiosGet(route,
                    function (response) {
                        instance.all_invoice_templates = response.data.invoice_template;
                    },
                );
            }
        }
    }
</script>