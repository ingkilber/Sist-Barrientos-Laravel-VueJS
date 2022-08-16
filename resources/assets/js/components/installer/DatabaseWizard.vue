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
                            <div class="card-header app-color text-white border-0 rounded text-center text-capitalize p-4">
                                <h4 class="mb-0">
                                    {{ trans('lang.install_gain_pos') }}
                                </h4>
                            </div>
                        </div>

                        <!--Database credential-->
                        <div class="card border-0 shadow mb-4">
                            <div class="card-header app-color text-white p-4">
                                <h5 class="mb-0">
                                    <i class="la la-database"/>
                                    {{ trans('lang.database_configuration')}}
                                </h5>
                            </div>
                            <div class="card-body p-5">
                                <div class="form-group row align-items-center">
                                    <label for="database_connection" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_connection')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <select v-model="setupInfo.database_connection" id="database_connection"
                                                class="form-control">
                                            <option value disabled>{{ trans('lang.choose_one') }}</option>
                                            <option v-for="connection in connectionList"
                                                    :value="connection.id">
                                                {{ connection.value }}
                                            </option>
                                        </select>
                                        <div class="heightError mb-3" v-if="checkError('database_connection')">
                                            <small class="text-danger" v-for="message in errorCollection.database_connection">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label for="database_hostname" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_hostname')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="database_hostname"
                                               class="form-control"
                                               type="text"
                                               v-model="setupInfo.database_hostname"
                                               :placeholder="trans('lang.enter_database_hostname')"/>
                                        <div class="heightError mb-3" v-if="checkError('database_hostname')">
                                            <small class="text-danger" v-for="message in errorCollection.database_hostname">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group row align-items-center">
                                    <label for="database_port" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_port')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="database_port"
                                               class="form-control"
                                               type="text"
                                               v-model="setupInfo.database_port"
                                               :placeholder="trans('lang.enter_database_port')"/>
                                        <div class="heightError mb-3" v-if="checkError('database_port')">
                                            <small class="text-danger" v-for="message in errorCollection.database_port">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group row align-items-center">
                                    <label for="database_name" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_name')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="database_name"
                                               class="form-control"
                                               type="text"
                                               v-model="setupInfo.database_name"
                                               :placeholder="trans('lang.enter_database_name')"/>
                                        <div class="heightError mb-3" v-if="checkError('database_name')">
                                            <small class="text-danger" v-for="message in errorCollection.database_name">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center">
                                    <label for="database_username" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_username')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="database_username"
                                               class="form-control"
                                               type="text"
                                               v-model="setupInfo.database_username"
                                               :placeholder="trans('lang.enter_database_username')"/>
                                        <div class="heightError mb-3" v-if="checkError('database_username')">
                                            <small class="text-danger" v-for="message in errorCollection.database_username">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row align-items-center mb-0">
                                    <label for="database_password" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.database_password')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="database_password"
                                               class="form-control"
                                               type="password"
                                               v-model="setupInfo.database_password"
                                               :placeholder="trans('lang.enter_database_password')"/>
                                        <div class="heightError mb-3" v-if="checkError('database_password')">
                                            <small class="text-danger" v-for="message in errorCollection.database_password">
                                                {{message}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Purchase code-->
                        <div class="card border-0 shadow mb-4">
                            <div class="card-header app-color text-white p-4">
                                <h5 class="mb-0">
                                    <i class="la la-key"/>
                                    {{ trans('lang.purchase_code')}}
                                </h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="form-group row align-items-center mb-0">
                                    <label for="code" class="col-sm-3 mb-sm-0">
                                        {{ trans('lang.code')}}
                                    </label>
                                    <div class="col-sm-9">
                                        <input id="code"
                                               class="form-control"
                                               type="text"
                                               v-model="setupInfo.code"
                                               :placeholder="trans('lang.enter_code')"/>
                                        <div class="heightError mb-2" v-if="checkError('code')">
                                            <small class="text-danger" v-for="message in errorCollection.code">
                                                {{message}}
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
                                                  buttonText="save_and_next"
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
    name: "DatabaseWizard",
    extends: axios,
    data() {

        return {
            buttonLoader: false,
            isActiveText: false,
            isDisabled: false,
            preloader:false,
            errorCollection: {},
            connectionList: [

                {id: 'mysql', value: this.trans('lang.mysql')},
                {id: 'pgsql', value: this.trans('lang.pgsql')},
                {id: 'sqlsrv', value: this.trans('lang.sqlsrv')},
            ],
            setupInfo: {
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

            const formData = {
                ...this.setupInfo,
            };

            axios.post(window.appConfig.appUrl + '/app/environment/database', formData)
                .then(response => {
                    window.location =  this.appUrl + "/app/environment/admin";
                })
                .catch(error => {

                    this.buttonLoader = false;
                    this.isDisabled = false;
                    this.isActiveText = false;
                    this.preloader = false;
                    this.errorToaster(this.trans('lang.something_wrong'));
                    if(error.response){
                        error.response.status === 422 ? this.errorCollection = error.response.data.errors : this.errorToaster(error.response.data.message);
                    }
                });

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
}
</script>

<style scoped lang="scss">

label{
    font-size: 1rem;
}
input{
    &::placeholder{
        color: lighten(black,70%);
    }
}

</style>

