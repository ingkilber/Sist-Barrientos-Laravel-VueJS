<template>
    <div v-if="due > 0 && rowData.invoice_id != 'Total' && rowData.invoice_id != 'Grand Total' && rowData.id != 'Total' && rowData.id != 'Grand Total'" class="action-button-wrapper">
        <div class='action-button-container'>
            <a href="" class='action-button' data-toggle="modal"
               data-target="#due-amount-edit-modal"
               @click.prevent="viewCustomerEdit(rowData,rowIndex)">
                <i class="la la-money la-2x"/>
            </a>
        </div>
        <i class="la la-ellipsis-v la-1x"/>
    </div>
</template>


<script>

    const {unformat} = require('number-currency-format');

    export default {
    props: ['rowData', 'rowIndex'],
    data(){
        return{
            selectedRowData : this.rowData,
            due : unformat(this.rowData.due_amount),
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

        viewCustomerEdit(rowData){
            this.$hub.$emit('viewSalesReportEdit', rowData);
        }
    }
}
</script>
