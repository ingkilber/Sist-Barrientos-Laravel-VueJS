<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h5 class="m-0">{{ trans('lang.are_you_sure') }}</h5>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <i class="la la-close icon-modal-cross"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">

            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>

            <form class="form-row" v-else>
                <div class="form-group col-md-12">
                    <label>{{ trans('lang.name') }}</label>
                    <h6 class="m-0"> {{ name }} </h6>
                </div>
                <div class="d-table-cell align-middle text-center">
                    <h6 v-if="parseInt(is_admin) === 1">{{ trans('lang.be_careful_you_are_make_a_new_admin_same_as_you') }}</h6>
                    <h6 v-else>{{ trans('lang.you_are_removing_from_admin') }}</h6><br>
                </div>

                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="enableDisableUserAdmin()">
                        {{ parseInt(this.is_admin) === 1 ? trans('lang.mark_as_admin') : trans('lang.remove_from_admin')}}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">
                        {{ trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
        {{ isAdmin }}
    </div>
</template>

<script>

import axiosGetPost from '../../../helper/axiosGetPostCommon';

export default {

    props: ['id', 'confirmModalID', 'isAdmin'],

    extends: axiosGetPost,

    data() {
        return {
            email: '',
            inviteAs: '',
            branchPermission: [],
            roles: [],
            branches: [],
            name: '',
            is_admin: '',
            adminId: '',
        }
    },

    created() {
        this.getRolesData();
        this.getBranchData();

        if (this.id) {
            this.getUserData('/get-user/' + this.id);
        }
    },

    methods: {
        enableDisableUserAdmin() {
            if (this.id) {
                this.inputFields = {
                    is_admin: this.is_admin,
                };
                this.postDataMethod('/make-admin-user/' + this.id, this.inputFields);

            } else {
                this.inputFields = {

                    email: this.email,
                    invited_as: this.inviteAs,
                    branchID: this.branchPermission,
                };
            }
        },
        postDataThenFunctionality() {
            $(this.confirmModalID).modal('hide');

            this.$hub.$emit('reloadDataTable');
        },
        getRolesData() {
            this.setPreLoader(false);

            let instance = this;

            this.axiosGet('/all-role-id',
                function (response) {
                    instance.roles = response.data;
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },
        getBranchData() {
            let instance = this;

            this.axiosGet('/branches',
                function (response) {
                    instance.branches = response.data;
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },

        is_disable() {
            this.is_disabled = true;
        },
        getUserData(route) {
            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(route,
                function (response) {
                    instance.name = response.data.rowUser.first_name + ' ' + response.data.rowUser.last_name;
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        }
    }
}
</script>