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
                        <h5 class="m-0">{{ trans('lang.taxes') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                       <common-submit-button :buttonLoader="buttonLoader"
                                             :isDisabled="isDisabled"
                                             :isActiveText="isActiveText"
                                             buttonText="export"
                                             v-on:submit="exportStatus">
                       </common-submit-button>
                    </div>
                </div>
            </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content"
                                 :options="tableOptions"
                                 :exportData="exportToVue"
                                 :exportFileName="trans('lang.taxes')"
                                 @resetStatus="resetExportValue">
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
                exportToVue: false,
                isActive: false,
                selectedItemId: '',
                tableOptions: {},
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                hasData: value => {
                    return !_.isEmpty(value)
                },
            }
        },
        created() {
            let instance = this;
            instance.getData();
        },
        methods: {

            getData() {
                let instance = this;
                instance.axiosGet('/branch-list',
                    function (response) {
                        if (response.data) {
                            let branches = [{text: 'All', value: 'all', selected: true}, ...response.data];
                            instance.tableOptions = {
                                tableName: 'taxes',
                                columns: [
                                    {title: 'lang.invoice_id', key: 'invoice_id', type: 'text', sortable: true},
                                    {title: 'lang.tax_date', key: 'date', type: 'text', sortable: true},
                                    {title: 'lang.type', key: 'order_type', type: 'text', sortable: true},
                                    {title: 'lang.branch', key: 'name', type: 'text', sortable: true},
                                    {title: 'lang.total', key: 'total', type: 'text', sortable: true},
                                    {title: 'lang.tax', key: 'tax', type: 'text', sortable: true},
                                ],
                                source: '/tax-report',
                                summary: true,
                                search:true,
                                formatting : ['total', 'tax'],
                                dateFormatting : ['date'],
                                summation:['total', 'tax'],
                                sortedBy:'id',
                                sortedType:'DESC',
                                summationKey:['invoice_id'],
                                right_align: ['total', 'tax'],
                                filters: [
                                    {
                                        title: 'lang.date_range', key: 'date_range', type: 'date_range'
                                    },
                                    {
                                        title: 'lang.type', key: 'type', type: 'dropdown', options: [
                                            {text: 'lang.all', value: 'all', selected: true},
                                            {text: 'lang.sales', value: 'sales'},
                                            {text: 'lang.receiving', value: 'receiving'},
                                        ]
                                    },
                                    {
                                        title: 'lang.branch',
                                        key: 'branch',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: branches
                                    },

                                ]
                            }
                        }
                        instance.setPreLoader(true);
                    },
                    function () {
                        instance.setPreLoader(true);
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
            }
        }
    }

</script>