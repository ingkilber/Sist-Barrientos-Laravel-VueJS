<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h4 class="m-0">{{trans('lang.register_info')}}</h4>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="close" @click.prevent="closeModal">
                            <i class="la la-close icon-modal-cross"/>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <div class="mb-1 d-none" :class="{'d-block': isCashRegisterBalanceInfo}">
                <div>
                    <span class="info-heading">{{ trans('lang.cash_opening_balance') }} :</span>
                    <span class="info-value">{{ numberFormat(cashRegisterSalesBalance.opening_amount) }}</span>
                </div>
                <div>
                    <span class="info-heading">{{ trans('lang.total_sales') }} :</span>
                    <span class="info-value">{{ numberFormat(cashRegisterSalesBalance.total_sales) }}</span>
                </div>
                <div>
                    <span class="info-heading">{{ trans('lang.total_cash_sales') }} :</span>
                    <span class="info-value">{{ numberFormat(cashRegisterSalesBalance.total_cash_sale) }}</span>
                </div>
            </div>
            <datatable-component class="datatable-cash-register-info"
                                 :options="tableOptions"/>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['modalID', 'current_branch'],
        data: () => ({
            isCashRegisterBalanceInfo: false,
            cashRegisterSalesBalance: '',
            tableOptions: {},
            hasData: value => {
                return !_.isEmpty(value)
            },
        }),
        created() {
            this.getRegisterInfo();
            this.cashSalesBalance();
        },
        methods: {
            getRegisterInfo() {
                let instance = this;

                instance.tableOptions = {
                    tableName: 'products',
                    columns: [
                        {
                            title: 'lang.invoice_id',
                            type: "component",
                            componentName: "datatable-invoice-modal-component",
                        },
                        {
                            title: 'lang.sold_to',
                            key: 'customer',
                            type: 'text',
                            sortable: true
                        },
                        {
                            title: 'lang.total',
                            key: 'total',
                            type: 'text'
                        },
                    ],
                    clickAbleModel: true,
                    sortedBy:'id',
                    sortedType:'DESC',
                    right_align: ['total'],
                    formatting : ['total'],
                    source: '/register-sales-info/' + instance.current_branch,
                }
            },
            cashSalesBalance() {
                let instance = this;
                instance.axiosGet('/cash-register-total-sales-balance/' + instance.current_branch,
                    function (response) {
                        instance.cashRegisterSalesBalance = response.data;
                        instance.isCashRegisterBalanceInfo = true;
                    })
            },
            closeModal() {
                $('#register-info-modal').modal('hide');
            }
        }
    }
</script>

