<template>
    <span>
        <div id="cart-section-1" class="modal-layout-header p-0">
            <div v-if="isSelectedBranch && customerNotAdded" class="sales-search p-2">
                <div class="row">
                    <div
                        :class="{'col-10': addCustomer === 'manage', 'col-12':(addCustomer !== 'manage' || salesOrReceivingType === 'internal' || salesOrReceivingType == 'internal-transfer'), 'pr-0':(order_type == 'sales' && salesOrReceivingType == 'customer' && addCustomer == 'manage')}">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="la la-user"></i></span>
                            </div>
                            <input type="text"
                                   class="form-control pr-4 rounded-right"
                                   v-if="salesOrReceivingType === 'customer'"
                                   v-model="customerSearchValue"
                                   :placeholder="trans('lang.search_customers')"
                                   aria-label="Search"
                                   aria-describedby="search"
                                   @input="searchCustomerInput"
                                   @keydown.enter="selectCustomer(customers[highlightIndex])"
                                   @keydown.down="down"
                                   @keydown.up="up">
                            <input type="text"
                                   class="form-control pr-4 rounded-right"
                                   v-else-if="salesOrReceivingType === 'internal' || salesOrReceivingType === 'internal-transfer'"
                                   v-model="branchSearchValue"
                                   :placeholder="trans('lang.search_branch')"
                                   aria-label="Search"
                                   aria-describedby="search"
                                   @input="searchBranchInput"
                                   @keydown.enter="selectSearchBranch(branches[highlightIndex])"
                                   @keydown.down="down"
                                   @keydown.up="up">
                            <input type="text"
                                   class="form-control pr-4 rounded-right"
                                   v-else
                                   v-model="customerSearchValue"
                                   :placeholder="trans('lang.search_suppliers')"
                                   aria-label="Search"
                                   aria-describedby="search"
                                   @input="searchCustomerInput"
                                   @keydown.enter="selectCustomer(customers[highlightIndex])"
                                   @keydown.down="down"
                                   @keydown.up="up">
                            <div v-if="customerSearchValue!=''">
                                <i class="la la-close position-absolute p-1 customer-search-cancel"
                                   @click.prevent="customerSearchValue=''"></i>
                            </div>
                            <div v-if="branchSearchValue!=''">
                                <i class="la la-close position-absolute p-1 customer-search-cancel"
                                   @click.prevent="branchSearchValue=''"></i>
                            </div>

                            <!-- Customer search result dropdown structure starts-->
                            <div class="dropdown-menu dropdown-menu-right w-100"
                                 :class="{'show':customerSearchValue}">
                                <pre-loader v-if="!hideCustomerSearchPreLoader"
                                            class="small-loader-container"></pre-loader>
                                <div class="px-3 py-1 text-center"
                                     v-else-if="hideCustomerSearchPreLoader && customers.length === 0">
                                    {{ trans('lang.no_result_found') }}
                                </div>
                                <div class="customers-container" v-else-if="customers.length !== 0">
                                    <span v-for="(customer,index) in customers">
                                        <a href="#"
                                           class="dropdown-item"
                                           :class="{ active: parseInt(index) === parseInt(highlightIndex) }"
                                           @click.prevent="selectCustomer(customer)">
                                            <h6 class="m-0">
                                                {{ customer.first_name + ' ' + customer.last_name }}
                                                <br>

                                                <small v-show="customer.email != null"><i
                                                    class="la la-envelope"/> {{ customer.email }}</small>
                                                <small v-show="customer.phone_number != null"><i
                                                    class="la la-phone-square"/> {{ customer.phone_number }}</small>
                                            </h6>
                                        </a>
                                        <div class="dropdown-divider m-0"></div>
                                    </span>
                                </div>
                            </div>
                            <!-- Customer search result dropdown structure ends-->

                            <!--Branches search result dropdown structure starts-->
                            <div class="dropdown-menu dropdown-menu-right w-100"
                                 :class="{'show':branchSearchValue}">
                                <pre-loader v-if="!hideCustomerSearchPreLoader"
                                            class="small-loader-container"></pre-loader>
                                <div class="px-3 py-1 text-center"
                                     v-else-if="hideCustomerSearchPreLoader && parseInt(branches.length) === 0">
                                    {{ trans('lang.no_result_found') }}
                                </div>
                                <div class="customers-container" v-else-if="branches.length !== 0">
                                    <span v-for="(branch,index) in branches">
                                        <a href="#"
                                           class="dropdown-item"
                                           :class="{ active: parseInt(index) === parseInt(highlightIndex) }"
                                           @click.prevent="selectSearchBranch(branch)">
                                            <h6 class="m-0">
                                                {{ branch.name }}
                                            </h6>
                                        </a>
                                        <div class="dropdown-divider m-0"></div>
                                    </span>
                                </div>
                            </div>
                            <!-- Branches search result dropdown structure ends-->
                        </div>
                    </div>
                    <!--Supplier Add button-->
                    <div class="col-2"
                         v-if="addCustomer === 'manage' && salesOrReceivingType !== 'supplier' && order_type ==='receiving'">
                        <span>
                            <a class="btn app-color"
                               data-toggle="modal"
                               data-target="#supplier-add-edit-modal"
                               href="#"
                               @click.prevent="isCustomerModalActive=true">
                                <i class="la la-user-plus"></i>
                            </a>
                        </span>
                    </div>
                    <!--Customer add button-->
                    <div class="col-2 pl-75"
                         v-if="addCustomer ==='manage' && salesOrReceivingType === 'customer' && order_type ==='sales'">
                        <span>
                            <a class="btn app-color"
                               data-toggle="modal"
                               data-target="#customer-add-edit-modal"
                               href="#"
                               @click.prevent="newCustomerAddModalOpen"
                               v-shortkey="add_customer_short_key"
                               @shortkey="commonMethodForAccessingShortcut('addCustomerShortcut')">
                                <i class="la la-user-plus"></i>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="p-2" v-if="!customerNotAdded" v-for=" (customer,index) in selectedCustomer">
                <h6 class="m-0 cart-product-details-parent">
                    <div class="cart-product-details-child">
                        {{ customer.first_name + ' ' + customer.last_name }}
                        <br>
                         <small v-show="customer.email != null"> <i
                             class="la la-envelope"></i> {{ customer.email }}</small>
                         <small v-show="customer.phone_number != null"> <i
                             class="la la-phone-square"></i> {{ customer.phone_number }}</small>
                    </div>
                    <a href="#" class="cart-product-details-child text-right"
                       @click.prevent="removeSelectedCustomer(index)">
                        <i class="la la-close"></i>
                    </a>
                </h6>
            </div>
            <div class="p-2"
                 v-if="(salesOrReceivingType === 'internal' || salesOrReceivingType === 'internal-transfer') && selectedSearchBranch != 0">
                <h6 class="m-0 cart-product-details-parent">
                    <div class="cart-product-details-child">
                        {{ selectedSearchBranch.name }}
                    </div>
                    <a href="#" class="cart-product-details-child text-right"
                       @click.prevent="removeSelectedBranch">
                        <i class="la la-close"></i>
                    </a>
                </h6>
            </div>
        </div>
        <div id="cart-section-2" class="cart-items-wrapper"
             :class="{'h-100 d-flex align-items-center justify-content-center' : parseInt(cart.length) === 0}">
            <span v-if="parseInt(cart.length) === 0">
                {{ trans('lang.empty_cart') }}
            </span>
            <div v-else class="cart-item-container py-1" v-for="(cartItem,index) in cart"
                 :class="{'active-cart-item': cartItem.showItemCollapse }">
                <div class="form-row mx-0 px-1 cart-item">
                    <div class="col-6 p-0 cart-item-btn"
                         @click.prevent="cartItemCollapse(index,cartItem.variantID)">
                        <div class="row mx-0 my-auto">
                            <div class="col-1 p-0 m-auto">
                                <i class="la la-chevron-circle-right la-2x cart-icon"
                                   :class="{'cart-icon-rotate':cartItem.showItemCollapse}"></i>
                            </div>
                            <div class="col-11  my-auto mx-0 pr-0">
                                <div class="row mx-0 cart-item-title"
                                     :class="{cartProduct: cartItem.productID == activeProductId && cartItem.variantID == activeVariantId && cartItem.orderType !== 'discount'}">
                                    <div class="col-12 pl-1 p-0 my-auto mx-0">
                                        {{ cartItem.productTitle }}
                                        <br>

                                        <span
                                            v-if="cartItem.variantTitle && cartItem.variantTitle !== 'default_variant' && cartItem.orderType !== 'discount'">( {{
                                                cartItem.variantTitle
                                            }} )</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-3 my-auto px-0 ml-3 mr-0">
                        <div class="d-flex justify-content-between mr-1 cart-quantity">
                            <div class="pl-0 mx-0">
                                <a href="#"
                                   :class="{disabled:cartItem.orderType ==='discount' || (salesOrReturnType ==='returns' && order_type === 'sales') || (order_type === 'receiving' && receiveOrReturnType === 'returns')}"
                                   @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'-')">
                                    <i class="la la-minus-circle la-2x cart-icon-color"></i>
                                </a>
                            </div>
                            <div class="align-self-center">
                                {{ numberFormatting(cartItem.quantity) }}
                            </div>
                            <div class="">
                                <a href="#"
                                   :class="{disabled:cartItem.orderType ==='discount'}"
                                   @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'+')">
                                    <i class="la la-plus-circle la-2x cart-icon-color"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-3 d-flex pr-0 pl-0 cart-calculatedPrice">
                        <div class="align-self-center ml-auto">
                            <span>{{ numberFormat(cartItem.calculatedPrice) }}</span>
                        </div>
                        <div v-if="salesOrReturnType !== 'returns'" class="align-self-center ml-2">
                            <a href="#" class="red-text"
                               :class="{disabled:cart.length == 1 && !isConnected && offline == 1}"
                               @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'delete')"><i
                                class="la la-trash del-icon-color"></i></a>
                        </div>
                        <div v-else class="align-self-center ml-2">
                            <a href="#" class="red-text"
                               :class="{disabled: (cart.length == 2 && !isConnected && offline == 1) || (cartItem.orderType === 'discount' && (cart.length == return_cart_length || cart.length == 2))}"
                               @click.prevent="cartItemButtonAction(cartItem.productID,cartItem.variantID,cartItem.orderType,'delete')"><i
                                class="la la-trash del-icon-color"></i></a>
                        </div>
                    </div>
                </div>
                <div class="form-row mx-0 px-2  collapse-animation"
                     :class="{'collapsed':cartItem.showItemCollapse}">
                    <div class="form-group pl-0 mb-zero" :class="[checkDiscount() ? 'col-4':'col-6']">
                        <label :for="'cart-item-quantity'+index" class="label-in-cart ">
                            {{ trans('lang.quantity') }}</label>
                        <payment-input :id="'cart-item-quantity'+index"
                                       :inputValue="numberFormatting(cartItem.quantity)" :index="index"
                                       @input="setQuantityInCart">
                        </payment-input>
                    </div>
                    <div class="form-group pr-0 mb-zero" :class="[checkDiscount() ? 'col-4':'col-6']"
                         v-if="order_type ==='receiving' || manage_price == 1 ">
                        <label :for="'cart-item-price'+index" class="label-in-cart">{{ trans('lang.price') }}</label>
                        <payment-input :id="'cart-item-price'+index"
                                       :inputValue="cartItem.unformPrice" :index="index"
                                       @input="setProductNewPrice"></payment-input>
                    </div>
                    <div class="form-group pr-0 mb-zero" :class="[checkDiscount() ? 'col-4':'col-6']"
                         v-if="checkDiscountType()">
                        <label :for="'cart-item-discount'+index" class="label-in-cart w-100">
                            {{ trans('lang.item_discount') }} (%)
                        </label>
                        <div class="position-relative">
                            <payment-input :id="'cart-item-discount'+index" v-model="cartItem.discount"
                                           :inputValue="discount"
                                           @input="setCartItemsToCookieOrDB(1)">
                            </payment-input>
                        </div>
                    </div>
                    <div class="col-12 p-0 pb-2">
                        <label :for="'cart-item-note'+index" class="label-in-cart">{{ trans('lang.note') }}</label>
                        <textarea :id="'cart-item-note'+index"
                                  @keyup="setCartItemsToCookieOrDB(1)"
                                  class="form-control"
                                  v-model="cartItem.cartItemNote">
                        </textarea>
                    </div>
                </div>
            </div>
        </div>

        <div id="cart-section-3" class="position-absolute fixed-bottom product-card-font">
            <div class="row mx-0 px-3 py-2 font-weight-bold border-top border-bottom">
                <div class="col-6 p-0">
                    {{ trans('lang.sub_total') }}
                </div>
                <div class="col-6 p-0 text-right">
                    {{ numberFormat(subTotal) }}
                </div>
            </div>
            <div class="row mx-0 px-3 py-2  border-bottom" v-if="this.sales_or_receiving_type !== 'internal-transfer'">
                <div class="col-6 p-0">
                    {{ trans('lang.tax') }}
                </div>
                <div class="col-6 p-0 text-right">
                    {{ numberFormat(tax) }}
                </div>
            </div>
            <div class="row mx-0 px-3 py-2  border-bottom" id="pop_mouse1" v-popover:foo.right
                 v-if="this.sales_or_receiving_type !== 'internal-transfer'">
                <div class="col-6 p-0">
                    {{ trans('lang.discount_all_items_by_percent') }} (%)
                    <popover name="foo">
                        <payment-input :inputValue="newDiscount"
                                       :salesOrReturnType="salesOrReturnType"
                                       @input="allProductDiscount"
                                       :discountField="discountOnAllItem">
                        </payment-input>
                    </popover>
                </div>
                <div class="col-3 p-0 text-center">
                    <div class="btn-group dropright" role="group"></div>
                </div>
                <div class="col-3 p-0 text-right">
                    <span v-if="discount">
                        <i class="la la-edit discount-all-item-popover"></i>{{ decimalFormat(discount) + '%' }}
                    </span>
                    <span v-else>
                        <i class="la la-edit discount-all-item-popover"></i>{{ numberFormat(discount) + '%' }}
                    </span>
                </div>
            </div>
            <div class="row mx-0 px-3 py-2" id="pop_mouse2" v-popover:foo1.right
                 v-if="sales_or_receiving_type !== 'internal-transfer'">
                <div class="col-6 p-0">
                    {{ trans('lang.discount_on_sub_total') }}
                </div>

                <div class="col-3 p-0 text-center">
                    <div class="btn-group dropright" role="group">
                        <popover name="foo1">
                            <payment-input :inputValue="newOverAllDiscount"
                                           :productTotalWithoutDiscount="productTotalWithoutDiscount"
                                           :salesOrReturnType="salesOrReturnType"
                                           @input="addOverAllDiscount">
                            </payment-input>
                        </popover>
                    </div>
                </div>
                <div class="col-3 p-0 text-right">
                    <span v-if="overAllDiscount">
                        <i class="la la-edit discount-on-subtotal-popover"></i>{{ numberFormat(newOverAllDiscount) }}
                    </span>
                    <span v-else>
                        <i class="la la-edit discount-on-subtotal-popover"></i>{{ numberFormat(newOverAllDiscount) }}
                    </span>
                </div>
            </div>

            <div class="row mx-0 px-3 py-2"
                 v-if="sales_or_receiving_type !== 'internal-transfer' && adjustedDiscount">
               <div class="col-6 p-0">
                   {{ trans('lang.discount_adjustment') }}
               </div>
               <div class="col-6 p-0 text-right">
                    {{ numberFormat(adjustedDiscount) }}
               </div>
           </div>

            <!-- Total amount section with tax exclude and include -->
            <div class="row mx-0 px-3 py-2 border-top border-bottom">
                <div class="col-6 p-0">
                    <span class="font-weight-bold">
                        {{ trans('lang.total') }}
                    </span>
                    <span
                        v-if="currentBranch !== null && salesOrReturnType!=='returns' && order_type === 'sales' && this.sales_or_receiving_type !== 'internal-transfer'">
                        <span class="">( {{ trans('lang.tax') }}</span>
                        <span v-if="parseInt(user.tax_excluded) === 0">{{ trans('lang.included') }})</span>
                        <span v-else>{{ trans('lang.excluded') }} )</span>
                    </span>
                </div>

                <div class="col-3 p-0 text-center">
                    <div class="btn-group dropright" role="group">
                        <!-- This div for middle space -->
                    </div>
                </div>

                <div class="col-3 p-0 text-right">
                    <span
                        v-if="currentBranch !== null && salesOrReturnType!=='returns' && order_type === 'sales' && sales_or_receiving_type !== 'internal-transfer'">
                        <a class=""
                           data-toggle="modal"
                           data-target="#tax-edit-modal"
                           href="#"
                           :class="{'disabled':!isConnected}"
                           @click.prevent="taxEditModalOpen"
                        ><i class="la la-edit discount-all-item-popover"></i></a>
                    </span>
                    <span class="col-6 p-0 font-weight-bold text-right">
                    {{ numberFormat(grandTotal) }}
                </span>
                </div>
            </div>

            <!-- Start Template for Restaurant Module -->
            <div class="row mx-0 px-3 py-2 font-weight-bold border-bottom"
                 v-if="currentBranch !== null && currentBranch.branch_type === 'restaurant' && salesOrReturnType!=='returns' && salesOrReceivingType === 'customer'">
                <div class="col-4 p-0 custom-line-height">
                    {{ trans('lang.order_type') }}
                </div>
                <div class="col-8 p-0 text-right">
                    <button class="btn btn-restaurant-order-type mr-2"
                            :class="{'selected-btn-restaurant-order-type': restaurantOrderType === 'dineIn'}"
                            @click.prevent="setRestaurantOrderType('dineIn')">
                        <i class="la la-cutlery"></i> {{ trans('lang.dine_in') }}
                    </button>
                    <button class="btn btn-restaurant-order-type"
                            :class="{'selected-btn-restaurant-order-type': restaurantOrderType === 'takeAway'}"
                            @click.prevent="setRestaurantOrderType('takeAway')">
                        <i class="la la-shopping-cart"></i> {{ trans('lang.take_away') }}
                    </button>
                </div>
            </div>
            <!-- Place order button for restaurant -->
            <div class="p-3 border-bottom"
                 v-if="currentBranch !== null && currentBranch.branch_type === 'restaurant' && restaurantOrderType === 'dineIn' && (salesOrReceivingType !== 'internal' || salesOrReceivingType === 'internal-transfer') && salesOrReturnType !== 'returns' && order_type === 'sales' && isPlaceOrderActive">
                <button class="btn pay-btn app-color"
                        href="#"
                        @click.prevent="openTableModal"
                        :disabled="enableDisablePay() || disabledPay() || !isConnected && parseInt(offline) === 0 || isHoldOrderDone === true">
                    {{ trans('lang.place_order') }}
                </button>
            </div>

            <!-- Pay button for restaurant -->
            <div
                v-if="currentBranch !== null && currentBranch.branch_type === 'restaurant' && salesOrReceivingType === 'customer' && !isPlaceOrderActive"
                class="p-3 border-bottom">
                <button class="btn pay-btn app-color"
                        data-toggle="modal"
                        data-target="#cart-payment-modal"
                        href="#"
                        @click.prevent="cartSave()"
                        :disabled="enableDisablePay() || disabledPay() || !isConnected && offline == 0"
                        v-shortkey="payment_short_key"
                        @shortkey="commonMethodForAccessingShortcut('payShortcut')">
                    {{ trans('lang.pay') }}
                </button>
            </div>
            <!-- End Template for Restaurant Module -->
            <div class="p-3 border-bottom"
                 v-if="salesOrReceivingType == 'internal' || salesOrReceivingType == 'internal-transfer'">
                <button class="btn pay-btn app-color"
                        data-toggle="modal"
                        data-target="#cart-payment-modal"
                        href="#"
                        @click.prevent="cartSave()"
                        :disabled="selectedSearchBranch == 0 || enableDisablePay() || disabledPay() || !isConnected && offline == 0"
                        v-shortkey="payment_short_key"
                        @shortkey="commonMethodForAccessingShortcut('payShortcut')">
                    {{ trans('lang.pay') }}
                </button>
            </div>

           <div
               v-if="(currentBranch !== null && currentBranch.branch_type !== 'restaurant' && salesOrReceivingType !== 'internal' && salesOrReceivingType !== 'internal-transfer') || (currentBranch !== null && currentBranch.branch_type === 'restaurant' && salesOrReceivingType != 'internal'  && (order_type === 'receiving' || salesOrReturnType =='returns'))"
               class="p-3 border-bottom">
               <button class="btn pay-btn app-color"
                       data-toggle="modal"
                       data-target="#cart-payment-modal"
                       href="#"
                       @click.prevent="cartSave()"
                       :disabled="enableDisablePay() || disabledPay() || (!isConnected && offline == 0) || (!isConnected && order_type == 'receiving')"
                       v-shortkey="payment_short_key"
                       @shortkey="commonMethodForAccessingShortcut('payShortcut')">
                   {{ trans('lang.pay') }}
               </button>
           </div>

            <div v-if="order_type=='sales'" class="row mx-0">

                <a v-if="salesOrReceivingType== 'internal' || salesOrReceivingType== 'internal-transfer'"
                   href="#"
                   class="col-4 p-0 text-center border-right hold-items"
                   :class="{'disabled': internalHoldOrders.length == 0  || internalTransferHoldOrders.length == 0 ||cart.length != 0 || salesOrReturnType == 'returns' }"
                   @click.prevent="openHoldOrderModal">
                    <i class="la la-recycle la-2x p-2 app-color-text hold-icon"></i>
                    <span class="badge badge-danger hold-items-badge-position"
                          v-if="countHoldOrder>0">{{ countHoldOrder }}</span>
                </a>

                <a v-else-if="salesOrReceivingType === 'customer'"
                   href="#"
                   class="col-4 p-0 text-center border-right hold-items"
                   :class="{'disabled': parseInt(customerHoldOrders.length) === 0 || parseInt(cart.length) !== 0 || salesOrReturnType === 'returns' }"
                   @click.prevent="openHoldOrderModal">
                    <i class="la la-recycle la-2x p-2 app-color-text hold-icon"></i>
                    <span class="badge badge-danger hold-items-badge-position"
                          v-if="countHoldOrder>0">{{ countHoldOrder }}</span>
                </a>

                <a v-if="salesOrReceivingType === 'internal' || salesOrReceivingType === 'internal-transfer'"
                   href="#"
                   class="col-4 p-0 text-center border-right hold-cart"
                   :class="{'disabled': cart.length === 0 || isEmptyObj(selectedSearchBranch) || !isConnected && offline == 0}"
                   @click.prevent="orderHold()"
                   v-shortkey="hold_card_item"
                   @shortkey="commonMethodForAccessingShortcut('holdCard')">
                    <i class="la la-pause la-2x p-2 text-warning"></i>
                </a>
                <a v-else
                   href="#"
                   class="col-4 p-0 text-center border-right hold-cart"
                   :class="{'disabled': cart.length === 0 || !isConnected && offline == 0}"
                   @click.prevent="orderHold()"
                   v-shortkey="hold_card_item"
                   @shortkey="commonMethodForAccessingShortcut('holdCard')">
                    <i class="la la-pause la-2x p-2 text-warning"></i>
                </a>

                <a href="#"
                   data-toggle="modal"
                   data-target="#clear-cart-modal"
                   class="col-4 p-0 text-center clear-cart"
                   :class="{'disabled': parseInt(cart.length) === 0}"
                   @click.prevent=""
                   v-shortkey="cancel_card_item"
                   @shortkey="commonMethodForAccessingShortcut('cancelCarditem')">
                    <i class="la la-times-circle la-2x p-2 text-danger"></i>
                </a>
            </div>
            <div v-else class="row mx-0 receiveDeleteButton">
                <a href="#"
                   data-toggle="modal"
                   data-target="#clear-cart-modal"
                   class="col-12 p-0 text-center clear-cart"
                   :class="{'disabled': parseInt(cart.length) === 0}"
                   @click.prevent="">
                    <i class="la la-times-circle la-2x p-2 text-danger"></i>
                </a>
            </div>
        </div>

    </span>
