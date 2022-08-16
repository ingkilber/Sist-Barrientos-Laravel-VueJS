<template>
    <div class="back-img">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8 col-xl-4 offset-xl-8">
                    <div class="sign-in-sign-up-content">
                        <form class="sign-in-sign-up-form">
                            <div class="text-center mb-4 application-logo">
                                <img :src="publicPath+'/uploads/logo/'+appLogo" alt="" class="img-fluid logo">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <h6 class="text-center mb-0">
                                        {{ trans('lang.hi_there') }}
                                    </h6>
                                    <label class="text-center d-block">
                                        {{ trans('lang.sign_up_for_your_new_account') }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <h5 class="text-center">{{ trans('lang.sign_up') }}</h5>
                                </div>
                            </div>
                            <div v-if="alertMessage.length>0" class="alertBranch">
                                <div class="alert alert-warning alertBranch" role="alert">
                                    {{alertMessage}}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="first_name">{{ trans('lang.first_name') }}</label>
                                    <input id="first_name"
                                           v-validate="'required'"
                                           v-model="first_name"
                                           data-vv-as="first name"
                                           type="text"
                                           name="first_name"
                                           class="form-control"
                                           :placeholder="trans('lang.enter_first_name')">
                                    <div class="heightError">
                                        <small class="text-danger" v-show="errors.has('first_name')">
                                            {{ errors.first('first_name') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="last_name">{{ trans('lang.last_name') }}</label>
                                    <input id="last_name"
                                           v-validate="'required'"
                                           v-model="last_name"
                                           data-vv-as="last name"
                                           type="text"
                                           name="last_name"
                                           class="form-control"
                                           :placeholder="trans('lang.enter_last_name')">
                                    <div class="heightError">
                                        <small class="text-danger" v-show="errors.has('last_name')">
                                            {{ errors.first('last_name') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="email">{{ trans('lang.login_email') }}</label>
                                    <input id="email"
                                           v-model="email"
                                           type="email"
                                           name="email"
                                           class="form-control"
                                           readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="password">{{ trans('lang.login_password') }}</label>
                                    <input id="password"
                                           v-model="password"
                                           ref="password"
                                           name="password"
                                           type="password"
                                           class="form-control"
                                           :placeholder="trans('lang.enter_password')"
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
                                <div class="form-group col-12">
                                    <label for="conf-password">{{ trans('lang.confirm_password') }}</label>
                                    <input id="conf-password"
                                           v-model="password_confirmation"
                                           data-vv-as="password"
                                           name="password_confirmation"
                                           type="password"
                                           class="form-control"
                                           :placeholder="trans('lang.confirm_password')"
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
                            <div class="form-row loginButton">
                                <div class="form-group col-12">
                                    <common-submit-button class="btn-block text-center auth-button"
                                                          :buttonLoader="buttonLoader"
                                                          :isDisabled="isDisabled"
                                                          :isActiveText="isActiveText"
                                                          buttonText="sign_up"
                                                          @submit="registersPost"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <a href="#"
                                       @click="login"
                                       class="bluish-text">
                                        <i class="fa fa-sign-in"/>
                                        {{ trans('lang.login_here') }}
                                    </a>
                                </div>
                            </div>
                            <!--<div class="form-row">
                                <div class="col-12">
                                    <p class="text-secondary text-center mt-5">
                                        {{ trans('lang.copyright_text') }}
                                    </p>
                                </div>
                            </div>-->
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
        props: ['emailadd', 'token', 'appurl'],
        data() {
            return {
                first_name: '',
                last_name: '',
                email: this.emailadd,
                password: '',
                password_confirmation: '',
                showPreloader: '',
                alertMessage: '',
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                submitted: false,
            }
        },
        validations: {
            password: {required, minLength: minLength(6)},
            password_confirmation: {required, sameAsPassword: sameAs('password')}
        },
        methods: {
            registersPost() {
                let instance = this;

                this.submitted = true;
                this.$v.$touch();

                if (this.$v.$invalid) {
                    return;
                }
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            first_name: this.first_name,
                            last_name: this.last_name,
                            password: this.password,
                            password_confirmation: this.password_confirmation,
                        };

                        this.buttonLoader = true;
                        this.isDisabled = true;
                        this.isActiveText = true;
                        this.postRegisterData('/register/' + instance.token, {
                            first_name: this.first_name,
                            last_name: this.last_name,
                            email: this.email,
                            password: this.password,
                            password_confirmation: this.password_confirmation,
                        });
                    }
                });
            },
            postRegisterData(route, fields) {
                let instance = this;
                instance.axiosPost(route, fields,
                    function (response) {
                        instance.redirect("/login");
                    },
                    function (error) {
                        instance.buttonLoader = false;
                        instance.isDisabled = false;
                        instance.isActiveText = false;
                        instance.alertMessage = error.data.message;
                    });
            },
            login() {
                let instance = this;
                instance.redirect('/login');
            }
        }
    }
</script>