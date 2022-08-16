<template>
    <div class="action-button-wrapper" @mouseenter="mouseOver($event,true)" @mouseleave="mouseOver($event,false)">
        <div class='action-button-container'>
            <a href="" class='action-button'  data-toggle="modal" data-target="#confirm-delete" @click.prevent="selectedDeletableId(rowData.id,rowIndex)"><i class="la la-trash-o la-2x"></i></a>
            <a href="" class='action-button'  data-toggle="modal" data-target="#attributes-add-edit-modal" @click.prevent="attributeAddEdit(rowData.id)"><i class="la la-edit la-2x"></i></a>
        </div>
        <i class="la la-ellipsis-v la-1x"></i>
    </div>
</template>


<script>
    export default {
        props: ['rowData', 'rowIndex'],
        data(){
            return{
                isHovering: '',            }
        },
        mounted(){

        },
        methods:{
            mouseOver(event,value){
                if(value)
                {
                    $(event.target).closest('.action-button-wrapper').addClass('active');
                }
                else
                {
                    $(event.target).closest('.action-button-wrapper').removeClass('active');
                }

            },
            attributeAddEdit(id){
                this.$hub.$emit('attributeAddEdit', id);
            },

            selectedDeletableId(id,index)
            {
                this.$hub.$emit('selectedDeletableId',id,index);
            },
        }
    }
</script>
