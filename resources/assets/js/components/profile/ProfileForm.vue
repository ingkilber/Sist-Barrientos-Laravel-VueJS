<template>
    <form>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="first_name">{{ trans('lang.first_name') }}</label>
                <input id="first_name" v-validate="'required|alpha_spaces'" v-model="first_name" type="text"
                       data-vv-as="first name" name="first_name" class="form-control"
                       :class="{ 'is-invalid': submitted && errors.has('first_name')}">
                <div class="heightError" v-if="submitted && errors.has('first_name')">
                    <small class="text-danger" v-show="errors.has('first_name')">{{ errors.first('first_name') }}
                    </small>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="last_name">{{ trans('lang.last_name') }}</label>
                <input id="last_name" v-validate="'required|alpha_spaces'" v-model="last_name" type="text"
                       data-vv-as="last name" name="last_name" class="form-control"
                       :class="{ 'is-invalid': submitted && errors.has('last_name') }">
                <div class="heightError" v-if="submitted && errors.has('last_name')">
                    <small class="text-danger" v-show="errors.has('last_name')">{{ errors.first('last_name') }}</small>
                </div>
            </div>
            <div class="form-group col-md-12">
                <label for="email">{{ trans('lang.email_address') }}</label>
                <input id="email" v-validate="'required'" v-model="email" type="email" name="email" class="form-control"
                       :class="{ 'is-invalid': submitted && errors.has('email') }">
                <div class="heightError" v-if="submitted && errors.has('email')">
                    <small class="text-danger" v-show="errors.has('email')">{{ errors.first('email') }}</small>
                </div>
            </div>
            <div class="form-group col-md-6 profileBirthDate">
                <label for="birth-date">{{ trans('lang.date_of_birth') }}</label>


                <datepicker id="birth-date" :disabledDates="state.disabledDates" v-model="selectedDate"
                            :format="profile.dateformat"
                            :bootstrap-styling="true"></datepicker>
            </div>

            <div class="form-group col-md-6">
                <label for="gender">{{ trans('lang.gender') }}</label>
                <select v-model="profile.gender" id="gender" class="custom-select">
                    <option value="" disabled>{{ trans('lang.choose_one') }}</option>
                    <option value="Male"> {{ trans('lang.male') }}</option>
                    <option value="Female"> {{ trans('lang.female') }}</option>
                    <option value="Others"> {{ trans('lang.others') }}</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label>{{ trans('lang.change_profile_image') }}</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="profile-image-change" accept="image/*"
                           @change="profileImageChange">
                    <label class="custom-file-label text-truncate" for="profile-image-change">{{
                        trans('lang.image_only') }}</label>
                </div>
            </div>
            <div class="col-12">
                <common-submit-button :buttonLoader="buttonLoader" :isDisabled="isDisabled" :isActiveText="isActiveText"
                                      buttonText="save" v-on:submit="updateProfile"></common-submit-button>
            </div>
        </div>
    </form>
</template>

<script>
    import Datepicker from 'vuejs-datepicker';
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        props: ["profile"],
        extends: axiosGetPost,
        components: {
            Datepicker
        },
        data() {
            return {
                first_name: '',
                last_name: '',
                email: '',
                date_of_birth: null,
                gender: '',
                password: '',
                avatar: '',
                isActive: 1,
                setNone: '',
                alertMessage: '',
                maxDate: '',
                currentDate: '',
                state: {},
                selectedDate: this.profile.date_of_birth,
                birthDate: '',
                remember: true,
                buttonLoader: false,
                isActiveText: false,
                isDisabled: false,
                submitted: false,
            }
        },
        mounted() {
            this.first_name = this.profile.first_name;
            this.last_name = this.profile.last_name;
            this.email = this.profile.email;
            this.state = {
                disabledDates: {
                    to: new Date(1001, 0, 5),
                    from: new Date(),
                }
            }
            var instance = this;
            $('.datepicker').on('change', function () {
                var dob = $(this).val();
                instance.profile.date_of_birth = dob;
            });


            $('#gender').on('change', function () {
                var gender2 = $(this).val();
                instance.profile.gender = gender2
            });

            this.currentDate = moment().format('l');
            this.maxDate = moment(this.currentDate, 'MM/DD/YYYY').format('YYYY-MM-DD');
        },

        methods: {
            customFormatter(date) {
                return moment(date).format(this.dateFormat);
            },
            profileImageChange(event) {
                let input = event.target;

                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = (e) => {
                        this.profile.avatar = e.target.result;
                        this.avatar = e.target.result;
                    }

                    reader.readAsDataURL(input.files[0]);
                    let fileName = input.files[0].name;
                    $('#profile-image-change').next('.custom-file-label').html(fileName);
                }
                else {
                    this.profile.avatar = '';
                    $('#profile-image-change').next('.custom-file-label').html(this.trans('lang.image_only'));
                }
            },
            updateProfile() {

                this.submitted = true;

                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            email: this.email,
                            first_name: this.first_name,
                            last_name: this.last_name,
                        };
                        this.buttonLoader = true;
                        this.isActiveText = true;
                        this.isDisabled = true;
                        this.postProfileData('/profile/' + this.profile.id, {
                            first_name: this.first_name,
                            last_name: this.last_name,
                            email: this.email,
                            date_of_birth: this.selectedDate,
                            gender: this.profile.gender,
                            password: this.profile.password,
                            avatar: this.profile.avatar,
                        });
                    }
                });
            },
            postProfileData(route, fields) {
                let instance = this;
                instance.axiosPost(route, fields,
                    function (response) {
                        window.location.reload();
                    },
                    function (response) {
                        let instance = this;
                        instance.buttonLoader = false;
                        instance.isActiveText = false;
                        instance.isDisabled = false;
                    });
            },
        },
    }
</script>