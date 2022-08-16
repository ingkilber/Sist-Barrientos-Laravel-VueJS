<template>
    <div class="action-button-wrapper">
        <div class='action-button-container'>
            <a class='action-button red-tooltip' data-toggle="tooltip"
               :title="trans('lang.shipment_status_delivered')"
               @click.prevent="setOrderStatus(rowData.id, 'delivered')"><i class="la la-check la-2x"></i></a>

            <a class='action-button red-tooltip' data-toggle="tooltip"
               :title="trans('lang.shipment_status_on_the_way')"
               @click.prevent="setOrderStatus(rowData.id, 'on the way')"><i class="la la-truck la-2x"></i></a>

            <a class='action-button red-tooltip' data-toggle="tooltip"
               :title="trans('lang.shipment_status_packet')"
               @click.prevent="setOrderStatus(rowData.id, 'packet')"><i class="la la-suitcase"></i></a>

            <a class='action-button red-tooltip' data-toggle="tooltip"
               :title="trans('lang.cancel')"
               @click.prevent="setOrderStatus(rowData.id, 'cancelled')"><i class="la la-times"></i></a>


        </div>
        <i class="la la-ellipsis-v la-1x"></i>
    </div>
</template>


<script>
    export default {
        props: ['rowData', 'rowIndex'],
        data(){
            return{

            }
        },
        mounted(){
            $(".action-button-wrapper")
                .on("mouseover", function () {
                    $(this).addClass("active");
                })
                .on("mouseleave", function () {
                    $(this).removeClass("active");
                });
        },
        methods:{

            setOrderStatus(id, status) {
                this.$hub.$emit('setShipmentStatus', id, status);
            }
        }
    }
</script>
