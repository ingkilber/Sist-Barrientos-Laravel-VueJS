<template>
    <div class="back-img">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-6 col-lg-4 offset-lg-8 col-xl-4 offset-xl-8 pl-0">
                    <div class="sign-in-sign-up-content">
                        <form class="sign-in-sign-up-form">
                            <div class="text-center mb-4 application-logo">
                                <img :src="publicPath+'/uploads/logo/'+appLogo" alt="" class="img-fluid logo">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <h6 class="text-center mb-0">
                                        {{ trans('lang.forgot_password') }}
                                    </h6>
                                    <label class="text-center d-block">
                                        {{ trans('lang.enter_email_address') }}
                                    </label>
                                </div>
                            </div>
                            <div v-if="alertMessage.length>0" class="alertBranch">
                                <div class="alert alert-warning alertBranch" role="alert">
                                    {{alertMessage}}
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="email"> {{ trans('lang.login_email') }}</label>
                                    <input id="email"
                                           v-validate="'required'"
                                           v-model="email"
                                           type="email"
                                           name="email"
                                           class="form-control"
                                           :class="{ 'is-invalid': submitted && errors.has('email') }">
                                    <div class="heightError" v-if="submitted && errors.has('email')">
                                        <small class="text-danger" v-show="errors.has('email')">
                                            {{ errors.first('email') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <common-submit-button class="btn-block text-center auth-button"
                                                          :buttonLoader="buttonLoader"
                                                          :isDisabled="isDisabled"
                                                          :isActiveText="isActiveText"
                                                          buttonText="submit"
                                                          v-on:submit="passRecover"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <span>{{ trans('lang.if_you_remember_your_password') }}</span> &nbsp;
                                    <a href="#"
                                       @click="login"
                                       class="bluish-text">
                                        {{ trans('lang.login') }}
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

    export default {
        extends: axiosGetPost,
        data() {
            return {
                email: '',
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                alertMessage: false,
                submitted: false,
            }
        },
        methods:
            {
                passRecover() {
                    this.submitted = true;
                    this.$validator.validateAll().then((result) => {
                        if (result) {
                            this.buttonLoader = true;
                            this.isDisabled = true;
                            this.isActiveText = true;

                            let instance = this;
                            instance.loginPostMethod('/recover', {
                                    email: this.email
                                }
                            );
                        }
                    });
                },
                loginPostSucces(response) {
                    let instance = this;
                    instance.showSuccessAlert(response.data.data.message);
                    instance.buttonLoader = false;
                    instance.isDisabled = false;
                    instance.isActiveText = false;
                },
                loginPostError(response) {

                    let instance = this;
                    instance.buttonLoader = false;
                    instance.isDisabled = false;
                    instance.isActiveText = false;
                    instance.alertMessage = response.data.error;
                },
                login() {
                    let instance = this;
                    instance.redirect('/');
                }
            }
    }
</script>