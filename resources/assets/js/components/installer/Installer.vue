<template>
  <div class="p-3">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div v-if="preloader">
          <pre-loader></pre-loader>
        </div>
        <div v-else>
          <form>
            <div class="card border-0 shadow mb-4">
              <div
                  class="card-header position-relative  app-color text-white border-0 rounded text-center text-capitalize p-4">
                <a href="#" class="position-absolute back" @click.prevent="previous">&#10094;
                  {{ trans('lang.back_page') }} </a>
                <h4 class="mb-0">
                  {{ trans('lang.install_gain_pos') }}
                </h4>
              </div>
            </div>
            <!--Admin login-->
            <div class="card border-0 shadow mb-4">
              <div class="card-header app-color text-white p-4">
                <h5 class="mb-0">
                  <i class="la la-user"/>
                  {{ trans('lang.admin_login_details') }}
                </h5>

              </div>
              <div class="card-body p-4">
                <div class="form-group row align-items-center">
                  <label for="full_name" class="col-sm-3 mb-sm-0">
                    {{ trans('lang.full_name') }}
                  </label>
                  <div class="col-sm-9">
                    <input id="full_name"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.full_name"
                           :placeholder="trans('lang.enter_full_name')"/>
                    <div class="heightError mb-2" v-if="checkError('first_name')">
                      <small class="text-danger" v-for="message in errorCollection.first_name">
                        {{ message }}
                      </small>
                    </div>
                  </div>

                </div>
                <div class="form-group row align-items-center">
                  <label for="email_address" class="col-sm-3 mb-sm-0">
                    {{ trans('lang.installer_email') }}
                  </label>
                  <div class="col-sm-9">
                    <input id="email_address"
                           class="form-control"
                           name="email"
                           type="email"
                           v-model="setupInfo.email"
                           :placeholder="trans('lang.enter_email')"/>
                    <div class="heightError mb-2" v-if="checkError('email')">
                      <small class="text-danger" v-for="message in errorCollection.email">
                        {{ message }}
                      </small>
                    </div>

                  </div>

                </div>
                <div class="form-group row align-items-center mb-0">
                  <label for="installer_password" class="col-sm-3 mb-sm-0">
                    {{ trans('lang.installer_password') }}
                  </label>
                  <div class="col-sm-9">
                    <input id="installer_password"
                           class="form-control"
                           type="password"
                           v-model="setupInfo.password"
                           :placeholder="trans('lang.enter_password')"/>
                    <div class="heightError mb-2" v-if="checkError('password')">
                      <small class="text-danger" v-for="message in errorCollection.password">
                        {{ message }}
                      </small>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <div class="">

              <common-submit-button class="btn-block btn-lg text-center app-color"
                                    :buttonLoader="buttonLoader"
                                    :isDisabled="isDisabled"
                                    :isActiveText="isActiveText"
                                    buttonText="install"
                                    v-on:submit="submit"/>


            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: "Layout",
  extends: axios,
  data() {

    return {
      buttonLoader: false,
      isActiveText: false,
      isDisabled: false,
      preloader: false,
      errorCollection: {},

      setupInfo: {
        full_name: '',
        database_connection: 'mysql'
      },
    }
  },
  methods: {
    submit() {
      this.errorCollection = {};
      this.buttonLoader = true;
      this.isDisabled = true;
      this.isActiveText = true;
      this.preloader = true;

      const {first_name, last_name} = this.names;
      const formData = {
        ...this.setupInfo,
        first_name, last_name
      };


      axios.post(window.appConfig.appUrl + '/app/environment/install', formData)
          .then(response => {
            this.preloader = false;
            this.successToaster(response.data.message);
            window.location = this.appUrl + "/";
          })
          .catch(error => {
            this.buttonLoader = false;
            this.isDisabled = false;
            this.isActiveText = false;
            this.preloader = false;
            this.errorToaster(this.trans('lang.something_wrong'));
            if (error.response) {
              error.response.status === 422 ? this.errorCollection = error.response.data.errors : this.errorToaster(error.response.data.message);
            }
          });

    },
    previous() {
      window.location = window.appConfig.appUrl + "/app/environment";
    },
    checkError(value) {
      return value in this.errorCollection
    },
    errorToaster(message) {
      this.$toasted.global.error({
        message: message,
      });
    },
    successToaster(message) {
      this.$toasted.global.success({
        message: message,
      });
    }
  },

  computed: {
    names() {
      const full_name_spited = this.setupInfo.full_name.split(' ').filter(name => name);

      if (full_name_spited.length) {
        if (full_name_spited.length === 2) {
          return {
            first_name: full_name_spited[0],
            last_name: full_name_spited[1]
          }
        } else if (full_name_spited.length === 1) {
          return {
            first_name: full_name_spited[0],
            last_name: ''
          }
        } else if (full_name_spited.length === 3) {
          return {
            first_name: `${full_name_spited[0]} ${full_name_spited[1]}`,
            last_name: full_name_spited[2]
          }
        } else {
          return {
            first_name: full_name_spited[0],
            last_name: full_name_spited.slice(1, full_name_spited.length).join(' ')
          }
        }
      }
      return {
        first_name: '',
        last_name: ''
      }
    },
  },
}
</script>

<style scoped lang="scss">

label {
  font-size: 1rem;
}

input {
  &::placeholder {
    color: lighten(black, 70%);
  }
}

a {
  &.back {
    left: 1.5rem;
    color: #ffffff;

    &:hover {
      color: lighten(black, 70%);
    }
  }
}

</style>

