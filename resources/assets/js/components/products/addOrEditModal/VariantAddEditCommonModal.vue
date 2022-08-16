<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h5 class="bluish-text" v-if="id">{{ trans(modalOptions.editLang) }}</h5>
                    <h5 class="bluish-text" v-else>{{ trans(modalOptions.addLang) }}</h5>
                </div>
                <div class="col-2 text-right">
                    <button type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click.prevent="closeModal()">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader" class="small-loader-container"></pre-loader>
            <form class="form-row margin-top" v-else>
                <div class="form-group col-md-12">
                    {{alertMessage}}
                    <label for="category">{{ trans('lang.name') }}</label>
                    <input v-validate="'required'" name="name" class="form-control" id="category" type="text"
                           v-model="name" :class="{ 'is-invalid': submitted && errors.has('name') }">
                    <div class="heightError" v-if="submitted && errors.has('name')">
                        <small class="text-danger" v-if="errors.has('name')">{{ errors.first('name') }}</small>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary app-color mobile-btn" type="submit" @click.prevent="save()">{{
                        trans('lang.save') }}
                    </button>
                    <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal"
                            @click.prevent="close()">{{ trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props: ['id', 'modalOptions'],
        extends: axiosGetPost,
        data() {
            return {
                name: '',
                alertMessage: '',
                checkStatus: true,
                saveStatus:false,
                submitted:false,
            }
        },
        created() {
            if (this.id) {
                this.getCommonData(this.modalOptions.getDataURL +'/'+ this.id);
            }
        },
        mounted(){
            let instance = this;
            $('#attributes-add-edit-modal').on('hidden.bs.modal', function (e){
               instance.close();
            });
        },

        methods: {
            close() {
                 this.name = '';
                 this.$validator.reset();
                 this.checkStatus = false;
                 this.saveStatus = false;
                 this.$emit('resetProductModal', this.checkStatus,this.saveStatus);
            },
            closeModal(){
                $(this.modalOptions.modalID).modal('toggle')
            },
            save() {
                this.submitted = true;
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {
                            name: this.name,
                        };
                        if (this.id) {
                            this.postDataMethod(this.modalOptions.postDataWithIDURL +'/'+ this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod(this.modalOptions.postDataWithoutIDURL, this.inputFields);
                        }
                    }
                });
            },
            getCommonData(route) {

                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.name = response.data.name;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response = null) {
                $(this.modalOptions.modalID).modal('toggle');
                let instance = this;
                instance.saveStatus = true;
                this.name = '';
                this.$validator.reset();
                this.checkStatus = false;
                this.$emit('resetProductModal', this.checkStatus,this.saveStatus);
                if (!this.modalOptions.turnOffLoader) {
                    this.$hub.$emit('reloadDataTable');
                }
                else {
                    this.$hub.$emit('reloadAttributeData');
                }

            },
            postDataCatchFunctionality(error) {

            },
        },
    }
</script>
