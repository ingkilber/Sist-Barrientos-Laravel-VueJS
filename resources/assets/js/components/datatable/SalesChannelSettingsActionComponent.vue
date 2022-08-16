<template>
    <div class="action-button-wrapper">
        <div class='action-button-container'>
            <a href="" class='action-button'  data-toggle="modal" data-target="#sales-channel-add-edit-modal" @click.prevent="salesChannelAddEdit(rowData.id)"><i class="la la-edit la-2x"></i></a>
            <a href="" class='action-button'  data-toggle="modal" data-target="#confirm-delete"  @click.prevent="selectedDeletableId(rowData.id,rowIndex)"><i class="la la-trash-o la-2x"></i></a>
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
            salesChannelAddEdit(id){
                this.$hub.$emit('salesChannelAddEdit', id);
            },
            selectedDeletableId(id,index)
            {
                this.$hub.$emit('selectedDeletableId',id,index);
            },
        }
    }
</script>
