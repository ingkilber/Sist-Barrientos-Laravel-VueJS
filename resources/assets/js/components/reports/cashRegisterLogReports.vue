<template>
    <div>
        <span v-if="!hasData(tableOptions)">
            <pre-loader></pre-loader>
        </span>
        <span v-else>
             <!--Export Button-->
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.register_logs') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                          :isActiveText="isActiveText" buttonText="export"
                                          v-on:submit="exportStatus"></common-submit-button>
                </div>
            </div>
        </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 :exportFileName="trans('lang.register_logs')"
                                 @resetStatus="resetExportValue"
                                 :tab_name="tabName"
                                 :route_name="routeName">

            </datatable-component>
        </span>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {

        extends: axiosGetPost,

        data() {
            return {
                tabnameActive:'cashRegister',
                exportToVue:false,
                isActive: false,
                isActiveAttributeModal: false,
                selectedItemId: '',
                hidePreLoader: false,
                tableOptions: {},
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                tabName:'register_report',
                routeName:'reports',
                hasData: value => {
                    return !_.isEmpty(value);
                }
            }
        },
        created(){
            this.getBranchName();
        },
        mounted(){
            let instance = this;
            this.modalCloseAction(this.modalID);
            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e)
            {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });
        },
        methods: {
            getActiveAttributeModal(isActive)
            {
                this.isActiveAttributeModal = isActive;
            },
            getBranchName()
            {
                let instance = this;
                instance.axiosGet('/cash-register-for-filter/',
                    function(response)
                    {
                        if(response.data){
                            /*Appending cash register static value(All) with dynamic cash register title from db*/
                            let cashRegisters = [{text: 'All', value: 'all', selected: true},...response.data.cashRegisters],
                            users = [{text: 'All', value: 'all', selected: true},...response.data.user],
                            branches = [{text: 'All', value: 'all', selected: true},...response.data.branch];
                            instance.tableOptions = {
                                tableName: 'cash_register_logs',
                                columns: [
                                    {title: 'lang.register_log_id',     key: 'id',              type: 'text',            sortable: true},
                                    {title: 'lang.register_title',      key: 'title',           type: 'text',            sortable: true},
                                    {title: 'lang.opened_by',           key: 'opened_by',       type: 'clickable_link',  source: 'user',  uniquefield: 'open_user', sortable: true},
                                    {title: 'lang.closed_by',           key: 'closed_user',     type: 'clickable_link',  source: 'user',  uniquefield: 'closed_by', sortable: true},
                                    {title: 'lang.log_status',          key: 'status',          type: 'text',            sortable: true},
                                    {title: 'lang.note',                key: 'note',            type: 'text',            sortable: false},
                                    {title: 'lang.opening_amount',      key: 'opening_amount',  type: 'text',            sortable: true},
                                    {title: 'lang.cash_sales',          key: 'cash_sales',      type: 'text',            sortable: false},
                                    {title: 'lang.cash_receives',       key: 'cash_receives',   type: 'text',            sortable: false},
                                    {title: 'lang.closing_amount',      key: 'closing_amount',  type: 'text',            sortable: true},
                                    {title: 'lang.difference',          key: 'difference',      type: 'text',            sortable: false},
                                ],
                                source: '/register-log-reports',
                                formatting : ['opening_amount','closing_amount','cash_receives','cash_sales','difference'],
                                right_align: ['opening_amount','closing_amount','cash_receives','cash_sales','difference'],
                                zero_format:true,
                                search: true,
                                sortedBy:'id',
                                sortedType:'DESC',
                                filters: [
                                    {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                    {title: 'lang.employee', key: 'employee', type: 'dropdown', languageType: "raw", options: users},
                                    {title: 'lang.branch', key: 'branch', type: 'dropdown', languageType: "raw", options: branches},
                                    /*dropdown filter for status*/
                                    {title: 'lang.log_status', key: 'status', type: 'dropdown', options: [
                                            {text: 'lang.all',      value: 'all', selected: true},
                                            {text: 'lang.open',     value: 'open'},
                                            {text: 'lang.closed',   value: 'closed'},
                                        ]},
                                    /*dropdown filter for cash register (dynamic value from db)*/
                                    {title: 'lang.cash_register_label', key: 'cashRegisterName', type: 'dropdown', languageType: "raw", options: cashRegisters},
                                ]
                            }
                        }

                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
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
            }
        }
    }

</script>