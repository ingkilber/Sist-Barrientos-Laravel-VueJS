<template>
    <div class="modal-content">
        <div class="modal-layout-header">
            <div class="row">
                <div class="col-10">
                    <h5 class="m-0">{{ trans('lang.stock_adjustment') }}</h5>
                </div>
                <div class="col-2 text-right">
                    <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click.prevent="closeAdjustStockModal">
                        <i class="la la-close icon-modal-cross"></i>
                    </button>
                </div>
            </div>
        </div>
        <div v-if="!hidePreLoader">
            <pre-loader></pre-loader>
        </div>
        <div v-else class="modal-layout-content app-bg-color p-3">
            <div class="bg-white rounded p-3">
                <div class="form-row mx-0">
                    <div class="form-group col-12">
                        <i class="la la-info-circle"></i>
                        {{ trans('lang.stock_adjustment_instruction') }}
                    </div>
                </div>
                <div class="form-row mx-0" v-if="branches.length > 1">
                    <div class="form-group col-md-5">
                        <label :for="'branch_id'">{{ trans('lang.branch') }}</label>
                        <select
                                id="branch_id"
                                v-model="branchId"
                                v-validate="'required'"
                                data-vv-as="branch"
                                name="branch_id"
                                class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option v-for="branch in branches" :value="branch.id">{{branch.name}}</option>
                        </select>
                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('branch_id')"
                            >{{ errors.first('branch_id') }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="form-row mx-0">
                    <div class="form-group col-md-5 mb-2">
                        <label>{{ trans('lang.product') }}</label>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label>{{ trans('lang.quantity') }}</label>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <label>{{ trans('lang.type') }}</label>
                    </div>
                </div>
                <div class="form-row mx-0" v-for="(rowData, index) in inputRows" :key="index">

                    <div class="form-group col-md-5">
                        <searchable-select v-model="rowData.product" v-validate="'required'" name="search_product"
                                           data-vv-as="prduct"
                                           :data="searchableSelectData"/>
                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('search_product')"
                            >{{ errors.first('search_product') }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group col-md-3">
                        <input
                                :id="'product-qty'+index"
                                v-model="rowData.stockQuantity"
                                v-validate="'required'"
                                data-vv-as="quantity"
                                :name="'quantity'+index"
                                type="number"
                                class="form-control"
                        />
                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('quantity'+index)"
                            >{{ errors.first('quantity'+index) }}
                            </small>
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <select
                                :id="'product-type'+index"
                                v-model="rowData.stockType"
                                v-validate="'required'"
                                data-vv-as="type"
                                :name="'type'+index"
                                class="custom-select"
                        >
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option
                                    v-for="item in adjustMentTypes"
                                    :value="item.id"
                            >{{ item.title }}
                            </option>
                        </select>
                        <div class="heightError">
                            <small
                                    class="text-danger"
                                    v-show="errors.has('type'+index)"
                            >{{ errors.first('type'+index) }}
                            </small>
                        </div>
                    </div>
                    <div class="form-group col-12 col-md-1 text-right position-relative">
                        <div class="btn-group btn-action-container">
                            <button
                                    class="btn btn-secondary"
                                    v-if="(index + 1 === inputRows.length || index + 1 !== inputRows.length) && inputRows.length !== 1"
                                    @click="removeRow(index)"
                            >
                                <i class="la la-trash"></i>
                            </button>
                            <button
                                    class="btn btn-secondary"
                                    v-if="index + 1 === inputRows.length"
                                    @click="addRow"
                            >
                                <i class="la la-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row mt-3">
                <div class="col-md-12">
                    <button
                            class="btn btn-primary app-color mobile-btn"
                            type="submit"
                            @click.prevent="save()"
                    >{{ trans('lang.save') }}
                    </button>
                    <button
                            class="btn btn-secondary cancel-btn mobile-btn"
                            data-dismiss="modal"
                            @click.prevent="closeAdjustStockModal"
                    >{{ trans('lang.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        name: 'AdjustStockModal',
        extends: axiosGetPost,
        data() {
            return {
                inputRows: [],
                removableInputRow: true,
                adjustStockProductData: [],
                adjustMentTypes: [],
                branches: [],
                branchId: null,
                hidePreLoader: true,
                option: '',
                searchValue: '',
                item_data: '',
                searchableSelect: null,
                searchableSelectData: {
                    label: this.trans('lang.chose_one'),
                    name: "searchable-select",
                    listValueField: 'title',
                    list: [],
                    url: "/products/search-product-for-stock-adjustment"
                }
            };
        },
        watch: {
            inputRows() {
                this.removableInputRow = this.inputRows.length <= 1;
            },
        },
        created() {
            this.getBarnchAndAdjustType();
        },
        mounted() {
            this.addRow();
            $("#adjust-stock-modal").on("hidden.bs.modal", (e) => {
                this.closeAdjustStockModal();
            });

        },
        methods: {

            addRow() {
                let checkEmptyRows = this.inputRows.filter(
                    line => line.number === null
                );
                if (checkEmptyRows.length >= 1 && this.inputRows.length > 0) return;
                this.inputRows.push({
                    product: null,
                    stockQuantity: null,
                    stockType: null
                });
            },
            removeRow(rowId) {
                if (!this.removableInputRow) {
                    this.inputRows.splice(rowId, 1);
                }
            },
            save() {
                let instance = this;
                this.$validator.validateAll().then(result => {
                    if (result) {

                        instance.hidePreLoader = false;
                        let postUrl = "/products/adjust-stock",
                            data = {
                                data: instance.inputRows,
                                branchId: instance.branchId
                            };
                        instance.axiosGETorPOST(
                            {
                                url: postUrl,
                                postData: data
                            },
                            (success, responseData) => {
                                if (success) {
                                    this.$hub.$emit("reloadDataTable");
                                    instance.branchId = null;
                                    instance.closeAdjustStockModal();
                                    instance.showSuccessAlert(responseData.message);
                                    instance.hidePreLoader = true;
                                }
                            }
                        );
                    }
                });
            },

            getBarnchAndAdjustType() {
                let instance = this,
                    postUrl = "/branches-and-adjust-type";
                this.hidePreLoader = false;
                instance.axiosGETorPOST(
                    {
                        url: postUrl
                    },
                    (success, responseData) => {
                        if (success) {
                            if (responseData.branches.length < 2) {
                                this.branchId = responseData.branches[0].id;
                            }
                            instance.branches = responseData.branches;
                            instance.adjustMentTypes = responseData.adjustMentTypes;
                        }
                        this.hidePreLoader = true;
                    }
                );
            },
            closeAdjustStockModal() {
                this.$emit("resetModal");
            }
        }
    };
</script>