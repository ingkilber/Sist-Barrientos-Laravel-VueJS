<template>
    <div>
        <a href=""
           class="action-button"
           data-toggle="modal"
           data-target="#datatable-invoice-modal"
           @click.prevent="getOrdersInfo(rowData.id)">
            {{ rowData.invoice_id }}
        </a>

        <div v-if="isActiveDatatableInvoiceModal" data-backdrop="static" class="modal fade" id="datatable-invoice-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="container-fluid p-0">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="m-0">{{trans('lang.invoice')}}</h4>
                                </div>
                                <div class="col-2 text-right">
                                    <button type="button" class="close" @click.prevent="closeModal()">
                                        <i class="la la-close icon-modal-cross"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-content">
                        <pre-loader v-if="showPreloader"/>
                        <div v-else id="cart-print-area" v-html="invoiceTemplate" style="min-height: 500px; max-width: 800px; margin: 0 auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        name: "DatatableInvoiceModalComponent",
        props: ['rowData', 'rowIndex', 'id', 'order_type', 'tab_name', 'route_name'],
        data() {
            return {
                isActiveDatatableInvoiceModal: false,
                selectedRowData: this.rowData,
                responseData:'',
                ordersDetailsData: {},
                showPreloader:true,
                tabName:'',
                routeName:'',
                invoiceTemplate:'',
                customerDetails:'',
                itemDetails:'',
                paymentDetails:'',
                invoiceLogo:'',
                exchange:'',
                subTotal:'',
                discount:'',
                total:'',
                totalTax:'',
            }
        },
        mounted() {
            let instance = this;

            $('#datatable-invoice-modal').on('hidePrevented.bs.modal', function (e) {
                instance.isActiveDatatableInvoiceModal = false;
            });
        },
        methods: {
            getOrdersInfo(id) {
                let instance = this;
                instance.isActiveDatatableInvoiceModal = true;
                this.axiosGet('/reports/ordersDetails/' + id,
                    function (response) {
                    instance.invoiceTemplate = response.data.templateData.data;
                    
                        instance.showPreloader = false;
                    });
            },
            printReceiptBeforeDonePayment() {
                $('#cart-print-area').printThis({
                    importCSS: true,
                    importStyle: true,
                    printContainer: true,
                    header: null,
                });
                this.$emit('resetGetInvoiceBeforeDonePayment', false);
            },
            closeModal() {
                $('#datatable-invoice-modal').modal('hide');
                this.isActiveDatatableInvoiceModal = false;
                this.$hub.$emit('resetRegisterInfoModal', false);
            }
        }
    }
</script>

<style scoped>
    @media print {
        * {
            color: #000000 !important;
        }
    }
</style>