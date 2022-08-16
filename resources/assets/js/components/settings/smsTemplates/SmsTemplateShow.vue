<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0">{{ trans('lang.'+name) }}</h4>
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

            <div class="container-fluid p-0" v-show="hidePreLoader">
                <div class="row">
                    <div class="col-12">
                        <div class="form-row">
                            <div class="form-group col-12">
                                <label for="subject">{{ trans('lang.subject') }}</label>
                                <input type="text" class="form-control" id="subject" v-model="subject">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12">
                                <label for="subject">{{ trans('lang.sms_content') }}</label>
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" v-model="custom_content"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-12" v-if="name=='pos_sms'">
                                <div class="chip" v-for="reset in available_variables.pos_sms">
                                    {{reset}}
                                </div>
                            </div>
                            <div class="form-group col-12" v-if="name=='customer_welcome_sms'">
                                <div class="chip" v-for="reset in available_variables.customer_welcome_sms">
                                    {{reset}}
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <button class="btn btn-primary app-color mobile-btn" type="submit" :disabled="is_disabled" @click.prevent="is_disable(),save()">{{ trans('lang.save') }}</button>
                                <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{ trans('lang.cancel') }}</button>
                                <button class="btn btn-danger waves-effect waves-light mobile-btn ml-auto" v-if="isCustom" @click.prevent="restoreToDefault()">{{trans('lang.restore_default')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {

        props:['id','name','modalID'],
        extends: axiosGetPost,
        data(){
            return {
                subject:'',
                custom_content:'',
                isCustom:'',
                available_variables:{
                    pos_sms:["{app_name}","{first_name}","{last_name}", "{invoice_id}", "{total}"],
                    customer_welcome_sms:["{app_name}","{first_name}","{last_name}"],
                },
                is_disabled:false,
                restoreButtonTriggered:false,
            }
        },

        created(){
            if(this.id)
            {
                this.getSmsTemplateData('/get-sms-template-content/'+this.id);
            }

        },
        mounted(){

            let instance = this;

            $("#custom-content").summernote(
                {
                    callbacks: {
                        onChange: function () {
                            var code = $(this).summernote("code");
                            instance.custom_content = code;
                        }
                    }
                }
            );


        },
        methods : {

            save()
            {
                this.inputFields = {
                    subject:this.subject,
                    custom_content:this.custom_content,
                    template_name:this.name
                };

                if(this.id)
                {
                    this.postDataMethod('/set-sms-custom-content/'+this.id, this.inputFields);

                }

            },
            getSmsTemplateData(route){
                let instance = this;
                instance.setPreLoader(false);
                instance.axiosGet(route,
                    function(response){
                        instance.subject = response.data.smsSubject;
                        instance.custom_content = response.data.content;
                        instance.isCustom = response.data.isCustom;
                        $("#custom-content").summernote("code", instance.custom_content);
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response)
            {
                if(!this.restoreButtonTriggered)
                {
                    $(this.modalID).modal('hide');
                }
                else
                {
                    this.getSmsTemplateData('/get-sms-template-content/'+this.id);

                }
            },
            postDataCatchFunctionality(error)
            {
                this.showErrorAlert(error.data.message);
            },

            restoreToDefault()
            {
                this.custom_content = '';

                this.restoreButtonTriggered = true;

                this.save();

            },

            is_disable()
            {
                this.is_disabled = true;
                this.restoreButtonTriggered = false;
            },
        },
    }
</script>