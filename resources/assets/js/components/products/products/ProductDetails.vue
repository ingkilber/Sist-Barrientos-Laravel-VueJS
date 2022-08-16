<template>
    <div>
        <div class="main-layout-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.product_details')}}</span>
                        <span><a href="#" @click="goBack()" >({{trans('lang.back_page')}})</a></span>
                    </li>
                </ol>
            </nav>
            <div class="main-layout-card">
                <div v-if="!hidePreLoader" class="productsLayout">
                    <pre-loader class="modal-layout-content"></pre-loader>
                </div>

                <div v-else class="productsLayout">
                    <h4 class="text-center">{{ products.title }}</h4>
                    <hr>
                    <div class="row">
                        <div class="pxs-0 col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                            <img class="img-thumbnail" :src="publicPath+'/uploads/products/' + products.imageURL">
                        </div>
                        <div class="pxs-0 offset-md-1 col-xs-12 col-sm-12 col-md-7 col-lg-7 col-xl-7">
                            <table class="table mb-0 product-detail-table">
                                <tr v-if="products.cat_name !== null">
                                    <th>{{ trans('lang.category') }}</th>
                                    <td>{{products.cat_name}}</td>
                                </tr>
                                <tr v-if="products.brand_name !== null">
                                    <th>{{ trans('lang.brand') }}</th>
                                    <td>{{products.brand_name}}</td>
                                </tr>
                                <tr v-if="products.group_name !== null">
                                    <th>{{ trans('lang.group') }}</th>
                                    <td>{{products.group_name}}</td>
                                </tr>
                                <tr v-if="products.unit_name !== null">
                                    <th>{{ trans('lang.unit') }}</th>
                                    <td>{{products.unit_name}}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('lang.item_tax') }}</th>
                                    <td>{{products.taxable}}</td>
                                </tr>
                                <tr v-if="products.taxable === 'custom'">
                                    <th>{{ trans('lang.tax_name') }}</th>
                                    <td>{{products.tax_name}} ({{products.percentage}})</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('lang.product_type') }}</th>
                                    <td>{{products.temp_product_type}}</td>
                                </tr>
                                <tr v-if="products.product_type === 'standard'">
                                    <th>{{ trans('lang.selling_price') }}</th>
                                    <td>{{numberFormat(selling_price)}}</td>
                                </tr>
                                <tr v-if="products.product_type === 'standard'">
                                    <th>{{ trans('lang.receiving_price') }}</th>
                                    <td>{{numberFormat(purchase_price)}}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('lang.description') }}</th>
                                    <td>{{products.description}}</td>
                                </tr>
                                <tr>
                                    <th>{{ trans('lang.created_by') }}</th>
                                    <td>{{products.first_name}} {{products.last_name}}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="variantData" v-if="products.product_type === 'variant'">
                        <datatable-component class="main-layout-card-content"
                                             :options="tableOptions"></datatable-component>
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
        props: ['id','tab_name', 'route_name'],
        data() {
            return {
                hidePreLoader: true,
                price: '',
                purchase_price: '',
                selling_price: '',
                products: {},
                tableOptions: {
                    tableName: 'products',
                    columns: [
                        {
                            title: 'lang.item_image',
                            key: 'image',
                            type: 'images',
                            source: '/uploads/products',
                            imagefield: 'imageURL',
                            sortable: false
                        },
                        {title: 'lang.variant_title', key: 'variant_title', type: 'text', sortable: true},
                        {title: 'lang.attribute_values', key: 'attribute_values', type: 'text', sortable: false},
                        {title: 'lang.selling_price', key: 'selling_price', type: 'text', sortable: true},
                        {title: 'lang.receiving_price', key: 'purchase_price', type: 'text', sortable: true},
                    ],
                    formatting : ['selling_price','purchase_price'],
                    source: '/products/variantDetails/' + this.id,
                },
            }
        },

        created() {
            this.getProductDetails('/products/getDetails/' + this.id);
        },
        mounted(){
            this.tabName = this.tab_name;
            this.routeName = this.route_name;
        },
        methods: {
                getProductDetails(route) {
                    let instance = this;
                    instance.hidePreLoader = false;
                    instance.axiosGet(route,
                        function (response) {
                            instance.products = response.data.productDetails;
                            if (instance.products.taxable === 0) {
                                instance.products.taxable = instance.trans('lang.no_tax');
                            }
                            else {
                                instance.products.taxable = instance.products.tax_type;
                            }
                            let productType = response.data.productDetails.product_type;
                            if (productType === 'standard') {
                                instance.price = response.data.variant.price;
                                instance.purchase_price = response.data.variant.purchase_price;
                                instance.selling_price = response.data.variant.selling_price;
                            }
                            instance.hidePreLoader = true;
                        },
                        function (response) {

                        },
                    );
                },
                goBack(){
                    let instance = this;
                    instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&${this.routeName}`);
                }
            },
    }
</script>