<template>
    <div class="modal-content">
        <div>
            <div class="modal-layout-header">
                <div class="row">
                    <div class="col-10">
                        <h5 class="mb-0">{{trans('lang.select_table')}}</h5>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close"
                                @click.prevent>
                            <i class="la la-close icon-modal-cross"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-layout-content scroll-modal">
                <form class="form-row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-3 col-lg-3" v-for="restaurantTable in restaurant_tables_branch_wise">
                                <a href="#" @click.prevent="selectTable(restaurantTable.id)" class="parent-class">
                                    <div class="card bg-white table-card" @click="activeTableEvent">
                                        <div class="card-body text-small text-center p-2">
                                            <p :class="{'text-app':restaurantTable.status != 'booked'}">
                                                {{ restaurantTable.name }}
                                            </p>
                                            <i class="la la-cutlery la-3x mb-2"></i>
                                            <p>
                                                <span v-if="includes(restaurantTable.id)" class="badge badge-warning">
                                                     {{ trans('lang.booked') }}
                                                </span>
                                                <span class="badge badge-success"
                                                      v-else> {{ trans('lang.available') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12 mb-0">
                        <div class="text-right">
                            <button type="button"
                                    class="btn app-color mobile-btn mb-0"
                                    @click.prevent="printReceiptBeforeDonePayment()">
                                <i class="la la-print"></i> {{ trans('lang.print_order') }}
                            </button>
                            <button type="button"
                                    class="btn app-color mobile-btn mb-0"
                                    :disabled="selectedTableID === ''"
                                    @click.prevent="setOrderInHold()">
                                {{ trans('lang.proceed_order') }}
                            </button>
                            <button type="button"
                                    class="btn btn-secondary cancel-btn mobile-btn mb-0"
                                    data-dismiss="modal">
                                {{ trans('lang.cancel') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <print-receipt-component :print_invoice_before_done_payment="printInvoiceBeforeDonePayment"
                                 :sales_or_receiving_type="sales_or_receiving_type"
                                 :transfer_branch_name="transfer_branch_name"
                                 :order_type="order_type"
                                 :final_cart="final_cart"
                                 :sold_to="sold_to"
                                 :sold_by="sold_by"
                                 :user="user"
                                 :logo="logo"
                                 :invoice_template="invoice_template"
                                 @resetGetInvoiceBeforeDonePayment="resetGetInvoiceBeforeDonePayment">
        </print-receipt-component>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: [
            'restaurant_tables_branch_wise',
            'sales_or_receiving_type',
            'transfer_branch_name',
            'transfer_branch',
            'final_cart',
            'order_type',
            'sold_to',
            'sold_by',
            'user',
            'logo',
            'booked_tables',
            'customer_hold_orders',
            'internal_hold_orders',
            'order_hold_items',
            'invoice_template'
        ],
        extends: axiosGetPost,
        data() {
            return {
                selectedTableID: '',
                printInvoiceBeforeDonePayment: false,
                isBooked: false,
                includes: value => {
                    return this.booked_tables.includes(value) ? true : false
                },
                countOrderInTables: {},
                orderHoldItems: [],
                ordersByBookedTable: [],
            }
        },
        created() {
            this.orderHoldItems = this.order_hold_items;
        },
        methods: {
            activeTableEvent(e) {
                let target = $(e.target).closest('.table-card'),
                    findActive = $(document).find('.active-card');
                Array.from(findActive).forEach(ele => {
                    ele.classList.remove('active-card');
                });
                target.addClass("active-card");
            },
            selectTable(tableID) {
                this.selectedTableID = tableID;
            },
            setOrderInHold() {
                this.$emit('getRestaurantTableId', this.selectedTableID);
                $('#table-select-modal').modal('hide');
            },
            printReceiptBeforeDonePayment() {
                this.printInvoiceBeforeDonePayment = true;
            },
            resetGetInvoiceBeforeDonePayment(resetGetInvoice) {
                this.printInvoiceBeforeDonePayment = resetGetInvoice;
            },
        },
    }
</script>