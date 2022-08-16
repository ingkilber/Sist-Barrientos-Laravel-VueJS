<template>
    <form>
        <div class="form-row margin-top">
            <div class="form-group col-12">
                <label for="new_password">{{ trans('lang.new_password') }}</label>
                <input id="new_password" ref="password" v-model="password" name="password" type="password"
                       class="form-control" :class="{'is-invalid': submitted && $v.password.$error}">
                <div class="heightError" v-if="submitted && $v.password.$error">
                    <small class="text-danger" v-if="!$v.password.required">{{trans('lang.password_is_required')}}
                    </small>
                    <small class="text-danger" v-if="!$v.password.minLength">
                        {{trans('lang.password_must_be_at_least_6_characters')}}
                    </small>
                </div>
            </div>
            <div class="form-group col-12 margin-top">
                <label for="confirm_password">{{ trans('lang.confirm_password') }}</label>
                <input id="confirm_password" v-model="password_confirmation" name="password_confirmation"
                       data-vv-as="password" type="password" class="form-control"
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
            <div class="col-12">
                <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled" :isActiveText="isActiveText"
                                      buttonText="save" v-on:submit="changePassword"></common-submit-button>
            </div>
        </div>
    </form>

</template>
<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';
    import {required, minLength, sameAs} from "vuelidate/lib/validators";

    export default {
        extends: axiosGetPost,
        props: ["user"],
        data() {
            return {

                password: '',
                password_confirmation: '',
                submitted: false,
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
            }
        },
        validations: {
            password: {required, minLength: minLength(6)},
            password_confirmation: {required, sameAsPassword: sameAs('password')}
        },
        methods: {
            changePassword() {
                this.submitted = true;
                let instance = this;
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return;
                }
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            password: this.password,
                            password_confirmation: this.password_confirmation,
                        };
                        this.buttonLoader = true;
                        this.isDisabled = true;
                        this.isActiveText = true;
                        this.postNewPassword('/update-password/' + this.user.id, {
                            password: this.password,
                            password_confirmation: this.password_confirmation,
                        })
                    }
                });
            },
            postNewPassword(route, fields) {
                let instance = this;
                instance.axiosPost(route, fields,
                    function (response) {
                        instance.redirect("/logout");
                    },
                    function (error) {
                        instance.buttonLoader = false;
                        instance.isDisabled = false;
                        instance.isActiveText = false;
                    });
            },
        }
    }
</script>