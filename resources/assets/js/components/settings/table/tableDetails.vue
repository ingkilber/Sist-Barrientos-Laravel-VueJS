<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_table') }}</h4>
                    <h4 class="m-0" v-else="id">{{ trans('lang.add_table') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row" v-else>
                <span v-if="alertMessage.length>0" class="alertBranch">
                    <div class="alert alert-warning alertBranch" role="alert">
                        {{alertMessage}}
                    </div>
                </span>
                <div class="form-group col-md-12">
                    <label for="name">{{ trans('lang.table_name_label') }}</label>
                    <input class="form-control" v-validate="'required'" id="name" name="name" type="text" v-model="name">
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('name')">{{ errors.first('name') }}</small>
                    </div>
                </div>
                <div class="form-group margin-top col-md-12">
                    <label for="branches">{{ trans('lang.branch') }}</label>
                    <select v-validate="'required'" name="branch" v-model="branch_id" id="branches"
                            class="custom-select">
                        <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option v-for="branch in branches" :value="branch.id" v-if="branch.branch_type == 'restaurant'"> {{branch.name}}</option>
                    </select>
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('branch')">{{ errors.first('branch') }}</small>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save') }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">
                        {{ trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        props: ['id', 'modalID'],
        extends: axiosGetPost,
        data() {
            return {
                name: '',
                alertMessage: '',
                branches: [],
                branch_id:''
            }
        },
        created() {
            if (this.id) {
                this.getTableData();
            }
        },
        mounted() {
            let instance = this;

            //get application setting data
            this.axiosGet('/allBranches',
                function (response) {
                    instance.branches = response.data;
                },
                function (error) {
                },
            );
        },
        methods: {
            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            name: this.name,
                            branch_id: this.branch_id
                        };
                        if (this.id) {
                            this.postDataMethod('/editTable/' + this.id, this.inputFields);
                        } else {
                            this.postDataMethod('/addTable', this.inputFields);
                        }
                    }
                });
            },
            postDataThenFunctionality(response) {
                $(this.modalID).modal('hide');
                this.$hub.$emit('reloadDataTable');
            },
            postDataCatchFunctionality(error) {
                this.alertMessage = error.data.message;
            },
            getTableData() {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet('/edit-table/' + this.id,
                    function (response) {
                        instance.name = response.data.name;
                        instance.branch_id = response.data.branch_id;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
        },
    }
</script>