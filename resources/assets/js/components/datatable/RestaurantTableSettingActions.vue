<template>
    <div class="action-button-wrapper">
        <div class='action-button-container'>
            <a href="" class='action-button' data-toggle="modal" data-target="#confirm-delete" v-if="!rowData.used" @click.prevent="selectedDeletableId(rowData.id,rowIndex)"><i class="la la-trash-o la-2x"></i></a>
            <a href="" class='action-button' data-toggle="modal" data-target="#restaurant-table-add-edit-modal" @click.prevent="tableAddEdit(rowData.id)"><i class="la la-edit la-2x"></i></a>
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
            tableAddEdit(id)
            {
                this.$hub.$emit('tableAddEdit', id);
            },
            selectedDeletableId(id,index)
            {
                this.$hub.$emit('selectedDeletableId', id, index);
            },
        }
    }
</script>