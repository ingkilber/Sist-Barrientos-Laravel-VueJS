<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.sms_settings') }}</h5>
                </div>
            </div>
        </div>

        <div class="main-layout-card-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>
            <form v-else>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="from_name_phone">{{ trans('lang.from_name_phone') }}</label>
                        <input
                                type="text"
                                name="from_name_phone_number"
                                v-validate="'required'"
                                data-vv-as="name/phone number"
                                v-model="namePhone"
                                class="form-control"
                                id="from_name_phone"/>
                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('from_name_phone_number')"
                            >{{ errors.first('from_name_phone_number') }}</small>
                        </div>
                    </div>

                    <div class="form-group offset-md-1 col-md-5">
                        <label for="sms-driver">{{ trans('lang.sms_driver') }}</label>
                        <select id="sms-driver"
                                name="driver"
                                v-validate="'required'"
                                v-model="smsDriver"
                                @change="setValidation()"
                                class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option value="nexmo">{{ trans('lang.nexmo') }}</option>
                        </select>

                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('driver')"
                            >{{ errors.first('driver') }}</small>
                        </div>
                    </div>

                    <div class="form-group col-md-5" v-show="smsDriver === 'nexmo' ">
                        <label for="key">{{ trans('lang.key') }}</label>
                        <input
                                type="text"
                                name="key"
                                v-validate="{required:isNexmoActive}"
                                v-model="key"
                                class="form-control"
                                id="key"/>

                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('key')"
                            >{{ errors.first('key') }}</small>
                        </div>
                    </div>

                    <div class="form-group offset-md-1 col-md-5" v-show="smsDriver === 'nexmo'">
                        <label for="secret_key">{{ trans('lang.secret_key') }}</label>
                        <input
                                type="text"
                                name="secret_key"
                                v-validate="{required:isNexmoActive}"
                                data-vv-as="secret key"
                                v-model="secretKey"
                                class="form-control"
                                id="secret_key"/>

                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('secret_key')"
                            >{{ errors.first('secret_key') }}</small>
                        </div>
                    </div>

                </div>
                <button
                        type="submit"
                        class="btn btn-primary app-color mobile-btn"
                        v-if="permission_key == 'manage'"
                        @click.prevent="updateSmsSetting()"
                >{{ trans('lang.save') }}</button>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {
                namePhone:"",
                smsDriver: "",
                key:"",
                secretKey:"",
                hidePreLoader: false,
                isNexmoActive: false,
            }
        },
        created() {
            this.getSmsData("/get-sms-data");
            this.setValidation();
        },

        methods:{
            setValidation() {
                this.$validator.reset();
                this.isNexmoActive = false;
                if (this.smsDriver === "nexmo") {
                    this.isNexmoActive = true;
                }
            },

            updateSmsSetting() {
                let instance = this;
                this.submitted = true;
                this.$validator.validateAll().then(result => {
                    if (result) {
                        instance.inputFields = {
                            sms_from_name_phone_number: this.namePhone,
                            sms_driver: this.smsDriver,
                            key: this.key,
                            secret_key: this.secretKey,
                        };
                        instance.postDataMethod(
                            "/sms-setting-update",
                            instance.inputFields
                        );
                    }
                });
            },
            postDataThenFunctionality(response) {},
            postDataCatchFunctionality(error) {},
            getSmsData(route) {
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet(
                    route,
                    function(response) {
                        instance.namePhone = response.data.sms_from_name_phone_number;
                        instance.smsDriver = response.data.sms_driver;
                        instance.key = response.data.key;
                        instance.secretKey = response.data.secret_key;
                        instance.setValidation();
                        instance.setPreLoader(true);
                    },
                    function(response) {
                        instance.setPreLoader(true);
                    }
                );
            }

        }
    }
</script>

