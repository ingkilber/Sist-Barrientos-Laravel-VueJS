<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h5 v-if="id" class="m-0">{{ trans('lang.edit_product') }}</h5>
                        <h5 v-else class="m-0">{{ trans('lang.add_new_product') }}</h5>
                    </div>
                    <div class="col-2 text-right">
                        <button
                            type="button"
                            class="close"
                            data-dismiss="modal"
                            aria-label="Close"
                            @click.prevent
                        >
                            <i class="la la-close icon-modal-cross"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <pre-loader v-if="!hidePreLoader"></pre-loader>
        <div class="modal-body scroll-modal app-bg-color p-3" v-show="hidePreLoader">
            <div class="form-row mx-0 mb-3 bg-white rounded p-3">
                <div class="form-group col-md-12">
                    <label for="product">{{ trans('lang.name') }}</label>
                    <input
                        id="product"
                        v-validate="'required'"
                        type="text"
                        class="form-control"
                        name="title"
                        v-model="product.pName"
                        @input="checkProductTitleDuplicate"
                        :class="{ 'is-invalid':submitted && errors.has('title') }"
                    />
                    <div class="heightError" v-if="submitted && errors.has('title')">
                        <small
                            class="text-danger"
                            v-show="errors.has('title')"
                        >{{ errors.first('title') }}</small>
                    </div>
                    <div class="heightError" v-if="isTitleDuplicate">
                        <small class="text-warning">{{ trans('lang.product_name_is_duplicate')}}</small>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <label for="description">{{ trans('lang.description') }}</label>

                    <textarea class="form-control" id="description" name="description" rows="3" v-model="product.description"></textarea>

                </div>

                <div class="form-group col-md-6 margin-top">
                    <label for="product-category">{{ trans('lang.category') }}</label>
                    <div class="input-group">
                        <select v-model="product.category" id="product-category" class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option v-for="category in categoryList"
                                    :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn app-color"
                                    data-toggle="modal"
                                    data-target="#attributes-add-edit-modal"
                                    @click.prevent="setCategoryModalOption">
                                <i class="la la-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6 margin-top">
                    <label for="product-brand">{{ trans('lang.brand') }}</label>
                    <div class="input-group">
                        <select v-model="product.brand" id="product-brand" class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option v-for="brand in brandList" :value="brand.id">{{ brand.name }}</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn app-color"
                                    data-toggle="modal"
                                    data-target="#attributes-add-edit-modal"
                                    @click.prevent="setBrandModalOption">
                                <i class="la la-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-group">{{ trans('lang.group') }}</label>
                    <div class="input-group">
                        <select v-model="product.group" id="product-group" class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option v-for="group in groupList" :value="group.id">{{ group.name }}</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn app-color"
                                    data-toggle="modal"
                                    data-target="#attributes-add-edit-modal"
                                    @click.prevent="setGroupModalOption">
                                <i class="la la-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-unit">{{ trans('lang.unit') }}</label>
                    <div class="input-group">
                        <select v-model="product.unit" id="product-unit" class="custom-select">
                            <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                            <option v-for="unit in unitList" :value="unit.id">{{ unit.name }}</option>
                        </select>
                        <div class="input-group-append">
                            <button class="btn app-color"
                                    data-toggle="modal"
                                    data-target="#unit-add-edit-modal"
                                    @click.prevent="setUnitModalOption">
                                <i class="la la-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mb-2 col-md-6">
                    <label>{{ trans('lang.upload_product_image') }}</label>
                    <div class="custom-file">
                        <input
                            type="file"
                            class="custom-file-input"
                            id="product-image"
                            accept="image/*"
                            @change="productImage"
                        />
                        <label class="custom-file-label text-truncate" for="product-image">
                            {{ trans('lang.image_only')
                            }}
                        </label>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="product-tax">{{ trans('lang.tax') }}</label>
                    <select
                        v-model="product.tax"
                        v-validate="'required'"
                        id="product-tax"
                        name="tax"
                        class="custom-select"
                    >
                        <option value disabled>{{ trans('lang.choose_one') }}</option>
                        <option value="no-tax">{{ trans('lang.no_tax') }}</option>
                        <option value="default-tax">{{ trans('lang.default_tax') }}</option>
                        <option v-for="tax in taxList" :value="tax.id">{{ tax.name }}</option>
                    </select>
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="errors.has('tax')"
                        >{{ errors.first('tax') }}</small>
                    </div>
                </div>

            </div>
            <div class="form-row mx-0 mb-3 bg-white rounded p-3" v-if="!id">
                <div class="col-md-12 mb-3">
                    <h5 class="mb-0">{{ trans('lang.chose_product_type') }}</h5>
                </div>
                <div class="col-md-12">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            name="productVariant"
                            class="custom-control-input"
                            id="standard-product"
                            checked="checked"
                            value="0"
                            v-model="product.variantType"
                            @change="selectStandardProduct()"
                        />
                        <label class="custom-control-label" for="standard-product">
                            {{ trans('lang.standard_product')
                            }}
                        </label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            name="productVariant"
                            class="custom-control-input"
                            id="variant-product"
                            value="1"
                            v-model="product.variantType"
                            @change="checkVariant = true"
                        />
                        <label class="custom-control-label" for="variant-product">
                            {{ trans('lang.variant_product')
                            }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row mx-0 mb-3 bg-white rounded p-3" v-if="!id && branchList.length > 0">
                <div class="col-md-12 mb-3">
                    <h5 class="mb-0">{{ trans('lang.adjective_stock') }}</h5>
                </div>
                <div class="col-md-12">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                                type="radio"
                                name="adjectivestock"
                                class="custom-control-input"
                                id="adjective-stock-yes"
                                value="1"
                                v-model="initialQuantity"

                        />
                        <label class="custom-control-label" for="adjective-stock-yes">
                            {{ trans('lang.yes')
                            }}
                        </label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                                type="radio"
                                name="adjectivestock"
                                class="custom-control-input"
                                id="adjective-stock-no"
                                checked="checked"
                                value="2"
                                v-model="initialQuantity"
                        />
                        <label class="custom-control-label" for="adjective-stock-no">
                            {{ trans('lang.no')
                            }}
                        </label>
                    </div>

                </div>
                <div class="form-group col-md-6" v-if="branchList.length > 1">
                <div v-if="initialQuantity == 1">
                    <label for="product-tax">{{ trans('lang.branch') }}</label>
                    <select
                            v-model="product.branch"
                            id="product-branch"
                            v-validate="'required'"
                            name="branch"
                            class="custom-select">
                        <option value disabled>{{ trans('lang.choose_one') }}</option>
                        <option v-for="branch in branchList" :value="branch.id">{{ branch.name }}</option>
                    </select>
                    <div class="heightError" v-if="submitted && errors.has('branch')">
                        <small class="text-danger" v-show="errors.has('branch')">
                            {{ errors.first('branch') }}
                        </small>
                    </div>
                </div>
                </div>

            </div>
            <div v-if="!checkVariant" class="form-row mx-0 mb-3 bg-white rounded p-3">
                <div class="form-group col-md-4" v-if="hideCommonInput">
                    <label>{{ trans('lang.purchase_price_label') }}</label>
                    <common-input
                        name="receivingPrice"
                        id="'standard-product-receiving-price'"
                        step="any"
                        :inputValue="decimalFormat(product.receivingPrice)"
                        @input="setReceivingValue"
                    ></common-input>
                </div>
                <div class="form-group col-md-4" v-if="hideCommonInput">
                    <label>{{ trans('lang.selling_price_label') }}</label>
                    <common-input
                        v-if="hidePreLoader"
                        name="sallingPrice"
                        id="'standard-product-salling-price'"
                        step="any"
                        :inputValue="decimalFormat(product.sallingPrice)"
                        @input="setSallingValue"
                        :class="{ 'is-invalid':submitted && errors.has('sallingPrice') }"
                    ></common-input>
                </div>
                <div class="form-group col-md-4">
                    <label for="standard-product-sku">{{trans('lang.sku')}}</label>
                    <input
                        id="standard-product-sku"
                        type="text"
                        step="any"
                        name="sku"
                        class="form-control"
                        v-model="standardProductSku"
                    />
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="sku.includes(standardProductSku) && standardProductSku != null && standardProductSku !== '' && checkSubmit"
                        >{{trans('lang.sku_already_exists') }}</small>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="standard-product-barcode">{{trans('lang.barcode')}}</label>
                    <input
                        id="standard-product-barcode"
                        type="text"
                        name="barcode"
                        step="any"
                        class="form-control"
                        v-model="standardProductBarcode"
                    />
                    <div class="heightError">
                        <small
                            class="text-danger"
                            v-show="barcode.includes(standardProductBarcode) && standardProductBarcode != null && standardProductBarcode !== '' && checkSubmit"
                        >{{trans('lang.barcode_already_exists') }}</small>
                    </div>
                </div>

                <div class="form-group col-md-4" v-if="initialQuantity == 1">
                    <label for="standard-product-quantity">{{trans('lang.quantity')}}</label>
                    <input
                            id="standard-product-quantity"
                            type="text"
                            v-validate="'required'"
                            name="quantity"
                            step="any"
                            class="form-control"
                            v-model="standardProductQuantity"
                    />


                    <div class="heightError" v-if="submitted && errors.has('quantity')">
                        <small class="text-danger" v-show="errors.has('quantity')">
                            {{ errors.first('quantity') }}
                        </small>
                    </div>

                </div>

                <div class="form-group col-md-4">
                    <label>{{trans('lang.re_order')}}</label>
                    <input
                        id="product-re-order"
                        type="number"
                        step="any"
                        class="form-control"
                        v-model="product.reorder"
                    />
                </div>
            </div>

            <div v-else id="addVariantSection" class="mb-3 bg-white rounded p-3">
                <div class="row">
                    <div class="col-5">
                        <h5>{{ trans('lang.add_product_variants') }}</h5>
                    </div>
                    <div class="col-7">
                        <div class="row no-gutters">
                            <div class="col">
                                <div class="text-right mr-2" v-if="!id">
                                    <a
                                        class="btn btn-primary app-color text-white"
                                        data-toggle="modal"
                                        data-target="#attributes-add-edit-modal"
                                        @click.prevent="setAttributeModalOption"
                                    >
                                        <i class="la la-plus-circle"></i>
                                        {{ trans('lang.add_new_variant') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col">
                                <div v-if="!id">
                                    <select
                                        id="inputState"
                                        class="custom-select"
                                        @change="addTempAttribute($event)"
                                    >
                                        <option
                                            selected
                                            disabled
                                        >{{ trans('lang.add_another_variant') }}</option>
                                        <option
                                            v-for="productAttribute in allAttributes"
                                            :value="productAttribute.id"
                                        >{{ productAttribute.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="loader" v-if="attributeLoader"></div>
                <div v-else v-for="(tempAttribute,index) in tempAttributeList">
                    <div class="row">
                        <div class="col-12">
                            <div class="variant-values">
                                <label>{{ capitalizeFirstLetter(tempAttribute.name) }}</label>
                                <div class="chips-container">
                                    <span
                                        class="chip"
                                        v-for="(chips,chipIndex) in chipArray[tempAttribute.id]"
                                    >
                                        {{ chips }}
                                        <i
                                            v-if="!id"
                                            class="la la-close close"
                                            @click.prevent="deleteChip($event,tempAttribute.id,chipIndex)"
                                        ></i>
                                    </span>
                                </div>
                                <div class="input-group mb-2">
                                    <input
                                        type="text"
                                        class="form-control"
                                        @keyup.enter="addChips($event,tempAttribute.id)"
                                        aria-describedby="basic-addon2"
                                    />
                                    <div class="input-group-append">
                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            @click.prevent="addChips($event,tempAttribute.id)"
                                        >
                                            <i class="la la-plus-circle text-info"></i>
                                        </button>
                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            v-if="!id"
                                            @click.prevent="removeTempAttribute(index,tempAttribute.id)"
                                        >
                                            <i class="la la-trash-o text-danger"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded p-3" v-if="productVariant.length>0 && checkVariant">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <h5 class="m-0">{{ trans('lang.add_variant_details') }}</h5>
                    </div>
                </div>
                <!--For Edit variants-->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="border-0">
                                <th></th>
                                <th class="text-center">{{ trans('lang.variants') }}</th>
                                <th class="text-center">{{ trans('lang.purchase_price_label') }}</th>
                                <th class="text-center">{{ trans('lang.selling_price_label') }}</th>
                                <th class="text-center">{{ trans('lang.barcode') }}</th>
                                <th class="text-center">{{ trans('lang.sku') }}</th>
                                <th v-if="initialQuantity == 1" class="text-center">{{ trans('lang.quantity') }}</th>
                                <th class="text-center">{{ trans('lang.re_order') }}</th>
                                <th class="text-center">{{ trans('lang.variant_image') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in productVariant">
                                <!--Edit check box-->
                                <td class="border-0 add-product-padding">
                                    <div
                                        class="custom-control custom-checkbox"
                                        style="top:-0.75rem"
                                    >
                                        <input
                                            type="checkbox"
                                            class="custom-control-input"
                                            :id="'variant-available-'+index"
                                            v-model="item.enabled"
                                        />
                                        <label
                                            class="custom-control-label"
                                            :for="'variant-available-'+index"
                                        ></label>
                                    </div>
                                </td>

                                <!--Edit Variant combination-->
                                <td class="border-0 add-product-padding">
                                    <input
                                        v-model="item.variant"
                                        type="text"
                                        class="form-control"
                                        :disabled="!item.enabled"
                                        style="width: 100%"
                                    />
                                </td>

                                <!--Edit price of the variant-->
                                <td class="border-0 add-product-padding">
                                    <input
                                        :id="'product_receiving_price'+index"
                                        v-model="item.purchasePrice"
                                        :disabled="!item.enabled"
                                        :name="'pReceivingPrice'+index "
                                        step="any"
                                        :index="index"
                                        class="form-control"
                                    />
                                </td>
                                <td class="border-0 add-product-padding">
                                    <input
                                        :id="'product_selling_price'+index"
                                        v-model="item.sellingPrice"
                                        :disabled="!item.enabled"
                                        :name="'pSellingPrice'+index"
                                        step="any"
                                        class="form-control"
                                    />
                                </td>

                                <!--Edit barcode of the variant-->
                                <td class="border-0 add-product-padding">
                                    <input
                                        :id="'product_barcode'+index"
                                        v-model="item.barcode"
                                        :disabled="!item.enabled"
                                        type="text"
                                        step="any"
                                        class="form-control"
                                        @input="checkBarcodeValue"
                                    />
                                    <div class="heightError text-nowrap">
                                        <small
                                            class="text-danger"
                                            v-show="item.isBarcodeExsist"
                                        >{{trans('lang.barcode_already_exists') }}</small>
                                    </div>
                                </td>

                                <!--Edit sku of the variant-->
                                <td class="border-0 add-product-padding">
                                    <input
                                        :id="'product_sku'+index"
                                        type="text"
                                        v-model="item.sku"
                                        :disabled="!item.enabled"
                                        step="any"
                                        class="form-control"
                                        @input="checkSkuValue"
                                    />
                                    <div class="heightError text-nowrap">
                                        <small
                                            class="text-danger"
                                            v-show="item.isSkuExsist"
                                        >{{trans('lang.sku_already_exists') }}</small>
                                    </div>
                                </td>

                                <!--Edit qty of the variant-->
                                <td class="border-0 add-product-padding" v-if="initialQuantity == 1">
                                    <input
                                            :id="'product_qty'+index"
                                            type="text"
                                            v-validate="'required'"
                                            data-vv-as="quantity"
                                            v-model="item.qty"
                                            :name="'productQuantity'+index"
                                            :disabled="!item.enabled"
                                            step="any"
                                            class="form-control"

                                    />
                                    <div class="heightError text-nowrap">
                                        <small
                                                class="text-danger"
                                                v-show="errors.has('productQuantity'+index)"
                                        >{{ errors.first('productQuantity'+index) }}</small>
                                    </div>

                                </td>

                                <td class="border-0 add-product-padding">
                                    <input
                                        :id="'product_reorder'+index"
                                        type="number"
                                        min="0"
                                        v-model="item.reOrder"
                                        :disabled="!item.enabled"
                                        step="any"
                                        class="form-control"
                                    />
                                </td>
                                <td class="border-0 add-product-padding">
                                    <div class="custom-file" style="padding-right: 100%">
                                        <input
                                            type="file"
                                            class="custom-file-input"
                                            :disabled="!item.enabled"
                                            :id="'variant-image-'+index"
                                            accept="image/*"
                                            @change="variantImage($event, index, '#variant-image-'+index)"
                                        />
                                        <label
                                            class="custom-file-label text-truncate"
                                            :for="'variant-image-'+index"
                                        >{{ trans('lang.image_only') }}</label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="form-row mt-3">
                <div class="col-md-12">
                    <button
                            class="btn btn-primary app-color mobile-btn"
                            type="submit"
                            @click.prevent="save()"
                    >{{ trans('lang.save') }}</button>
                    <button
                        class="btn btn-secondary cancel-btn mobile-btn"
                        data-dismiss="modal"
                        aria-label="Close"
                        @click.prevent
                    >{{ trans('lang.cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

let sourceURL = "/products/attribute/";
export default {
    props: ["id", "bus", "productData"],
    extends: axiosGetPost,
    data() {
        return {
            submitted: false,
            checkVariant: false,
            categoryList: [],
            groupList: [],
            unitList: [],
            brandList: [],
            branchList: [],
            allAttributes: [],
            attributeList: [],
            taxList: [],
            tempAttributeList: [],
            totalChipValue: [],
            product: [],
            standardProductSku: "",
            standardProductBarcode: "",
            pVariant: [],
            pPrice: [],
            pSellingPrice: [],
            pReceivingPrice: [],
            pSku: [],
            pBarcode: [],
            pReorder: [],
            sku: [],
            showSkuError: false,
            showBarcodeError: false,
            checkSubmit: false,
            barcode: [],
            reorder: [],
            pQuantity: [],
            pEnabled: [],
            productVariant: [],
            productVariantImages: [],
            chipArray: [],
            totalProductCount: 0,
            hidePreLoader: true,
            variantDetails: [],
            attributeLoader: false,
            productsInfo: {},
            editAttributeData: [],
            idCheck: "",
            sallingPrice: "",
            receivingPrice: "",
            productQuantity: "",
            standardProductQuantity:"",
            productEnabled: "",
            productSku: "",
            productBarcode: "",
            productQty:"",
            isActiveModal: false,
            productReorder: "",
            defaultReorder: "",
            variantCountRow: 0,
            isActive: false,
            variantItemCount: 0,
            countMethodCalled: 0,
            variantCombination: [],
            duplicateSku: [],
            duplicateBarcode: [],
            hideCommonInput: false,
            productVariantIndex: "",
            modalOptions: {},
            modalID: "#product-add-edit-modal",
            isSaveBtnDisabled: false,
            initialQuantity:2,
            isTitleDuplicate : false,
            isButtonDisable : false,

        };
    },
    watch: {
        id: function(newVal) {
            Object.assign(this.$data, this.$options.data.apply(this));
            if (newVal) {
                this.getEditData();
            } else {
                this.getAddData();
            }
        },
        initialQuantity: function(newVal) {
            if (newVal == 2) {
                this.product.branch = "";
            } else {
                if (this.branchList.length == 1) {
                    this.product.branch = this.branchList[0].id;
                }
            }
        }
    },

    mounted() {
        //here this code carry data in one time
        let instance = this;
        if (instance.id) {
            instance.getEditData();
        } else {
            instance.getAddData();
        }
        instance.product.tax = "no-tax";
        instance.bus.$on("saveStatus", this.saveStatus);
        instance.countVariant();
        instance.product.enable = true;
        instance.product.variantType = 0;

        this.$hub.$on("attributeAddEdit", function(id, name) {
            instance.addEditAction(id, name);
        });

        $("#product-add-edit-modal").on("hidden.bs.modal", function(e) {
            instance.$emit("disable");
        });

        this.modalCloseAction(this.modalOptions.modalID);
    },
    methods: {

        checkProductTitleDuplicate(){
            let instance = this,
                data = this.productData.find(function (element) {
                return element.title.toLowerCase() === instance.product.pName.toLowerCase();
            });
            if(data != undefined && data){
                this.isTitleDuplicate = true;
            }else{
                this.isTitleDuplicate = false;
            }
        },
        setCategoryModalOption(){
            let sourceURL = '/products/category';
            this.modalOptions = {
                modalID: "#attributes-add-edit-modal",
                addLang: "lang.add_category",
                editLang: "lang.edit_category",
                getDataURL: sourceURL,
                postDataWithIDURL: sourceURL,
                postDataWithoutIDURL: sourceURL + "/store",
                turnOffLoader: true,
                closeModal: "#attributes-add-edit-modal"
            };

            this.isActiveModal = true;
            this.$emit("setModalOptionValue", this.isActiveModal, this.modalOptions);
        },
        setAttributeModalOption() {
            let sourceURL = '/products/attribute/';
            this.modalOptions = {
                modalID: "#attributes-add-edit-modal",
                addLang: "lang.add_new_attribute",
                editLang: "lang.edit_attribute",
                getDataURL: sourceURL,
                postDataWithIDURL: sourceURL,
                postDataWithoutIDURL: sourceURL + "store",
                turnOffLoader: true,
                closeModal: "#attributes-add-edit-modal"
            };
            this.isActiveModal = true;
            this.$emit("setModalOptionValue", this.isActiveModal, this.modalOptions);
        },
        setBrandModalOption(){
            let sourceURL = '/products/brand';
            this.modalOptions = {
                modalID: "#attributes-add-edit-modal",
                addLang: "lang.add_brand",
                editLang: "lang.edit_brand",
                getDataURL: sourceURL,
                postDataWithIDURL: sourceURL,
                postDataWithoutIDURL: sourceURL + "/store",
                turnOffLoader: true,
                closeModal: "#attributes-add-edit-modal"
            };
            this.isActiveModal = true;
            this.$emit("setModalOptionValue", this.isActiveModal, this.modalOptions);
        },
        setGroupModalOption(){
            let sourceURL = '/products/group';
            this.modalOptions = {
                modalID: "#attributes-add-edit-modal",
                addLang: "lang.add_new_group",
                editLang: "lang.edit_group",
                getDataURL: sourceURL,
                postDataWithIDURL: sourceURL,
                postDataWithoutIDURL: sourceURL + "/store",
                turnOffLoader: true,
                closeModal: "#attributes-add-edit-modal"
            };
            this.isActiveModal = true;
            this.$emit("setModalOptionValue", this.isActiveModal, this.modalOptions);
        },
        setUnitModalOption(){
            let sourceURL = '/products/unit';
            this.modalOptions = {
                modalID: "#unit-add-edit-modal",
                addLang: "lang.add_new_unit",
                editLang: "lang.edit_unit",
                getDataURL: sourceURL,
                postDataWithIDURL: sourceURL,
                postDataWithoutIDURL: sourceURL + "/store",
                turnOffLoader: true,
                closeModal: "#unit-add-edit-modal"
            };
            this.isActiveModal = true;
            this.$emit("setModalOptionValue", this.isActiveModal, this.modalOptions);
        },

        amountCheck(amount) {
            if (amount > 1000000) {
                return true;
            } else return false;
        },
        checkBarcodeValue() {
            this.productVariant.forEach(function(item, index) {
                item.isBarcodeExsist = false;
            });
        },
        checkSkuValue() {
            this.productVariant.forEach(function(item, index) {
                item.isSkuExsist = false;
            });
        },
        isDublicateBarcodeOrSku() {
            let barcodeData = this.productVariant.find(function(element) {
                return (
                    element.isBarcodeExsist === true ||
                    element.isSkuExsist === true
                );
            });
            if (barcodeData != undefined && barcodeData)
                this.isSaveBtnDisabled = true;
            else this.isSaveBtnDisabled = false;
        },
        closeModal() {
            this.$emit("resetModal");
        },
        setReceivingValue(amount) {
            this.product.receivingPrice = amount;
        },
        setSallingValue(amount) {
            this.product.sallingPrice = amount;
        },

        // setReceivingValueVariant(item, index) {
        //     this.productVariant[
        //         index
        //     ].variantReceivePriceMoreThan = this.amountCheck(
        //         item.purchasePrice
        //     );
        // },
        // setSellingValueVariant(item, index) {
        //     this.productVariant[
        //         index
        //     ].variantSalePriceMoreThan = this.amountCheck(item.sellingPrice);
        // },
        saveStatus() {
            let instance = this;
            this.axiosGet(
                "/products/attribute",
                function(response) {
                    instance.allAttributes = response.data.productAttribute;
                    instance.supportingData(
                        response.data.productSupportingData
                    );
                    $("#inputState")
                        .prop("selected", false)
                        .find("option:first")
                        .prop("selected", true);
                },
                function(error) {}
            );
        },
        resetImport() {
            this.isFileLoaded = false;
            this.hasError = false;
            this.isDisabled = false;
            this.$emit("resetModal");
        },

        getAddData() {
            let instance = this;
            instance.setPreLoader(false);
            instance.attributeLoader = true;
            this.axiosGet(
                "/products/attribute",
                function(response) {
                    instance.barcode = response.data.getAllBarcode.allBarcode;
                    instance.sku = response.data.getAllSku.allSku;

                    instance.defaultReorder =
                        response.data.productSupportingData.defaultReorder;
                    instance.product.reorder = instance.defaultReorder;
                    instance.allAttributes =
                        response.data.productSupportingData.attributes;
                    instance.supportingData(
                        response.data.productSupportingData
                    );
                    instance.attributeList = response.data.productAttribute;
                    instance.tempAttributeList = instance.attributeList;
                    instance.attributeLoader = false;
                    instance.hideCommonInput = true;
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                    instance.attributeLoader = false;
                }
            );
        },
        getEditData() {
            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(
                "/products/edit-product/" + instance.id,
                function(response) {
                    instance.variantItemCount =
                        response.data.variantDetails.length;
                    let result = _.clone(response.data);
                    let allAttributesProduct = _.clone(
                        response.data.AllAttributesProduct
                    );
                    instance.productsInfo = result;
                    instance.productVariant = result.variantDetails;

                    instance.barcode = response.data.getAllBarcode.allBarcode;
                    instance.sku = response.data.getAllSku.allSku;

                    instance.product.pName =
                        instance.productsInfo.productDetails.title;
                    instance.product.description =
                        instance.productsInfo.productDetails.description;
                    instance.product.category =
                        instance.productsInfo.productDetails.category_id;
                    instance.product.brand =
                        instance.productsInfo.productDetails.brand_id;
                    instance.product.group =
                        instance.productsInfo.productDetails.group_id;
                    instance.product.unit =
                        response.data.productDetails.unit_id;
                    instance.product.sku = response.data.variantDetails.sku;
                    instance.product.barcode =
                        response.data.variantDetails.bar_code;
                    instance.defaultReorder = response.data.defaultReorder;
                    if (instance.productsInfo.productDetails.taxable == 0) {
                        instance.product.tax = "no-tax";
                    } else {
                        if (
                            instance.productsInfo.productDetails.tax_type ==
                            "default"
                        ) {
                            instance.product.tax = "default-tax";
                        } else {
                            instance.product.tax =
                                instance.productsInfo.productDetails.tax_id;
                        }
                    }
                    if (
                        instance.productsInfo.productDetails.product_type ==
                        "standard"
                    ) {
                        instance.product.variantType = 0;
                        instance.product.receivingPrice =
                            instance.productsInfo.variantDetails[0].purchasePrice;
                        instance.product.sallingPrice =
                            instance.productsInfo.variantDetails[0].sellingPrice;
                        instance.standardProductSku =
                            response.data.variantDetails[0].sku;
                        instance.standardProductBarcode =
                            response.data.variantDetails[0].barcode;
                        instance.product.reorder =
                            response.data.variantDetails[0].reOrder;
                    } else {
                        instance.product.variantType = 1;
                        instance.checkVariant = true;
                        let deleteIndex = [];

                        allAttributesProduct.forEach(function(value, index) {
                            let arrribute_id = value.id;
                            if (
                                instance.productsInfo.variantData[
                                    arrribute_id
                                ] === undefined
                            ) {
                                deleteIndex.push(index);
                            } else {
                                instance.chipArray[arrribute_id] =
                                    instance.productsInfo.variantData[
                                        arrribute_id
                                    ];
                            }
                        });

                        let pulled = _.pullAt(
                            allAttributesProduct,
                            deleteIndex
                        );
                        instance.attributeList = allAttributesProduct;
                        instance.tempAttributeList = instance.attributeList;
                    }
                    instance.supportingData(
                        response.data.productSupportingData
                    );
                    instance.hideCommonInput = true;
                    instance.setPreLoader(true);
                },
                function(response) {
                    instance.setPreLoader(true);
                }
            );
        },
        makeVariantObject(
            variant,
            purchasePrice,
            sellingPrice,
            barcode,
            sku,
            reOrder,
            imageURL,
            enabled
        ) {
            return {
                id: null,
                variant: variant,
                purchasePrice: purchasePrice,
                sellingPrice: sellingPrice,
                barcode: barcode,
                sku: sku,
                reOrder: reOrder,
                imageURL: imageURL,
                enabled: enabled,
                isImageChanged: false,
                isBarcodeExsist: false,
                isSkuExsist: false,
                variantSalePriceMoreThan: false,
                variantReceivePriceMoreThan: false
            };
        },
        comparer(otherArray) {
            let instance = this;
            return function(current) {
                return (
                    otherArray.filter(function(other) {
                        return (
                            instance.capitalizeFirstLetter(other.variant) ==
                            instance.capitalizeFirstLetter(current.variant)
                        );
                    }).length == 0
                );
            };
        },
        countVariant() {
            this.productVariantImages = [];
            let tempProductVariant = [],
                tempVariantDetails = [],
                chipArrayLoopingTime = 0,
                count = 0,
                combinationIndex = 0,
                newVariantCombination = 0,
                a = [],
                instance = this;
            for (let i = 0; i < this.chipArray.length; i++) {
                if (this.chipArray[i]) {
                    chipArrayLoopingTime++;
                    if (tempProductVariant.length == 0) {
                        let item = instance.productVariant[i];

                        for (let item of this.chipArray[i]) {
                            tempProductVariant.push(
                                this.makeVariantObject(
                                    item,
                                    null,
                                    null,
                                    null,
                                    null,
                                    this.defaultReorder,
                                    null,
                                    true
                                )
                            );
                        }
                    } else {
                        if (chipArrayLoopingTime > 2) {
                            tempProductVariant = tempVariantDetails;
                            tempVariantDetails = [];
                            count = 0;
                        }

                        for (let t = 0; t < tempProductVariant.length; t++) {
                            for (let j = 0; j < this.chipArray[i].length; j++) {
                                let variantName =
                                    tempProductVariant[t].variant +
                                    "," +
                                    this.chipArray[i][j];
                                tempVariantDetails.push(
                                    this.makeVariantObject(
                                        variantName,
                                        null,
                                        null,
                                        null,
                                        null,
                                        this.defaultReorder,
                                        null,
                                        true
                                    )
                                );
                                count++;
                            }
                        }
                    }
                }
            }

            if (this.id) {
                let tempArr = [];
                if (tempVariantDetails.length > 0) {
                    tempArr = tempVariantDetails;
                } else {
                    tempArr = tempProductVariant;
                }
                var diff = tempArr.filter(
                    this.comparer(instance.productVariant)
                );
                if (diff) {
                    instance.productVariant = instance.productVariant.concat(
                        diff
                    );
                }
            } else {
                if (tempVariantDetails.length > 0) {
                    this.productVariant = tempVariantDetails;
                } else {
                    this.productVariant = tempProductVariant;
                }
            }
        },
        save() {
            this.submitted = true;
            let instance = this;
            instance.checkSubmit = true;
            instance.showSkuError = false;
            instance.showBarcodeError = false;
            if (instance.checkVariant) {
                let tempBarcode = [];
                let tempSku = [];
                instance.productVariant.forEach(function(item, index, array) {
                    // barcode duplicate check
                    let isTempBarcodeExsist = tempBarcode.includes(
                        item.barcode
                    );
                    let isStoredBarcodeExsist = instance.barcode.includes(
                        item.barcode
                    );
                    if (isTempBarcodeExsist || isStoredBarcodeExsist) {
                        instance.productVariant[index].isBarcodeExsist = true;
                    } else {
                        instance.productVariant[index].isBarcodeExsist = false;
                        if (item.barcode != null)
                            tempBarcode.push(item.barcode);
                    }

                    // sku duplicate check
                    let isTempSkuExsist = tempSku.includes(item.sku);
                    let isStoredSkuExsist = instance.sku.includes(item.sku);

                    if (isTempSkuExsist || isStoredSkuExsist) {
                        instance.productVariant[index].isSkuExsist = true;
                    } else {
                        instance.productVariant[index].isSkuExsist = false;
                        if (item.sku != null) tempSku.push(item.sku);
                    }
                });

                instance.product.sallingPrice = "";
                instance.product.receivingPrice = "";
            } else {

                instance.productVariant = [];
                instance.chipArray = [];

                if (!_.isEmpty(instance.standardProductSku)) {
                    instance.showSkuError = instance.sku.includes(
                        instance.standardProductSku
                    );
                }
                if (!_.isEmpty(instance.standardProductBarcode)) {
                    instance.showBarcodeError = instance.barcode.includes(
                        instance.standardProductBarcode
                    );
                }

            }
            setTimeout(() => {
                this.isDublicateBarcodeOrSku();
                this.$validator.validateAll().then(result => {
                    if (
                        result &&
                        !instance.isSaveBtnDisabled &&
                        !instance.showSkuError &&
                        !instance.showBarcodeError
                    ) {
                        if (instance.checkVariant) {
                            instance.sallingPrice = instance.pSellingPrice;
                            instance.receivingPrice = instance.pReceivingPrice;
                            instance.productQuantity = instance.pQuantity;
                            instance.productEnabled = instance.pEnabled;
                            instance.productSku = instance.pSku;
                            instance.productQuantity = instance.qty;
                            instance.productBarcode = instance.pBarcode;
                            instance.productReorder = instance.pReorder;
                        } else {
                            instance.sallingPrice =
                                instance.product.sallingPrice;
                            instance.receivingPrice =
                                instance.product.receivingPrice;
                            // instance.productQuantity =
                            //     instance.product.quantity;
                            instance.productEnabled = instance.product.enable = true;
                            instance.productSku = instance.standardProductSku;
                            instance.productBarcode =
                                instance.standardProductBarcode;
                            instance.productQuantity =
                                instance.standardProductQuantity;
                            instance.productReorder = instance.product.reorder;
                        }
                        if (this.id) {
                            this.postDataMethod(
                                "/products/edit/" + instance.id,
                                {
                                    name: instance.product.pName,
                                    description: instance.product.description,
                                    taxID: instance.product.tax,
                                    category: instance.product.category,
                                    brand: instance.product.brand,
                                    group: instance.product.group,
                                    unit: instance.product.unit,
                                    branch: instance.product.branch,
                                    type: instance.product.variantType,
                                    variant: instance.pVariant,
                                    variantImage: instance.productVariantImages,
                                    sallingPrice: instance.sallingPrice,
                                    receivingPrice: instance.receivingPrice,
                                    sku: instance.productSku,
                                    barcode: instance.productBarcode,
                                    reorder: instance.productReorder,
                                    quantity: instance.productQuantity,
                                    enabled: instance.productEnabled,
                                    variantDetails: instance.productVariant,
                                    image: instance.product.image,
                                    chipValues: instance.chipArray
                                }
                            );
                        } else {
                            this.postDataMethod("/products/store", {
                                name: instance.product.pName,
                                description : instance.product.description,
                                taxID: instance.product.tax,
                                category: instance.product.category,
                                brand: instance.product.brand,
                                group: instance.product.group,
                                unit: instance.product.unit,
                                branch: instance.product.branch,
                                type: instance.product.variantType,
                                variant: instance.pVariant,
                                variantImage: instance.productVariantImages,
                                sallingPrice: instance.sallingPrice,
                                receivingPrice: instance.receivingPrice,
                                sku: instance.productSku,
                                barcode: instance.productBarcode,
                                reorder: instance.productReorder,
                                quantity: instance.productQuantity,
                                enabled: instance.productEnabled,
                                variantDetails: instance.productVariant,
                                image: instance.product.image,
                                chipValues: instance.chipArray
                            });
                        }
                    }
                });
            });
        },
        productImage(event) {
            let fileName = event.target.files[0].name;
            $("#product-image")
                .next(".custom-file-label")
                .html(fileName);
            let input = event.target;
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = e => {
                    this.product.image = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                this.product.image = "";
            }
        },
        variantImage(event, index, classID) {
            let input = event.target;
            if (input.files && input.files[0]) {
                let fileName = event.target.files[0].name;
                // this.productVariant[index].imageURL = fileName;

                $(classID)
                    .next(".custom-file-label")
                    .html(fileName);

                let reader = new FileReader();
                reader.onload = e => {
                    this.productVariantImages[index] = e.target.result;
                    this.productVariant[index].imageURL = e.target.result;
                    this.productVariant[index].isImageChanged = true;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                this.productVariantImages[index] = "";
                this.productVariant[index].imageURL = "";
            }
        },
        postDataThenFunctionality(response) {
            this.closeModal();
            let instance = this;
            $("#product-add-edit-modal").modal("hide");
            instance.$hub.$emit("reloadDataTable");
        },
        postDataCatchFunctionality(error) {},
        supportingData(data) {
            let instance = this;
            instance.brandList = data.brands;
            instance.categoryList = data.categories;
            instance.groupList = data.groups;
            instance.unitList = data.units;
            instance.taxList = data.taxes;
            instance.branchList = data.branches;
            instance.allAttributes = data.attributes;
            // if (instance.branchList.length == 1) {
            //     instance.product.branch = instance.branchList[0].id;
            // }
        },
        setActiveAttributeModal(value) {
            this.$emit("setActiveAttributeModal", value);
        },
        setPreLoader(value) {
            this.hidePreLoader = value;
        },
        onImageUpload(e) {
            let files = e.target.files || e.dataTransfer.files;
            if (!files.length) return;
            this.createImage(files[0]);
        },
        createImage(file) {
            let reader = new FileReader();
            let instance = this;
            reader.onload = e => {
                instance.product.image = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        arrayRemove(array, element) {
            let index = array.indexOf(element);
            if (index !== -1) {
                array.splice(index, 1);
            }
        },
        addChips(event, tempAttributeID) {
            let value = $(event.target)
                .closest(".variant-values")
                .find("input[type=text]")
                .val();
            if (value != "") {
                if (!this.chipArray[tempAttributeID]) {
                    this.chipArray[tempAttributeID] = [];
                }
                let isDublicate = this.chipArray[tempAttributeID].includes(
                    this.capitalizeFirstLetter(value)
                );
                if (!isDublicate) {
                    this.chipArray[tempAttributeID].push(
                        this.capitalizeFirstLetter(value)
                    );
                }

                $(event.target)
                    .closest(".variant-values")
                    .find("input[type=text]")
                    .val(null);
                let temp = this.tempAttributeList;
                this.tempAttributeList = [];
                this.tempAttributeList = temp;
            }
            this.countVariant();
        },
        deleteChip(event, tempAttributeID, index) {
            this.chipArray[tempAttributeID].splice(index, 1);
            if (this.chipArray[tempAttributeID].length == 0) {
                this.chipArray[tempAttributeID] = null;
            }
            let temp = this.tempAttributeList;
            this.tempAttributeList = [];
            this.tempAttributeList = temp;
            this.countVariant();
        },
        addTempAttribute(event) {
            let index = event.target.value,
                instance = this,
                attribute;
            instance.allAttributes.forEach(function(element) {
                if (element.id == index) {
                    attribute = element;
                }
            });

            if (
                _.find(this.tempAttributeList, { id: attribute.id }) ===
                undefined
            ) {
                instance.tempAttributeList.push(attribute);
            }
        },
        removeTempAttribute(index, tempAttributeID) {
            this.tempAttributeList.splice(index, 1);
            _.unset(this.chipArray, tempAttributeID);
            this.countVariant();
        },
        selectStandardProduct() {
            this.tempAttributeList = [];
            this.tempAttributeList = this.attributeList;
            this.checkVariant = false;
        },
        getActiveAttributeModal(isActive) {
            this.isActiveAttributeModal = isActive;
        }
    }
};

</script>
