<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.product_settings') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="hidePreLoader!='hide'"></pre-loader>
            <form v-else>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="re-order">{{ trans('lang.default_re_order_quantity') }}</label>
                        <input
                            type="number"
                            v-validate="'required'"
                            name="reorder"
                            data-vv-as="re-order"
                            class="form-control"
                            id="re-order"
                            v-model="reOrder"
                        />
                        <div class="heightError">
                            <small
                                class="text-danger"
                                v-show="errors.has('reorder')"
                            >{{ errors.first('reorder') }}</small>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="skuPrefix">{{ trans('lang.sku_prefix') }}</label>
                        <input type="text" class="form-control" id="skuPrefix" v-model="skuPrefix" />
                    </div>
                </div>
                <button
                    v-if="permission_key == 'manage'"
                    type="submit"
                    class="btn btn-primary app-color mobile-btn"
                    @click.prevent="productSettingUpdate()"
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
            hidePreLoader: "",
            reOrder: "",
            skuPrefix: ""
        };
    },
    created() {
        this.getProductSettings();
    },
    mounted() {},
    methods: {
        getProductSettings() {
            let instance = this;
            instance.axiosGet(
                "/product-setting",
                function(response) {
                    instance.reOrder = response.data.reOrder;
                    instance.skuPrefix = response.data.skuPrefix;
                    instance.hidePreLoader = "hide";
                },
                function(error) {
                    instance.hidePreLoader = "hide";
                }
            );
        },
        productSettingUpdate() {
            this.$validator.validateAll().then(result => {
                if (result) {
                    let instance = this;
                    instance.hidePreLoader = "load";

                    this.axiosPost(
                        "/product-setting-save",
                        {
                            reOrder: this.reOrder,
                            skuPrefix: this.skuPrefix
                        },
                        function(response) {
                            instance.hidePreLoader = "hide";
                        },
                        function(error) {
                            instance.hidePreLoader = "hide";
                            instance.showErrorAlert(error.data.message);
                        }
                    );
                }
            });
        }
    }
};
</script>