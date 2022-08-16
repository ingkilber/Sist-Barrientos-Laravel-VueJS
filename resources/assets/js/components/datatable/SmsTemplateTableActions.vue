<template>

    <div class="action-button-wrapper">
        <div class='action-button-container'>
            <a href="" class='action-button'  data-toggle="modal" data-target="#sms-template-modal" @click.prevent="viewTemplate(rowData.id,rowData.template_type)"><i class="la la-edit la-2x"></i></a>
        </div>
        <i class="la la-ellipsis-v la-1x"></i>
    </div>
</template>


<script>

    export default {
        props: ['rowData','rowIndex'],
        data(){
            return{

            }
        },
        computed: {

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

            viewTemplate(id,title){
                this.$hub.$emit('viewTemplateEdit', id, title);
            },
        }
    }
</script>
