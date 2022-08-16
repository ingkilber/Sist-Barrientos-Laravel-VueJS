<template>
    <div>
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h4 class="m-0">
                        {{ id ? trans('lang.edit_invoice_template') : trans('lang.add_invoice_template') }}</h4>
                </div>
                <div class="col-2 text-right">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
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
                                <label for="title">{{ trans('lang.title') }}</label>
                                <input v-validate="'required'" name="title" type="text" class="form-control" id="title"
                                       v-model="title">
                                <div class="heightError">
                                    <small class="text-danger" v-show="errors.has('title')">{{
                                            errors.first('title')
                                        }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="templateType">{{ trans('lang.type') }}</label>
                                <select v-validate="'required'" data-vv-as="type" name="template_type"
                                        :disabled="parseInt(isDefaultTemplate) === 1 || id !== ''"
                                        v-model="template_type" @change="changeType()" id="templateType"
                                        class="custom-select">
                                    <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                                    <option value="sales">{{ trans('lang.sales') }}</option>
                                    <option value="receiving">{{ trans('lang.receiving') }}</option>
                                </select>
                                <div class="heightError">
                                    <small class="text-danger" v-show="errors.has('template_type')">{{
                                            errors.first('template_type')
                                        }}
                                    </small>
                                </div>
                            </div>
                            <div class="form-group  col-6">
                                <label for="invoice_size">{{ trans('lang.invoice_size') }}</label>
                                <select v-validate="'required'" data-vv-as="type" name="template_type"
                                        v-model="invoice_size" id="invoice_size" class="custom-select">
                                    <option value="" disabled selected>{{ trans('lang.choose_one') }}</option>
                                    <option value="large">{{ trans('lang.large') }}</option>
                                    <option value="small">{{ trans('lang.small') }}</option>
                                </select>
                                <div class="heightError">
                                    <small class="text-danger">
                                        {{ trans('lang.invoice_size_change_warning') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="width">{{ trans('lang.width') }} ({{ widthRange }})</label>
                                <input
                                    @input="invoiceWidthValidation"
                                    name="width" type="number" class="form-control"
                                    :min="invoice_size === 'large' ? 95 : 70"
                                    :max="invoice_size === 'large' ? 160 : 104"
                                    id="width"
                                    v-model="invoice_width">
                            </div>
                            <div class="form-group col-6">
                                <label for="templateType">{{ trans('lang.is_default') }}</label>
                                <div class=" d-flex align-items-center">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="is_default_template" class="custom-control-input"
                                               id="default-template-yes" value="1" v-model="isDefaultTemplate">
                                        <label class="custom-control-label" for="default-template-yes">{{
                                                trans('lang.yes')
                                            }}</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline"
                                         v-if="parseInt(isDefaultTemplate) !== 1">
                                        <input type="radio" name="is_default_template" class="custom-control-input"
                                               id="default-template-no" checked="checked" value="0"
                                               v-model="isDefaultTemplate">
                                        <label class="custom-control-label" for="default-template-no">{{
                                                trans('lang.no')
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-12">
                                <div class="form-group">
                                    <textarea class="form-control" rows="3" v-model="content"
                                              id="custom-content"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light rounded p-2 mb-3">
                            <h6 class="mb-3">{{ trans('lang.independent_chips') }}</h6>
                            <div class="">
                                <div class="chip" v-for="reset in independentChips">
                                    {{ reset }}
                                </div>
                            </div>
                        </div>
                        <div class="bg-light rounded p-2 mb-3">
                            <h6 class="mb-3">{{ trans('lang.table_chips') }}</h6>
                            <div class="">
                                <div class="chip" v-for="reset in tableChips">
                                    {{ reset }}
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-7 mb-0">
                                <button class="btn btn-primary app-color mobile-btn" type="submit"
                                        :disabled="is_disabled" @click.prevent="is_disable(), save()">
                                    {{ trans('lang.save') }}
                                </button>
                                <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal"
                                        @click.prevent="">
                                    {{ trans('lang.cancel') }}
                                </button>
                            </div>
                            <div class="form-group col-5 mb-0">
                                <div class="text-right">
                                    <button class="btn btn-danger waves-effect waves-light mobile-btn ml-auto"
                                            v-if="isReStoreShow" @click.prevent="restoreToDefault()">
                                        {{ trans('lang.restore_default') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <confirmation-modal
            id="confirm-restore"
            :message="'content_of_the_invoice_will_be_restored_to_new_default_content_of_app_version'"
            :messageHeader="'warning'"
            :firstButtonName="'yes'" :secondButtonName="'no'"
            @confirmationModalButtonAction="confirmationModalButtonAction"
        >

        </confirmation-modal>

    </div>
</template>

<script>

import axiosGetPost from '../../../helper/axiosGetPostCommon';

export default {

    props: ['id', 'modalID', 'all_invoice_templates'],
    extends: axiosGetPost,
    data() {
        return {
            parseInt,
            isActive: false,
            title: '',
            template_type: !this.id ? 'sales' : '',
            isDefaultTemplate: 0,
            content: '',
            isCustom: '',
            invoice_size: 'small',
            invoice_width: '',
            independentChips: [
                "{app_logo}", "{app_name}", "{table_name}", "{invoice_id}", "{shipment_info}",
                "{supplier_name}", "{employee_name}", "{date}", "{time}", "{customer_name}", "{phone_number}", "{address}", "{tin}", "{note}"
            ],
            tableChips: [
                "{item_details}",
                "{sub_total}",
                "{tax}",
                "{discount}",
                "{total}",
                "{payment_details}",
                "{exchange}",
                "{due}",
                "{barcode}",

            ],
            is_disabled: false,
            restoreButtonTriggered: false,
            isReStoreShow: false,
            allInvoiceTemplates: [],
            widthRange: '',
            app_logo_template: '<img src="{logo_source}" width="100" class="img-fluid" alt="">'
        }
    },
    watch: {
        invoice_width: {
            handler: function (value) {
                if (value) {
                    this.content = this.content.replace(/class="thermal-invoice" style="width:.*(\d)[''mmpxremcminptpcemvhvw]*/g,
                        "class=\"thermal-invoice\" style=\"width: " + value + 'mm'
                    )
                } else {
                    this.content = this.content.replace(/class="thermal-invoice" style="width:.*(\d)[''mmpxremcminptpcemvhvw]*/g,
                        "class=\"thermal-invoice\" style=\"width: " + this.invoice_size === 'large' ? 190 : 70 + 'mm'
                    )
                }
                $("#custom-content").summernote('code', this.content);

            },
            immediate: true
        },
        invoice_size: {
            handler: function (value) {
                this.widthRange = value === 'large' ? '95 mm to 160 mm' : '70 mm to 104 mm';

                if (!this.id) {
                    this.invoice_width = value === 'large' ? 95 : 70;
                }

                if (this.allInvoiceTemplates.length > 0) {
                    this.setTemplate();
                }
            },
            immediate: true
        },
        template_type: function () {
            if (!this.id) {
                this.setTemplate();
            }
        }
    },
    created() {

        this.allInvoiceTemplates = this.all_invoice_templates;
        if (this.id) {
            this.getInvoiceTemplateData('/get-invoice-edit-data/' + this.id);
        } else {
            this.getAddInvoice()
        }
    },
    mounted() {

        let instance = this;

        $("#custom-content").summernote(
            {
                callbacks: {
                    onChange: function () {
                        instance.content = $(this).summernote("code");
                    }
                }
            }
        );
    },
    methods: {
        changeType() {
            if (this.template_type === "receiving") {
                const shipmentInfoIndex = this.independentChips.indexOf('{shipment_info}');
                if (shipmentInfoIndex >= 0) {
                    this.independentChips.splice(shipmentInfoIndex, 1);
                }

                const noteIndex = this.independentChips.indexOf('{note}');
                if (noteIndex >= 0) {
                    this.independentChips.splice(noteIndex, 1);
                }

            } else {
                this.independentChips = [...this.independentChips, "{shipment_info}", "{note}"]
            }
        },
        save() {
            this.content = this.content.replace('{invoice_width}', this.invoice_width + "mm");
            this.content = this.content.replace('{app_logo}', this.app_logo_template);

            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.inputFields = {
                        title: this.title,
                        template_type: this.template_type,
                        is_default_template: this.isDefaultTemplate,
                        invoice_size: this.invoice_size,
                        content: this.content,
                    };

                    if (this.id) {
                        this.postDataMethod('/save-invoice-template/' + this.id, this.inputFields);
                    } else {
                        this.postDataMethod('/add-invoice-template', this.inputFields);
                    }
                }
            });
        },
        getInvoiceTemplateData(route) {

            let instance = this;
            instance.setPreLoader(false);
            instance.axiosGet(route,
                function (response) {
                    instance.title = response.data.template_title;
                    instance.template_type = response.data.template_type;
                    instance.isDefaultTemplate = response.data.is_default_template;
                    instance.content = response.data.content;
                    instance.invoice_size = response.data.invoice_size;
                    instance.isReStoreShow = response.data.isReStoreShow;
                    instance.content = instance.content.replace(instance.app_logo_template, '{app_logo}');
                    instance.invoice_width = Number(instance.hasWidth() ? instance.hasWidth() : (instance.invoice_size === 'large' ? 190 : 70));

                    //showing shipment info only for sales invoice
                    if (instance.template_type === "sales") {
                        instance.independentChips.push('{shipment_info}', '{note}');
                    }

                    $("#custom-content").summernote("code", instance.content);
                    instance.setPreLoader(true);
                },
                function () {
                    instance.setPreLoader(true);
                },
            );
        },
        hasWidth() {
            let mySubString = this.content.substring(
                this.content.lastIndexOf("thermal-invoice\" style=\"width:") + 1,
                this.content.lastIndexOf(";")
                ),
                numberPattern = /\d+/g,
                invoiceWidth = mySubString.match(numberPattern);

            return invoiceWidth.length > 0 ? invoiceWidth[0] : false;

        },
        postDataThenFunctionality() {
            let instance = this;
            instance.$hub.$emit('reloadDataTable');
            $(this.modalID).modal('hide');
        },
        restoreToDefault() {
            $('#confirm-restore').modal('show');
        },
        confirmationModalButtonAction() {
            this.content = '';
            this.restoreButtonTriggered = true;
            this.save();
            $('#confirm-restore').modal('hide');
        },
        is_disable() {
            this.is_disabled = true;
            this.restoreButtonTriggered = false;
        },
        invoiceWidthValidation() {

            if (this.invoice_size === 'large') {
                if (this.invoice_width > 160) {
                    this.invoice_width = 160;
                }

                if (this.invoice_width < 95) {
                    this.invoice_width = 95;
                }
            }

            if (this.invoice_size === 'small') {
                if (this.invoice_width > 104) {
                    this.invoice_width = 104;
                }

                if (this.invoice_width < 70) {
                    this.invoice_width = 70;
                }
            }
        },
        setTemplate() {
            let instance = this,
                tempInvoice = instance.allInvoiceTemplates.find((e) => e.invoice_size === instance.invoice_size && e.template_type === instance.template_type),
                tempInvoiceTemplate = tempInvoice.custom_content === '' ? tempInvoice.default_content : tempInvoice.custom_content;

            instance.content = tempInvoiceTemplate.replace(instance.app_logo_template, '{app_logo}');

            $("#custom-content").summernote("code", instance.content);
        },
        getAddInvoice() {
            let instance = this,
                tempInvoice = this.allInvoiceTemplates.find((e) => e.invoice_size === 'small' && e.template_type),
                tempInvoiceTemplate = tempInvoice.default_content;
            instance.content = tempInvoiceTemplate.replace(instance.app_logo_template, '{app_logo}');
            $("#custom-content").summernote("code", instance.content);
        }
    }
}
</script>