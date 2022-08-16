<template>

    <div class="action-button-wrapper" v-if="!rowData.currentuser">
        <div class='action-button-container'>

            <!--Enable button  tick-->
            <a class='action-button red-tooltip' data-toggle="tooltip"
               :title="trans('lang.enable_user')" v-if="rowData.enabled==0"
               @click.prevent="disableEnableUser(rowData.id,1)"><i class="la la-check la-2x"></i></a>

            <!--Disable button cross-->
            <a class='action-button' data-toggle="tooltip"
               :title="trans('lang.disable_user')" v-if="rowData.enabled==1"
               @click.prevent="disableEnableUser(rowData.id,0)"><i class="la la-times la-2x"></i></a>

            <a class='action-button' v-if="rowData.is_admin == 1" data-toggle="modal"
               data-target="#confirm-admin-enable-disable" @click.prevent="makeNewAdmin(rowData.id)">
                <i class="la la-certificate la-2x"></i>
            </a>
            <span data-toggle="modal" data-target="#user-invite-modal" >
                 <a href="" class='action-button' data-toggle="tooltip" :title="trans('lang.edit')"
                    @click.prevent="changeUserRole(rowData.id)"><i class="la la-edit la-2x"></i></a>
            </span>
        </div>
        <i class="la la-ellipsis-v la-1x"></i>
    </div>
</template>


<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        props: ['rowData', 'rowIndex'],

        mounted() {
            $(document).ready(function () {
                $("a").tooltip();
            });

            $(".action-button-wrapper")
                .on("mouseover", function () {
                    $(this).addClass("active");
                })
                .on("mouseleave", function () {
                    $(this).removeClass("active");
                });
        },

        methods: {
            changeUserRole(id) {
                this.$hub.$emit('changeUserRole', id);
            },

            disableEnableUser(id, status) {
                this.$hub.$emit('disableEnableUser', id, status);
            },

            makeNewAdmin(id) {
                this.$hub.$emit('makeNewAdmin', id);
            }
        }
    }
</script>

