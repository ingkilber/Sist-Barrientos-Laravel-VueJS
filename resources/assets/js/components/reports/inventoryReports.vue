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
                        <h5 class="m-0">{{ trans('lang.inventories') }}</h5>
                    </div>
                    <div class="main-layout-card-header-contents text-right">
                       <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                             :isActiveText="isActiveText" buttonText="export"
                                             v-on:submit="exportStatus"></common-submit-button>
                    </div>
                </div>
            </div>
            <!--Export Button end-->
            <datatable-component class="main-layout-card-content" :options="tableOptions" :exportData="exportToVue"
                                 :exportFileName="trans('lang.inventory')"
                                 @resetStatus="resetExportValue"></datatable-component>
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
                isActiveAttributeModal: false,
                selectedItemId: '',
                tableOptions: {},
                buttonLoader: false,
                isDisabled: false,
                isActiveText: false,
                hasData: value => {
                    return !_.isEmpty(value);
                },
            }
        },
        created() {
            this.getInventoryFilterAttributes();
        },

        mounted() {
            let instance = this;

            this.modalCloseAction(this.modalID);

            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
                instance.isActiveAttributeModal = false;
                $('body').addClass('modal-open');
            });
        },

        methods: {

            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
            },

            getInventoryFilterAttributes() {
                let instance = this;
                instance.axiosGet('/inventory-reports-filter',
                    function (response) {
                        if (response.data) {

                            let branchName = [],
                                brandName = [],
                                categoryName = [],
                                groupName = [];

                            //Appending static value(All) with dynamic Filter options from db
                            if (response.data.branchName) branchName = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.branchName];
                            if (response.data.brandName) brandName = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.brandName];
                            if (response.data.categoryName) categoryName = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.categoryName];
                            if (response.data.groupName) groupName = [{
                                text: instance.trans('lang.all'),
                                value: 'all',
                                selected: true
                            }, ...response.data.groupName];

                            instance.tableOptions = {

                                tableName: 'cash_register_logs',
                                columns: [
                                    {title: 'lang.inventory_id', key: 'id', type: 'text', sortable: true},
                                    {title: 'lang.sku', key: 'sku', type: 'text', sortable: true},
                                    {title: 'lang.barcode', key: 'barcode', type: 'text', sortable: true},
                                    {title: 'lang.item_name', key: 'porductTitle', type: 'text', sortable: true},
                                    {title: 'lang.variant_name', key: 'variantTitle', type: 'text', sortable: true},
                                    {title: 'lang.category_name', key: 'categoryTitle', type: 'text', sortable: true},
                                    {title: 'lang.group_name', key: 'groupTitle', type: 'text', sortable: true},
                                    {title: 'lang.brand_name', key: 'brandTitle', type: 'text', sortable: true},
                                    {title: 'lang.purchase_price', key: 'purchase_price', type: 'text', sortable: true},
                                    {title: 'lang.selling_price', key: 'selling_price', type: 'text', sortable: true},
                                    {title: 'lang.inventory', key: 'inventory', type: 'text', sortable: true},
                                ],
                                source: '/inventory-reports',
                                search: true,
                                summary: true,
                                sortedBy: 'id',
                                sortedType: 'DESC',
                                formatting: ['purchase_price', 'selling_price'],
                                right_align: ['purchase_price', 'selling_price', 'inventory'],
                                summation: ['purchase_price', 'selling_price', 'inventory'],
                                summationKey: ['id'],
                                filters: [
                                    //dropdown filter for inventory report (dynamic value from db)
                                    {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                    {
                                        title: 'lang.branch',
                                        key: 'branchName',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: branchName
                                    },
                                    {
                                        title: 'lang.brand',
                                        key: 'brandName',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: brandName
                                    },
                                    {
                                        title: 'lang.category',
                                        key: 'categoryName',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: categoryName
                                    },
                                    {
                                        title: 'lang.group',
                                        key: 'groupName',
                                        type: 'dropdown',
                                        languageType: "raw",
                                        options: groupName
                                    },
                                    {
                                        title: 'lang.re_order', key: 'type', type: 'dropdown', options: [
                                            {text: 'lang.all', value: 'all', selected: true},
                                            {text: 'lang.yes', value: 'yes'},
                                            {text: 'lang.no', value: 'no'},
                                        ]
                                    },
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
