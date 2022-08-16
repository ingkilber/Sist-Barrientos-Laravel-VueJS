<template>
    <div v-if="(rowData.invoice_id != 'Total' && rowData.invoice_id != 'Grand Total') && (due != 0 || salesListDelete == 1 || salesListEdit == 1)" class="action-button-wrapper">
        <div class='action-button-container'>
            <a v-if="due != 0" href="" class='action-button' data-toggle="modal"
               data-target="#due-amount-edit-modal"
               @click.prevent="viewCustomerEdit(rowData,rowIndex)">
                <i class="la la-money la-2x"/>
            </a>

            <a v-if="salesListDelete == 1" href="" class='action-button'  data-toggle="modal" data-target="#confirm-delete" @click.prevent="selectedDeletableId(rowData.id,rowIndex)"><i class="la la-trash-o la-2x"></i></a>

            <a v-if="salesListEdit == 1" href="" class='action-button'  data-toggle="modal"
               data-target="#date-edit-modal"
               @click.prevent="editOrderDate(rowData,rowIndex)">
                <i class="la la-edit la-2x"></i>
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
            },
            selectedDeletableId(id,index) {
                this.$hub.$emit('selectedDeletableId', id, index);
            },
            editOrderDate(rowdata, index) {
                this.$hub.$emit('editSalesDate', rowdata, index);
            },

        }
    }
</script>
