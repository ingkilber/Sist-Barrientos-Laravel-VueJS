<template>
    <div class="main-layout-wrapper" v-if="parseInt(manage_sales) === 1 || parseInt(manage_receives) === 1">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent m-0 pl-0">
                <li class="breadcrumb-item" v-if="order_type === 'sales'">
                    <a href="#" data-toggle="modal" class="sales-nav app-color"
                       :class="{disabled:cart.length != 0 || !isConnected && parseInt(offline) === 1}"
                       data-target="#sales-or-return-type-select-modal">
                        {{ capitalizeFirstLetter(salesOrReturnType) }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item" v-else>
                    <a href="#" data-toggle="modal" class="sales-nav app-color"
                       :class="{disabled:cart.length != 0 || !isConnected && parseInt(offline) === 1}"
                       data-target="#sales-or-return-type-select-modal">
                        {{ capitalizeFirstLetter(receiveOrReturnType) }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item" v-if="order_type === 'receiving'">
                    <span>
                        <a href="#" class="sales-nav" data-toggle="modal"
                           data-target="#sales-or-receiving-type-select-modal"
                           :class="{disabled:cart.length != 0}">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                            <i class="la la-angle-down"/>
                        </a>
                    </span>
                </li>
                <li class="breadcrumb-item" v-if="order_type === 'sales'">
                    <a href="#" class="sales-nav" data-toggle="modal"
                       data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0 || salesOrReturnType === 'Sales List' || salesOrReturnType === 'shipment_list'}"
                       v-if="salesOrReceivingType ==='customer'">
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                        <i class="la la-angle-down"></i>
                    </a>

                    <a href="#" class="sales-nav" data-toggle="modal"
                       data-target="#sales-or-receiving-type-select-modal"
                       :class="{disabled:cart.length != 0 || salesOrReturnType === 'Sales List' || salesOrReturnType === 'shipment_list'}"
                       v-else>
                        {{ capitalizeFirstLetter(salesOrReceivingType) }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item" v-if="selectedBranchID && total_branch>1">
                    <a href="#" class="sales-nav" @click.prevent="branchModalAction()"
                       :class="{disabled:!isConnected && parseInt(offline) === 1 || cart.length != 0}">
                        {{ selectedBranchName }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page"
                    v-if="selectedCashRegisterID && selectedBranchID == selectedCashRegisterBranchID">
                    <a href="#" class="sales-nav" @click.prevent="cashRegisterModalAction()"
                       :class="{disabled:!isConnected && parseInt(offline) === 1}">
                        {{ selectedCashRegisterName }}
                        <i class="la la-angle-down"/>
                    </a>
                </li>
                <li>
                    <a href="#" class="p-1 px-2 ml-2 rounded app-color sales-nav text-white"
                       @click.prevent="openRegisterInfoModal">
                        {{ trans('lang.register_info') }}
                    </a>
                </li>
                <li v-if="!isConnected">
                    <span class="offline-label mx-3 animated fadeIn delay-2s">
                        <i class="la la-wifi text-danger"/> {{ trans('lang.offline') }}
                    </span>
                </li>
                <li id="onlineLabel" v-if="isConnected && hideOnlineMessage">
                    <span class="online-label mx-3 animated fadeOut delay-5s">
                        <i class="la la-wifi"/> {{ trans('lang.online') }}
                    </span>
                </li>
            </ol>
        </nav>

        <div class="d-flex" style="height: calc(100vh - 6rem);"
             v-if="salesOrReturnType !== 'Sales List' && salesOrReturnType !== 'shipment_list' ">
            <div style="flex: 1 0;">
                <div class="main-layout-card mb-75">
                    <div class="main-layout-card-content p-2">
                        <div class="row mx-0">
                            <div class="col-md-9 px-0 mb-2 mb-md-0">

                                <!--Product search starts-->
                                <div class="input-group"
                                     v-if="salesOrReturnType === 'sales' || receiveOrReturnType === 'purchase' ">

                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input id="search"
                                           type="text"
                                           class="form-control pr-4 rounded-right"
                                           :placeholder="trans('lang.search_product')"
                                           aria-label="Search"
                                           aria-describedby="search"
                                           v-model="productSearchValue"
                                           @keyup="searchProductInput"
                                           v-shortkey="productSearch"
                                           @shortkey="commonMethodForAccessingShortcut('productSearch')"
                                           ref="search">
                                    <div v-if="productSearchValue">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="productSearchValue = '', getProductsBySearchBtn()">
                                        </i>
                                    </div>
                                </div>

                                <div class="input-group" v-else>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="la la-search"></i></span>
                                    </div>
                                    <input type="text" class="form-control pr-4 rounded-right"
                                           v-model="orderSearchValue"
                                           :placeholder="trans('lang.search_orders')"
                                           aria-label="Search"
                                           ref="searchOrder"
                                           id="searchOrder"
                                           aria-describedby="search"
                                           @input="searchOrderInput">
                                    <div v-if="orderSearchValue != ''">
                                        <i class="la la-close position-absolute p-1 customer-search-cancel"
                                           @click.prevent="orderSearchValue=''"></i>
                                    </div>
                                    <!-- order search result dropdown structure starts-->
                                    <div class="dropdown-menu dropdown-menu-right w-100"
                                         :class="{'show':orderSearchValue}">
                                        <pre-loader v-if="!hideOrderSearchPreLoader"
                                                    class="small-loader-container"></pre-loader>
                                        <div class="px-3 py-1 text-center"
                                             v-else-if="hideOrderSearchPreLoader && orders.length == 0">
                                            {{ trans('lang.no_result_found') }}
                                        </div>
                                        <div class="customers-container" v-else-if="orders.length != 0">
                                            <span v-for=" (order,index) in orders" @click.prevent="selectOrder(order)">
                                                <a href="#" class="dropdown-item">
                                                <h6 class="m-0"> {{ trans('lang.invoice_id') }} : {{ order.invoice_id }}
                                                    <br>
                                                    <small>{{ trans('lang.date') }} : {{
                                                            dateFormats(order.date)
                                                        }} {{ dateFormatsWithTime(order.date) }}</small>
                                                </h6>
                                                </a>
                                                <div class="dropdown-divider m-0"></div>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 px-0 pl-md-2">
                                <div class="dropdown keep-inside-clicks-open w-100 product-category-filter">

                                    <button v-if="categorySearchValue.length > 0"
                                            class="btn btn-block btn-outline-dark dropdown-toggle d-flex justify-content-between align-items-center"
                                            type="button"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                        {{ trans('lang.selected') }}
                                    </button>

                                    <button v-else
                                            class="btn btn-block btn-outline-dark dropdown-toggle d-flex justify-content-between align-items-center"
                                            type="button"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                        {{ trans('lang.category') }}
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right">

                                        <div class="category-options-wrapper custom-scrollbar">
                                            <div class="row mx-0">
                                                <pre-loader v-if="categoryPreloader"
                                                            class="small-loader-container"/>

                                                <template v-else>
                                                    <div class="col-6" v-for="category in categories">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                   :id="['product-category']+category.id"
                                                                   :value="category.id" v-model="categorySearchValue">
                                                            <label class="custom-control-label"
                                                                   :for="['product-category']+category.id">{{
                                                                    category.name
                                                                }}</label>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center filter-footer">
                                            <a href="#" class="app-color-text"
                                               @click.prevent="clearCategorySearch">{{ trans('lang.clear') }}</a>
                                            <button class="btn app-color" @click.prevent="categorySearch">
                                                {{ trans('lang.apply') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Products result starts-->
                <pre-loader
                    v-if="!hideProductSearchPreLoader && !(order_type==='sales' && salesOrReturnType==='returns')"/>

                <div v-else>
                    <div class="row all-products mr-0 custom-scrollbar">
                        <div
                            v-if="salesOrReturnType === 'sales' || order_type === 'receiving' && receiveOrReturnType !== 'returns'"
                            class="col-6 col-md-4 col-lg-4 col-xl-3 pr-0 mb-75 pl-75 standard-product"
                            v-for="product in products">
                            <a href="#" class="app-color-text" data-toggle="modal"
                               :data-target="variantProductCard(product.variants)"
                               @click.prevent="productCardAction(product)">
                                <div class="product-card bg-white rounded">
                                    <div class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + product.productImage + ')' }">
                                    </div>
                                    <div class="product-card-content product-content">

                                        <div v-if="product.variants.length == 1" class="position-relative h-100">

                                            <!-- Standard product -->
                                            <div v-for="variant in product.variants"
                                                 :class="{ 'h-100': variant.attribute_values === 'default_variant' || product.variants.length > 1}">

                                                <div v-if="variant.attribute_values[0] !== 'default_variant'"
                                                     :class="{ 'h-100': variant.attribute_values === 'default_variant' || product.variants.length > 1}">
                                                    <div
                                                        class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                        {{ product.title }}
                                                        <br> {{ '(' + variant.variant_title + ')' }}
                                                    </div>
                                                </div>
                                                <div v-else class="h-100">
                                                    <div
                                                        class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                                        <span class="limit"> {{ product.title }} </span>
                                                    </div>
                                                </div>
                                                <div
                                                    class="product-card-font position-absolute rounded-right app-color text-white product-price price-section">
                                                    {{ numberFormat(variant.price) }}
                                                </div>
                                                <div v-if="variant.availableQuantity <= 0 && order_type === 'sales'"
                                                     class="product-card-font warning-message text-white sold-out">
                                                    {{ trans('lang.out_of_stock') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div v-else class="position-relative h-100">

                                            <div
                                                class="p-2 h-100 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center ">

                                                <span class="limit"> {{ product.title }} </span>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <!--Show in mobile screen-->
                        <div id="cartShow" v-show='toggleCart'>
                            <a href="#" data-toggle="modal"
                               data-target="#cart-modal-for-mobile-view"
                               @click.prevent="openCartModalForMobile">
                                <i class="la la-shopping-cart"></i>
                            </a>
                        </div>
                        <div class="col-12"
                             v-if="products.length == 0 && !(order_type === 'sales'&& salesOrReturnType === 'returns')">
                            <div class="main-layout-card">
                                <div class="main-layout-card-content text-center">
                                    {{ trans('lang.no_result_found') }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="showLoadMore && isConnected && isEmptyObj(productSearchValue) && ( salesOrReturnType === 'sales' || order_type === 'receiving')"
                            class="col-12 text-center">
                            <load-more :buttonLoader="buttonLoader" :isDisabled="isLoadMoreDisabled"
                                       @submit="loadMoreSubmit"></load-more>
                        </div>
                    </div>
                </div>
            </div>
            <div id="layoutTop" style="position: relative; width: 30rem;">
                <div class="main-layout-card"
                     style="position: absolute; top: 0; bottom: 0; left: 0.75rem; right: 0; min-height: 450px;">
                    <cart-component
                        v-if="isCartComponentActive"
                        :invoice_size="invoice_size"
                        :is_selected_branch="isSelectedBranch"
                        :selected_branch_id="selectedBranchID"
                        :add_customer="addcustomer"
                        :sales_or_receiving_type="salesOrReceivingType"
                        :sales_return_status="salesOrReturnType"
                        :receive_return_status="receiveOrReturnType"
                        :order_type="order_type"
                        :customer_group="customer_group"
                        :offline_customers="offlineCustomers"
                        :newAddedCustomer="newAddedCustomer"
                        :offline_all_Branch="offlineAllBranch"
                        :cart_arr="cart"
                        :active_variant_id="activeVariantId"
                        :active_product_id="activeProductId"
                        :manage_price="manage_price"
                        :user="user"
                        :selectedCashRegisterID="selectedCashRegisterID"
                        :sold_to="sold_to"
                        :sold_by="sold_by"
                        :final_cart="finalCart"
                        :invoice_logo="invoice_logo"
                        :last_invoice_number="lastInvoiceNumber"
                        :invoicePrefix="invoice_prefix"
                        :invoiceSuffix="invoice_suffix"
                        :invoiceTemplate="invoiceTemplate"
                        :bankOrCardAmount="bankOrCardAmount"
                        :bankOrCardOptions="bankOrCardOptions"
                        :calculateBank="calculateBank"
                        :auto_invoice="auto_invoice"
                        :payment_types="payment_types"
                        :selectedBranchID="selectedBranchID"
                        :is_shipment_enable="isShipmentEnable"
                        :shipping_data="shippingAreaData"
                        :sub_total="subTotal"
                        :grand_total="grandTotal"
                        :supplier="supplier"
                        :count_hold_order="countHoldOrder"
                        :order_hold_items="orderHoldItems"
                        :internal_hold_orders="internalHoldOrders"
                        :internal_transfer_hold_orders="internalTransferHoldOrders"
                        :customer_hold_orders="customerHoldOrders"
                        :customer_not_added="customerNotAdded"
                        :selected_customer="selectedCustomer"
                        :selected_search_branch="selectedSearchBranch"
                        :restaurant_order_type="restaurantOrderType"
                        :current_branch="currentBranch"
                        :is_cash_register_branch="isCashRegisterBranch"
                        :restaurant_table_id="restaurantTableId"
                        :is_cash_register_used="isCashRegisterUsed"
                        :is_hold_order_done="isHoldOrderDone"
                        :is_place_order_active="isPlaceOrderActive"
                        :order_id="orderID"
                        :order_id_internal_transfer="orderIdInternalTransfer"
                        :profit="profit"
                        :is_connected="isConnected"
                        :all_restaurant_tables="allRestaurantTables"
                        :new_over_all_discount="newOverAllDiscount"
                        :over_all_discount="overAllDiscount"
                        :discount_amount="discount"
                        :newDiscount_amount="newdiscount"
                        :tax_amount="tax"
                        :add_customer_short_key="addCustomerShortKey"
                        :payment_short_key="paymentShortKey"
                        :hold_card_item="holdCardItem"
                        :cancel_card_item="cancelCardItem"
                        :return_cart_length="returnCartLength"
                        :adjusted_discount="adjustedDiscount"
                        @activeCartPaymentModal="activeCartPaymentModal"
                        @newCustomerAddModalOpen="newCustomerAddModalOpen"
                        @selectCustomerFromCart="selectCustomerFromCart"
                        @taxEditModal="taxEditModal"
                        @removeSelectedCustomerFromCart="removeSelectedCustomerFromCart"
                        @selectSearchBranchFromCart="selectSearchBranchFromCart"
                        @removeSelectedBranchFromCart="removeSelectedBranchFromCart"
                        @openTableModalFromCart="openTableModalFromCart"
                        @setRestaurantOrderTypeFromCart="setRestaurantOrderTypeFromCart"
                        @setCartItemsToCookieOrDBFromCart="setCartItemsToCookieOrDBFromCart"
                        @openHoldOrderModalFromCart="openHoldOrderModalFromCart"
                        @addOverAllDiscountFromCart="addOverAllDiscount"
                        @allProductDiscountFromCart="allProductDiscount"
                        @setTaxIncludedOrExcludedFromCart="setTaxIncludedOrExcludedFromCart"
                        @orderHoldFromCart="orderHoldFromCart">
                    </cart-component>
                </div>
            </div>
        </div>

        <div class="modal fade" id="register-info-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <register-info-modal class="modal-content"
                                     v-if="registerInfoModal"
                                     :current_branch="selectedBranchID"
                                     :modalID="registerInfoModalID"/>
            </div>
        </div>


        <!-- Modal in mobile screen -->
        <div id="cart-modal-for-mobile-view" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><i class="la la-close"></i>
                        </button>
                    </div>
                    <div class="modal-body" id="cartBody">
                        <div style="position: relative; width: 100%; margin-top: -20px; margin-bottom: -15px;">
                            <div class="main-layout-card" style="min-height: 90vh;">
                                <cart-component
                                    v-if="isCartComponentActiveForMobile"
                                    :invoice_size="invoice_size"
                                    :is_selected_branch="isSelectedBranch"
                                    :selected_branch_id="selectedBranchID"
                                    :add_customer="addcustomer"
                                    :sales_or_receiving_type="salesOrReceivingType"
                                    :sales_return_status="salesOrReturnType"
                                    :receive_return_status="receiveOrReturnType"
                                    :order_type="order_type"
                                    :customer_group="customer_group"
                                    :offline_customers="offlineCustomers"
                                    :newAddedCustomer="newAddedCustomer"
                                    :offline_all_Branch="offlineAllBranch"
                                    :cart_arr="cart"
                                    :active_variant_id="activeVariantId"
                                    :active_product_id="activeProductId"
                                    :manage_price="manage_price"
                                    :user="user"
                                    :selectedCashRegisterID="selectedCashRegisterID"
                                    :sold_to="sold_to"
                                    :sold_by="sold_by"
                                    :final_cart="finalCart"
                                    :invoice_logo="invoice_logo"
                                    :last_invoice_number="lastInvoiceNumber"
                                    :invoicePrefix="invoice_prefix"
                                    :invoiceSuffix="invoice_suffix"
                                    :invoiceTemplate="invoiceTemplate"
                                    :bankOrCardAmount="bankOrCardAmount"
                                    :bankOrCardOptions="bankOrCardOptions"
                                    :calculateBank="calculateBank"
                                    :auto_invoice="auto_invoice"
                                    :payment_types="payment_types"
                                    :selectedBranchID="selectedBranchID"
                                    :is_shipment_enable="isShipmentEnable"
                                    :shipping_data="shippingAreaData"
                                    :is_cash_register_branch="isCashRegisterBranch"
                                    :restaurant_table_id="restaurantTableId"
                                    :sub_total="subTotal"
                                    :grand_total="grandTotal"
                                    :count_hold_order="countHoldOrder"
                                    :order_hold_items="orderHoldItems"
                                    :internal_hold_orders="internalHoldOrders"
                                    :internal_transfer_hold_orders="internalTransferHoldOrders"
                                    :customer_hold_orders="customerHoldOrders"
                                    :customer_not_added="customerNotAdded"
                                    :selected_customer="selectedCustomer"
                                    :selected_search_branch="selectedSearchBranch"
                                    :restaurant_order_type="restaurantOrderType"
                                    :current_branch="currentBranch"
                                    :is_cash_register_used="isCashRegisterUsed"
                                    :is_hold_order_done="isHoldOrderDone"
                                    :is_place_order_active="isPlaceOrderActive"
                                    :supplier="supplier"
                                    :order_id="orderID"
                                    :order_id_internal_transfer="orderIdInternalTransfer"
                                    :profit="profit"
                                    :is_connected="isConnected"
                                    :all_restaurant_tables="allRestaurantTables"
                                    :new_over_all_discount="newOverAllDiscount"
                                    :over_all_discount="overAllDiscount"
                                    :discount_amount="discount"
                                    :newDiscount_amount="newdiscount"
                                    :tax_amount="tax"
                                    :add_customer_short_key="addCustomerShortKey"
                                    :payment_short_key="paymentShortKey"
                                    :hold_card_item="holdCardItem"
                                    :cancel_card_item="cancelCardItem"
                                    :return_cart_length="returnCartLength"
                                    :adjusted_discount="adjustedDiscount"
                                    @activeCartPaymentModal="activeCartPaymentModal"
                                    @newCustomerAddModalOpen="newCustomerAddModalOpen"
                                    @taxEditModal="taxEditModal"
                                    @selectCustomerFromCart="selectCustomerFromCart"
                                    @removeSelectedCustomerFromCart="removeSelectedCustomerFromCart"
                                    @selectSearchBranchFromCart="selectSearchBranchFromCart"
                                    @removeSelectedBranchFromCart="removeSelectedBranchFromCart"
                                    @openTableModalFromCart="openTableModalFromCart"
                                    @setRestaurantOrderTypeFromCart="setRestaurantOrderTypeFromCart"
                                    @setCartItemsToCookieOrDBFromCart="setCartItemsToCookieOrDBFromCart"
                                    @openHoldOrderModalFromCart="openHoldOrderModalFromCart"
                                    @addOverAllDiscountFromCart="addOverAllDiscount"
                                    @allProductDiscountFromCart="allProductDiscount"
                                    @setTaxIncludedOrExcludedFromCart="setTaxIncludedOrExcludedFromCart"
                                    @orderHoldFromCart="orderHoldFromCart">
                                </cart-component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Product Variant Modal Structure -->
        <div class="modal fade" id="show-product-variant-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered big-modal-dialog" role="document">
                <div class="modal-content pt-3 px-3" v-if="selectedProductWithVariants">
                    <div class="mb-3">
                        <a href="#"
                           class="close"
                           data-dismiss="modal"
                           aria-label="Close"
                           @click.prevent="">
                            <i class="la la-close text-grey"></i>
                        </a>
                        <h5 class="m-0 text-center">{{ selectedProductWithVariants.title }}</h5>
                    </div>
                    <div class="row  mx-0">

                        <div class="col-4 mb-4" v-for="(variant,index) in selectedProductWithVariants.variants">
                            <a href="#" class="app-color-text"
                               @click.prevent="addProductToCart(selectedProductWithVariants,variant.id)">
                                <div class="product-card bg-white border rounded">
                                    <div v-if="variant.imageURL" class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + variant.imageURL+ ')' }">
                                    </div>
                                    <div v-else class="product-img-container image-property"
                                         :style="{ 'background-image': 'url(' + publicPath+'/uploads/products/' + selectedProductWithVariants.productImage + ')'}">

                                    </div>
                                    <div class="product-variant-card-content position-relative p-2">
                                        <div
                                            class="mb-2 d-flex align-items-center product-card-font font-weight-bold text-center justify-content-center">
                                            {{ variant.variant_title }}
                                        </div>


                                        <h6 class="product-card-font"
                                            v-for="(variantAttribute,index) in variant.attributeName">
                                            {{ variantAttribute }} : {{ variant.attribute_values[index] }}
                                        </h6>

                                        <h6 class="product-card-font position-absolute rounded-right app-color text-white price-section">
                                            {{ numberFormat(variant.price) }}
                                        </h6>
                                        <h6 v-if="variant.availableQuantity <= 0 && order_type ==='sales'"
                                            class="product-card-font warning-message text-white sold-out">
                                            {{ trans('lang.out_of_stock') }}
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Add supplier Modal Structure -->
        <div class="modal fade" id="supplier-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <supplier-create-edit class="modal-content" v-if="isCustomerModalActive" :id="selectedItemId"
                                      :order_type="order_type"
                                      :modalID="'#supplier-add-edit-modal'"
                                      @newSupplier="newCustomer">
                </supplier-create-edit>
            </div>
        </div>
        <!-- End Modal -->

        <div class="modal fade modal-hide" id="bank-transfer-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <bank-transfer-details
                    v-if="isActiveTrans"
                    :paid="paidAmount"
                    @bankPayment="defaultPayment"/>
            </div>
        </div>


        <!--Branch or cash register select modal-->
        <div class="modal fade" id="branch-or-cash-register-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <div class="modal-content modal-layout-content" v-if="isBranchModalActive">
                    <pre-loader v-if="!hideBranchPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="dashboard()">
                            <i class="la la-angle-left"/> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">{{ trans('lang.choose_branch') }}</h6>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':selectedBranchID == branch.id}" v-for="branch in branchList"
                               @click.prevent="selectBranch(branch.id, branch.name, branch.branch_type, branch.is_cash_register, branch.is_shipment)">
                                {{ branch.name }}
                            </a>

                        </div>
                    </div>
                </div>
                <div class="modal-content modal-layout-content" v-if="isCashRegisterModalActive">
                    <pre-loader v-if="!hideCashRegisterPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="" v-if="checkCashRegisterOpen()">
                            <i class="la la-close text-grey"/>
                        </a>
                        <a href="#" class="position-absolute p-2 back-button"
                           @click.prevent="branchModalAction(1), isCashRegisterModalActive = false">
                            <i class="la la-angle-left"/> {{ trans('lang.back_page') }}
                        </a>
                        <h6 class="mb-3 text-center">
                            {{ trans('lang.select_cash_register') }}
                        </h6>
                        <div class="accordion" id="accordionExample">
                            <div class="card" v-for="(cashRegister,index) in cashRegisterList">
                                <div class="d-flex justify-content-between">
                                    <a href="#" :id="'cash-register-'+index" data-toggle="collapse"
                                       :data-target="'#collapse-'+index" aria-expanded="true"
                                       :aria-controls="'collapse-'+index"
                                       class="card-header app-color-text p-2 d-flex justify-content-between align-items-center border-bottom-0"
                                       :class="{'card-header-with-enroll-btn':!checkCashRegisterOpenByUser(cashRegister)} && cashRegister.multiple_access==1"
                                       @click.prevent="cashRegisterCollapse(index,cashRegister.id,cashRegister,cashRegister.status)">
                                        <div class="d-flex  align-items-center">
                                            <i class="la la-chevron-circle-right la-2x cart-icon"
                                               :class="{'cart-icon-rotate':cashRegister.showItemCollapse}"/>
                                            <div>
                                                <div class="pl-2">{{ cashRegister.title }}</div>
                                                <div v-if="cashRegister.status === 'open'"
                                                     class="pl-2 sales-cash-register">{{ cashRegister.register_status }}
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="cashRegister.status === 'closed'"
                                              class="badge badge-danger badge-pill">{{ trans('lang.closed') }}</span>
                                        <span v-else
                                              class="badge badge-success badge-pill">{{ trans('lang.open') }}</span>
                                    </a>
                                    <a href="#"
                                       v-if="!checkCashRegisterOpenByUser(cashRegister) && parseInt(cashRegister.multiple_access) === 1 && selectedCashRegisterID != cashRegister.id"
                                       class="p-2 text-white enroll-btn d-flex align-items-center font-weight-bold product-card-font"
                                       @click.prevent="setCashRegisterData(cashRegister,'enroll')">
                                        {{ trans('lang.join') }}</a>
                                </div>
                                <div :id="'collapse-'+index" class="collapse border-top"
                                     :aria-labelledby="'cash-register-'+index" data-parent="#accordionExample">
                                    <div class="card-body card-body pb-3 pt-2 px-0">
                                        <form>
                                            <div class="row mx-0">

                                                <div class="mb-3 col-12" v-if="cashRegister.status === 'open'">
                                                    <label :for="'note-'+index" class="label-in-cart">{{
                                                            trans('lang.note')
                                                        }}{{ cashRegister.note }}</label>

                                                    <label>
                                                    <textarea :id="'note-'+index" :name="'note'"
                                                              v-validate="(!(!(openingAmount == closingAmount)&&!note&&!noteValidation) )? '':'required'"
                                                              class="form-control"
                                                              v-model="note"/>
                                                    </label>
                                                    <div class="heightError">
                                                        <small class="text-danger" v-show="errors.has('note')">
                                                            {{ errors.first('note') }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="col-9" v-if="cashRegister.status === 'closed'">
                                                    <label
                                                        :for="'opening-amount-'+index">{{
                                                            trans('lang.opening_amount_label')
                                                        }}</label>

                                                    <payment-input :id="'opening-amount-'+index"
                                                                   v-model="cashRegister.openingAmount"></payment-input>

                                                </div>

                                                <div class="col-9" v-else>
                                                    <label>{{ trans('lang.expected_closing_amount') }}:
                                                        {{ numberFormat(expectedClosingAmount) }}</label>
                                                    <br>
                                                    <label
                                                        :for="'closing-amount-'+index">{{
                                                            trans('lang.closing_amount_label')
                                                        }}</label>
                                                    <payment-input :id="'closing-amount-'+index"
                                                                   v-model="closingAmount"></payment-input>
                                                </div>

                                                <div class="col-3 mt-auto" v-if="cashRegister.status === 'closed'">
                                                    <button class="btn app-color float-right"
                                                            :disabled="!(cashRegister.openingAmount || parseInt(cashRegister.openingAmount) === 0)"
                                                            @click.prevent="setCashRegisterData(cashRegister,'open')">
                                                        {{ trans('lang.open') }}
                                                    </button>
                                                </div>

                                                <div class="col-3 mt-auto" v-else>
                                                    <button class="btn btn-danger float-right"
                                                            :disabled="!(disableCloseButton  && parseInt(cashRegister.permision) === 1)"
                                                            @click.prevent="setCashRegisterData(cashRegister,'close')">
                                                        {{ trans('lang.close') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sales or returns  Type Select Modal Structure -->
        <div class="modal fade" id="sales-or-return-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <pre-loader v-if="!hideSalesReturnsPreLoader" class="small-loader-container"/>
                    <div v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h6 v-if="order_type === 'sales'" class="mb-3 text-center">
                            {{ trans('lang.select_sales_or_returns_type') }}</h6>
                        <div class="list-group" v-if="order_type === 'sales'">

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType === 'sales'}"
                               @click.prevent="selectSalesOrReturnType('sales')">{{ trans('lang.sales') }}</a>

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType === 'returns'}"
                               @click.prevent="selectSalesOrReturnType('returns')">{{ trans('lang.returns') }}</a>

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType === 'Sales List'}"
                               @click.prevent="selectSalesOrReturnType('Sales List')">{{ trans('lang.sales_list') }}</a>

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReturnType ==='shipment_list'}"
                               @click.prevent="selectSalesOrReturnType('shipment_list')">{{
                                    trans('lang.shipment_list')
                                }}</a>

                        </div>

                        <h6 v-if="order_type === 'receiving'" class="mb-3 text-center">
                            {{ trans('lang.select_purchase_or_returns_type') }}</h6>
                        <div class="list-group" v-if="order_type === 'receiving'">

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':receiveOrReturnType === 'purchase'}"
                               @click.prevent="selectReceiveOrReturnType('purchase')">{{ trans('lang.purchase') }}</a>

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':receiveOrReturnType === 'returns'}"
                               @click.prevent="selectReceiveOrReturnType('returns')">{{ trans('lang.returns') }}</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!--Due payment component-->
        <div class="main-layout-card">
            <sales-list-component
                v-if="salesOrReturnType === 'Sales List' && isSalesListComponentActive"
                :branch_id="selectedBranchID"
                @resetBranchAndCashRegisterModal="resetBranchAndCashRegisterModal"/>

            <shipment-list-component
                v-if="salesOrReturnType === 'shipment_list' && isShipmentListComponentActive"
                :branch_id="selectedBranchID"
            />
        </div>


        <!-- Sales or returns  Type Select Modal Structure -->
        <div class="modal fade" id="increase-local-storage-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3 text-center">
                    <div class="my-2">
                        <i class="la la-exclamation-circle la-5x text-info"/>
                    </div>
                    <h5>{{ trans('lang.your_local_storage_is_full') }}</h5>
                    <h6>{{ trans('lang.are_you_want_to_increase_your_local_storage') }}</h6>
                    <div class="my-4">
                        <button class="btn app-color" @click="increaseLocalStorageInChrome()">
                            {{ trans('lang.yes') }}
                        </button>
                        <button class="btn btn-secondary cancel-btn mobile-btn"
                                @click="hideIncreaseLocalStorageModal()">
                            {{ trans('lang.no') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!--Sales orReceiving Type Select Modal Structure-->
        <div class="modal fade" id="sales-or-receiving-type-select-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content px-4 pb-4 pt-3">
                    <div>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h6 class="mb-3 text-center">{{ trans('lang.select_sales_or_receiving_type') }}</h6>
                        <div class="list-group">

                            <a href="#" class="list-group-item list-group-item-action" v-if="order_type === 'sales'"
                               :class="{'active':salesOrReceivingType === 'customer'}"
                               @click.prevent="selectSalesOrReceivingType('customer')">{{ trans('lang.customer') }}</a>

                            <a href="#" class="list-group-item list-group-item-action" v-else
                               :class="{'active':salesOrReceivingType ==='supplier'}"
                               @click.prevent="selectSalesOrReceivingType('supplier')">{{ trans('lang.supplier') }}</a>

                            <a href="#" class="list-group-item list-group-item-action"
                               :class="{'active':salesOrReceivingType === 'internal' && isActive}"
                               @click.prevent="selectSalesOrReceivingType('internal')">{{ trans('lang.internal') }}</a>

                            <a href="#" class="list-group-item list-group-item-action" v-if="order_type === 'sales'"
                               :class="{'active':salesOrReceivingType === 'internal-transfer'}"
                               @click.prevent="selectSalesOrReceivingType('internal-transfer')">{{
                                    trans('lang.internal_transfer')
                                }}</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restaurant Table Selection Modal -->
        <div class="modal fade modal-hide" id="table-select-modal" tabindex="-1" role="dialog" aria-hidden="true"
             style="overflow-y: hidden;">
            <div class="modal-dialog modal-dialog-centered biggest-modal-dialog" role="document">
                <table-selection-modal
                    v-if="isTableModalActive"
                    :restaurant_tables_branch_wise="allRestaurantTables"
                    :transfer_branch_name="selectedSearchBranch.name"
                    :sales_or_receiving_type="salesOrReceivingType"
                    :transfer_branch="selectedSearchBranch.id"
                    :order_type="order_type"
                    :final_cart="finalCart"
                    :logo="invoice_logo"
                    :sold_to="sold_to"
                    :sold_by="sold_by"
                    :user="user"
                    :booked_tables="bookedTables"
                    :customer_hold_orders="customerHoldOrders"
                    :internal_hold_orders="internalHoldOrders"
                    :internal_transfer_hold_orders="internalTransferHoldOrders"
                    :order_hold_items="orderHoldItems"
                    :invoice_template="invoiceTemplate"
                    @getRestaurantTableId="getRestaurantTableId">
                </table-selection-modal>
            </div>
        </div>

        <!-- Show Cart Payment Modal Structure -->
        <div class="modal fade modal-hide" id="cart-payment-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered biggest-modal-dialog" role="document">
                <cart-payment-details
                    v-if="isPaymentModalActive"
                    :invoice_size="invoice_size"
                    :is_template_default="isTemplateDefault"
                    :selectedCashRegisterID="selectedCashRegisterID"
                    class="modal-content"
                    :orderType="order_type"
                    :salesOrReceivingType="salesOrReceivingType"
                    :salesOrReturnType="salesOrReturnType"
                    :receiveOrReturnType="receiveOrReturnType"
                    :sold_to="sold_to"
                    :sold_by="sold_by"
                    :finalCart='finalCart'
                    :user="user"
                    :orderID="orderID"
                    :add_shipping="addShipping"
                    :orderIdInternalTransfer="orderIdInternalTransfer"
                    :invoiceId="invoiceId"
                    :logo="invoice_logo"
                    :last_invoice_number="lastInvoiceNumber"
                    :invoice_prefix="invoicePrefix"
                    :invoice_suffix="invoiceSuffix"
                    :invoice_template="invoiceTemplate"
                    :bankOrCardAmount="bankOrCardAmount"
                    :bankOrCardOptions="bankOrCardOptions"
                    :donePaymentShortcut="donePaymentItem"
                    :calculateBank="calculateBank"
                    :transferBranch="selectedSearchBranch.id"
                    :transferBranchName="selectedSearchBranch.name"
                    :autoInvoice="auto_invoice"
                    :paymentTypes="payment_types"
                    :selectedBranchID="selectedBranchID"
                    :is_shipment_enable="isShipmentEnable"
                    :shipping_data="shippingAreaData"
                    :is_connected="isConnected"
                    :is_cash_register_branch="isCashRegisterBranch"
                    :is_cash_register_used="isCashRegisterUsed"
                    :sales_default_invoice_template="salesDefaultTemplate"
                    :receives_default_invoice_template="receiveDefaultTemplate"
                    @setDestroyCart="destroyCart"
                    @amount="bankOrCardTransfer"
                    @getUpdatedInvoice="getUpdatedInvoice"
                    @makeInvoiceIdNull="makeInvoiceIdNull"
                    @makePlaceOrderTrue="makePlaceOrderTrue"
                    @addShipmentInfo="addShipmentInfo">
                </cart-payment-details>
            </div>
        </div>

        <!-- Add Customer Modal Structure -->
        <div class="modal fade" id="customer-add-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <customer-create-edit v-if="isCustomerModalActive"
                                      class="modal-content" :order_type="order_type"
                                      :modalID="'#customer-add-edit-modal'"
                                      :customerGroups="customer_group"
                                      status=true
                                      @newCustomer="newCustomer">
                </customer-create-edit>
            </div>
        </div>

        <!--modal tax start-->
        <div class="modal fade" id="tax-edit-modal" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <tax-edit-modal
                    v-if="isTaxModalActive"
                    class="modal-content"
                    :modalID="'#tax-edit-modal'"
                    :user="user"
                >
                </tax-edit-modal>
            </div>
        </div>

        <!-- Hold Orders Modal Structure -->
        <div class="modal fade" id="hold-orders-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <pre-loader class="small-loader-container" v-if="!hideOrderHoldItemsPreLoader"/>
                    <div class="pt-2 px-4 pb-4" v-else>
                        <a href="#" class="close" data-dismiss="modal"
                           aria-label="Close" @click.prevent="">
                            <i class="la la-close text-grey"/>
                        </a>
                        <h5 class="mb-3 text-center">
                            {{ trans('lang.hold_order_list') }}
                        </h5>

                        <div v-if="currentBranch != null && salesOrReceivingType === 'internal'"
                             class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <label>
                                            <input type="text"
                                                   :placeholder="trans('lang.search_invoice')"
                                                   v-model="searchHoldOrder"
                                                   class="form-control rounded-right">
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(internalHoldOrder) in filteredHoldOrder"
                                             v-if="internalHoldOrder.status === 'hold'">
                                            <div class="col-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(internalHoldOrder)">
                                                    <div class="row">
                                                        <div class="col-5 text-left">
                                                            <span class="font-weight-bold pl-1">{{
                                                                    internalHoldOrder.invoice_id
                                                                }}</span>
                                                        </div>
                                                        <div class="col-7">
                                                            <span class="text-center">
                                                                {{ dateFormats(internalHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(internalHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(internalHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length == 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="currentBranch != null && currentBranch.branch_type !== 'restaurant' && salesOrReceivingType === 'customer'"
                            class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <label>
                                            <input type="text"
                                                   :placeholder="trans('lang.search_invoice')"
                                                   v-model="searchHoldOrder"
                                                   class="form-control rounded-right">
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(customerHoldOrder) in filteredHoldOrder"
                                             v-if="customerHoldOrder.status === 'hold'">
                                            <div class="col-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(customerHoldOrder)">

                                                    <div class="row">
                                                        <div class="col-5 text-left">
                                                            <span class="font-weight-bold pl-1">{{
                                                                    customerHoldOrder.invoice_id
                                                                }}</span>
                                                        </div>
                                                        <div class="col-7">
                                                            <span class="text-center">
                                                                {{ dateFormats(customerHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(customerHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(customerHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length == 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hold Orders For Restaurant Module -->
                        <div
                            v-else-if="currentBranch != null && currentBranch.branch_type === 'restaurant' && salesOrReceivingType === 'customer'"
                            class="container-hold-orders">
                            <div class="row mr-0">
                                <div class="col-12 pr-0">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="la la-search"></i>
                                            </span>
                                        </div>
                                        <label>
                                            <input type="text"
                                                   :placeholder="trans('lang.search_invoice')"
                                                   v-model="searchHoldOrder"
                                                   class="form-control rounded-right">
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 pr-0">
                                    <div>
                                        <div class="row mx-0 h-100 border hold-order-list-item"
                                             v-for="(customerHoldOrder, index) in filteredHoldOrder"
                                             v-if="customerHoldOrder.status === 'hold'">
                                            <div class="col-10 col-sm-11 col-md-11 col-lg-11 px-0">
                                                <a href="#"
                                                   class="d-block hold-items app-color-text"
                                                   @click.prevent="setHoldOrderToCart(customerHoldOrder)">
                                                    <div class="row">
                                                        <div class="col-7 col-sm-4 col-md-4 col-lg-4 text-left">
                                                            <span class="font-weight-bold invoice-id">{{
                                                                    customerHoldOrder.invoice_id
                                                                }}</span>
                                                        </div>
                                                        <div class="col-5 col-sm-3 col-md-3 col-lg-3">
                                                            <span class="font-weight-bold invoice-table">{{
                                                                    customerHoldOrder.tableName
                                                                }}</span>
                                                        </div>
                                                        <div class="col-12 col-sm-5 col-md-5 col-lg-5">
                                                            <span class="invoice-time">
                                                                {{ dateFormats(customerHoldOrder.date) }}
                                                                {{ timeFormateForDatetime(customerHoldOrder.time) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-2 col-sm-1 col-md-1 col-lg-1 px-0 my-auto">
                                                <a href="#"
                                                   data-toggle="modal"
                                                   data-target="#clear-cart-modal"
                                                   class="d-block pr-1"
                                                   @click.prevent="deleteHoldOrder(customerHoldOrder)">
                                                    <i class="la la-times-circle text-danger hold-delete-icon p-1 h-100"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <p class="text-center"
                                           v-if="order_type === 'sales' && filteredHoldOrder.length == 0">
                                            {{ trans('lang.no_result_found') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <confirmation-modal id="clear-cart-modal"
                            :message="'order_will_be_cancelled'"
                            :firstButtonName="'yes'"
                            :secondButtonName="'no'"
                            @confirmationModalButtonAction="confirmationModalButtonAction"/>
    </div>
</template>

<script>
import templateHelperMixin from "../../mixins/templateHelperMixin";
import axiosGetPost from '../../helper/axiosGetPostCommon';
import salesOrReceiveMixin from "./mixin/SalesOrPurchaseMixin";

export default {
    props: [
        'user',
        'order_type',
        'sold_to',
        'sold_by',
        'addcustomer',
        'manage_price',
        'manage_sales',
        'manage_receives',
        'current_branch',
        'current_cash_register',
        'total_branch',
        'sales_return_status',
        'purchase_return_status',
        'sales_receiving_type',
        'auto_invoice',
        'payment_types',
        'customer',
        'customer_group',
        'product',
        'shortcutKeyCollection',
        'app_name',
        'invoice_prefix',
        'invoice_suffix',
        'last_invoice',
        'is_branch_selected',
        'all_branch',
        'supplier',
        'hold_orders',
        'default_sales_invoice_template',
        'default_receive_invoice_template',
        'restaurant_tables',
        'booked_tables',
    ],
    extends: axiosGetPost,
    mixins: [templateHelperMixin, salesOrReceiveMixin],

}
</script>