<template>
    <div class="modal-content">
        <pre-loader class="small-loader-container" v-if="!hideOrderHoldItemsPreLoader"></pre-loader>
        <div class="p-4 text-center" v-else>
            <a href="#" class="position-absolute variant-modal-close-btn p-2 close" data-dismiss="modal"
               aria-label="Close" @click.prevent="">
                <i class="la la-close text-grey"></i>
            </a>
            <h4 class="mb-3">
                {{trans('lang.hold_order_list')}}
            </h4>
            <div v-if="salesOrReceivingType == 'internal'" class="border rounded">
                <div class="row mx-0 " :class="{'border-bottom': index!=internalHoldOrders.length-1}"
                     v-for="(internalHoldOrder, index) in internalHoldOrders"
                     v-if="internalHoldOrder.status == 'hold'">
                    <a href="#"
                       class="col-10 d-flex align-items-center justify-content-center hold-items app-color-text"
                       @click.prevent="setHoldOrderToCart(internalHoldOrder)">

                        <span v-if=""></span>{{ dateFormats(internalHoldOrder.date) }} {{
                        timeFormateForDatetime(internalHoldOrder.time) }}
                    </a>
                    <a href="#" class="col-2">
                        <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                           class="col-4 p-0 text-center" @click.prevent="orderID=internalHoldOrder.orderID"
                           :class="{'disabled':!isConnected}">
                            <i class="la la-trash la-2x p-2 text-danger hold-delete-icon"></i>
                        </a>
                    </a>
                </div>
            </div>
            <div v-else-if="salesOrReceivingType == 'customer'" class="border rounded">
                <div class="row mx-0 " :class="{'border-bottom': index!=customerHoldOrders.length-1}"
                     v-for="(customerHoldOrder, index) in customerHoldOrders"
                     v-if="customerHoldOrder.status == 'hold'">
                    <a href="#"
                       class="col-10 d-flex align-items-center justify-content-center hold-items app-color-text"
                       @click.prevent="setHoldOrderToCart(customerHoldOrder)">

                        <span v-if=""></span>{{ dateFormats(customerHoldOrder.date) }} {{
                        timeFormateForDatetime(customerHoldOrder.time) }}
                    </a>
                    <a href="#" class="col-2">
                        <a href="#" data-toggle="modal" data-target="#clear-cart-modal"
                           class="col-4 p-0 text-center" @click.prevent="orderID=customerHoldOrder.orderID"
                           :class="{'disabled':!isConnected}">
                            <i class="la la-trash la-2x p-2 text-danger hold-delete-icon"></i>
                        </a>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

    }
</script>

<style scoped>

</style>