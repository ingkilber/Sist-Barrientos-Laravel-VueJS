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
                    <h5 class="m-0">{{ trans('lang.receives') }}</h5>
                </div>
                <div class="main-layout-card-header-contents text-right">
                    <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled"
                                          :isActiveText="isActiveText" buttonText="export"
                                          v-on:submit="exportStatus"></common-submit-button>
                </div>
            </div>
        </div>

            <datatable-component class="main-layout-card-content" :options="tableOptions"
                                 :exportData="exportToVue" exportFileName="Receiving"
                                 @resetStatus="resetExportValue"
                                 :tab_name="tabName"
                                 :route_name="routeName"></datatable-component>

            <!-- Modal -->
            <div class="modal fade" id="due-amount-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                    <cart-due-payment
                        class="modal-content"
                        v-if="isActive"
                        :rowdata="selectedItemId"
                        :orderType="order_type"
                        :modalID="modalID"
                        :modalTitle="trans('lang.due_total')"
                        @cartItemsToStore="cartItemsToStore">
                    </cart-due-payment>
                </div>
            </div>
        </span>
    </div>
</template>

<script>

import axiosGetPost from '../../../helper/axiosGetPostCommon';
import {ucWords} from "../../../helper/textHelper";

export default {
    props: ['permission'],
    extends: axiosGetPost,

    data() {
        return {
            isActive: false,
            isActiveAttributeModal: false,
            selectedItemId: '',
            modalID: '#due-amount-edit-modal',
            order_type: 'receiving',
            exportToVue: false,
            hidePreLoader: false,
            buttonLoader: false,
            isDisabled: false,
            isActiveText: false,
            tabName: 'receiving_report',
            routeName: 'reports',
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value)
            },
        }
    },
    created() {
        this.getPurchaseReport();
    },
    methods: {
        getPurchaseReport() {
            let instance = this;
            instance.axiosGet('/purchase-report-filter',
                function (response) {

                    if (response.data) {
                        /*Appending cash register static value(All) with dynamic cash register title from db*/
                        let suppliers = [{text: 'All', value: 'all', selected: true}, ...response.data.suppliers];

                        instance.tableOptions = {
                            tableName: 'orders',
                            columns: [
                                {
                                    title: 'lang.invoice_id',
                                    key: 'invoice_id',
                                    type: 'clickable_link',
                                    source: 'reports/receiving',
                                    uniquefield: 'id',
                                    sortable: true
                                },
                                {title: 'lang.received_date', key: 'date', type: 'text', sortable: true},
                                {title: 'lang.received_type', key: 'type', type: 'text', sortable: true},
                                {
                                    title: 'lang.supplier_name',
                                    key: 'supplier_name',
                                    type: 'text',
                                    sortable: true,
                                },
                                {
                                    title: 'lang.received_by',
                                    key: 'full_name',
                                    type: 'clickable_link',
                                    source: 'user',
                                    uniquefield: 'user_id',
                                    sortable: true
                                },
                                {title: 'lang.item_received', key: 'item_purchased', type: 'text', sortable: true},
                                {title: 'lang.total', key: 'total', type: 'text', sortable: true},
                                {title: 'lang.due', key: 'due_amount', type: 'text', sortable: true},

                            ],
                            source: '/purchase-report',
                            search: true,
                            sortedBy: 'id',
                            sortedType: 'DESC',
                            right_align: ['item_purchased', 'total', 'due_amount'],
                            formatting: ['total', 'due_amount'],
                            dateFormatting: ['date'],
                            summary: true,
                            summation: ['total', 'item_purchased', 'due_amount'],
                            summationKey: ['invoice_id'],
                            filters: [
                                {title: 'lang.date_range', key: 'date_range', type: 'date_range'},
                                {
                                    title: 'lang.supplier',
                                    key: 'supplier',
                                    type: 'dropdown',
                                    languageType: "raw",
                                    options: suppliers
                                },
                                {
                                    title: 'lang.receive_type', type: 'dropdown', key: 'receive_type', options: [
                                        {text: 'lang.all', value: 'all', selected: true},
                                        {text: 'lang.supplier', value: 'supplier'},
                                        {text: 'lang.internal_receivings', value: 'internal'},
                                        {text: 'lang.internal_transfer', value: 'internal-transfer'},
                                        {text: 'lang.purchase_return', value: 'returns'},

                                    ]
                                },
                                {
                                    title: 'lang.payment_type', type: 'dropdown', key: 'payment_type', options: [
                                        {text: 'lang.all', value: 'all', selected: true},
                                        {text: 'lang.paid', value: 'paid'},
                                        {text: 'lang.due', value: 'due'},
                                    ]
                                }
                            ]
                        }
                    }
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },
        cartItemsToStore(cartItemsToStore) {
            let instance = this;
            instance.hideSalesReturnsPreLoader = false;
            cartItemsToStore.paymentType = 'credit';

            instance.axiosGETorPOST(
                {
                    url: '/save-due-amount',
                    postData: {cartItemsToStore},
                },
                (success, responseData) => {

                    if (success) {
                        instance.hideSalesReturnsPreLoader = true;
                        instance.showSuccessAlert(responseData.message);

                        $(`${this.modalID}`).modal('hide');
                        instance.$hub.$emit('reloadDataTable');

                    } else {
                        instance.hideSalesReturnsPreLoader = true;
                        $(`${this.modalID}`).modal('hide');
                    }
                }
            );
        },

        getActiveAttributeModal(isActive) {
            this.isActiveAttributeModal = isActive;
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
    },
    mounted() {
        let instance = this;

        this.modalCloseAction(this.modalID);

        $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e) {
            instance.isActiveAttributeModal = false;
            $('body').addClass('modal-open');
        });

        this.$hub.$on('viewSalesReportEdit', function (rowdata) {
            instance.addEditAction(rowdata);
        });
    },
}

</script>