<template>
    <div>
        <div
                class="modal fade"
                id="barcode-preview-modal"
                tabindex="-1"
                role="dialog"
                aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="bluish-text">{{ trans('lang.barcode_preview') }}</h5>
                            </div>
                            <div class="col-2 text-right">
                                <button type="button"
                                        class="close"
                                        data-dismiss="modal"
                                        aria-label="Close"
                                        @click.prevent="closePreviewModal">
                                    <i class="la la-close icon-modal-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="barcode" class="modal-body app-bg-color p-3">
                        <div class="bg-white rounded p-3">
                            <div class="page full-a4" v-for="(barcode,index) in newBarcode">
                                <div class="row equal">
                                    <div
                                            class="mb-4"
                                            :class="[(totalColumns==1?'col-12':''),(totalColumns==2?'col-6':''),(totalColumns==3?'col-4':'')]"
                                            v-for="(variant,index) in barcode"
                                    >
                                        <div class="barcode-container">
                                            <h4 v-if="includeAppNameInBarcode == 1" style="margin: 0; padding: 0;">{{app_name}}</h4>
                                            <!-- class limit-title will made eclips after product title but client use large name so need to find another solution -->
                                            <!-- <span class="limit-title" style="margin: 0; padding: 0 3px;">{{variant.title}}</span>-->
                                            <!--<span style="margin: 0; padding: 0 3px;">{{variant.title}}</span>-->
                                            <span class="limit-title" style="margin: 0; padding: 0 3px;">{{variant.title}}</span>
                                            <br>
                                            <span class="barcode-variant-title" style="margin: 0; padding: 0;"
                                                  v-if="variant.variant_title !== 'default_variant'">
                                                {{variant.variant_title }}

                                            </span>
                                           <div  v-html="variant.newBarcode"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                                type="button"
                                class="btn btn-primary app-color"
                                @click="closePreviewModal"
                        >{{ trans('lang.cancel') }}</button>
                        <button
                                type="button"
                                class="btn btn-primary app-color"
                                @click="print()"
                        >{{ trans('lang.print') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['data', 'totalColumns','barcodeHeight', 'totalRows', 'copies','includeAppNameInBarcode'],
        data() {
            return {

            }
        },
        computed: {
            newBarcode: function() {
                let checkedItem = [],
                    i,
                    count = this.copies,
                    barcodePerPage = this.totalColumns * this.totalRows,
                    index,
                    newArray,
                    newBarcode = [];
                this.data.forEach(function(element) {
                    element.variants.forEach(function(item) {
                        item.title = element.title;
                        for (i = 0; i < count; i++) {
                            checkedItem.push(item);
                        }
                    });
                });

                for (
                    index = 0;
                    index <= checkedItem.length;
                    index += barcodePerPage
                ) {
                    newArray = checkedItem.filter(function(element, i) {
                        return index <= i && i < index + barcodePerPage;
                    });
                    newBarcode.push(newArray);
                }
                return newBarcode;
            }
        },
        mounted() {
            let instance = this;
            $("#barcode-preview-modal").on("hidden.bs.modal", function() {
                instance.closePreviewModal();
            });

            // Limit Product Title
            let maximum = 15;
            let total, string;
            $(".limit-title").each(function() {
                string = String($(this).html());
                total = string.length;
                string =
                    total <= maximum
                        ? string
                        : string.substring(0, maximum + 1) + "...";
                $(this).html(string);
            });
        },
        methods: {
            closePreviewModal() {
                this.$emit("resetImport");
                $("#barcode-preview-modal").modal("hide");
            },
            print() {
                $("#barcode").printThis({
                    importCSS: true,
                    importStyle: true,
                    printContainer: true,
                    header: null
                });
                this.$emit("resetGetInvoice", false);
            }
        }
    };
</script>
