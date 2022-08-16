<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0" v-if="id">{{ trans('lang.edit_supplier') }}</h4>
                    <h4 class="m-0" v-else>{{ trans('lang.add_supplier') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click.prevent>
                        <i class="la la-close icon-modal-cross"></i>
                    </button>

                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>

            <form class="form-row" v-else>
                <div class="form-group col-md-6">
                    <label for="first_name">{{ trans('lang.first_name') }}</label>
                    <input
                        v-validate="'required'"
                        name="firstname"
                        data-vv-as="first name"
                        id="first_name"
                        type="text"
                        class="form-control"
                        v-model="firstName"
                        :class="{ 'is-invalid': submitted && errors.has('firstname') }"
                    />
                    <div v-if="submitted && errors.has('firstname')" class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('firstname')"
                        >{{ errors.first('firstname') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="last_name">{{ trans('lang.last_name') }}</label>
                    <input
                        v-validate="'required'"
                        name="lastname"
                        data-vv-as="last name"
                        id="last_name"
                        v-model="lastName"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': submitted && errors.has('lastname') }"
                    />
                    <div v-if="submitted && errors.has('lastname')" class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('lastname')"
                        >{{ errors.first('lastname') }}</small>
                    </div>
                </div>
                <div class="form-group margin-top col-md-6">
                    <label for="email">{{ trans('lang.customer_email') }}</label>
                    <input
                        v-validate="'email'"
                        name="email"
                        id="email"
                        type="text"
                        class="form-control"
                        v-model="email"
                        :class="{ 'is-invalid': submitted && errors.has('email') }"
                    />
                    <div class="heightError" v-if="submitted && errors.has('email')">
                        <small
                            class="text-danger"
                            v-show="errors.has('email')"
                        >{{ errors.first('email') }}</small>
                    </div>
                </div>
                <div class="form-group margin-top col-md-6">
                    <label for="phonenumber">{{ trans('lang.phone_number') }}</label>
                    <input
                        name="phonenumber"
                        id="phoneNumber"
                        type="text"
                        class="form-control"
                        v-model="phoneNumber"
                        v-validate="'phoneNumber'"
                    />
                    <div class="heightError">
                        <small class="text-danger" v-show="errors.has('phonenumber')">
                            {{ errors.first('phonenumber')
                            }}
                        </small>
                    </div>
                </div>
                <div class="form-group margin-top col-md-6">
                    <label for="address">{{ trans('lang.customer_address') }}</label>
                    <input id="address" type="text" class="form-control" v-model="address" />
                </div>
                <div class="form-group margin-top col-md-6">
                    <label for="tin_number">{{ trans('lang.tin_number') }}</label>
                    <input id="tin_number" type="text" class="form-control" v-model="tinNumber">
                </div>
                <div class="form-group margin-top col-md-12">
                    <label for="company">{{ trans('lang.customer_company') }}</label>
                    <input id="company" type="text" class="form-control" v-model="company" />
                </div>
                <div class="col-12">
                    <button class="btn app-color mobile-btn" type="submit" @click.prevent="save()">
                        {{ trans('lang.save')
                        }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent>
                        {{
                        trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

export default {
    props: ["id", "modalID", "order_type"],
    extends: axiosGetPost,
    data() {
        return {
            firstName: "",
            lastName: "",
            email: "",
            phoneNumber: "",
            tinNumber: "",
            address: "",
            company: "",
            supplier: {},
            submitted: false
        };
    },

    created() {
        if (this.id) {
            this.getSupplierData("/supplier-edit/" + this.id);
        }
    },
    mounted() {},

    methods: {
        save() {
            this.submitted = true;
            this.$validator.validateAll().then(result => {
                if (result) {
                    this.inputFields = {
                        first_name: this.firstName,
                        last_name: this.lastName,
                        email: this.email,
                        company: this.company,
                        phone_number: this.phoneNumber,
                        tin_number: this.tinNumber,
                        address: this.address
                    };

                    if (this.id) {
                        this.postDataMethod(
                            "/supplier/" + this.id,
                            this.inputFields
                        );
                    } else {
                        this.postDataMethod(
                            "/supplier/store",
                            this.inputFields
                        );
                    }
                }
            });
        },
        getSupplierData(route) {
            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(
                route,
                function(response) {
                    instance.firstName = response.data.supplierData.first_name;
                    instance.lastName = response.data.supplierData.last_name;
                    instance.email = response.data.supplierData.email;
                    instance.phoneNumber =
                        response.data.supplierData.phone_number;
                    instance.address = response.data.supplierData.address;
                    instance.tinNumber = response.data.supplierData.tin_number;
                    instance.company = response.data.supplierData.company;
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                }
            );
        },
        postDataThenFunctionality(response) {
            $(this.modalID).modal("hide");
            if (this.order_type != "receiving") {
                this.$hub.$emit("customerAddedFromSales");
                this.$hub.$emit("reloadDataTable");
            }

            this.$emit("newSupplier", response.data.id);
        },
        postDataCatchFunctionality(error) {}
    }
};
</script>