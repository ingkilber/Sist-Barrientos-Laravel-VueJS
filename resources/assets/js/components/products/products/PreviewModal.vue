<template>
    <div>
        <div class="modal fade" id="preview-modal" tabindex="-1">
            <div class="modal-dialog layout-preview-after-import import-preview-content-view modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="container-fluid p-0">
                            <div class="row">
                                <div class="col-10">
                                    <h5 class="m-0">{{ trans('lang.preview') }} {{ trans(importOptions.title) }}</h5>
                                </div>
                                <div class="col-2 text-right">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                            @click.prevent="closeModal">
                                        <span aria-hidden="true"><i class="la la-close"></i></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="hasError && hasEmptyField && hasDublicateField" class="alert alert-warning preview-alert"
                         role="alert">
                        {{trans('lang.incorrect_column_name') }}{{trans('lang.and')
                        }}{{trans('lang.required_field_is_empty') }} {{trans('lang.and') }}
                        {{trans('lang.duplicate_email_not_allowed') }}
                    </div>
                    <div v-else-if="hasError && hasEmptyField" class="alert alert-warning preview-alert" role="alert">
                        {{trans('lang.incorrect_column_name') }}{{trans('lang.and')
                        }}{{trans('lang.required_field_is_empty') }}
                    </div>
                    <div v-else-if="hasError && hasDublicateField" class="alert alert-warning preview-alert"
                         role="alert">
                        {{trans('lang.incorrect_column_name') }}{{trans('lang.and')
                        }}{{trans('lang.duplicate_email_not_allowed') }}
                    </div>
                    <div v-else-if="hasEmptyField && hasDublicateField" class="alert alert-warning preview-alert"
                         role="alert">
                        {{trans('lang.required_field_is_empty') }}{{trans('lang.and')
                        }}{{trans('lang.duplicate_email_not_allowed') }}
                    </div>
                    <div v-else-if="hasError" class="alert alert-warning preview-alert" role="alert">
                        {{trans('lang.incorrect_column_name') }}
                    </div>
                    <div v-else-if="hasEmptyField" class="alert alert-warning preview-alert" role="alert">
                        {{trans('lang.required_field_is_empty') }}
                    </div>
                    <div v-else-if="hasDublicateField" class="alert alert-warning preview-alert" role="alert">
                        {{trans('lang.duplicate_email_not_allowed') }}
                    </div>
                    <div class="preview-main-layout-content app-bg-color p-3">
                        <div class="form-row import-data-layout mx-0 bg-white rounded p-3 ">
                            <div v-if="fileColumnTitles.length <= 0" class="mx-auto">
                                {{ trans('lang.file_should_not_empty') }}
                            </div>
                            <div class="product-import-uploading" v-if="!hidePreLoader">
                                <div class="product-import-uploading-wrapper">
                                    <div class="product-import-uploading-loader">
                                        <pre-loader class="modal-layout-content"></pre-loader>
                                    </div>
                                    <div class="product-import-uploading-message">
                                        <h5 class="m-0">{{ trans('lang.uploading') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div v-else class=" table-responsive preview-table">
                                <table v-if="exportExcelData.length>0" class="table table-bordered text-nowrap">
                                    <thead class="preview-header preview-side">
                                    <tr>
                                        <th v-for="(data, index) in columnHeader">
                                            <span>{{ data }}</span>
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="data in exportExcelData">
                                            <td v-for="fileColumnTitle in columnHeader">
                                                <span :class="{'text-danger': errorData.includes(data[fileColumnTitle]) || data.INVALID_DATA}">
                                                    {{data[fileColumnTitle]}}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>

                                </table>
                                <table v-else class="table table-bordered preview-table text-nowrap">
                                    <thead class="preview-header preview-side">
                                    <tr>
                                        <th v-for="(data, index) in fileColumnTitles">
                                            <span :class="{'text-danger': !isRequired.includes(data) }">
                                            {{ data }}
                                        </span>
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="data in jsonData">
                                        <td v-for="fileColumnTitle in fileColumnTitles">
                                            <span v-if="data[fileColumnTitle]"
                                                  :class="{'text-danger': dublicateArr.includes(data[fileColumnTitle])}">{{data[fileColumnTitle]}}</span>
                                            <span v-else-if="emptyFields.includes(fileColumnTitle) || errorData.includes(data[fileColumnTitle])"
                                                  class="text-danger">
                                                {{ trans('lang.required_field') }}</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-layout-header col-12">
                        <div class="container-fluid p-0">
                            <div>
                                <button :disabled="isDisabled || fileColumnTitles.length <= 0" type="button"
                                        class="btn btn-primary app-color mobile-btn"
                                        data-toggle="modal"
                                        @click.prevent="upload()">{{ trans('lang.upload') }}
                                </button>
                                <button class="btn btn-secondary cancel-btn mobile-btn" data-dismiss="modal"
                                        aria-label="Close" @click.prevent="closeModal">{{ trans('lang.cancel') }}
                                </button>
                                <a v-if="isImport" href="" class="button-right-aligned p-2"
                                   @click.prevent="exportFile()">{{ trans('lang.download_error_file') }}</a>
                            </div>
                            <export-data :isImport="isImport" :exportExcelShow="exportExcelShow"
                                         :exportExcelData="exportExcelData"
                                         :columnHeader="columnHeader" :excelFileName="fileName"
                                         @resetExport="resetExcel"></export-data>
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
        props: ['jsonData', 'errorColumnNames', 'fileColumnTitles', 'importOptions', 'fileName', 'emptyFields', 'dublicateArr'],
        extends: axiosGetPost,
        data() {
            return {
                hidePreLoader: true,
                isDisabled: false,
                isValidColumnNames: false,
                isSuccess: true,
                exportExcelData: [],
                exportExcelShow: false,
                invalidDataColumn: [],
                isImport: false,
                hasError: false,
                errorData: [],
                columnHeader: [],
                isRequired: this.importOptions.requiredColumns,
                hasEmptyField: false,
                hasDublicateField: false,
            }
        },
        mounted() {
            let instance = this;
            this.setInitialValues();
            $('#preview-modal').on('hidden.bs.modal', function () {
                instance.closeModal();
            });
        },
        watch: {
            errorColumnNames: function (newVal) {
                if (Object.keys(newVal).length > 0) {
                    this.isDisabled = true;
                    this.hasError = true;
                } else {
                    this.isDisabled = false;
                    this.hasError = false;
                }
            },
            importOptions: function (newVal, oldval) {
                if (newVal) {
                    this.isRequired = newVal.requiredColumns;
                } else {
                    this.isRequired = oldval.requiredColumns;
                }
            },

        },
        methods:
            {
                upload() {
                    let instance = this;
                    instance.data = instance.jsonData;
                    instance.isValidColumnName(instance.data[0]);
                    instance.hidePreLoader = false;
                    instance.importProductUpload();
                },

                isValidColumnName(importedFileData) {
                    let instance = this;
                    for (let i = 0; i < instance.importOptions.requiredColumns.length; i++) {
                        if (_.has(importedFileData, instance.importOptions.requiredColumns[i])) {
                            instance.isValidColumnNames = true;
                        }
                        else {
                            instance.isValidColumnNames = false;
                            break;
                        }
                    }
                },
                importProductUpload() {
                    let instance = this;
                    if (instance.isValidColumnNames) {
                        $('#import-modal').modal('hide');
                        instance.hidePreLoader = false;
                        instance.postDataMethod(instance.importOptions.routeToImport,
                            {
                                importData: instance.data,
                                requiredColumns: instance.importOptions.requiredColumns,
                                requiredFields: instance.importOptions.requiredFields,
                                fill_able: instance.importOptions.fill_able,
                            }
                        );
                        this.isDisabled = true;
                    }
                    else {
                        instance.showErrorAlert(instance.trans('lang.column_name_does_not_match_with_the_sample'));
                        instance.hidePreLoader = true;
                    }
                },
                postDataThenFunctionality(response) {
                    let instance = this;
                    this.isDisabled = false;
                    instance.$hub.$emit('reloadDataTable');
                    instance.hidePreLoader = true;
                    $('#preview-modal').modal('hide');
                    $('#import-modal').modal('hide');
                    this.$emit('resetImport');
                },
                postDataCatchFunctionality(error) {
                    let instance = this,
                        columnNames = [];
                    //to show error in preview modal
                    instance.errorData = error.data.errorPreviewData;
                    instance.exportExcelData = error.data.excelInvalidDatas;
                    instance.isImport = true;
                    instance.hidePreLoader = true;
                    instance.isSuccess = false;
                    instance.columnHeader = error.data.requiredColumns;
                    $('#import-modal').modal('hide');
                    columnNames = Object.keys(error.data.excelInvalidDatas[0]);
                    if (!columnNames.includes("INVALID_DATA")) columnNames.push("INVALID_DATA");
                    instance.columnHeader = columnNames;
                    //showInvalidData column names
                    _.forEach(columnNames, function (value) {
                        let obj = {
                            title: value,
                            key: value
                        };
                        instance.invalidDataColumn.push(obj);
                    });
                },
                setInitialValues() {
                    if (Object.keys(this.errorColumnNames).length > 0) {
                        this.hasError = true;
                        this.isDisabled = true;
                    }
                    if (Object.keys(this.emptyFields).length > 0) {
                        this.hasEmptyField = true;
                        this.isDisabled = true;
                    }
                    if (Object.keys(this.dublicateArr).length > 0) {
                        this.hasDublicateField = true;
                        this.isDisabled = true;
                    }
                },
                resetExcel(value) {
                    this.exportExcelShow = value;
                },
                exportFile() {
                    this.exportExcelShow = true;
                },
                closeModal() {
                    this.exportExcelData = [];
                    this.invalidDataColumn = [];
                    this.errorData = [];
                    this.columnHeader = [];
                    this.isImport = false;
                    this.hasEmptyField = false;
                    this.hasDublicateField = false;
                    this.$emit('resetImport');
                },
            }
    }
</script>