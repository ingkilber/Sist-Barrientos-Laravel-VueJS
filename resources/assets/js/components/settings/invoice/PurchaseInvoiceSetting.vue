<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.purchase_invoice_settings') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="hidePreloader!='hide'"></pre-loader>
            <form v-else>
                <div class="mb-3">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="invoice-prefix">{{ trans('lang.invoice_prefix') }}</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="invoice-prefix"
                                    v-model="invoice.purchase_invoice_prefix"
                            />
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label for="invoice-suffix">{{ trans('lang.invoice_suffix') }}</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="invoice-suffix"
                                    v-model="invoice.purchase_invoice_suffix"
                            />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="invoice-starts-from">{{ trans('lang.invoice_starts_from') }}</label>
                            <input
                                    type="number"
                                    class="form-control"
                                    id="invoice-starts-from"
                                    v-model="invoice.purchase_invoice_starts_from"
                            />
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label>{{ trans('lang.change_invoice_logo') }}</label>
                            <div class="custom-file">
                                <input
                                        type="file"
                                        class="custom-file-input"
                                        id="app-logo"
                                        accept="image/*"
                                        @change="appLogo"
                                />
                                <label
                                        class="custom-file-label text-truncate"
                                        for="app-logo"
                                >{{ trans('lang.image_only') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-5">
                            <label
                                    for="invoice-starts-from"
                            >{{ trans('lang.auto_generate_receipt') }}</label>
                            <div class="form-check">
                                <input
                                        class="form-check-input"
                                        v-model="invoice.purchase_auto_generate_invoice"
                                        type="radio"
                                        name="auto-generate-invoice"
                                        id="auto-generate-invoice-1"
                                        value="1"
                                />
                                <label
                                        for="auto-generate-invoice-1"
                                        class="radio-button-label"
                                >{{ trans('lang.yes') }}</label>
                                <input
                                        class="form-check-input"
                                        v-model="invoice.purchase_auto_generate_invoice"
                                        type="radio"
                                        name="auto-generate-invoice"
                                        id="auto-generate-invoice-2"
                                        value="0"
                                />
                                <label
                                        for="auto-generate-invoice-2"
                                        class="radio-button-label"
                                >{{ trans('lang.no') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                        v-if="permission_key == 'manage'"
                        type="submit"
                        class="btn btn-primary app-color mobile-btn"
                        @click.prevent="invoiceSettingsUpdate()"
                >{{ trans('lang.save') }}</button>
            </form>
        </div>
    </div>
</template>

<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";
    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {
                invoice: {},
                hidePreloader: "",
                invoiceLogo: "",
                preloaderType: "load"
            };
        },
        created() {
            this.getInvoiceSettingData("/purchase-invoice-setting-data");
        },

        mounted() {},
        methods: {
            getInvoiceSettingData(route) {
                let instance = this;
                //get invoice setting data
                this.axiosGet(
                    route,
                    function(response) {
                        instance.currentInvoice =
                            response.data.currentInvoiceNumber;
                        instance.invoice = response.data.invoiceSettings;
                        instance.hidePreloader = "hide";
                    },
                    function(error) {
                        instance.hidePreloader = "hide";
                    }
                );
            },
            appLogo(event) {
                let fileName = event.target.files[0].name;
                $("#app-logo")
                    .next(".custom-file-label")
                    .html(fileName);
                var input = event.target;
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = e => {
                        this.invoiceLogo = e.target.result;
                    };
                    this.invoiceLogo = input.files[0].name;
                    reader.readAsDataURL(input.files[0]);
                } else {
                    this.invoiceLogo = "";
                }
            },
            
            invoiceSettingsUpdate() {
                let instance = this;
                instance.hidePreloader = "load";

                this.inputFields = {
                    invoicePrefix: this.invoice.purchase_invoice_prefix,
                    invoiceSuffix: this.invoice.purchase_invoice_suffix,
                    invoiceStartsFrom: this.invoice.purchase_invoice_starts_from,
                    autoGenerateInvoice: this.invoice.purchase_auto_generate_invoice,
                    invoiceLogo: this.invoiceLogo
                };

                this.axiosPost(
                    "/purchase-invoice-setting-save",
                    this.inputFields,
                    function(response) {
                        instance.hidePreloader = "hide";
                    },
                    function(error) {
                        instance.hidePreloader = "hide";
                        instance.showErrorAlert(error.data.message);
                    }
                );
            }
        }
    };
</script>