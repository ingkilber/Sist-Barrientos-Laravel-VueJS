<template>
    <div>
        <div class="modal fade" id="barcode-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="bluish-text">{{ trans('lang.print_barcode') }}</h5>
                            </div>
                            <div class="col-2 text-right">
                                <button type="button"
                                        class="close"
                                        data-dismiss="modal"
                                        aria-label="Close"
                                        @click.prevent="closeModal">
                                    <i class="la la-close icon-modal-cross"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-contents app-bg-color p-3">
                        <div class="bg-white rounded p-3">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs">
                                <li class="nav-item active">
                                    <a class="nav-link active" data-toggle="tab" href="#all_product" @click="productsLoad()">{{trans('lang.products')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#settings" @click="addSetting()">{{trans('lang.settings')}}</a>
                                </li>
                            </ul>
                            <!-- Tab panes -->
                            <div class="tab-content">

                                <div v-if="isProduct" id="all_product" class="tab-pane fade show active"><br>
                                    <datatable-component class="main-layout-card-content p-0" :options="tableOptions" @data-set="checkboxEmit"></datatable-component>
                                    <br>
                                </div>
                                <div id="settings" class="tab-pane fade">
                                </div>
                            </div>


                            <div v-if="isSetting" class="row mr-0 justify-content-center">
                                <div class="col-12">
                                    <div class="form-group row mt-3">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_columns')}}</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                   id="inlineRadio1" value="option1" @click='column(1)' checked>
                                            <label class="form-check-label" for="inlineRadio1">1</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                   id="inlineRadio2" value="option2" @click='column(2)'>
                                            <label class="form-check-label" for="inlineRadio2">2</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                   id="inlineRadio3" value="option3" @click='column(3)'>
                                            <label class="form-check-label" for="inlineRadio3">3</label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_copies')}}</label>
                                        <select class="custom-select col-sm-6" id="sel1" v-model="copies">
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>4</option>
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.select_how_many_rows_in_every_pages')}}</label>
                                        <select class="custom-select col-sm-6" id="sel" v-model="totalRows">
                                            <option>1</option>
                                            <option>2</option>
                                            <option>3</option>
                                            <option>4</option>
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-6 col-form-label">{{trans('lang.include_application_name')}}</label>
                                        <div class="form-check">
                                            <input class="form-check-input" v-model="includeAppNameInBarcode"
                                                   type="radio" id="offline_mode_enable" value="1">
                                            <label for="offline_mode_enable" class="radio-button-label">
                                                {{trans('lang.yes')}} </label>
                                            <input class="form-check-input" v-model="includeAppNameInBarcode"
                                                   type="radio" id="offline_mode_dissable" value="0">
                                            <label for="offline_mode_enable" class="radio-button-label">
                                                {{trans('lang.no')}} </label>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary app-color" data-dismiss="modal"
                                    aria-label="Close" @click="closeModal">
                                {{ trans('lang.cancel') }}
                            </button>
                            <button class="btn btn-primary app-color mobile-btn"
                                    @click="previewBarcode()">
                                {{trans('lang.preview')}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Barcode preview modal -->
        <barcode-preview-modal v-if="isBarcodePreviewModalActive"
                               :data="data"
                               :totalColumns="totalColumns"
                               :totalRows="totalRows"
                               :barcodeHeight="height"
                               :copies="copies"
                               :includeAppNameInBarcode="includeAppNameInBarcode"
                               @resetImport="resetImport"
                               @barcodeModalActive="barcodeModalActive">
        </barcode-preview-modal>
    </div>
</template>
<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        data() {
            return {
                isCheckAll: true,
                isSetting: false,
                isProduct: true,
                data: [],
                totalColumns: '1',
                totalRows: '4',
                copies: '1',
                isBarcodePreviewModalActive: false,
                isClicked:true,
                includeAppNameInBarcode:1,
                barcodeHeight: 30,
                includeAdvanceOption:0,
                height:'',
                tableOptions : {
                    tableName: 'products',
                    columns: [
                        {title: 'lang.title', key: 'title', type: 'text', sortable: true},
                        {title: 'lang.group', key: 'group_name', type: 'text', sortable: true},
                        {title: 'lang.brand', key: 'brand_name', type: 'text', sortable: true},
                        {title: 'lang.category', key: 'category_name', type: 'text', sortable: false},
                    ],
                    source: '/products/products',
                    search: true,
                    checkbox: true,
                }
            }
        },

        mounted() {
            let instance = this;
            $('#barcode-modal').on('hidden.bs.modal', function () {
                if (instance.isClicked) {
                    instance.closeModal();
                }
            });
            this.productsLoad();
        },
        methods:
            {
                checkAll() {
                    this.isCheckAll = !this.isCheckAll;
                    this.data = [];

                    if (this.isCheckAll) { // Check all
                        for (var key in this.barcodeData) {
                            this.data.push(this.barcodeData[key]);
                        }
                    }
                },
                updateCheckall() {
                    if (this.data.length == this.barcodeData.length) {
                        this.isCheckAll = false;
                    }
                },
                productsLoad() {
                    this.isCheckAll = true;
                    this.isProduct = true;
                    this.isSetting = false;
                },
                previewBarcode() {
                    this.isClicked = false;
                    let instance = this;
                    instance.height = (instance.barcodeHeight * 3.7795275591) - 20;
                    instance.isBarcodePreviewModalActive = true;
                    setTimeout(function () {
                        $('#barcode-preview-modal').modal('show');
                    });
                    $('#barcode-modal').modal('hide');
                },
                emptyCheckData() {
                    this.data = [];
                },
                column: function (val) {
                    this.totalColumns = val;
                },
                resetModal() {
                    this.$emit('resetModal');
                    this.isBarcodePreviewModalActive = false;
                },
                closeModal() {
                    this.$emit('resetModal');
                },
                addSetting() {
                    this.isSetting = true;
                    this.isProduct = false;
                },
                resetImport() {
                    this.$emit('resetImport');
                },
                barcodeModalActive() {
                    this.isBarcodePreviewModalActive = true;
                },
                checkboxEmit(products){
                    this.barcodeData = products.filter(product => product.isChecked === true);
                    this.data = this.barcodeData;
                }
            }
    }
</script>

