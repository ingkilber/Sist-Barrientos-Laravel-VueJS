<template>
    <div>
        <div>
            <div class="main-layout-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent m-0">
                        <li class="breadcrumb-item">
                            <span v-if="order_type=='sales'">{{trans('lang.sales_details')}}
                                (<a href="#" @click="goBack">{{trans('lang.back_page')}}</a>)
                            </span>
                            <span v-else>{{trans('lang.receives_details')}}
                                    (<a href="#" @click="goBack">{{trans('lang.back_page')}}</a>)
                            </span>
                        </li>
                    </ol>
                </nav>
                <div class="main-layout-card" style="padding-bottom: 20px;">
                    <div class="main-layout-card-header-with-button">
                        <div class="main-layout-card-content-wrapper">
                            <div class="main-layout-card-header-contents text-right">
                                <button class="btn btn-info app-color mobile-btn" type="submit" @click.prevent="printReceipt()">
                                    Print
                                </button>
                            </div>
                        </div>
                    </div>
                    <pre-loader v-if="showPreloader"/>
                    <div v-else>
                        <div id="cart-print-area" v-html="invoiceTemplate" style="min-height: 500px; max-width: 800px; margin: 0 auto;"></div>
                    </div>

                    <invoice :printInvoice="printInvoice"
                             :rawHtml="invoiceTemplate"
                             :invoiceID="invoiceId"
                             @resetGetInvoice="resetGetInvoice">
                    </invoice>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        props: ['id', 'order_type', 'tab_name', 'route_name'],
        data() {
            return {
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
                invoiceId:'',
                printInvoice:false,
            }
        },
        mounted() {
            this.tabName = this.tab_name;
            this.routeName = this.route_name;
            this.getOrdersInfo();
        },
        methods: {
            getOrdersInfo() {
                let instance = this;
                this.axiosGet('/reports/ordersDetails/' + instance.id,
                    function (response) {
                        instance.invoiceId = response.data.invoiceId;
                        instance.invoiceTemplate = response.data.templateData.data;
                        instance.showPreloader = false;
                    },
                );
            },

            printReceipt() {
                this.printInvoice = true;
            },
            resetGetInvoice(resetGetInvoice) {
                this.printInvoice = resetGetInvoice;
            },
            goBack() {
                let instance = this;
                instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&${this.routeName}`);
            }
        },
    }
</script>
<style scoped>
    @media print {
        * {
            color: #000 !important;
        }
    }

</style>