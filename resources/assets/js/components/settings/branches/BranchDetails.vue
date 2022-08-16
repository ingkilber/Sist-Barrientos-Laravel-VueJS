<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_branch') }}</h4>
                    <h4 class="m-0" v-else="id">{{ trans('lang.add_branch') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                        @click.prevent
                    >
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row" v-else>
                <span v-if="alertMessage.length>0" class="alertBranch">
                    <div class="alert alert-warning alertBranch" role="alert">{{alertMessage}}</div>
                </span>
                <div class="form-group col-md-12">
                    <label for="name">{{ trans('lang.name') }}</label>
                    <input
                        class="form-control"
                        v-validate="'required'"
                        id="name"
                        name="name"
                        type="text"
                        v-model="name"
                    />
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('name')"
                        >{{ errors.first('name') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label for="branch-tax">{{ trans('lang.branch_type') }}</label>
                    <select
                        v-model="branchType"
                        v-validate="'required'"
                        data-vv-as="branch type"
                        id="branch-type"
                        name="branch_type"
                        class="custom-select"
                    >
                        <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option value="retail">{{ trans('lang.retail') }}</option>
                        <option value="restaurant">{{ trans('lang.restaurant') }}</option>
                    </select>
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('branch_type')"
                        >{{ errors.first('branch_type') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label for="branch-tax">{{ trans('lang.branch_manager_sm') }}</label>
                    <select
                        v-model="branchManager"
                        v-validate="'required'"
                        data-vv-as="branch manager"
                        id="branch-manager"
                        name="branch_manager"
                        class="custom-select"
                    >
                        <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option :value="user.id" v-for="user in users">{{ user.branch_manager }}</option>
                    </select>
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('branch_manager')"
                        >{{ errors.first('branch_manager') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="branch-tax">{{ trans('lang.tax') }}</label>
                    <select
                        v-model="tax"
                        v-validate="'required'"
                        id="branch-tax"
                        name="tax"
                        class="custom-select"
                    >
                        <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                        <option value="no-tax">{{ trans('lang.no_tax') }}</option>
                        <option value="default-tax">{{ trans('lang.default_tax') }}</option>
                        <option v-for="tax in allTax" :value="tax.id">{{ tax.name }}</option>
                    </select>
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('tax')"
                        >{{ errors.first('tax') }}</small>
                    </div>
                </div>
                <div class="col-md-5 margin-top ml-4">
                    <label>{{ trans('lang.use_cash_register') }}</label>
                    <div class="d-flex align-items-center">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                name="taxable"
                                class="custom-control-input"
                                id="cash-register-yes"
                                checked="checked"
                                value="1"
                                v-model="isCashRegisterUser"
                            />
                            <label
                                class="custom-control-label"
                                for="cash-register-yes"
                            >{{ trans('lang.yes') }}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                name="taxable"
                                class="custom-control-input"
                                id="cash-register-no"
                                value="0"
                                v-model="isCashRegisterUser"
                            />
                            <label
                                class="custom-control-label"
                                for="cash-register-no"
                            >{{ trans('lang.no') }}</label>
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-5 margin-top">
                    <label>{{ trans('lang.enable_shipment') }}</label>
                    <div class="d-flex align-items-center">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                    type="radio"
                                    name="shipment"
                                    class="custom-control-input"
                                    id="shipment-yes"
                                    checked="checked"
                                    value="1"
                                    v-model="isEnableShipment"
                            />
                            <label
                                    class="custom-control-label"
                                    for="shipment-yes"
                            >{{ trans('lang.yes') }}</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                    type="radio"
                                    name="shipment"
                                    class="custom-control-input"
                                    id="shipment-no"
                                    value="0"
                                    v-model="isEnableShipment"
                            />
                            <label
                                    class="custom-control-label"
                                    for="shipment-no"
                            >{{ trans('lang.no') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <button
                        class="btn app-color mobile-btn"
                        type="submit"
                        @click.prevent="save()"
                    >{{ trans('lang.save') }}</button>
                    <button
                        class="btn cancel-btn mobile-btn"
                        data-dismiss="modal"
                        @click.prevent
                    >{{ trans('lang.cancel') }}</button>
                </div>
            </form>
        </div>

        <div
            class="modal fade"
            id="tax-add-edit-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <taxes-add-edit class="modal-content" v-if="isActive"></taxes-add-edit>
            </div>
        </div>
    </div>
</template>

<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";
export default {
    props: ["id", "modalID"],
    extends: axiosGetPost,

    data() {
        return {
            name: "",
            branchType: "",
            branchManager: null,
            tax: "",
            allTax: [],
            users: [],
            alertMessage: "",
            isCashRegisterUser: 1,
            isEnableShipment: 0,
        };
    },

    created() {
        this.getSupportingData();

        if (this.id) {
            this.getBranchData();
        }
    },

    methods: {
        save() {
            let instance = this;
            this.$validator.validateAll().then(result => {
                if (result) {
                    this.inputFields = {
                        name: this.name,
                        branchType: this.branchType,
                        tax_id: this.tax,
                        isCashRegisterUser: this.isCashRegisterUser,
                        isEnableShipment: this.isEnableShipment,
                        user_id: this.branchManager
                    };

                    if (this.id) {
                        this.postDataMethod(
                            "/edit-branch/" + this.id,
                            this.inputFields
                        );
                    } else {
                        this.postDataMethod("/add-branch", this.inputFields);
                    }
                }
            });
        },

        postDataThenFunctionality(response) {
            $(this.modalID).modal("hide");
            this.$hub.$emit("reloadDataTable");
        },
        postDataCatchFunctionality(error) {
            this.alertMessage = error.data.message;
        },
        getSupportingData() {
            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(
                "/branch-settings-support-data",
                function(response) {
                    instance.allTax = response.data.taxes;
                    instance.users = response.data.users;
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                }
            );
        },
        getBranchData() {
            let instance = this;
            instance.setPreLoader(false);
            instance.axiosGet(
                "/edit-branch/" + this.id,
                function(response) {
                    instance.name = response.data.name;
                    instance.isCashRegisterUser =
                        response.data.is_cash_register;
                    instance.isEnableShipment = response.data.is_shipment;
                    if (response.data.taxable == 0) {
                        instance.tax = "no-tax";
                    } else if (response.data.is_default == 1) {
                        instance.tax = "default-tax";
                    } else {
                        instance.tax = response.data.tax_id;
                    }
                    if (response.data.branch_type == "restaurant") {
                        instance.branchType = "restaurant";
                    } else {
                        instance.branchType = "retail";
                    }
                    instance.branchManager = response.data.user_id;
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                }
            );
        }
    }
};
</script>