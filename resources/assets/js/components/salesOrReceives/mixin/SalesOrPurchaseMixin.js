import {delayCall} from "../../../helper/delayCall";
import {cartItemsToCookie, deleteCartItemsFromCookieOrDB, subTotalAmount} from "../helper/salesComponentCommonMethod";
import {concatProductArray, productConverter} from "../helper/productConverter";
import {productRequestGenerator} from "../helper/helpers";

export default {
    data: () => ({
        parseInt,
        bookedTables: [],
        allRestaurantTables: [],

        //shortcuts
        productSearch: [],
        addCustomerShortKey: [],
        paymentShortKey: [],
        holdCardItem: [],
        cancelCardItem: [],
        donePaymentItem: [],

        offlineInvoiceNumber: '',
        internalSalesBranch: '',
        internalSalesBranchId: '',
        discountOnEntire: 'discountOnEntire',
        discountOnAllItem: 'discountOnAllItem',
        productTotalWithoutDiscount: 0,
        currentBranch: null,
        currentCashRegister: null,
        //products variables
        products: [],
        selectedProductWithVariants: null,
        productSearchValue: null,
        barcodeSearch: false,
        hideProductSearchPreLoader: false,
        //customers variables
        customers: [],
        customerNotAdded: true,
        selectedCustomer: [],
        newAddedCustomer: [],
        customerSearchValue: '',
        isCustomerModalActive: false,
        isTaxModalActive: false,
        isNewCustomerAdded: false,
        hideCustomerSearchPreLoader: false,
        //cart variables
        cart: [],
        newCart: [],
        isPaymentModalActive: false,
        //final cart variables
        finalCart: [],
        total: 0,
        subTotal: 0,
        grandTotal: 0,
        discount: null,
        profit: 0,
        overAllDiscount: null,
        tax: 0,
        orderID: null,
        orderIdInternalTransfer: null,
        salesOrReceivingType: null,
        salesOrReturnType: null,
        //order hold variables
        orderHoldItems: [],
        hideOrderHoldItemsPreLoader: '',
        //branch variables
        branchList: [],
        selectedBranchID: '',
        selectedBranchName: '',
        selectedBranchType: '',
        isCashRegisterUsed: '',
        isShipmentEnable: '',
        tempBranchID: null,
        hideBranchPreLoader: '',
        hideBranchSearchPreLoader: false,
        branchSearchValue: '',
        branches: [],
        selectedSearchBranch: [],
        offlineAllBranch: [],
        //cash register variables
        cashRegisterList: [],
        selectedCashRegisterID: '',
        selectedCashRegisterName: '',
        selectedCashRegisterBranchID: '',
        hideCashRegisterPreLoader: '',
        status: '',
        isBranchModalActive: false,
        isCashRegisterModalActive: false,
        invoiceTemplate: '',
        lastInvoiceNumber: '',
        invoiceSuffix: '',
        invoicePrefix: '',
        invoice_logo: '',
        activeProductId: '',
        activeVariantId: '',
        showDiscount: false,
        showOverAllDisc: false,
        hideCloseBtn: true,
        noteValidation: false,
        disableCloseButton: false,
        closingAmount: '',
        openingAmount: '',
        note: '',
        newCustomerId: '',
        paidAmount: '',
        bankOrCardAmount: '',
        bankOrCardOptions: {},
        orderSearchValue: '',
        orders: [],
        expectedClosingAmount: '',
        hideOrderSearchPreLoader: false,
        hideSalesReturnsPreLoader: true,
        isActiveTrans: false,
        calculateBank: false,
        newOverAllDiscount: null,
        newdiscount: null,
        isActive: false,
        // Active keyboard event
        open: null,
        highlightIndex: 0,
        intermediateSalesType: '',
        toggleCart: false,
        isSelectedBranch: true,
        paymentTypes: null,
        invoiceTemplateID: '',
        productDetails: [],
        productOfflineData: [],
        newCustomerInfo: null,
        offlineCustomers: [],
        hideOnlineMessage: false,
        totalStorage: 4500,
        remainingStorage: null,
        minimumSizeOfLocalStorage: 500,
        holdOrders: [],
        customerHoldOrders: [],
        internalHoldOrders: [],
        internalTransferHoldOrders: [],
        countHoldOrder: 0,
        invoiceId: null,
        isCartComponentActive: false,
        isCartComponentActiveForMobile: false,
        isCashRegisterBranch: '',
        salesDefaultTemplate: '',
        receiveDefaultTemplate: '',
        isSalesListComponentActive: false,
        isEmptyObj: (object) => {
            return _.isEmpty(object);
        },
        // Restaurant Variables
        isTableModalActive: false,
        searchHoldOrder: '',
        restaurantTableId: '',
        restaurantOrderType: 'dineIn',
        takeAway: false,
        dineIn: false,
        isHoldOrderDone: false,
        isPlaceOrderActive: true,
        justPayRestaurantTableId: '',
        isTaxExcludedFromCart: true,
        orderFromHold: null,
        // load more btn
        buttonLoader: false,
        isLoadMoreDisabled: false,
        showLoadMore: false,
        loadMoreBtnOffset: 0,
        productRowLimit: 28,
        totalProductCount: 0,
        registerInfoModal: false,
        registerInfoModalID: '#register-info-modal',
        receiveOrReturnType: null,
        totalProducts: 0,
        shippingAreaData: [],
        isShipmentListComponentActive: false,
        addShipping: false,
        returnCartLength: 0,
        categories: [],
        categorySearchValue: [],
        categoryPreloader: true,
        isTemplateDefault: '',
        invoice_size: '',
        adjustedDiscount: 0,
        originalSoldProductForReturn: {},
    }),
    computed: {
        filteredHoldOrder() {
            if (this.salesOrReceivingType === 'customer' && this.currentBranch != null && this.currentBranch.branch_type === 'restaurant') {
                // Returns result in restaurant customer
                return this.customerHoldOrders.filter(customerHoldOrder => {
                    if (customerHoldOrder.tableId === null) return customerHoldOrder.invoice_id.toLowerCase().includes(this.searchHoldOrder.toLowerCase());
                    else return (customerHoldOrder.invoice_id.toLowerCase().includes(this.searchHoldOrder.toLowerCase()) || customerHoldOrder.tableName.toLowerCase().includes(this.searchHoldOrder.toLowerCase()));
                });
            } else if (this.salesOrReceivingType === 'internal') {
                // Returns result both retail and restaurant internal
                return this.filteredHoldOrderSearch(this.internalHoldOrders);
            } else if (this.salesOrReceivingType === 'internal-transfer') {
                // Returns result both retail and restaurant internal
                return this.filteredHoldOrderSearch(this.internalTransferHoldOrders);
            } else if (this.salesOrReceivingType === 'customer' && this.currentBranch != null && this.currentBranch.branch_type !== 'restaurant') {
                // Returns result in retail customer
                return this.filteredHoldOrderSearch(this.customerHoldOrders);
            }
        },
    },
    watch: {
        discount: function (newVal, oldVal) {
            this.showDiscount = newVal != null && newVal != '';
        },

        remainingStorage: function (newVal, oldVal) {
        },
        closingAmount: function (value) {
            if (value || parseInt(value) === 0) this.disableCloseButton = true;
        },
        overAllDiscount: function (value) {
            if (value != null && value !== '') {
                this.showOverAllDisc = true;
                this.showDiscount = true;
            } else {
                this.showOverAllDisc = false;
                this.showDiscount = false;
            }
        },
        isConnected: function (value) {

            if (!value && parseInt(this.offline) === 1) {
                if (this.salesOrReceivingType === 'internal') {
                    this.internalHoldOrders = this.getDataByStatus(this.orderHoldItems, 'hold');
                    this.countHoldOrder = this.internalHoldOrders.length;
                } else if (this.salesOrReceivingType === 'internal-transfer') {
                    this.internalTransferHoldOrders = this.getDataByStatus(this.orderHoldItems, 'hold');
                    this.countHoldOrder = this.internalTransferHoldOrders.length;
                } else {
                    this.customerHoldOrders = this.getDataByStatus(this.orderHoldItems, 'hold');
                    this.countHoldOrder = this.customerHoldOrders.length;
                }
            }
            if (parseInt(this.offline) === 1 && this.order_type === 'sales') {
                if (value) {
                    this.onlineOfflineStatus(value);
                    this.showSuccessAlert(this.trans('lang.online_alert'));
                } else this.showOfflineAlert(this.trans('lang.offline_alert'));
            }
        },
        totalProducts: {
            handler: function (totalProducts) {
                if (totalProducts > 0) {
                    this.getProductDataForOffline();
                }
            }
        },
        salesOrReturnType: {
            handler: function (type) {
                if (type === 'returns') {
                    this.destroyCart(true);
                }
            }
        }
    },
    created() {
        this.setCartItemsToCookieOrDB();
        this.isTaxExcludedFromCart = this.user.tax_excluded !== "included";

        let tempBookedTables = [];
        this.booked_tables ? tempBookedTables = JSON.parse(this.booked_tables) : [];
        for (let item of tempBookedTables) {
            item = parseInt(item);
            this.bookedTables.push(item);
        }

        if (screen.width > 667) {
            this.toggleCart = false;
            this.isCartComponentActive = !(this.sales_return_status === 'Sales List' || this.sales_return_status === 'shipment_list');
        } else {
            this.isCartComponentActive = false;
            this.toggleCart = true;
        }

        this.isSalesListComponentActive = this.sales_return_status === "Sales List";

        this.isShipmentListComponentActive = this.sales_return_status === "shipment_list";

        this.getRestaurantTables = this.get_restaurant_table;
        this.salesDefaultTemplate = this.default_sales_invoice_template;
        this.receiveDefaultTemplate = this.default_receive_invoice_template;
        this.salesOrReceivingType = this.sales_receiving_type;
        this.lastInvoiceNumber = this.last_invoice;
        this.invoiceSuffix = this.invoice_suffix;
        this.invoicePrefix = this.invoice_prefix;
        this.isActive = "true";
        this.holdOrders = this.hold_orders;
        this.getShortCutValues();
        if (this.current_branch) {
            this.currentBranch = JSON.parse(this.current_branch);
        }
        if (!this.currentBranch) {
            this.getBranchData();
        } else {
            if (parseInt(this.currentBranch.is_cash_register) === 1) {
                this.isCashRegisterBranch = true;
                if (this.current_cash_register) {
                    this.currentCashRegister = JSON.parse(this.current_cash_register);

                    if (this.order_type === 'sales') {
                        this.invoiceTemplateID = this.currentCashRegister.sales_invoice_id;
                    } else {
                        this.invoiceTemplateID = this.currentCashRegister.receiving_invoice_id;
                    }
                } else this.currentCashRegister = this.current_cash_register;

                if (!this.currentCashRegister) {
                    this.getCashRegisterData();
                } else {
                    this.getExpectedAmount(this.currentCashRegister.id);
                    this.selectedCashRegisterID = this.currentCashRegister.id;
                    this.selectedCashRegisterName = this.currentCashRegister.title;
                    this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;

                    if (this.order_type === 'sales') {
                        this.invoiceTemplateID = this.currentCashRegister.sales_invoice_id;
                    } else {
                        this.invoiceTemplateID = this.currentCashRegister.receiving_invoice_id;
                    }
                    this.getInvoiceTemplate();
                }
            } else {
                if (this.order_type === 'sales') {
                    this.invoiceTemplate = this.salesDefaultTemplate;
                    this.isCashRegisterBranch = false;
                } else {
                    this.invoiceTemplate = this.receiveDefaultTemplate;
                    this.isCashRegisterBranch = false;
                }
            }

            this.selectedBranchID = this.currentBranch.id;
            this.isShipmentEnable = this.currentBranch.is_shipment;

            this.getRestaurantTablesBranchWise(this.selectedBranchID);

            this.selectedBranchName = this.currentBranch.name;
            this.isCashRegisterUsed = this.currentBranch.is_cash_register;

            if (parseInt(this.currentBranch.is_cash_register) === 1) {
                if (this.current_cash_register) {
                    this.currentCashRegister = JSON.parse(this.current_cash_register);
                }
                if (!this.currentCashRegister) {
                    this.getCashRegisterData();
                } else {
                    this.getExpectedAmount(this.currentCashRegister.id);
                    this.selectedCashRegisterID = this.currentCashRegister.id;
                    this.selectedCashRegisterName = this.currentCashRegister.title;
                    this.selectedCashRegisterBranchID = this.currentCashRegister.branchID;
                }
            }
        }
        if (this.order_type === 'sales') {
            this.getHoldOrders();
        }
        this.getInvoiceData('/invoice-logo');
        this.getCategories();
        window.addEventListener('online', () => {
            this.storeLocalStorage();
            this.controlShowLoadMore();
            this.getProductData();
            this.getProductDataForOffline();
        });
    },
    mounted() {

        this.axiosGet(
            "/get-areal-list",
            response => {
                this.shippingAreaData = response.data.shippingData;
            },
        )

        let instance = this;
        instance.offlineAllBranch = JSON.parse(this.all_branch);

        if (!(this.order_type !== 'sales' && !(instance.customer != '' && instance.customer != null))) this.offlineCustomers = JSON.parse(this.customer);

        if (instance.currentBranch != null) {
            instance.hideOrderSearchPreLoader = true;
            instance.getProductData();
            instance.getProductDataForOffline();
        }

        $(document).ready(function () {
            $("#pop_mouse1").click(function () {
                $("input").focus();
            });
            $("#pop_mouse2").click(function () {
                $("input").focus();
            });
        });

        if (this.order_type === 'sales') {
            this.salesOrReturnType = this.sales_return_status;
        }
        if (this.order_type === 'receiving') {
            this.receiveOrReturnType = this.purchase_return_status;
        }

        let stopPropagationElements = document.querySelectorAll("#d-1, #d-2");
        for (let stopPropagationElement of stopPropagationElements) {
            stopPropagationElement.addEventListener("click", function () {
                event.stopPropagation();
            });
        }

        if (!this.currentBranch) {
            this.isBranchModalActive = true;
            this.openBranchOrCashRegisterModal();
        } else {

            if (parseInt(JSON.parse(this.current_branch).is_cash_register) === 1) {
                if (!this.currentCashRegister) {
                    this.isCashRegisterModalActive = true;
                    this.openBranchOrCashRegisterModal();
                } else {
                    if (parseInt(this.currentCashRegister.branchID) !== parseInt(this.currentBranch.id)) {
                        this.isCashRegisterModalActive = true;
                        this.getCashRegisterData();
                        this.openBranchOrCashRegisterModal();
                    }
                }
            }
        }

        $('#branch-or-cash-register-select-modal').on('hidden.bs.modal', function () {
            instance.isBranchModalActive = false;
            instance.isCashRegisterModalActive = false;
        });

        $('#show-product-variant-modal').on('hidden.bs.modal', function () {
            instance.selectedProductWithVariants = null;
        });

        $('#cart-modal-for-mobile-view').on('hidden.bs.modal', function () {
            instance.isCartComponentActiveForMobile = false;
        });

        $('#cart-payment-modal').on('hidden.bs.modal', function () {
            instance.isPaymentModalActive = false;
        });

        $('#table-select-modal').on('hidden.bs.modal', function () {
            instance.isTableModalActive = false;
            instance.setRestaurantOrderTypeDineIn();
        });

        $('#hold-orders-modal').on('hidden.bs.modal', function () {
            instance.searchHoldOrder = '';
        });

        $('#register-info-modal').on('hidden.bs.modal', function () {
            instance.registerInfoModal = false;
        });

        this.$hub.$on('setOrderID', function (orderID, lastInvoiceId, orderIdInternalTransfer) {
            instance.lastInvoiceNumber = lastInvoiceId;
            instance.getExpectedAmount(instance.selectedCashRegisterID);
            instance.orderID = orderID;
            instance.orderIdInternalTransfer = orderIdInternalTransfer;
            instance.setCartItemsToCookieOrDB(1);
        });

        if (this.salesOrReturnType === "shipment_list" || this.salesOrReturnType === "Sales List") {
        } else {
            $(window).resize(function () {
                instance.productHeightSet();
                instance.productVariantHeightSet();

                if (window.innerWidth > 667) {
                    instance.toggleCart = false;
                    instance.isCartComponentActive = true;
                    instance.isCartComponentActiveForMobile = false;
                    instance.toggleCart = false;
                    $("#cart-modal-for-mobile-view").modal('hide');
                } else {
                    instance.toggleCart = true;
                    instance.isCartComponentActive = false;
                    instance.isCartComponentActiveForMobile = true;
                    instance.toggleCart = true;
                }

            });
        }

        $('#bank-transfer-modal').on('hidden.bs.modal', function () {
            instance.isActiveTrans = false;
        });

        $('#customer-add-edit-modal').on('hidden.bs.modal', function () {
            instance.isCustomerModalActive = false;
        });

        $('#tax-edit-modal').on('hidden.bs.modal', function () {
            instance.isTaxModalActive = false;
        });
        instance.focusOnSearchBar();
    },
    methods: {
        getCategories() {
            let instance = this;
            instance.axiosGet('/products/category',
                function (response) {
                    instance.categories = response.data;
                    instance.categoryPreloader = false;
                },
                function (error) {

                }
            );
        },
        categorySearch() {
            let instance = this;
            let sortedProducts = instance.productOfflineData;

            if (instance.categorySearchValue.length > 0) {
                const type = typeof this.categorySearchValue[0];
                instance.products = sortedProducts.filter(function (element) {
                    return instance.categorySearchValue.includes(type === "string" ? String(element.category_id) : Number(element.category_id));
                });
            } else {
                instance.products = instance.productOfflineData;
            }
            $(".product-category-filter .dropdown-menu").removeClass('show');
        },
        clearCategorySearch() {
            this.products = this.productOfflineData;
            this.categorySearchValue = [];
            $(".product-category-filter .dropdown-menu").removeClass('show');
        },
        openRegisterInfoModal() {
            this.registerInfoModal = true;
            $("#register-info-modal").modal("show");
        },
        resetBranchAndCashRegisterModal() {
            this.isBranchModalActive = false;
            this.isCashRegisterModalActive = false;
        },
        focusOnSearchBar() {
            let instance = this;
            if (parseInt(instance.manage_sales) === 1 || parseInt(instance.manage_receives) === 1) {
                setTimeout(() => {
                    if (instance.salesOrReturnType === 'returns' || this.receiveOrReturnType === 'returns') {
                        instance.$refs.searchOrder.focus();
                    } else {
                        this.isCartComponentActive = true;
                        instance.$refs.search.focus();
                    }
                }, 1000);
            }
        },
        focusOnSearchBarWithOutTimeOut() {
            let instance = this;
            if (parseInt(this.manage_sales) === 1 || parseInt(this.manage_receives) === 1) {
                if (this.salesOrReturnType === 'returns' || this.receiveOrReturnType === 'returns') {
                    this.$refs.searchOrder.focus();
                } else if (instance.salesOrReturnType === 'Sales List' || instance.salesOrReturnType === 'shipment_list') {
                } else this.$refs.search.focus();

            }
        },
        filteredHoldOrderSearch(data) {
            let instance = this;
            return data.filter(item => {
                return item.invoice_id.toLowerCase().includes(instance.searchHoldOrder.toLowerCase());
            });
        },
        getDataByStatus(data, status) {
            let instance = this;
            return data.filter(function (element) {
                return (element.salesOrReceivingType == instance.salesOrReceivingType && element.status == status && element.branchId == instance.selectedBranchID);
            });
        },
        deleteHoldOrder(data) {
            if (data) {
                this.orderID = data.orderID;
                this.invoiceId = data.invoice_id;
                this.orderFromHold = data;
            }
        },
        allProductDiscount(value, index, unformatted) {
            let instance = this;
            instance.discount = value;
            instance.newdiscount = unformatted;
            this.cart.forEach(function (element) {
                if (element.quantity > 0) {
                    element.discount = instance.discount;
                }
            });
            instance.setCartItemsToCookieOrDB(1);
        },
        addOverAllDiscount(value, index, unformatted) {

            this.overAllDiscount = value;
            this.newOverAllDiscount = unformatted;
            let flag = true;
            let instance = this;

            if (this.overAllDiscount) {
                instance.cart.forEach(function (element) {
                    if (element.orderType === 'discount') {
                        element.price = instance.overAllDiscount;
                        element.calculatedPrice = -(instance.overAllDiscount);
                        flag = false;
                    }
                });
                if (flag) {
                    instance.cart.push({
                        productID: null,
                        variantID: null,
                        taxID: null,
                        orderType: 'discount',
                        productTitle: instance.trans('lang.discount'),
                        price: this.overAllDiscount,
                        quantity: -1,
                        discount: null,
                        calculatedPrice: -(instance.overAllDiscount),
                        cartItemNote: '',
                        showItemCollapse: false,
                    });
                }
            } else {
                instance.cart = instance.cart.filter(element => element.orderType !== 'discount');
            }
            instance.setCartItemsToCookieOrDB(1);
        },
        productHeightSet() {
            $("div.product-card-content").removeAttr("style");
            this.$nextTick(function () {
                let maxHeight = 0;
                $("div.product-card-content").each(function () {
                    if ($(this).height() > maxHeight) {
                        maxHeight = $(this).height();
                    }
                });
                $("div.product-card-content").css('height', maxHeight + 'px');
            })
        },
        productVariantHeightSet() {
            $("div.product-variant-card-content .d-flex").removeAttr("style");
            let maxHeight2 = 0;

            $("div.product-variant-card-content .d-flex").each(function (e) {

                if ($(this).height() > maxHeight2) {
                    maxHeight2 = $(this).height();
                }
            });

            $("div.product-variant-card-content .d-flex").css('height', maxHeight2 + 'px');
        },
        openCartModalForMobile() {
            this.isCartComponentActive = false;
            this.isCartComponentActiveForMobile = true;
        },
        onlineOfflineStatus(value) {
            let instance = this;
            if (value) {
                this.hideOnlineMessage = true;
                setTimeout(function () {
                    instance.hideOnlineMessage = false;
                }, 3000);
            }
        },
        increaseLocalStorageInChrome() {
            window.webkitStorageInfo.requestQuota(
                window.PERSISTENT,
                513010,
                function (bytes) {
                },
                function (e) {
                });
            this.hideIncreaseLocalStorageModal();

        },
        hideIncreaseLocalStorageModal() {
            $('#increase-local-storage-modal').modal('hide');
        },
        suggestionSelected(suggestion) {
            this.open = false;
            this.customerSearchValue = suggestion[0]
            this.branchSearchValue = suggestion[0]
            this.$emit('input', suggestion[1])
        },
        getExpectedAmount(id) {
            let instance = this;
            if (navigator.onLine && id != null && id !== '') {
                this.axiosGet('/get-register-amount/' + id,
                    function (response) {
                        instance.expectedClosingAmount = response.data;
                    },
                );
            }
        },
        defaultPayment(amount, options) {
            this.calculateBank = true;
            this.bankOrCardAmount = amount;
            this.bankOrCardOptions = options;
        },
        getShortCutValues() {
            let instance = this;
            instance.axiosGet('/shortcut-setting-data/{id}',
                function (response) {
                    let shortcutCollection = response.data.shortcutSettings,
                        keys = ['productSearch', 'addCustomer', 'pay', 'holdCard', 'cancelCardItem', 'donePaymentItem'];

                    keys.map(key => {
                        if (shortcutCollection[key].shortcut_key.includes("+")) {
                            instance[key] = shortcutCollection[key].shortcut_key.split("+");
                        } else {
                            instance[key] = [shortcutCollection[key].shortcut_key];
                        }
                    });
                },
            );
        },
        commonMethodForAccessingShortcut(data) {
            if (data == "productSearch" && parseInt(this.shortcutKeyInfo.productSearchShortcut.status) === 1 && parseInt(this.shortcutStatus) === 1) {
                this.$refs.search.focus();
            }
        },
        addCustomer(event) {  // remove for cart
            $('#customer-add-edit-modal').modal('show');
            this.isCustomerModalActive = true;
        },
        holdCard() {
            this.orderHold();
        },
        cancelCard() {
            $('#clear-cart-modal').modal('show');
        },
        pay() {
            $('#cart-payment-modal').modal('show');
            this.isPaymentModalActive = true;
        },
        getInvoiceData(route) {
            let instance = this;
            this.setPreLoader(false);
            this.axiosGet(route,
                function (response) {
                    instance.invoice_logo = response.data.logo.setting_value;
                    instance.setPreLoader(true);
                },
                function (response) {
                    instance.setPreLoader(true);
                },
            );
        },
        capitalizeFirstLetter(value) {
            return _.startCase(_.toLower(value));
        },
        selectSalesOrReceivingType(value) {
            let instance = this;
            this.isActive = "true";
            instance.salesOrReceivingType = value;
            instance.isSelectedBranch = true;
            instance.selectedCustomer = [];
            instance.selectedSearchBranch = [];

            if (instance.salesOrReceivingType === 'internal') {
                instance.customerNotAdded = true;
            } else if (instance.salesOrReceivingType === 'internal-transfer') {
                instance.customerNotAdded = true;
            }

            $('#sales-or-receiving-type-select-modal').modal('hide');
            if (navigator.onLine) {
                this.axiosPost('/sales-receiving-type-set', {
                    salesOrReceivingType: value,
                    orderType: instance.order_type,
                }, function () {
                    instance.focusOnSearchBarWithOutTimeOut();
                });
            }

            if (this.order_type === 'sales') {
                this.getHoldOrders();
            }

            this.adjustPrice();

            instance.focusOnSearchBarWithOutTimeOut();
        },
        adjustPrice() {
            let instance = this;
            if (instance.order_type === 'sales') {
                instance.products.forEach(function (product) {
                    product.variants.forEach(function (variant) {
                        if (instance.salesOrReceivingType === 'customer') variant.price = variant.selling_price;
                        else variant.price = variant.purchase_price;
                    });
                })
            }
        },
        selectSalesOrReturnType(value) {
            let instance = this;

            instance.isCartComponentActive = !(value === 'Sales List' || value === 'shipment_list');

            instance.hideSalesReturnsPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: '/sales-returns-type-set',
                    postData: {salesOrReturnType: value},
                },
                (success) => {

                    if (success) {
                        instance.hideSalesReturnsPreLoader = true;
                        instance.salesOrReturnType = value;

                        this.isSalesListComponentActive = instance.salesOrReturnType === "Sales List";

                        this.isShipmentListComponentActive = instance.salesOrReturnType === "shipment_list";

                        $('#sales-or-return-type-select-modal').modal('hide');
                        instance.focusOnSearchBar();
                    } else {
                        instance.hideSalesReturnsPreLoader = true;
                        $('#sales-or-return-type-select-modal').modal('hide')
                    }
                }
            );
        },
        selectReceiveOrReturnType(value) {
            let instance = this;

            instance.hideSalesReturnsPreLoader = false;
            instance.axiosGETorPOST(
                {
                    url: '/receive-type-set',
                    postData: {purchaseOrReturnType: value},
                },
                (success) => {

                    if (success) {
                        instance.hideSalesReturnsPreLoader = true;
                        instance.receiveOrReturnType = value;
                        $('#sales-or-return-type-select-modal').modal('hide');
                    } else {
                        instance.hideSalesReturnsPreLoader = true;
                        $('#sales-or-return-type-select-modal').modal('hide')
                    }
                }
            );
        },
        openBranchOrCashRegisterModal() {
            $('#branch-or-cash-register-select-modal').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        },
        searchProductInput(event) {
            let instance = this;
            if (navigator.onLine) {
                if (instance.productSearchValue) {
                    if (parseInt(event.keyCode) === 13) {
                        instance.barcodeSearch = true;
                        instance.getProductDataOfflineSearch();
                        instance.productSearchValue = null;
                    } else {
                        delayCall(function () {
                            if (instance.productSearchValue) {
                                instance.getProductDataOfflineSearch();
                            }
                        });
                    }
                } else {
                    this.loadMoreBtnOffset = 0;
                    instance.getProductData();
                    instance.getProductDataForOffline();
                }
            } else {
                if (instance.productSearchValue) {

                    if (parseInt(event.keyCode) === 13) {
                        instance.barcodeSearch = true;
                        instance.getProductDataOfflineSearch();
                        instance.productSearchValue = null;
                    } else {
                        delayCall(function () {
                            if (instance.productSearchValue) {
                                instance.getProductDataOfflineSearch();
                            }
                        });
                    }
                } else {
                    instance.getProductDataOfflineSearch();
                }
            }
        },
        cancelOrder() {
            let instance = this;
            if (navigator.onLine) {
                if (this.orderID) this.onlineCancelOrder();
            } else {
                if (this.orderID || this.invoiceId) this.offlineCancelOrder();
            }
            if (instance.order_type === 'sales' && instance.filteredHoldOrder !== undefined && parseInt(instance.filteredHoldOrder.length) === 0) {
                $('#hold-orders-modal').modal('hide');
            }
        },
        onlineCancelOrder() {
            this.hideOrderHoldItemsPreLoader = false;
            this.axiosGETorPOST(
                {
                    url: '/sales-cancel',
                    postData: {orderID: this.orderID, orderIdInternalTransfer: this.orderIdInternalTransfer}
                },
                (success) => {
                    if (success) {
                        if (this.order_type === 'sales') {
                            this.getHoldOrders(true);
                            this.hideOrderHoldItemsPreLoader = true;
                            this.orderID = null;
                            this.invoiceId = null;
                            this.restaurantTableId = '';
                        }
                    }
                }
            );
        },
        offlineCancelOrder() {
            let instance = this;

            if (this.orderFromHold !== undefined && this.orderFromHold != null) {
                this.orderFromHold.status = 'cancelled';
            }
            this.getSetLocalStorage(this.orderFromHold, this.orderID, this.invoiceId);
            instance.hideOrderHoldItemsPreLoader = true;
            if (this.order_type === 'sales') {
                this.getHoldOrders(true);
            }
            this.orderID = null;
            this.invoiceId = null;
            this.restaurantTableId = '';
            this.destroyCart(true);
        },
        searchOrderInput(event) {
            let instance = this;
            instance.hideOrderSearchPreLoader = false;
            if (instance.orderSearchValue) {
                delayCall(function () {
                    instance.orders = [];
                    instance.getOrderData();
                });
            } else {
                instance.hideOrderSearchPreLoader = true;
            }
        },
        getOrderData() {
            let instance = this;
            instance.axiosGETorPOST(
                {
                    url: '/get-return-orders',
                    postData: {
                        receivingType: instance.receiveOrReturnType,
                        orderId: instance.orderSearchValue,
                    }
                },
                (success, responseData) => {
                    if (success) {
                        instance.orders = responseData;
                        instance.originalSoldProductForReturn = _.cloneDeep(responseData);
                        instance.hideOrderSearchPreLoader = true;
                    }
                });
        },
        variantProductCard(productVariantInfo) {
            if (productVariantInfo.length > 1) {
                return '#show-product-variant-modal';
            }
        },
        productCardAction(product) {
            if (parseInt(product.variants.length) === 1) {
                this.addProductToCart(product, product.variants[0].id);
            } else {
                this.selectedProductWithVariants = product;
                let instance = this;
                setTimeout(function () {
                    instance.productVariantHeightSet();
                }, 200)
            }
        },

        addProductToCart(product, productVariantID) {
            let flag = 0, instance = this;

            instance.activeProductId = product.productID;
            instance.activeVariantId = productVariantID;

            setTimeout(function () {
                instance.activeProductId = '';
                instance.activeVariantId = '';
            }, 1000);


            if (this.cart.length > 0) {
                this.cart.forEach(function (cartItem, index, cartArray) {
                    if (parseInt(cartItem.productID) === parseInt(product.productID) && parseInt(cartItem.variantID) === parseInt(productVariantID)) {
                        cartArray[index].quantity++;
                        if (cartArray[index].quantity > product.variants[0].availableQuantity && instance.order_type === 'sales') {
                            let variantTitle = product.variants[0].variant_title === 'default_variant' ? '' : `(${product.variants[0].variant_title})`;
                            let alertMessage = product.title + ' ' + variantTitle + ' ' + instance.trans('lang.is_out_of_stock');
                            instance.showWarningAlert(alertMessage);
                        }
                        flag = 1;
                    }
                });
            }
            if (flag == 0) {
                let insertCheckedVariant = _.filter(product.variants, ['id', productVariantID]);

                if (!_.isEmpty(insertCheckedVariant)) {

                    if (insertCheckedVariant[0].availableQuantity <= 0 && instance.order_type === 'sales') {
                        let variantTitle = insertCheckedVariant[0].variant_title === 'default_variant' ? '' : `(${insertCheckedVariant[0].variant_title})`;
                        let alertMessage = product.title + ' ' + variantTitle + ' ' + instance.trans('lang.is_out_of_stock');
                        this.showWarningAlert(alertMessage);
                    }
                    this.cart.push({
                        productID: product.productID,
                        productTitle: product.title,
                        taxID: product.tax_id,
                        orderType: instance.order_type,
                        productTaxPercentage: product.taxPercentage,
                        variantID: insertCheckedVariant[0].id,
                        price: insertCheckedVariant[0].price,
                        unformPrice: insertCheckedVariant[0].price,
                        purchase_price: insertCheckedVariant[0].purchase_price,
                        variantTitle: insertCheckedVariant[0].variant_title,
                        quantity: 1,
                        discount: this.discount,
                        calculatedPrice: insertCheckedVariant[0].price,
                        cartItemNote: '',
                        showItemCollapse: false,
                        availbleQuantity: insertCheckedVariant[0].availableQuantity
                    });
                }
            }
            this.setCartItemsToCookieOrDB(1);
            this.newCart = this.cart;
            $('#show-product-variant-modal').modal('hide');
        },
        setProductNewPrice(price, index, value) {
            this.cart[index].price = price;
            this.cart[index].unformPrice = value;
            this.setCartItemsToCookieOrDB(1);
        },

        setCartItemsToCookieOrDB(flag = 0) {
            let obj = {};

            obj = {
                user: this.user,
                cart: this.cart,
                selectedCustomer: this.selectedCustomer,
                selectedSearchBranch: this.selectedSearchBranch,
                orderID: this.orderID,
                discount: this.discount,
                overAllDiscount: this.overAllDiscount,
                lastInvoiceNumber: this.lastInvoiceNumber,
                salesOrReceivingType: this.salesOrReceivingType,
                isSelectedBranch: this.isSelectedBranch,
                newOverAllDiscount: this.newOverAllDiscount,
                customerNotAdded: this.customerNotAdded,
                order_type: this.order_type,
                addShipping: this.addShipping,
                newDiscount: this.newdiscount,
            };
            if (parseInt(this.cart.length) === 0){
                obj.discount = 0;
                obj.overAllDiscount = 0;
                obj.newDiscount = 0;
            }else {
                obj.discount = this.discount;
                obj.overAllDiscount = this.overAllDiscount;
                obj.newDiscount = this.newDiscount;
            }

            let cookieData = cartItemsToCookie(flag, obj, this.appVersion);

            this.setCookieDataToGlobal(cookieData);

            let subTotalAmountMethodData = subTotalAmount(
                this.overAllDiscount,
                this.salesOrReturnType,
                this.receiveOrReturnType,
                this.total,
                this.profit,
                this.tax,
                this.subTotal,
                this.cart,
                this.productTotalWithoutDiscount,
                this.isTaxExcludedFromCart,
                this.orders,
                this.originalSoldProductForReturn,
            );
            this.setSubTotalDataToGlobal(subTotalAmountMethodData);
        },
        setCookieDataToGlobal(cookieData) {
            this.customerNotAdded = cookieData.customerNotAdded;
            this.discount = cookieData.discount;
            this.isSelectedBranch = cookieData.isSelectedBranch;
            this.newdiscount = cookieData.newDiscount;
            this.newOverAllDiscount = cookieData.newOverAllDiscount;
            this.orderID = cookieData.orderID;
            this.overAllDiscount = cookieData.overAllDiscount;
            this.cart = cookieData.cart;
            this.addShipping = cookieData.addShipping;
            this.selectedCustomer = cookieData.selectedCustomer;
            this.selectedSearchBranch = cookieData.selectedSearchBranch;
        },
        setSubTotalDataToGlobal(data) {
            this.total = data.total;
            this.grandTotal = data.grandTotal;
            this.profit = data.profit;
            this.tax = data.tax;
            this.subTotal = data.subTotal;
            this.productTotalWithoutDiscount = data.cart;
            this.adjustedDiscount = data.adjustedDiscount;
        },
        focusOnCashRegister(index, status) {
            setTimeout(() => {
                if (status === 'open') {
                    $("#closing-amount-" + index).focus();
                } else if (status === 'closed') {
                    $("#opening-amount-" + index).focus();
                }
            }, 1000);
        },
        cashRegisterCollapse(index, cashRegisterID, cashRegister, status) {
            this.focusOnSearchBar();
            this.focusOnCashRegister(index, status);
            this.$validator.reset();
            this.note = '';

            if (cashRegister.opening_amount) {
                this.openingAmount = cashRegister.opening_amount;
            }

            this.noteValidation = status === 'closed';

            this.cashRegisterList.forEach(function (cashRegister, i, array) {
                if (i == index && cashRegister.id == cashRegisterID) {
                    array[i].showItemCollapse = !array[i].showItemCollapse;
                } else {
                    array[i].showItemCollapse = false;
                }
            });
        },
        getProductsBySearchBtn() {
            if (navigator.onLine) this.getProductData();
            else this.getProductDataOfflineSearch();
        },
        getProductDataOfflineSearch() {
            let instance = this;
            if (this.productSearchValue === '' || this.productSearchValue == null) {
                instance.products = instance.productOfflineData.slice(0, 20);
            } else {
                let sortedProducts = instance.productOfflineData;
                let productBarcodedSearch = this.productBarcodedSearch();
                if (!productBarcodedSearch) {
                    instance.products = sortedProducts.filter(function (element) {
                        return element.title.toLowerCase().includes(instance.productSearchValue.toLowerCase());
                    });
                }
                if (this.productSearchValue == null) {
                    instance.products = instance.productDetails.slice(0, 20);
                }
            }
            instance.hideProductSearchPreLoader = true;

            instance.$nextTick(function () {
                instance.productHeightSet();
            });

            instance.adjustPrice();
        },
        getProductData() { //getProductDataForOnline
            let instance = this;
            instance.hideProductSearchPreLoader = false;
            let data = {
                    rowLimit: instance.productRowLimit,
                    offset: instance.loadMoreBtnOffset,
                    currentBranch: this.currentBranch.id,
                    orderType: this.order_type,
                    searchValue: this.productSearchValue,
                },
                postUrl = '/sales-product';
            instance.axiosGETorPOST(
                {
                    url: postUrl,
                    postData: data,
                },
                (success, responseData) => {

                    if (success) {
                        instance.hideProductSearchPreLoader = true;
                        this.totalProducts = responseData.total_products;
                        if (responseData.barcodeResultValue) {
                            instance.barcodeSearch = true;
                            this.productSearchValue = '';
                        } else {
                            if (instance.loadMoreBtnOffset <= 0) {
                                this.products = productConverter(responseData.products, responseData.variants);
                                this.productDetails = productConverter(responseData.products, responseData.variants);
                            } else {
                                this.products = this.products.concat(
                                    productConverter(responseData.products, responseData.variants)
                                );

                                this.productDetails = this.productDetails.concat(
                                    productConverter(responseData.products, responseData.variants)
                                );
                            }
                        }

                        instance.$nextTick(function () {
                            instance.productHeightSet();
                        });
                        instance.adjustPrice();
                        instance.buttonLoader = false;
                        instance.isLoadMoreDisabled = false;
                        instance.controlShowLoadMore();
                    }

                }
            );
        },
        controlShowLoadMore() {
            let instance = this;
            instance.showLoadMore = instance.totalProductCount > instance.products.length;
        },
        getProductDataForOffline() {
            let instance = this;
            let data = {
                currentBranch: this.currentBranch.id,
                orderType: this.order_type,
                searchValue: '',
                rowLimit: null,
                offset: 0,
            };
            productRequestGenerator(this.totalProducts, data).then(responseData => {
                let products = concatProductArray(responseData);
                this.productOfflineData = products;
                this.totalProductCount = products.length;
                instance.adjustPrice();
                instance.controlShowLoadMore();
            });

        },
        productBarcodedSearch() {
            let instance = this;
            let sortedProducts = instance.productOfflineData, productVariant;

            sortedProducts.forEach(function (element) {

                let filteredVariantBarcode = null;


                filteredVariantBarcode = element.variants.find(function (variant) {
                    return variant.bar_code == instance.productSearchValue;
                });

                if (filteredVariantBarcode == undefined) {
                    filteredVariantBarcode = element.variants.find(function (variant) {
                        return variant.sku == instance.productSearchValue;
                    });
                    if (filteredVariantBarcode !== undefined) productVariant = filteredVariantBarcode;

                } else productVariant = filteredVariantBarcode;
            });

            if (productVariant !== undefined) {
                instance.barcodeSearch = true;
                let searchProduct = sortedProducts.find(function (product) {
                    return parseInt(product.productID) === parseInt(productVariant.product_id);
                });
                let cart = {
                    productID: productVariant.product_id,
                    productTitle: searchProduct.title,
                    taxID: searchProduct.tax_id,
                    productTaxPercentage: searchProduct.taxPercentage,
                    variantID: productVariant.id,
                    price: productVariant.price,
                    purchase_price: productVariant.purchase_price,
                    variantTitle: productVariant.variant_title,
                }
                instance.barcodeSearchedProductAddToCart(cart);
                instance.productSearchValue = null;
                return true;
            }
            return false;
        },
        barcodeSearchedProductAddToCart(data) {
            let flag = 0;
            if (this.barcodeSearch) {
                if (this.cart.length > 0) {
                    this.cart.forEach(function (cartItem, index, cartArray) {
                        if (parseInt(cartItem.productID) === parseInt(data.productID) && parseInt(cartItem.variantID) === parseInt(data.variantID)) {
                            cartArray[index].quantity++;
                            flag = 1;
                        }
                    });
                }
                if (flag == 0) {
                    this.cart.push({
                        productID: data.productID,
                        productTitle: data.productTitle,
                        taxID: data.taxID,
                        orderType: this.order_type,
                        productTaxPercentage: data.productTaxPercentage,
                        variantID: data.variantID,
                        price: data.price,
                        unformPrice: data.price,
                        purchase_price: data.purchase_price,
                        variantTitle: data.variantTitle,
                        quantity: 1,
                        discount: this.discount,
                        calculatedPrice: data.price,
                        cartItemNote: '',
                        showItemCollapse: false,
                    });
                }
                this.setCartItemsToCookieOrDB(1);
                this.barcodeSearch = false;
            }
        },
        getBranchData() {
            let instance = this;
            instance.axiosGETorPOST(
                {url: '/branches'},
                (success, responseData) => {
                    if (success) {
                        instance.branchList = responseData;
                        if (instance.currentBranch) {
                            instance.branchList.forEach(function (branch) {
                                if (parseInt(instance.currentBranch.id) === parseInt(branch.id)) {
                                    instance.selectedBranchID = branch.id;
                                    instance.selectedBranchName = branch.name;
                                    instance.selectedBranchType = branch.branch_type;
                                    instance.isCashRegisterUsed = branch.is_cash_register;
                                    instance.isShipmentEnable = branch.is_shipment;

                                    if (parseInt(instance.isCashRegisterUsed) === 0) instance.selectedCashRegisterID = '';

                                    if (instance.order_type === 'sales') {
                                        instance.getRestaurantTablesBranchWise(instance.selectedBranchID);
                                    }

                                    instance.focusOnSearchBar();
                                }
                            })
                        }
                    }
                    instance.hideBranchPreLoader = true;
                });
        },
        selectBranch(branchID, branchName, branchType, cashRegisterUsed, is_shipment) {
            if (parseInt(this.selectedBranchID) === parseInt(branchID)) {
                $('#branch-or-cash-register-select-modal').modal('hide');
            } else {
                this.selectedBranchID = branchID;
                this.selectedBranchName = branchName;
                this.selectedBranchType = branchType;
                this.isCashRegisterUsed = cashRegisterUsed;
                this.isShipmentEnable = is_shipment;
                this.isSalesListComponentActive = false;
                this.isShipmentListComponentActive = false;
                if (parseInt(this.isCashRegisterUsed) === 0) this.selectedCashRegisterID = '';
                if (!this.currentBranch || this.currentBranch.id !== this.selectedBranchID) {
                    this.setBranchData();
                    if (!this.currentBranch) {
                        this.currentBranch = [];
                    }
                }
            }
        },
        setBranchData() {
            let instance = this;
            this.hideBranchPreLoader = false;
            this.tempBranchID = this.selectedBranchID;
            if (navigator.onLine) {
                instance.axiosGETorPOST(
                    {
                        url: '/sales-branch-set',
                        postData: {branchID: this.selectedBranchID, orderType: this.order_type},
                    },
                    (success, responseData) => {
                        if (success) {
                            instance.hideBranchPreLoader = true;
                            instance.hideOrderSearchPreLoader = true;
                            instance.hideProductSearchPreLoader = true;
                            instance.isBranchModalActive = false;
                            instance.isCashRegisterModalActive = true;
                            instance.hideCashRegisterPreLoader = false;
                            this.currentBranch.id = this.selectedBranchID;
                            this.currentBranch.name = this.selectedBranchName;
                            this.currentBranch.branch_type = this.selectedBranchType;
                            this.currentBranch.isShipmentEnable = this.isShipmentEnable;
                            this.checkCashRegister(this.currentBranch.id);
                            this.getProductData();

                            if (this.order_type === 'sales') {
                                this.getHoldOrders();
                            }
                            this.isSalesListComponentActive = true;
                            this.isShipmentListComponentActive = true;

                            if (this.order_type === 'sales') {
                                this.getRestaurantTablesBranchWise(this.currentBranch.id);
                            }
                        }
                    }
                );
            } else {
                if (this.order_type === 'sales') {
                    this.getHoldOrders();
                }
            }
        },
        checkCashRegister(branch_id) {
            let instance = this;
            this.axiosGet('/edit-branch/' + branch_id,
                function (response) {
                    if (parseInt(response.data.is_cash_register) === 1) {
                        instance.getCashRegisterData();
                        instance.isCashRegisterBranch = true;
                    } else {
                        instance.isCashRegisterModalActive = false;
                        instance.hideCashRegisterPreLoader = true;
                        instance.isCashRegisterBranch = false;

                        if (instance.order_type === 'sales') {
                            instance.invoiceTemplate = instance.salesDefaultTemplate;
                        } else {
                            instance.invoiceTemplate = instance.receiveDefaultTemplate;
                        }

                        instance.invoiceTemplate = instance.salesDefaultTemplate;
                        $('#branch-or-cash-register-select-modal').modal('hide');
                    }
                },
            );
        },
        getInvoiceTemplate() {
            let instance = this;
            let invoiceId = this.invoiceTemplateID;
            instance.invoiceTemplate = '';
            this.axiosGet('/get-invoice-template/' + invoiceId,
                function (response) {
                    instance.invoiceTemplate = response.data.content;
                    instance.isTemplateDefault = response.data.is_default;
                    instance.invoice_size = response.data.invoice_size;
                }
            );
        },
        setHoldOrderToCart(holdItem) {
            let instance = this;
            this.orderFromHold = holdItem;
            // restaurant
            this.isPlaceOrderActive = false;
            instance.restaurantTableId = holdItem.tableId;
            if (holdItem.all_discount !== 0) instance.discount = holdItem.all_discount;
            holdItem.cart.forEach(function (product) {
                if (product.orderType === 'discount') {
                    product.productTitle = instance.trans('lang.discount');
                    instance.overAllDiscount = product.calculatedPrice;
                    instance.newOverAllDiscount = product.calculatedPrice;
                    product.quantity = -1;
                    product.calculatedPrice = -(product.calculatedPrice);
                }
            });
            if (navigator.onLine) {
                if (holdItem.transfer_branch_id == null) {
                    instance.selectedSearchBranch = [];
                    instance.isSelectedBranch = true;
                } else {
                    instance.selectedSearchBranch = {
                        name: holdItem.transfer_branch_name,
                        id: holdItem.transfer_branch_id
                    }
                    instance.isSelectedBranch = false;
                }
            } else {
                if (holdItem.transfer_branch_id != null) {
                    instance.selectedSearchBranch = {
                        name: holdItem.transfer_branch_name,
                        id: holdItem.transfer_branch_id
                    }
                    instance.isSelectedBranch = false;
                } else if (holdItem.transferBranch == null) {
                    instance.selectedSearchBranch = [];
                    instance.isSelectedBranch = true;
                } else {
                    instance.selectedSearchBranch = {
                        name: holdItem.transferBranchName,
                        id: holdItem.transferBranch
                    }
                    instance.isSelectedBranch = false;
                }
            }
            if (parseInt(instance.cart.length) === 0) {
                instance.cart = holdItem.cart;
                instance.orderID = holdItem.orderID;
                instance.invoiceId = holdItem.invoice_id;
                if (holdItem.customer != null) {
                    if (instance.salesOrReceivingType === 'customer' && !_.isEmpty(holdItem.customer)) {
                        instance.selectedCustomer = [];
                        instance.selectedCustomer.push(holdItem.customer);
                        instance.customerNotAdded = false;
                    }
                }
                instance.cart.forEach(function (element) {
                    element.unformPrice = element.price;
                });


                if (instance.salesOrReceivingType === 'internal') {
                    instance.internalHoldOrders = instance.setHoldToPending(
                        instance.internalHoldOrders,
                        holdItem.orderID,
                        holdItem.invoice_id,
                    );
                } else if (instance.salesOrReceivingType === 'internal-transfer') {
                    instance.internalTransferHoldOrders = instance.setHoldToPending(
                        instance.internalTransferHoldOrders,
                        holdItem.orderID,
                        holdItem.invoice_id,
                    );
                } else {
                    instance.customerHoldOrders = instance.setHoldToPending(
                        instance.customerHoldOrders,
                        holdItem.orderID, holdItem.invoice_id,
                    );
                }

                instance.setCartItemsToCookieOrDB(1);
                if (!this.isCartComponentActive) {
                    this.isCartComponentActiveForMobile = true;
                    $('#cart-modal-for-mobile-view').modal('show');
                }
                $('#hold-orders-modal').modal('hide');
            } else {
                $('#clear-cart-modal').modal();
            }
            if (holdItem.tableId) {
                this.justPayRestaurantTableId = holdItem.tableId;
                this.getHoldOrders();
            }
        },
        setHoldToPending(data, orderID, invoice_id) {
            let instance = this, itemStatus = true, temp;

            data.forEach(function (orderHoldItem) {
                if (orderHoldItem.orderID && parseInt(orderHoldItem.orderID) === parseInt(orderID) && orderHoldItem.status === 'hold') {
                    orderHoldItem.status = 'pending';
                    itemStatus = false;
                } else if (orderHoldItem.orderID == null && parseInt(orderHoldItem.invoice_id) === parseInt(invoice_id) && orderHoldItem.status === 'hold' && itemStatus) {
                    orderHoldItem.status = 'pending';
                }
            });

            temp = this.getDataByStatus(data, 'hold');
            instance.countHoldOrder = temp.length;
            return data;
        },
        selectOrder(order) {
            let instance = this;
            instance.orderSearchValue = '';

            instance.returnCartLength = order.cart.length;
            if (order.all_discount !== 0) instance.discount = order.all_discount;

            if (parseInt(instance.cart.length) === 0) {
                if (order.customer) {
                    instance.selectedCustomer.push(order.customer);
                    instance.customerNotAdded = false;
                }
                instance.cart = _.filter(order.cart, function (product) {

                    if (product.orderType === "discount") {
                        product.productTitle = instance.trans('lang.discount');

                        //cart type discount price
                        product.calculatedPrice = product.price;

                        instance.overAllDiscount = product.price;

                        //Discount on entire sale
                        instance.newOverAllDiscount = product.price;
                    }
                    return product.quantity !== 0;
                });

                instance.setCartItemsToCookieOrDB(1);

            }
        },
        branchModalAction(flag = 0) {
            this.isBranchModalActive = true;

            if (flag == 0) {
                this.openBranchOrCashRegisterModal();
            }

            if (parseInt(this.branchList.length) === 0) {
                this.getBranchData();
            }
        },
        cashRegisterModalAction() {
            this.isCashRegisterModalActive = true;
            this.openBranchOrCashRegisterModal();
            if (parseInt(this.cashRegisterList.length) === 0) {
                this.getCashRegisterData();
            }
        },
        getCashRegisterData() {
            let instance = this,
                tempData;
            instance.axiosGETorPOST(
                {url: '/cash-registers'},
                (success, responseData) => {
                    if (success) {
                        tempData = responseData;
                        _.mapValues(tempData, function (cashRegister) {
                            cashRegister.openingAmount = null;
                            cashRegister.openingTime = null;
                            cashRegister.closingAmount = null;
                            cashRegister.closingTime = null;
                            cashRegister.note = null;
                            cashRegister.showItemCollapse = false;
                        });
                        instance.cashRegisterList = tempData;
                    }
                    if (instance.tempBranchID) {
                        let autoSelectCashRegister = _.filter(this.cashRegisterList, function (cashRegister) {
                            if (cashRegister.status === 'open' && _.includes(cashRegister.userID, instance.user.id.toString())) {

                                return cashRegister;
                            }
                        });
                        if (!_.isEmpty(autoSelectCashRegister)) {
                            instance.selectedCashRegisterID = autoSelectCashRegister[0].id;
                            instance.selectedCashRegisterName = autoSelectCashRegister[0].title;
                            instance.selectedCashRegisterBranchID = autoSelectCashRegister[0].branchID;
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                        instance.tempBranchID = null;
                    }
                    instance.getExpectedAmount(instance.selectedCashRegisterID);
                    instance.hideCashRegisterPreLoader = true;
                    instance.focusOnSearchBarWithOutTimeOut();
                })
            ;
        },
        checkCashRegisterOpenByUser(cashRegister) {
            let instance = this;
            if (cashRegister.status === 'open') {

                if (parseInt(instance.user.is_admin) === 1) {
                    return false;
                } else {
                    return _.includes(cashRegister.userID, instance.user.id.toString());
                }

            } else {
                return true;
            }
        },
        checkCashRegisterOpen() {
            let instance = this, status;
            instance.cashRegisterList.forEach(function (cashRegister, index) {
                if (cashRegister.status === 'open' && parseInt(cashRegister.open_user_id) === parseInt(instance.user.id)) {
                    status = true
                }
            });

            return !!status;
        },
        setCashRegisterData: function (cashRegister, status) {
            if (this.order_type === 'sales') {
                this.invoiceTemplateID = cashRegister.sales_invoice_id;
            } else {
                this.invoiceTemplateID = cashRegister.receiving_invoice_id;
            }
            this.$validator.validateAll().then((result) => {
                if (result || status === 'enroll'
                ) {
                    this.disableCloseButton = false;
                    let cashRegisterData = cashRegister,
                        flag = 0;
                    if (status === 'open') {
                        if (this.checkCashRegisterOpen()) {
                            flag = 1;
                            this.showSuccessAlert(this.trans('lang.please_close_the_current_cash_register_to_continue'));
                        } else {
                            cashRegisterData.status = 'open';
                            cashRegisterData.openingTime = moment().format('YYYY-MM-DD H:mm');
                            $('#branch-or-cash-register-select-modal').modal('hide');
                        }
                    } else if (status === 'close') {
                        cashRegisterData.closingAmount = this.closingAmount;
                        cashRegisterData.note = this.note;
                        cashRegisterData.status = 'closed';
                        cashRegisterData.closingTime = moment().format('YYYY-MM-DD H:mm');
                        this.hideCashRegisterPreLoader = false;
                    } else {
                        $('#branch-or-cash-register-select-modal').modal('hide');
                    }
                    if (flag == 0) {
                        this.selectedCashRegisterID = cashRegisterData.id;
                        this.selectedCashRegisterName = cashRegisterData.title;
                        this.selectedCashRegisterBranchID = cashRegisterData.branchID;
                        let instance = this;
                        instance.axiosGETorPOST(
                            {
                                url: '/cash-register-open-close',
                                postData: cashRegisterData,
                            },
                            (success, responseData) => {
                                if (success && (status === 'close' || status === 'open')
                                ) {
                                    instance.getCashRegisterData();
                                    if (status === 'close') {
                                        instance.selectedCashRegisterID = null;
                                        instance.selectedCashRegisterName = null;
                                    }
                                    instance.focusOnSearchBarWithOutTimeOut();
                                }
                            }
                        );
                        if (status === 'open') {
                            instance.getInvoiceTemplate();
                        }
                    }
                }
            });
        },
        dashboard() {
            let instance = this;
            instance.redirect('/dashboard');
        },

        //emit from customer
        newCustomer(customer) {
            this.newCustomerId = customer.id;
            this.offlineCustomers.push(customer);
            this.newAddedCustomer = customer;
        },
        selectCustomerFromCart(selectedCustomer) {
            this.customerNotAdded = false;
            this.selectedCustomer = selectedCustomer;
            this.customerSearchValue = '';
        },
        removeSelectedCustomerFromCart(selectedCustomer) {
            this.customerNotAdded = true;
            this.selectedCustomer = selectedCustomer;
            this.customerSearchValue = '';
        },
        selectSearchBranchFromCart(branch) {
            this.selectedSearchBranch = branch;
            this.isSelectedBranch = false;
            this.setCartItemsToCookieOrDB(1);
        },
        removeSelectedBranchFromCart() {
            this.selectedSearchBranch = [];
            this.isSelectedBranch = true;
        },
        // emit from cart-component
        setTaxIncludedOrExcludedFromCart(value) {
            this.isTaxExcludedFromCart = value;
            this.setCartItemsToCookieOrDB();
        },
        setCartItemsToCookieOrDBFromCart(flag) {
            this.setCartItemsToCookieOrDB(flag);
        },
        bankOrCardTransfer(amount, modalId) {
            this.isActiveTrans = true;
            this.calculateBank = false,
                $(modalId).modal('show');
            this.paidAmount = amount;
        },
        getUpdatedInvoice(updatedInvoiceForOffline) {
            this.lastInvoiceNumber = updatedInvoiceForOffline;
        },
        makeInvoiceIdNull(check) {
            if (check) {
                this.invoiceId = null;
                this.destroyCart(check);
            }
        },
        makePlaceOrderTrue(tableId) {

            this.restaurantOrderType = 'dineIn';
            this.isPlaceOrderActive = true;
            this.justPayRestaurantTableId = tableId;
            if (this.order_type === 'sales') {
                this.getHoldOrders();
            }
            this.restaurantTableId = '';
            if (navigator.onLine) {
                this.getProductData();
                this.getProductDataForOffline();
            }
        },
        orderHoldFromCart() {
            this.isHoldOrderDone = true;
            this.orderHold();
        },
        removeBookedTableIfEmpty() {
            let instance = this;
            let tempHoldOrders = [];
            if (instance.salesOrReceivingType === 'internal') tempHoldOrders = this.internalHoldOrders;
            else if (instance.salesOrReceivingType === 'internal-transfer') tempHoldOrders = this.internalTransferHoldOrders;
            else tempHoldOrders = this.customerHoldOrders;
            let temp = tempHoldOrders.filter(function (el) {
                return parseInt(el.tableId) === parseInt(instance.justPayRestaurantTableId);
            });
            if (temp.length === 0) {
                this.bookedTables.forEach(function (el, index, array) {
                    if (parseInt(el) === parseInt(instance.justPayRestaurantTableId)) {
                        array.splice(index, 1);
                    }
                });
            }
            ;
            this.justPayRestaurantTableId = null;
        },
        activeCartPaymentModal(value) {
            this.finalCart = value;
            this.isPaymentModalActive = true;
            if (this.isCartComponentActiveForMobile) {
                $('#cart-modal-for-mobile-view').modal('hide');
            }
        },
        newCustomerAddModalOpen() {
            this.isCustomerModalActive = true;
        },
        taxEditModal() {
            this.isTaxModalActive = true;
        },
        bookedTableFromAvailable(selectedTableID) {
            let temp = this.bookedTables.find(function (el) {
                return el == selectedTableID;
            });
            if (temp == undefined) this.bookedTables.push(selectedTableID);
        },
        confirmationModalButtonAction() {
            this.getRestaurantIdByOrderId();
            this.cancelOrder();
            this.orderID = null;
            this.invoiceId = null;
            this.destroyCart(true);
            this.overAllDiscount = null;
            this.newOverAllDiscount = null;
            this.selectedSearchBranch = [];
            this.isSelectedBranch = true;
            $('#clear-cart-modal').modal('hide');
        },
        getRestaurantIdByOrderId() {
            let instance = this;
            if (this.restaurantTableId) {
                this.justPayRestaurantTableId = this.restaurantTableId;
            } else {
                let obj = this.orderHoldItems.find(function (el) {
                    return el.orderID == instance.orderID;
                });
                this.justPayRestaurantTableId = obj !== undefined ? obj.tableId : '';
            }
        },
        getHoldOrders(newOrderHold = false) {
            let instance = this;
            this.hideOrderHoldItemsPreLoader = false;
            if (navigator.onLine) {
                instance.axiosGETorPOST(
                    {url: '/get-hold-orders'},
                    (success, responseData) => {
                        if (success) {
                            responseData.forEach(function (orderHoldItem, index, array) {
                                if (orderHoldItem.orderID == instance.orderID) {
                                    array.splice(index, 1); //removing data from orderHoldItems if orderID matches which is set previously
                                }
                            });

                            this.orderHoldItems = responseData;

                            if (instance.order_type === 'sales') {
                                this.getHoldOrdersByBranchType();
                            } else {
                                instance.orderHoldItems = responseData;
                            }

                            instance.hideOrderHoldItemsPreLoader = true;

                            if (this.currentBranch !== null) {
                                if (instance.filteredHoldOrder.length == 0) {
                                    $('#hold-orders-modal').modal('hide');
                                }
                            }
                        }
                    }
                );
            } else {
                this.getHoldOrdersByBranchType();
            }
            instance.hideOrderHoldItemsPreLoader = true;
            if (this.order_type === 'sales' && this.currentBranch !== null) {
                if (instance.filteredHoldOrder.length == 0) {
                    $('#hold-orders-modal').modal('hide');
                }
            }
        },
        getHoldOrdersByBranchType() {
            let instance = this;
            this.internalHoldOrders = this.getHoldOrdersBySalesOrReceivingType(this.orderHoldItems, 'internal');
            this.internalTransferHoldOrders = this.getHoldOrdersBySalesOrReceivingType(this.orderHoldItems, 'internal-transfer');
            this.customerHoldOrders = this.getHoldOrdersBySalesOrReceivingType(this.orderHoldItems, 'customer');

            if (instance.salesOrReceivingType === 'internal') instance.countHoldOrder = instance.internalHoldOrders.length;
            else instance.countHoldOrder = instance.customerHoldOrders.length;
            this.removeBookedTableIfEmpty();
        },
        getHoldOrdersBySalesOrReceivingType(orderHoldItems, type) {
            let instance = this;
            return orderHoldItems.filter(function (element) {
                element.isCashRegisterBranch = instance.isCashRegisterBranch;
                element.cashRagisterId = instance.selectedCashRegisterID;
                return (element.salesOrReceivingType === type && element.status === 'hold' && element.branchId == instance.selectedBranchID);
            });
        },
        destroyCart(check) {
            if (check) {
                deleteCartItemsFromCookieOrDB(this.user, this.order_type, this.appVersion);
                this.cart = [];
                this.discount = null;
                this.tax = 0;
                this.grandTotal = 0;
                this.total = 0;
                this.subTotal = 0;
                this.date = null;
                this.selectedCustomer = [];
                this.customerNotAdded = true;
                this.customerSearchValue = '';
                this.branchSearchValue = '';
                this.overAllDiscount = null;
                this.newOverAllDiscount = null;
                this.newdiscount = null;
                this.selectedSearchBranch = [];
                this.isSelectedBranch = true;
                this.addShipping = false;
            }
        },
        // Restaurant Module Methods
        setRestaurantOrderTypeDineIn() {
            this.restaurantOrderType = 'dineIn';
        },
        getRestaurantTableId(selectedTableID) {
            this.setRestaurantOrderTypeDineIn();
            this.restaurantTableId = selectedTableID;
            this.finalCart.tableId = this.restaurantTableId;
            this.orderHold();
            this.bookedTableFromAvailable(selectedTableID);
        },
        searchRestaurantOrders() {
            if (this.searchOrderTable) {
                this.filteredRestaurantOrder = this.customerHoldOrders.filter((customerHoldOrder) => {
                    return customerHoldOrder.tableId == this.searchOrderTable;
                })
            } else {
                this.filteredRestaurantOrder = this.customerHoldOrders;
            }
        },
        getRestaurantTablesBranchWise(branchId) {
            let restaurantTable = this.restaurant_tables ? JSON.parse(this.restaurant_tables) : [];

            this.allRestaurantTables = restaurantTable.filter(function (element) {
                return (element.branch_id == branchId);
            });
        },
        //emit restaurant
        openTableModalFromCart(finalCart) {
            this.finalCart = finalCart;
            this.isTableModalActive = true;
            $('#table-select-modal').modal('show');
            this.isCartComponentActiveForMobile = false;
            $('#cart-modal-for-mobile-view').modal('hide');
        },
        setRestaurantOrderTypeFromCart(type) {
            this.restaurantOrderType = type;
            this.makeIsPlaceOrderActive(this.restaurantOrderType);
        },
        makeIsPlaceOrderActive(type) {
            if (type === 'dineIn') this.isPlaceOrderActive = true;
            else if (type === 'takeAway') this.isPlaceOrderActive = false;
        },
        openHoldOrderModalFromCart() {
            $('#hold-orders-modal').modal('show');
            if (!this.isCartComponentActive) $('#cart-modal-for-mobile-view').modal('hide');
        },
        makeFinalCart(status) {
            let selectCustomerForCart = [];
            if (this.selectedCustomer[0]) selectCustomerForCart = this.selectedCustomer[0];
            this.finalCart = {
                orderID: this.orderID,
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
                transferBranch: this.selectedSearchBranch.id,
                transferBranchName: this.selectedSearchBranch.name,
                tableId: this.restaurantTableId,
                date: moment().format('YYYY-MM-DD h:mm A'),
                time: moment().format('YYYY-MM-DD h:mm A'),
            };
        },
        makeFinalCartForOffLine() {
            let instance = this;
            let selectedTable = this.allRestaurantTables.find(function (el) {
                return el.id == instance.restaurantTableId;
            });
            this.finalCart.isCashRegisterBranch = this.isCashRegisterBranch;
            this.finalCart.cashRagisterId = this.selectedCashRegisterID;
            this.finalCart.exchangeValue = 0;
            this.finalCart.payments = [];
            this.finalCart.current_invoice_number = this.finalCart.highest_invoice_number;
            this.finalCart.selectedBranchID = this.selectedBranchID;
            this.finalCart.time = moment().format('YYYY-MM-DD h:mm A');
            this.finalCart.status = 'hold';
            this.finalCart.tableName = selectedTable !== undefined ? selectedTable.name : '';
        },
        makeInvoiceIdForFinalCart() {
            if (this.orderID) {
                this.finalCart.invoice_id = this.invoiceId;
            } else {
                if (this.invoiceId == null) {
                    this.finalCart.highest_invoice_number = this.lastInvoiceNumber;
                    if (parseInt(this.isCashRegisterUsed) === 0) {
                        this.finalCart.invoice_id = this.invoicePrefix + this.finalCart.highest_invoice_number + '-' + '0' + '-' + this.user.id + this.invoiceSuffix;
                    } else {
                        this.finalCart.invoice_id = this.invoicePrefix + this.finalCart.highest_invoice_number + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoiceSuffix;
                    }
                    this.lastInvoiceNumber = parseInt(this.lastInvoiceNumber) + 1;
                } else {
                    this.finalCart.highest_invoice_number = this.lastInvoiceNumber;
                    this.finalCart.invoice_id = this.invoiceId;
                }
            }
        },
        orderHold() {
            this.cartSave('hold');
        },
        cartSave(status = 'done') {

            let instance = this;
            if (status === 'done') {
                this.isPaymentModalActive = true;
            }

            this.makeFinalCart(status);

            if (status === 'hold') {
                //hold and offline
                this.isHoldOrderDone = true;
                if (!navigator.onLine) {
                    this.makeInvoiceIdForFinalCart();
                    this.makeFinalCartForOffLine();
                    if (this.finalCart.orderID != null) {
                        this.orderHoldItems.forEach(function (orderHoldItem, index, array) {
                            if (parseInt(orderHoldItem.orderID) === parseInt(instance.finalCart.orderID)) {
                                orderHoldItem.status = 'hold';
                            }
                        });
                    } else {
                        this.orderHoldItems.push(this.finalCart);
                    }
                    this.getHoldOrders(true);
                    this.getSetLocalStorage(this.finalCart, this.finalCart.orderID, this.finalCart.invoice_id);
                    if (this.finalCart) this.bookedTableFromAvailable(this.finalCart.tableId);
                    this.resetAfterCartSave();
                    this.transitionEffectOnHoldOrderIcon();
                } else {
                    let instance = this;
                    instance.axiosGETorPOST(
                        {
                            url: '/store',
                            postData: this.finalCart
                        },
                        (success, responseData) => {
                            if (success) {
                                if (this.finalCart) this.bookedTableFromAvailable(this.finalCart.tableId);
                                instance.invoiceId = null;
                                this.resetAfterCartSave();
                                this.transitionEffectOnHoldOrderIcon();
                            }
                        }
                    );
                }
            }
        },
        resetAfterCartSave() {
            this.orderID = null;
            this.isSelectedBranch = true;
            this.destroyCart(true);
            this.selectedSearchBranch = [];

            if (this.order_type === 'sales') {
                this.getHoldOrders(true);
            }

            this.isHoldOrderDone = false;
            this.isPlaceOrderActive = true;
            this.customerNotAdded = true;
            this.restaurantTableId = '';
            this.makeIsPlaceOrderActive(this.restaurantOrderType);
        },
        transitionEffectOnHoldOrderIcon() {
            $('.hold-icon').addClass('order-hold-transition');
            setTimeout(function () {
                $('.hold-icon').removeClass('order-hold-transition');
            }, 1000);
        },
        getSetLocalStorage(newData, orderID, invoiceId) {

            newData ? newData = newData : newData = [];

            let localStorageData = localStorage.getItem('salesProduct'),
                orderDetails = localStorageData ? JSON.parse(localStorageData) : [];
            if (orderDetails.length > 0) {
                orderDetails.forEach(function (orderHoldItem, index, array) {
                    if ((orderHoldItem == null) || (orderHoldItem.orderID == orderID && orderHoldItem.orderID) || (orderHoldItem.invoice_id == invoiceId && orderHoldItem.orderID == null)) {
                        array.splice(index, 1);
                    }
                });
                orderDetails.push(newData);
            } else {
                orderDetails = this.internalHoldOrders.concat(this.customerHoldOrders);
                orderDetails = this.internalTransferHoldOrders.concat(this.customerHoldOrders);
                if (orderDetails.length == 0) orderDetails.push(newData);
            }
            localStorage.setItem('salesProduct', JSON.stringify(orderDetails));
        },
        // load more
        loadMoreSubmit() {
            this.buttonLoader = true;
            this.isLoadMoreDisabled = true;
            this.loadMoreBtnOffset += parseInt(this.productRowLimit);
            this.getProductData();
        },
        addShipmentInfo(value, bool) {
            this.cart.forEach(function (element, index, shippingArray) {
                if (element.orderType === 'shipment') {
                    shippingArray.splice(index, 1);
                }
            });
            if (bool) {
                this.cart.push(value);
            }
            this.addShipping = bool;
            this.setCartItemsToCookieOrDB(1);
            this.makeFinalCart('done');
        }
    }
}