</template>

<script>
import axiosGetPost from '../../../helper/axiosGetPostCommon';

export default {
    extends: axiosGetPost,
    props: [
        'supplier',
        'is_selected_branch',
        'selected_branch_id',
        'add_customer',
        'newAddedCustomer',
        'sales_or_receiving_type',
        'sales_return_status',
        'receive_return_status',
        'order_type',
        'order_id',
        'order_id_internal_transfer',
        'customer_group',
        'offline_customers',
        'offline_all_Branch',
        'current_branch',
        'user',
        'cart_arr',
        'active_variant_id',
        'active_product_id',
        'manage_price',
        'selectedCashRegisterID',
        'sold_to',
        'sold_by',
        'final_cart',
        'invoice_logo',
        'last_invoice_number',
        'invoicePrefix',
        'invoiceSuffix',
        'invoiceTemplate',

        'bankOrCardAmount',
        'bankOrCardOptions',
        'calculateBank',
        'auto_invoice',
        'payment_types',
        'selectedBranchID',
        'is_shipment_enable',

        'sub_total',
        'grand_total',

        'count_hold_order',
        'order_hold_items',
        'internal_hold_orders',
        'internal_transfer_hold_orders',
        'customer_hold_orders',
        'customer_not_added',
        'selected_customer',
        'selected_search_branch',
        'branch_search_value',

        'restaurant_order_type',
        'restaurant_table_id',
        'is_cash_register_branch',
        'is_cash_register_used',
        'is_hold_order_done',
        'is_place_order_active',
        'all_restaurant_tables',
        'profit',
        'is_connected',
        'new_over_all_discount',
        'over_all_discount',
        'newDiscount_amount',
        'discount_amount',
        'tax_amount',
        'add_customer_short_key',
        'payment_short_key',
        'hold_card_item',
        'cancel_card_item',
        'shipping_data',
        'return_cart_length',
        'invoice_size',
        'adjusted_discount',
    ],
    data() {
        return {
            parseInt,
            adjustedDiscount: 0,
            hideCustomerSearchPreLoader: true,
            salesOrReceivingType: '',
            salesOrReturnType: null,
            receiveOrReturnType: null,
            isPaymentModalActive: false,
            customerNotAdded: true,
            selectedCustomer: [],
            customerSearchValue: '',
            customers: [],
            addCustomer: null,
            isCustomerModalActive: false,
            isNewCustomerAdded: false,
            offlineCustomers: [],
            newCustomerId: '',
            branchSearchValue: '',
            isSelectedBranch: null,
            branches: [],
            selectedSearchBranch: [],
            offlineAllBranch: [],
            currentBranch: [],
            selectedBranchId: null,
            open: null,
            highlightIndex: 0,
            newOverAllDiscount: 0,
            showOverAllDisc: false,
            showDiscount: false,
            discountOnEntire: 'discountOnEntire',
            discountOnAllItem: 'discountOnAllItem',
            //cart variables
            cart: [],
            finalCart: [],
            newCart: [],
            total: 0,
            grandTotal: 0,
            subTotal: 0,
            productTotalWithoutDiscount: 0,

            orderID: null,
            orderIdInternalTransfer: null,
            invoiceId: null,
            lastInvoiceNumber: 0,
            //product
            activeProductId: null,
            activeVariantId: null,

            //local storage
            totalStorage: 4500,
            remainingStorage: null,
            minimumSizeOfLocalStorage: 500,

            orderHoldItems: [],
            internalHoldOrders: [],
            internalTransferHoldOrders: [],
            customerHoldOrders: [],
            countHoldOrder: 0,
            hideOrderHoldItemsPreLoader: false,

            payment: [],
            holdCarditem: [],
            cancelCarditem: [],
            donePaymentItem: [],

            restaurantOrderType: '',
            isCashRegisterBranch: '',
            restaurantTableId: '',
            isHoldOrderDone: '',
            isTaxExcluded: true,
            isPlaceOrderActive: null,
            allRestaurantTables: [],
            isConnected: null,
            discount: 0,
            newDiscount: 0,
            tax: 0,
            cartLength: 0,
            discountEnable: false,
            returnDiscount: true,
            overAllDiscount: 0,
            isEmptyObj: (object) => {
                if (_.isEmpty(object)) {
                    return true;
                }
            },
            // Shortcuts Variable
            addCustomerShortKey: '',
            paymentShortKey: '',
            holdCardItem: '',
            cancelCardItem: '',
            isDisableCartPlusBtn: false,
        }
    },
    watch: {
        //on the fly discount calculation
        over_all_discount: function (value) {
            if (value != null && value !== '') {
                this.showOverAllDisc = true;
                this.showDiscount = true;
            } else {
                this.showOverAllDisc = false;
                this.showDiscount = false;
            }
            this.overAllDiscount = value;

        },
        sales_or_receiving_type: function (value) {
            this.salesOrReceivingType = value;
        },
        is_selected_branch: function (value) {
            this.isSelectedBranch = value;
        },
        selected_branch_id: function (value) {
            this.selectedBranchID = value;
        },
        add_customer: function (value) {
            this.addCustomer = value;
        },
        cart_arr: function (value) {
            this.cart = value;
            this.cartLength = value.length;

            this.cartMinHeightSet();
        },
        sales_return_status: function (value) {
            this.salesOrReturnType = value;
        },
        receive_return_status: function (value) {
            this.receiveOrReturnType = value;
        },
        active_variant_id: function (value) {
            this.activeVariantId = value;
        },
        active_product_id: function (value) {
            this.activeProductId = value;
        },
        final_cart: function (value) {
            this.finalCart = value;
        },
        sub_total: function (value) {
            this.subTotal = value;
        },
        grand_total: function (value) {
            this.grandTotal = value;
        },
        newAddedCustomer: function (value) {
            this.getCustomerData(value);
        },
        count_hold_order: function (value) {
            this.countHoldOrder = value;
        },
        order_hold_items: function (value) {
            this.orderHoldItems = value;
        },
        internal_hold_orders: function (value) {
            this.internalHoldOrders = value;
        },
        internal_transfer_hold_orders: function (value) {
            this.internalTransferHoldOrders = value;
        },
        customer_hold_orders: function (value) {
            this.customerHoldOrders = value;
        },
        customer_not_added: function (value) {
            this.customerNotAdded = value;
        },
        selected_customer: function (value) {
            this.selectedCustomer = value;
        },
        selected_search_branch: function (value) {
            this.selectedSearchBranch = value;
        },
        branch_search_value: function (value) {

        },
        restaurant_order_type: function (value) {
            this.restaurantOrderType = value;
        },
        current_branch: function (value) {
            this.currentBranch = value;
        },
        is_cash_register_branch: function (value) {
            this.isCashRegisterBranch = value;
        },
        restaurant_table_id: function (value) {
            this.restaurantTableId = value;
        },
        last_invoice_number: function (value) {
            this.lastInvoiceNumber = value;
        },
        is_hold_order_done: function (value) {
            this.isHoldOrderDone = value;
        },
        is_place_order_active: function (value) {
            this.isPlaceOrderActive = value;
        },
        offline_customers: function (value) {
            this.offlineCustomers = value;
        },
        offline_all_Branch: function (value) {
            this.offlineAllBranch = value;
        },
        order_id: function (value) {
            this.orderID = value;
        },
        order_id_internal_transfer: function (value) {
            this.orderIdInternalTransfer = value;
        },
        all_restaurant_tables: function (value) {
            this.allRestaurantTables = value;
        },
        is_connected: function (value) {
            this.isConnected = value;
        },
        new_over_all_discount: function (value) {
            this.newOverAllDiscount = value;
        },
        discount_amount: function (value) {
            this.discount = value;
        },
        tax_amount: function (value) {
            this.tax = value;
        },
        newDiscount_amount: function (value) {
            this.newDiscount = value;
        },
        add_customer_short_key: function (value) {
            this.addCustomerShortKey = value;
        },
        payment_short_key: function (value) {
            this.paymentShortKey = value;
        },
        hold_card_item: function (value) {
            this.holdCardItem = value;
        },
        cancel_card_item: function (value) {
            this.cancelCardItem = value;
        },
        adjusted_discount: function (value) {
            this.adjustedDiscount = value;
        },
    },
    created() {
        this.isTaxExcluded = parseInt(this.user.tax_excluded) !== 0;
        this.setTaxIncludedOrExcluded(this.isTaxExcluded);
    },
    mounted() {
        let instance = this;
        this.isSelectedBranch = this.is_selected_branch;
        this.addCustomer = this.add_customer;
        this.salesOrReceivingType = this.sales_or_receiving_type;
        this.cart = this.cart_arr;
        this.salesOrReturnType = this.sales_return_status;
        this.receiveOrReturnType = this.receive_return_status;
        this.activeVariantId = this.active_variant_id;
        this.activeProductId = this.active_product_id;
        this.finalCart = this.final_cart;
        this.grandTotal = this.grand_total;
        this.subTotal = this.sub_total;
        this.selectedBranchID = this.selected_branch_id;
        this.countHoldOrder = this.count_hold_order;
        this.orderHoldItems = this.order_hold_items;
        this.internalHoldOrders = this.internal_hold_orders;
        this.internalTransferHoldOrders = this.internal_transfer_hold_orders;
        this.customerHoldOrders = this.customer_hold_orders;
        this.customerNotAdded = this.customer_not_added;
        this.selectedCustomer = this.selected_customer;
        this.selectedSearchBranch = this.selected_search_branch;
        this.restaurantOrderType = this.restaurant_order_type;
        this.currentBranch = this.current_branch;
        this.isCashRegisterBranch = this.is_cash_register_branch;
        this.restaurantTableId = this.restaurant_table_id;
        this.customerSearchValue = '';
        this.branchSearchValue = '';
        this.lastInvoiceNumber = this.last_invoice_number;
        this.isHoldOrderDone = this.is_hold_order_done;
        this.isPlaceOrderActive = this.is_place_order_active;
        this.offlineCustomers = this.offline_customers;
        this.offlineAllBranch = this.offline_all_Branch;
        this.orderID = this.order_id;
        this.orderIdInternalTransfer = this.order_id_internal_transfer;
        this.allRestaurantTables = this.all_restaurant_tables;
        this.isConnected = this.is_connected;
        this.newOverAllDiscount = this.new_over_all_discount;
        this.discount = this.discount_amount;
        this.tax = this.tax_amount;
        this.newDiscount = this.newDiscount_amount;
        this.overAllDiscount = this.over_all_discount;
        this.addCustomerShortKey = this.add_customer_short_key;
        this.paymentShortKey = this.payment_short_key;
        this.holdCardItem = this.hold_card_item;
        this.cancelCardItem = this.cancel_card_item;
        // Set cart items wrapper height dynamically
        this.$nextTick(function () {
            instance.cartMinHeightSet();
        });
        $(window).resize(function () {
            setTimeout(function () {
                instance.cartMinHeightSet();
            });
        });

        this.modalCloseAction(this.modalID);
        // cart modal for mobile
        $('#cart-modal-for-mobile-view').on('shown.bs.modal', function (e) {
            instance.cartMinHeightSet();
        });
    },
    methods: {
        checkDiscountType() {
            if (this.order_type === 'sales') {
                return this.sales_or_receiving_type !== 'internal-transfer';

            }
            return false;

        },
        checkDiscount() {
            if (this.manage_price == 1) {
                return this.sales_or_receiving_type !== 'internal-transfer';

            }
        },
        cartMinHeightSet() {
            let section1 = $("#cart-section-1").height();
            let section2 = $("#cart-section-3").position();
            $("#cart-section-2").css('max-height', (section2.top - section1) + 'px');
        },
        /* customer*/
        newCustomerAddModalOpen() {
            this.$emit('newCustomerAddModalOpen');
        },
        taxEditModalOpen() {
            this.$emit('taxEditModal');
        },
        storeCustomer() {
            $('#customer-add-edit-modal').modal('show')
            this.isCustomerModalActive = true;
        },
        searchCustomerInput() {
            let instance = this;
            if (!this.open) {
                this.open = true
            }
            this.highlightIndex = 0;
            let sortedResults;

            if (instance.order_type === 'sales') {
                sortedResults = instance.offlineCustomers;
                instance.commonCustomerSupplierSearch(sortedResults);
            } else {
                sortedResults = JSON.parse(instance.supplier);
                instance.commonCustomerSupplierSearch(sortedResults);
            }

            instance.hideCustomerSearchPreLoader = true;
        },
        commonCustomerSupplierSearch(sortedResults) {
            let instance = this;
            if (instance.customerSearchValue) {
                instance.customers = sortedResults.filter(function (element) {
                    let firstName = element.first_name.toLowerCase().includes(instance.customerSearchValue.toLowerCase()),
                        lastName = element.last_name.toLowerCase().includes(instance.customerSearchValue.toLowerCase()),
                        fullName = element.first_name + ' ' + element.last_name,
                        fullNameSearch = fullName.toLowerCase().includes(instance.customerSearchValue.toLowerCase());

                    if ((element.email != null && element.email != '') || (element.phone_number != null && element.phone_number != '')) {
                        if ((element.email != null && element.email != '') && (element.phone_number != null && element.phone_number != '')) {
                            return (firstName || lastName || fullNameSearch
                                || element.email.toLowerCase().includes(instance.customerSearchValue.toLowerCase())
                                || element.phone_number.includes(instance.customerSearchValue.toLowerCase())
                            );

                        } else if ((element.email != null && element.email != '')) {
                            return (firstName || lastName || fullNameSearch
                                || element.email.toLowerCase().includes(instance.customerSearchValue.toLowerCase())
                            );
                        } else {
                            return (firstName || lastName || fullNameSearch
                                || element.phone_number.includes(instance.customerSearchValue.toLowerCase())
                            );
                        }
                    } else {
                        return (firstName || lastName || fullNameSearch);
                    }
                });
            } else {
                instance.customers = sortedResults;
            }
        },
        selectCustomer(customer) {
            this.selectedCustomer = [];
            this.selectedCustomer.push(customer);
            this.$emit('selectCustomerFromCart', this.selectedCustomer);

            if (this.order_type === 'sales') {

                let customerGroups = JSON.parse(this.customer_group);

                if (customer.customer_group_discount != undefined) {
                    this.allProductDiscount(customer.customer_group_discount);
                } else {
                    let customerCurrentGroup = customerGroups.find(function (element) {
                        return element.id == customer.customer_group;
                    })
                    this.allProductDiscount(customerCurrentGroup.discount)
                }
            }

            this.setCartItemsToCookieOrDB(1);
            this.customerSearchValue = '';
        },
        getCustomerData(data) {
            let instance = this;
            instance.customers = this.offlineCustomers;
            if (instance.isNewCustomerAdded) {
                instance.selectCustomer(0);
            }
            instance.isNewCustomerAdded = false;
            instance.hideCustomerSearchPreLoader = true;
            if (data) {
                instance.selectCustomer(data);
                instance.newCustomerId = '';

            } else {
                let customer = instance.customers.filter(function (element) {
                    return parseInt(element.id) === parseInt(instance.newCustomerId);
                });
                instance.selectCustomer(customer[0]);
            }
        },
        removeSelectedCustomer(index) {
            this.selectedCustomer.splice(index, 1);
            this.customerSearchValue = '';
            this.discount = null;
            this.allProductDiscount();
            this.setCartItemsToCookieOrDB(1);
            this.$emit('removeSelectedCustomerFromCart', this.selectedCustomer);
        },
        // branch
        searchBranchInput(event) {
            let instance = this;
            instance.hideCustomerSearchPreLoader = false;
            if (!this.open) {
                this.open = true
            }
            this.highlightIndex = 0;
            let sortedResults;
            sortedResults = instance.offlineAllBranch;
            if (instance.branchSearchValue) {

                instance.branches = sortedResults.filter(function (element) {
                    return (element.name.toLowerCase().includes(instance.branchSearchValue.toLowerCase()) && element.id !== instance.currentBranch.id);
                });
            } else {
                instance.branches = sortedResults;
            }
            instance.hideCustomerSearchPreLoader = true;

        },
        selectSearchBranch(branch) {
            this.$emit('selectSearchBranchFromCart', branch);
            this.branchSearchValue = '';
        },
        removeSelectedBranch() {
            this.$emit('removeSelectedBranchFromCart');
            this.branchSearchValue = '';
        },

        allProductDiscount(value, index, unformatted) {
            this.$emit('allProductDiscountFromCart', Number(value), index, unformatted);
        },

        addOverAllDiscount(value, index, unformatted) {
            this.$emit('addOverAllDiscountFromCart', value, index, unformatted);
        },
        cartItemButtonAction(cartProductID, cartVariantID, orderType, action) {
            let instance = this;
            instance.$emit('returnCartChange', true);
            this.cart.forEach(function (cartItem, index, cartArray) {
                if (cartItem.productID == cartProductID && cartItem.variantID == cartVariantID) {
                    if (action === '+') {
                        cartArray[index].quantity++;

                        if (cartArray[index].quantity >= cartArray[index].availbleQuantity && instance.order_type === 'sales' && instance.outOfStockProduct == 1) {
                            instance.isDisableCartPlusBtn = true;
                        }
                        if (cartArray[index].quantity > cartArray[index].availbleQuantity && instance.order_type === 'sales') {
                            let variantTitle = cartArray[index].variantTitle === 'default_variant' ? '' : `(${cartArray[index].variantTitle})`;
                            let alertMessage = cartArray[index].productTitle + ' ' + variantTitle + ' ' + instance.trans('lang.is_out_of_stock');
                            instance.showWarningAlert(alertMessage);
                        }
                        if (parseInt(cartItem.quantity) === 0) {
                            cartArray.splice(index, 1);
                        }
                        if ((instance.order_type === 'sales' && instance.salesOrReturnType === 'returns') || (instance.order_type === 'receiving' && instance.receiveOrReturnType === 'returns')) {
                            cartItem.calculatedPrice = cartItem.quantity * cartItem.price;
                        }
                    } else if (action === '-') {
                        --cartArray[index].quantity;

                        if (cartArray[index].quantity < cartArray[index].availbleQuantity) {
                            instance.isDisableCartPlusBtn = false;
                        }
                        if (parseInt(cartItem.quantity) === 0) {
                            cartArray.splice(index, 1);
                        }
                    } else {
                        if (orderType === 'discount') {
                            instance.overAllDiscount = null;
                            instance.newOverAllDiscount = null;
                        }
                        cartArray.splice(index, 1);
                    }
                }

                if (parseInt(instance.cart.length) === 0) {
                    instance.discount = null;
                    instance.newdiscount = null;
                    instance.newOverAllDiscount = 0;
                    instance.adjustedDiscount = 0;
                }
            });
            this.setCartItemsToCookieOrDB(1);
        },
        cartItemCollapse(index, variantID) {
            let instance = this;
            this.cart.forEach(function (cartItem, i, array) {
                if (parseInt(i) === parseInt(index) && cartItem.variantID === variantID && cartItem.orderType !== 'discount' && !(instance.order_type === 'sales' && instance.salesOrReturnType === 'returns')) {
                    array[i].showItemCollapse = !array[i].showItemCollapse;
                } else {
                    array[i].showItemCollapse = false;
                }
            });
        },
        setQuantityInCart(value, index) {
            let instance = this;
            value = Number(value);
            if (parseInt(this.outOfStockProduct) === 1 && value > this.cart[index].availbleQuantity && this.order_type === 'sales') {
                this.cart[index].quantity = 0;
                this.isDisableCartPlusBtn = true;
                let variantTitle = this.cart[index].variantTitle === 'default_variant' ? '' : `(${this.cart[index].variantTitle})`;
                let alertMessage = this.cart[index].productTitle + ' ' + variantTitle + '' + this.trans('lang.is_out_of_stock') + this.trans('lang.available_quantity') + this.cart[index].availbleQuantity;
                this.showWarningAlert(alertMessage);
                setTimeout(function () {
                    instance.cart[index].quantity = instance.cart[index].availbleQuantity;
                });
            } else {
                this.cart[index].quantity = value;
                this.isDisableCartPlusBtn = false;
            }
            if (this.cart[index].quantity > this.cart[index].availbleQuantity && this.order_type === 'sales') {
                let variantTitle = this.cart[index].variantTitle === 'default_variant' ? '' : `(${this.cart[index].variantTitle})`;
                let alertMessage = this.cart[index].productTitle + ' ' + variantTitle + '' + this.trans('lang.is_out_of_stock') + this.trans('lang.available_quantity') + this.cart[index].availbleQuantity;
                this.showWarningAlert(alertMessage);
            }
            this.setCartItemsToCookieOrDB(1);
        },
        setProductNewPrice(price, index, value) {
            this.cart[index].price = price;
            this.cart[index].unformPrice = value;
            this.setCartItemsToCookieOrDB(1);
        },
        enableDisablePay() {

            let cartData = this.cart.filter(function (element) {
                return element.orderType !== 'discount';
            });
            return parseInt(cartData.length) === 0;
        },
        disabledPay() {
            let instance = this;
            let data = '',
                currentStorage = null;
            instance.totalStorage = 4500;

            for (let key in window.localStorage) {
                if (window.localStorage.hasOwnProperty(key)) {
                    data += window.localStorage[key];
                }
            }
            currentStorage = ((data.length * 16) / (8 * 1024)).toFixed(2);

            if (this.checkBrowser() === "Chrome") {
                window.webkitStorageInfo.queryUsageAndQuota(webkitStorageInfo.PERSISTENT, function (usage, total) {
                    if (total != 0) instance.totalStorage = total;
                    instance.remainingStorage = instance.totalStorage - currentStorage;
                });
                return instance.remainingStorage < instance.minimumSizeOfLocalStorage;
            } else {
                instance.remainingStorage = instance.totalStorage - currentStorage;
                return instance.remainingStorage < instance.minimumSizeOfLocalStorage;
            }
        },

        orderHold() {
            this.cartSave('hold');
        },
        cartSave(status = 'done') {
            this.makeFinalCart(status);
            if (status === 'done') {
                this.isPaymentModalActive = true;
                this.$emit('activeCartPaymentModal', this.finalCart);
            }
            if (status === 'hold') {
                this.$emit('orderHoldFromCart');
            }
        },
        makeFinalCart(status) {
            let selectCustomerForCart = [];

            if (this.selectedCustomer[0]) {
                selectCustomerForCart = this.selectedCustomer[0];
            }
            this.finalCart = {
                orderID: this.orderID,
                orderIdInternalTransfer: this.orderIdInternalTransfer,
                orderType: this.order_type,
                salesOrReceivingType: this.salesOrReceivingType,
                createdBy: this.user.id,
                status: status,
                cart: this.cart,
                customer: selectCustomerForCart,
                subTotal: this.subTotal,
                discount: this.discount,
                overAllDiscount: this.overAllDiscount,
                tax: this.tax,
                profit: this.profit,
                grandTotal: this.grandTotal,
                cartNote: '',
                branchId: this.selectedBranchID,
                tableId: this.restaurantTableId,
                transferBranch: this.selectedSearchBranch.id,
                transferBranchName: this.selectedSearchBranch.name,
                date: moment().format('YYYY-MM-DD h:mm A'),
                time: moment().format('YYYY-MM-DD h:mm A'),
            };
        },
        setCartItemsToCookieOrDB(flag = 0) {
            this.$emit('setCartItemsToCookieOrDBFromCart', flag);
        },
        up() {
            if (this.open) {
                if (this.highlightIndex > 0) {
                    this.highlightIndex--
                }
            } else {
                this.setOpen(true)
            }
        },
        down() {
            if (this.open) {
                if (this.highlightIndex < this.customers.length - 1 || this.highlightIndex < this.branches.length - 1) {
                    this.highlightIndex++
                }
            } else {
                this.setOpen(true)
            }
        },
        commonMethodForAccessingShortcut(data) {

            if (data === 'addCustomerShortcut' && parseInt(this.shortcutKeyInfo.addCustomerShortcut.status) === 1 && parseInt(this.shortcutStatus) === 1) {
                $('#customer-add-edit-modal').modal('show');
                this.newCustomerAddModalOpen();
            }
            if (data === 'payShortcut' && this.shortcutKeyInfo.payShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                if (this.salesOrReceivingType === 'internal') {
                    if (!this.isEmptyObj(this.selectedSearchBranch)) {
                        $('#cart-payment-modal').modal('show');
                        this.cartSave();
                    }
                } else if (this.salesOrReceivingType === 'internal-transfer') {
                    if (!this.isEmptyObj(this.selectedSearchBranch)) {
                        $('#cart-payment-modal').modal('show');
                        this.cartSave();
                    }
                } else {
                    $('#cart-payment-modal').modal('show');
                    this.cartSave();
                }
            }
            if (data === 'holdCard' && this.shortcutKeyInfo.holdCardShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                if (this.salesOrReceivingType === 'internal') {
                    if (!this.isEmptyObj(this.selectedSearchBranch)) {
                        this.orderHold();
                    }
                } else if (this.salesOrReceivingType === 'internal-transfer') {
                    if (!this.isEmptyObj(this.selectedSearchBranch)) {
                        this.orderHold();
                    }
                } else {
                    this.orderHold();
                }
            }
            if (data == 'cancelCarditem' && this.shortcutKeyInfo.cancelCardShortcut.status == 1 && this.shortcutStatus == 1 && this.cart.length != 0) {
                $('#clear-cart-modal').modal('show');
            }
        },

        //restaurant module
        openTableModal() {
            this.makeFinalCart('hold');
            this.$emit('openTableModalFromCart', this.finalCart);
        },
        setRestaurantOrderType(type) {
            this.$emit('setRestaurantOrderTypeFromCart', type);
        },
        openHoldOrderModal() {
            this.$emit('openHoldOrderModalFromCart');
        },
        setTaxIncludedOrExcluded(value) {
            this.isTaxExcluded = value;
            this.$emit('setTaxIncludedOrExcludedFromCart', this.isTaxExcluded);
        },
    }
}
</script>