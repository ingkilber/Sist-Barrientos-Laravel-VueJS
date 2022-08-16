<template>
    <div>
        <div class="modal fade" id="import-modal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-layout-header">
                        <div class="row">
                            <div class="col-10">
                                <h5 class="m-0">{{ trans('lang.import') }} {{ trans(importOptions.title) }}</h5>
                            </div>
                            <div class="col-2 text-right">
                                <button type="button"
                                        class="close"
                                        data-dismiss="modal"
                                        aria-label="Close"
                                        @click.prevent="resetImport">
                                    <i class="la la-close icon-modal-cross"/>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-layout-content app-bg-color p-3" v-show="hidePreLoader">
                        <div class="form-row mx-0 bg-white rounded p-3">
                            <label>{{ trans('lang.upload_file_to_import') }} {{ trans(importOptions.title).toLowerCase() }}</label>
                            <div class="form-row mt-2 mb-2 custom-file">
                                <div class="col-md-12">
                                    <input type="file" class="custom-file-input" id="product-import"
                                           accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                           @change="importData" ref="uploadedFile">
                                    <label class="custom-file-label text-truncate" for="product-import">{{
                                        trans('lang.choose_xlsx_file_only') }}</label>
                                    <small class="text-danger" v-if="hasError">{{trans('lang.this_field_is_required')
                                        }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row p-3 import-preview-button-area">
                        <div class="col-md-12">
                            <button class="btn btn-primary app-color mobile-btn"
                                    :disabled="isDisabled"
                                    @click="previewData()">
                                    {{ trans(importOptions.title) }}
                                    {{ trans('lang.preview') }}
                            </button>
                            <button class="btn btn-secondary cancel-btn mobile-btn"
                                    data-dismiss="modal"
                                    id="btn-example-file-reset"
                                    aria-label="Close"
                                    @click.prevent="resetImport">
                                    {{ trans('lang.cancel') }}
                            </button>
                            <a class="button-right-aligned p-2 " :href=importOptions.downloadSample>{{
                                trans('lang.download_sample_file') }}</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <preview-modal v-if="isPreviewModalActive" :jsonData="jsonData" :importOptions="importOptions"
                       :errorColumnNames="errorColumnNames" :fileColumnTitles="fileColumnTitles"
                       :fileName="fileName" :emptyFields="emptyFields" :dublicateArr="dublicateArr"
                       @resetImport="resetImport"></preview-modal>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        props: ['importOptions'],
        extends: axiosGetPost,
        data() {
            return {
                isFileLoaded: false,
                jsonData: [],
                isPreviewModalActive: false,
                errorColumnNames: [],
                fileColumnTitles: [],
                fileName: '',
                hasError: false,
                isDisabled: false,
                importedDataRows: '',
                importedRowKey: '',
                emptyFields: [],
                dublicateArr: [],
            }
        },
        methods:
            {
                importData() {
                    if (Object.keys(this.$refs.uploadedFile.files).length > 0) {
                        let files = this.$refs.uploadedFile.files,
                            data = [],
                            instance = this;

                        instance.fileName = files[0].name.replace(/\.[^/.]+$/, "");
                        $('#product-import').next('.custom-file-label').html(instance.fileName);

                        this.ExcelToJSON(files, function (data) {
                            instance.jsonData = data;
                        });

                        instance.isFileLoaded = true;
                        instance.hasError = false;
                        instance.isDisabled = false;
                    }
                },
                previewData() {
                    let instance = this;
                    if (instance.isFileLoaded) {
                        instance.isPreviewModalActive = true;

                        setTimeout(function () {
                            $('#preview-modal').modal('show');
                        });
                        $('#import-modal').modal('hide');
                        instance.importedDataColumnName = instance.jsonData[0];

                        if(instance.importedDataColumnName === undefined) {
                            instance.importedDataColumnName = 0;
                        }
                        
                        instance.importedDataRows = instance.jsonData;

                        //duplicate emails code
                        var newArr = [];
                        var dublicateElementArr = [];

                        instance.importedDataRows.forEach(function (value) {
                            if (value.email) {
                                if (!newArr.includes(value.email)) {
                                    newArr.push(value.email);
                                } else {
                                    dublicateElementArr.push(value.email);
                                }
                            }
                        });
                        let reduceDublicateArr = (dublicateElementArr) => dublicateElementArr.filter((v, i) => dublicateElementArr.indexOf(v) === i);

                        this.dublicateArr = reduceDublicateArr(dublicateElementArr);
                        //end duplicate emails codes

                        if (instance.importOptions.requiredFields) {
                            instance.importedDataRows.forEach(function (importedDataRow) {

                                instance.importedRowKey = Object.keys(importedDataRow);

                                instance.importOptions.requiredFields.forEach(function (requiredField) {

                                    if (instance.importedRowKey.includes(requiredField) && !importedDataRow[requiredField]) {
                                        instance.emptyFields.push(requiredField);
                                    }
                                });
                            })
                        }

                        //comparing which column do not match with the required column names
                        this.errorColumnNames = _.values(_.difference(Object.keys(instance.importedDataColumnName), this.importOptions.requiredColumns));

                        //imported file column names
                        instance.fileColumnTitles = Object.keys(instance.importedDataColumnName);

                    }
                    else {
                        instance.hasError = true;
                        instance.isDisabled = true;
                    }
                },
                resetImport() {
                    this.isFileLoaded = false;
                    this.hasError = false;
                    this.isDisabled = false;
                    this.$emit('resetModal');
                }
            }
    }
</script>