<template>
    <div class="back-img">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8 col-xl-4 offset-xl-8">
                    <div class="sign-in-sign-up-content">
                        <form class="sign-in-sign-up-form">
                            <div class="text-center mb-4 application-logo">
                                <img :src="publicPath+'/uploads/logo/'+appLogo" alt="" class="img-fluid logo">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <h5 class="text-center">{{ trans('lang.reset_password') }}</h5>
                                </div>
                            </div>
                            <div v-if="alertMessage.length>0" class="alertBranch">
                                <div class="alert alert-warning alertBranch" role="alert">
                                    {{alertMessage}}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="email">{{ trans('lang.login_email') }}</label>
                                    <input id="email"
                                           v-validate="'required'"
                                           v-model="email"
                                           type="email"
                                           name="email"
                                           class="form-control"
                                           :class="{'is-invalid': errors.email}"
                                           readonly>
                                    <div class="heightError">
                                        <small class="text-danger" v-show="errors.has('email')">
                                            {{ errors.first('email') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col s12">
                                    <label for="email">{{ trans('lang.login_password') }}</label>
                                    <input id="password" ref="passwordRef" v-model="password" type="password"
                                           name="password" class="form-control"
                                           :class="{'is-invalid': submitted && $v.password.$error}">
                                    <div class="heightError" v-if="submitted && $v.password.$error">
                                        <small class="text-danger" v-if="!$v.password.required">
                                            {{trans('lang.password_is_required')}}
                                        </small>
                                        <small class="text-danger" v-if="!$v.password.minLength">
                                            {{trans('lang.password_must_be_at_least_6_characters')}}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col s12">
                                    <label for="email">{{ trans('lang.confirm_password') }}</label>
                                    <input id="conf-password" v-model="password_confirmation" type="password"
                                           name="conf-password" class="form-control"
                                           :class="{'invalid': submitted && $v.password_confirmation}">
                                    <div class="heightError" v-if="submitted && $v.password_confirmation.$error">
                                        <small class="text-danger" v-if="!$v.password_confirmation.required">
                                            {{trans('lang.confirm_password_is_required')}}
                                        </small>
                                        <small class="text-danger" v-else-if="!$v.password_confirmation.sameAsPassword">
                                            {{trans('lang.passwords_must_match')}}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <common-submit-button :buttonLoader="buttonLoader"
                                                          :isDisabled="isDisabled"
                                                          :isActiveText="isActiveText"
                                                          buttonText="save"
                                                          @submit="changePassword"/>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    import {required, minLength, sameAs} from "vuelidate/lib/validators";

    export default {
        extends: axiosGetPost,

        props: ["token", "email"],
        data() {
            return {
                not_match: false,
                isSubmitted: false,
                preloaderType: 'load',
                hidePreloader: 'hide',
                submitted: false,
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                alertMessage: '',
                password: '',
                password_confirmation: '',

            }
        },

        validations: {
            password: {required, minLength: minLength(6)},
            password_confirmation: {required, sameAsPassword: sameAs('password')}
        },

        created() {
        },

        methods:
            {
                changePassword() {
                    this.submitted = true;

                    this.$v.$touch();
                    if (this.$v.$invalid) {
                        return;
                    }
                    this.$validator.validateAll().then((result) => {
                        if (result) {

                            this.inputFields = {
                                email: this.email,
                                password: this.password,
                            };

                            this.buttonLoader = true;
                            this.isDisabled = true;
                            this.isActiveText = true;

                            let instance = this;
                            instance.axiosPost('/password/reset/' + instance.token, {
                                    token: instance.token,
                                    email: instance.email,
                                    password: instance.password,
                                    password_confirmation: instance.password_confirmation,
                                },
                                function (response) {
                                    instance.redirect("/login");

                                },
                                function (error) {
                                    instance.alertMessage = error.data.message;

                                });
                        }
                    });

                }
            }
    }
</script>