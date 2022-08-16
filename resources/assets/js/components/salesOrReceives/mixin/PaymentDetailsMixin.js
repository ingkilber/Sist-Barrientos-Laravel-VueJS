export default {
    data() {
        return {
            parseInt,
            status: {
                isPaymentDone: false,
                printReceiptView: false,
                autoInvoiceGenerate: false,
                donePaymentCheck: false,
                hidePaymentListGetLoader: null,
                printInvoiceBeforeDonePayment: false,
                printInvoice: false,
                isConnected: true,
                isSendSms: false,
                isSubmitted: false,
            },
            paymentList: [],
            invoice: [],
            paymentGuard: true,
            payments: [],
            savedPayments: [],
            balance: this.finalCart.grandTotal,
            invoiceID: '',
            exchangeValue: '',
            currentOrderId: '',
            paymentListData: [],
            paymentName: '',
            paymentId: '',
            paymentStatus: '',
            paid: '',
            noRoundingAmount: '',
            finalCartAmount: '',
            roundingDifference: 0,
            paymentType: '',
            paymentValue: '',
            paymentOptions: {},
            paidAmount: '',
            rawHtml: '',
            autoInvoiceStatus: '',
            highestInvoiceNumber: '',
            lastInvoiceNumber: '',
            isCashRegisterBranch: '',
            addShipment: '',
            addShipmentInfo: '',
            shippingAreaId: '',
            shippingArea: '',
            shippingPrice: '',
            shippingAddress: '',
            shippingData: [],
            shippingInfo: {},
            returnCondition: this.salesOrReturnType === 'returns' || this.receiveOrReturnType === 'returns',
            salesNote: "",
            isEmptyObj: (object) => {
                if (_.isEmpty(object)) {
                    return true;
                }
            },

        };
    },
    created() {
        this.noRoundingAmount = this.finalCart.grandTotal;
        this.addShipmentInfo = this.add_shipping;
        this.addShipmentInfo ? this.addShipment = 1 : this.addShipment = 0;

        this.isCashRegisterBranch = this.is_cash_register_branch;
        this.setPaymentListData();
        this.setAutoInvoiceStatus();
        if (this.orderID) {
            this.setSavedPayments();
        }
        this.currentOrderId = this.orderID;
        this.shippingInfo = {
            productID: null,
            variantID: null,
            taxID: null,
            orderType: "shipment",
            productTitle: "Shipment",
            price: 0,
            quantity: -1,
            calculatedPrice: 0,
            cartItemNote: "",
            showItemCollapse: false,
            variantTitle: "",
        };
    },
    watch: {
        calculateBank: function (newValue) {
            if (newValue) {
                this.defaultPayment(this.bankOrCardAmount, this.bankOrCardOptions);
            }
        },
        is_connected: function (value) {
            this.status.isConnected = value;
        },
        finalCart: function (newValue) {
            if (newValue) {
                this.paymentValue = this.finalCart.grandTotal;
            }
        },
        shippingPrice: function (value) {
            if (value == '') value = 0;
            else value = parseFloat(value);
            this.shippingInfo.price = value;
            this.shippingInfo.calculatedPrice = value;
            this.addShipmentStatus(1);
        },
        paid: 'preventCreditPayment',
    },
    mounted() {
        this.shippingData = this.shipping_data.map((area) => {
            return area;
        });

        let instance = this;
        instance.lastInvoiceNumber = parseInt(instance.last_invoice_number);
        $(window).resize(function () {
            instance.setPaymentDescHeight();
        });

    },
    beforeDestroy() {
        if (!this.status.donePaymentCheck) {
            this.storeInvoice('continue');
        }
    },
    methods: {
        setShippingPrice() {
            let tempShipping = this.shippingData.find(elem => elem.id == this.shippingAreaId);
            this.shippingPrice = 'price' in tempShipping ? tempShipping.price: 0;
            this.shippingArea = 'area' in tempShipping ?  tempShipping.area : '';
        },
        closeModal() {
        },
        shortcutForDonePayment(value) {
            if (parseInt(this.shortcutKeyInfo.donePayment.status) === 1 && parseInt(this.shortcutStatus) === 1) {
                this.storeInvoice();
            }
        },
        addShipmentStatus(value) {
            this.addShipment = value;
            if (parseInt(this.addShipment) === 1) {
                this.addShipmentInfo = true;
                this.$emit('addShipmentInfo', this.shippingInfo, true);
            } else {
                this.addShipmentInfo = false;
                this.$emit('addShipmentInfo', this.shippingInfo, false);
            }
        },
        getPaymentAmount(value) {
            this.paid = value;
            this.calculateBalance();
        },
        defaultPayment(amount, options) {
            this.paid = amount;
            this.paymentValue = amount;
            this.paymentOptions = options;
            this.calculateBalance();
            this.storeInvoice();
        },
        setAutoInvoiceStatus() {
            let instance = this;
            instance.autoInvoiceStatus = JSON.parse(instance.autoInvoice);

            if (parseInt(instance.autoInvoiceStatus.setting_value) === 1) {
                instance.status.autoInvoiceGenerate = true;
            }
        },
        setPayment(id, name, status, type, check = true) {
            let instance = this;
            instance.paymentGuard = false;
            this.paymentName = name;
            this.paymentId = id;
            this.paymentStatus = status;
            this.paymentType = type;
            this.paymentOptions = {};
            this.paid = parseFloat(this.rounding(this.noRoundingAmount)).toFixed(2);
            this.paymentValue = this.paid;
            this.roundingDifference = parseFloat(this.paid - this.noRoundingAmount);
            setTimeout(function () {
                instance.paymentGuard = true;
            });
            if (check) {
                this.calculateBalance();
            }
        },
        selectDefaultMethod() {
            for (let i = 0; i < this.paymentList.length; i++) {
                this.payments.push({
                    paymentID: this.paymentList[i].id,
                    paymentName: null,
                    paid: null,
                    exchange: null,
                });
                if (parseInt(this.paymentList[i].is_default) === 1) {
                    this.setPayment(this.paymentList[i].id, this.paymentList[i].name, this.paymentList[i].status, this.paymentList[i].type, false);
                }
            }
        },
        clearPayment(index, payment_id, paid) {
            this.paymentListData.splice(index, 1);
            this.noRoundingAmount += parseFloat(paid);
            this.paid = this.rounding(this.noRoundingAmount).toFixed(2);
            this.paymentValue = this.paid;
            this.roundingDifference = this.paid - this.noRoundingAmount;
            this.calculateBalance();
        },
        checkPaymentSelected() {
            return _.filter(this.payments, ['paymentName', null]).length;
        },
        calculateBalance() {
            let data = this.getPaidAndExchangeAmount();
            if (this.salesOrReceivingType === 'internal-transfer') this.finalCart.grandTotal = this.paid;

            if (this.returnCondition) {
                this.exchangeValue = data.exchangedAmount;
            } else {
                this.exchangeValue = this.noRoundingAmount < 0 ? Math.abs(data.exchangedAmount) + Math.abs(parseFloat(this.noRoundingAmount)) : data.exchangedAmount;  // for salses or purchase
            }

            const lastIndex = this.paymentListData.length - 1;
            if (lastIndex >= 0) this.paymentListData[lastIndex].exchange = this.exchangeValue;

            this.paidAmount = data.paidAmount ? data.paidAmount : this.paid;
            this.balance = (this.finalCart.grandTotal + this.roundingDifference - data.paidAmount - (this.paid)).toFixed(2);
        },
        getPaidAndExchangeAmount() {
            let paidAmount = 0,
                exchangedAmount = 0,
                condition = this.returnCondition;

            this.paymentListData.forEach(function (payment) {
                if (payment.paid) {
                    paidAmount += parseFloat(payment.paid);

                    if (condition) {
                        if (payment.paid > 0) {
                            exchangedAmount += parseFloat(payment.paid);
                            payment.is_active = false;
                        } else {
                            payment.is_active = true;
                            exchangedAmount += 0;
                        }
                    } else {
                        if (payment.paid < 0) {
                            exchangedAmount += parseFloat(payment.paid);
                            payment.is_active = false;
                        } else {
                            exchangedAmount += 0;
                            payment.is_active = true;
                        }
                    }
                }
            });
            return {
                paidAmount: paidAmount,
                exchangedAmount: exchangedAmount,
            }
        },

        setDestroyCart(value) {
            this.$emit('setDestroyCart', value);
        },
        setPaymentListData() {
            let instance = this;
            instance.paymentList = JSON.parse(instance.paymentTypes);
            instance.selectDefaultPayment();
            instance.status.hidePaymentListGetLoader = true;
            instance.setPaymentDescHeight();
        },
        selectDefaultPayment() {
            let instance = this;
            for (let i = 0; i < instance.paymentList.length; i++) {
                this.payments.push({
                    paymentID: instance.paymentList[i].id,
                    paymentName: null,
                    paid: null,
                    exchange: null,
                });
                if (parseInt(instance.paymentList[i].is_default) === 1) {
                    instance.setPayment(instance.paymentList[i].id, instance.paymentList[i].name, instance.paymentList[i].status, instance.paymentList[i].type);
                }
            }
        },
        preventCreditPayment() {
            if (this.paid < 0 && this.paymentType === 'credit') {
                this.paymentList.forEach((item, index, arr) => {
                    if (parseInt(item.is_default) === 1) {
                        this.setPayment(item.id, item.name, item.status, item.type);
                    }
                });
            }
        },
        setSavedPayments() {
            let instance = this;

            if (navigator.onLine) {
                instance.axiosGETorPOST(
                    {
                        url: '/continue-sale-payments',
                        postData: {orderID: this.orderID}
                    },
                    (success, responseData) => {
                        if (success) {
                            instance.savedPayments = responseData;
                        }
                    });
            }
        },
        printReceipt() {
            this.status.printInvoice = true;
        },
        sendCustomerSms() {
            let instance = this;
            instance.inputFields = {
                id: instance.finalCart.customer.id,
                first_name: instance.finalCart.customer.first_name,
                last_name: instance.finalCart.customer.last_name,
                phone_number: instance.finalCart.customer.phone_number,
                subTotal: instance.finalCart.subTotal,
                invoiceId: instance.invoiceID,
            }
            instance.postDataMethod("/customer-send-sms", instance.inputFields);

        },
        postDataThenFunctionality() {
            this.status.isSendSms = true;
        },
        postDataCatchFunctionality(error) {
        },
        printReceiptBeforeDonePayment() {
            this.status.printInvoiceBeforeDonePayment = true;
        },
        storeInvoice(action = 'store') {

            if (parseInt(this.addShipment) === 1) {

                this.$validator.validateAll().then((result) => {
                    if (result) {
                        let cartItemsToStore = this.finalCart;
                        cartItemsToStore.shippingPrice = this.shippingPrice;
                        cartItemsToStore.shippingAreaId = this.shippingAreaId;
                        cartItemsToStore.shippingAreaSddress = this.shippingAddress;
                        this.storeInvoiceCart(action);
                    } else if (!result && action === 'continue') {
                        this.storeInvoiceCart(action);
                    }
                });
            } else {
                this.storeInvoiceCart(action);
            }
        },

        storeInvoiceCart(action) {
            let instance = this,
                paidAmount = 0,
                exchangedAmount = 0;

            this.paymentListData.push({
                paid: this.paid,
                paymentID: this.paymentId,
                paymentName: this.paymentName,
                paymentType: this.paymentType,
                PaymentTime: moment().format('YYYY-MM-DD H:mm'),
                options: this.paymentOptions,
            });
            this.paymentListData.forEach(function (payment, index, paymentListArray) {
                if (payment.paid) {
                    paidAmount += parseFloat(payment.paid);
                    payment.exchange = 0;
                    if (paymentListArray.length - 1 === parseInt(index)) payment.exchange = instance.exchangeValue;
                }
            });

            if ((parseInt(this.balance) === 0 || this.paidAmount == this.finalCart.grandTotal) || (parseInt(this.paidAmount) === 0 && this.salesOrReceivingType === "internal-transfer")) {

                let cartItemsToStore = this.finalCart,
                    postURL;

                cartItemsToStore.salesOrReturnType = this.salesOrReturnType;
                cartItemsToStore.highest_invoice_number = this.lastInvoiceNumber;

                cartItemsToStore.orderType === 'sales' ? postURL = '/store' : postURL = '/purchase-store';

                this.returnCondition ? this.calculateBalance() : this.getPaidAndExchangeAmount();

                cartItemsToStore.payments = this.paymentListData;
                cartItemsToStore.orderID = this.orderID;
                cartItemsToStore.salesNote = this.salesNote;
                cartItemsToStore.orderIdForInternalTransfer = this.orderIdForInternalTransfer;
                cartItemsToStore.exchangeValue = this.exchangeValue;
                cartItemsToStore.selectedBranchID = this.selectedBranchID;
                cartItemsToStore.user = this.user;
                cartItemsToStore.transferBranch = this.transferBranch;


                if (this.isCashRegisterBranch === true) {
                    cartItemsToStore.cashRagisterId = this.selectedCashRegisterID;
                }

                cartItemsToStore.isCashRegisterBranch = this.isCashRegisterBranch;
                cartItemsToStore.time = moment().format('YYYY-MM-DD h:mm A');

                if (action === 'continue') {
                    cartItemsToStore.status = 'pending';

                    cartItemsToStore.orderType === 'sales' ? postURL = '/continue-sale' : postURL = '/continue-purchase';
                }

                instance.status.hidePaymentListGetLoader = false;
                instance.status.isPaymentDone = false;

                if (!navigator.onLine && parseInt(this.offline) === 1 && action !== 'continue') {
                    this.offlineDataProcessing(cartItemsToStore, action);

                } else if (navigator.onLine) {
                    this.onlineDataProcessing(cartItemsToStore, postURL, action)
                }
            } else {
                instance.noRoundingAmount = parseFloat(instance.finalCart.grandTotal - paidAmount);
                instance.paid = parseFloat(instance.rounding(instance.noRoundingAmount));
                instance.paymentValue = instance.paid;
                instance.roundingDifference = parseFloat(instance.paid - instance.noRoundingAmount);
                instance.calculateBalance();
            }
        },
        rounding(amount) {

            if (this.paymentStatus === 'near_half') {
                return Math.round((amount) * 2).toFixed() / 2;

            } else if (this.paymentStatus === 'near_integer') {
                return Math.round(amount);

            } else {
                return amount;
            }
        },
        setPaymentDescHeight() {
            setTimeout(function () {
                let totalHeight = $('#js-payment').height();
                let paymentTitleHeight = $('#js-payment-title').height();
                let paymentActionHeight = $('#js-payment-action').height();
                let paymentDescHeight = totalHeight - (paymentTitleHeight + paymentActionHeight + 30);
                $('#js-payment-description').height(paymentDescHeight);
            }, 1000);
        },
        resetGetInvoice(resetGetInvoice) {
            this.status.printInvoice = resetGetInvoice;
        },
        resetGetInvoiceBeforeDonePayment(resetGetInvoice) {
            this.status.printInvoiceBeforeDonePayment = resetGetInvoice;
        },
        absValue(value) {
            return Math.abs(value);
        },


        getLogo() {

            let invoiceLogo = this.publicPath + "/uploads/logo/" + this.purchaseInvoiceLogo;

            return this.rawHtml = this.rawHtml.replace('{logo_source}', invoiceLogo);
        },

        generateOfflineInvoice(cartItemsToStore) {

            let instance = this,
                employeeName = cartItemsToStore.user.first_name + " " + cartItemsToStore.user.last_name,
                itemDetails = instance.getInvoiceDetails(cartItemsToStore.cart),
                paymentDetails = instance.makePaymentDetailsForInvoice(cartItemsToStore.payments),
                subTotal = instance.numberFormat(cartItemsToStore.subTotal),
                tax = instance.numberFormat(cartItemsToStore.tax),
                total = instance.numberFormat(cartItemsToStore.grandTotal),
                exchange = instance.numberFormat(cartItemsToStore.exchangeValue),
                discount = instance.numberFormat(cartItemsToStore.overAllDiscount),
                invoiceTemplate = this.invoice_template;

            let customerName,
                customerPhone = '',
                customerAddress = '',
                tinNumber = '';

            if (this.orderType === 'sales') {
                if (this.salesOrReceivingType === 'customer') {
                    customerName = cartItemsToStore.customer.length === 0 ? 'Walk In Customer' : `${cartItemsToStore.customer.first_name} ${cartItemsToStore.customer.last_name}`;
                    customerPhone = cartItemsToStore.customer.phone_number ? cartItemsToStore.customer.phone_number : '';
                    customerAddress = cartItemsToStore.customer.address ? cartItemsToStore.customer.address : '';
                    tinNumber = cartItemsToStore.customer.tin_number ? cartItemsToStore.customer.tin_number : '';

                } else {
                    customerName = this.transferBranchName;
                }
            } else {

                if (this.salesOrReceivingType === 'supplier') {
                    customerName = cartItemsToStore.customer.length === 0 ? 'Walk In Supplier' : `${cartItemsToStore.customer.first_name} ${cartItemsToStore.customer.last_name}`;
                    tinNumber = cartItemsToStore.customer.tin_number ? cartItemsToStore.customer.tin_number : '';
                } else {
                    customerName = this.transferBranchName;
                }
            }

            let obj = {
                '{app_name}': this.app_name,
                '{invoice_id}': '',
                '{employee_name}': employeeName,
                '{customer_name}': customerName,
                '{supplier_name}': customerName,
                '{date}': this.dateFormats(this.date),
                '{time}': this.dateFormatsWithTime(this.date),
                '{item_details}': itemDetails,
                '{payment_details}': paymentDetails,
                '{sub_total}': subTotal,
                '{tax}': tax,
                '{total}': total,
                '{exchange}': exchange,
                '{discount}': discount,
                '{phone_number}': customerPhone,
                '{address}': customerAddress,
                '{tin}': tinNumber,
                '{note}': this.salesNote,
                '{barcode}': '',
                '{shipment_info}': '',
            };

            if (parseInt(this.is_cash_register_used) === 0) {
                this.invoiceID = this.invoiceId ? this.invoiceId : this.invoice_prefix + cartItemsToStore.current_invoice_number + '-' + '0' + '-' + this.user.id + this.invoice_suffix;
                obj['{invoice_id}'] = this.invoiceID;
            } else {
                this.invoiceID = this.invoiceId ? this.invoiceId : this.invoice_prefix + cartItemsToStore.current_invoice_number + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoice_suffix;
                obj['{invoice_id}'] = this.invoiceID;
            }

            if (this.addShipmentInfo) {
                obj['{shipment_info}'] = this.shipping_address;
            }


            for (let [key, value] of Object.entries(obj)) {
                invoiceTemplate = invoiceTemplate.replace(key, value);
            }


            this.rawHtml = invoiceTemplate;
        },
        getInvoiceDetails(itemDetails) {
            let row = "";
            for (let item of itemDetails) {
                if (item.variantTitle === 'default_variant' || item.variantTitle == undefined) {
                    item.variantTitle = '';
                }
                if (item.discount == null) {
                    item.discount = '0.00';
                }
                let newRow = `<tr>
                            <td style="padding: 7px 0; text-align: left; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${item.productTitle} ${item.variantTitle}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormatting(item.quantity)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.price)}</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.decimalFormat(item.discount)}%</td>
                            <td style="padding: 7px 0; text-align: right; border-bottom: 1px solid #bfbfbf; border-spacing: 0;">${this.numberFormat(item.calculatedPrice)}</td>
                        </tr>`;
                row = row + newRow;
            }
            return row;
        },
        makePaymentDetailsForInvoice(paymentDetails) {
            let row = "";
            for (let item of paymentDetails) {
                let newRow = `<tr style="text-align: left;">
                        <th style="padding: 7px 0;">${item.paymentName}</th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <th style="padding: 7px 0;"></th>
                        <td style="padding: 7px 0; text-align: right;">${this.numberFormat(item.paid)}</td>
                    </tr>`;
                row = row + newRow;
            }
            return row;
        },
        onlineDataProcessing(cartItemsToStore, postURL, action) {
            let instance = this;
            this.lastInvoiceNumber = this.lastInvoiceNumber + 1;
            cartItemsToStore.highest_invoice_number = this.lastInvoiceNumber;

            this.status.isSubmitted = true;

            instance.axiosGETorPOST(
                {
                    url: postURL,
                    postData: cartItemsToStore,
                },
                (success, responseData) => {
                    if (success) {
                        if ("invoiceTemplate" in responseData) {
                            instance.rawHtml = responseData.invoiceTemplate.data;
                        }

                        this.lastInvoiceNumber = responseData.lastInvoiceId;

                        if (action === 'continue') {
                            instance.$hub.$emit('setOrderID', responseData.orderID, responseData.lastInvoiceId, responseData.orderIdInternalTransfer,);
                            instance.$emit('makeInvoiceIdNull', false);
                        } else {
                            instance.invoiceID = responseData.invoiceID;

                            if ("checkAvailableQuantity" in responseData) {
                                instance.$emit('makeInvoiceIdNull', false);
                                for (let i = 0; i < responseData.message.length; i++) {
                                    let alertMessage = responseData.message[i];

                                    instance.showWarningAlertForOutOfStock(alertMessage);
                                }
                                $('#cart-payment-modal').modal('hide');
                            } else {
                                instance.$emit('makeInvoiceIdNull', true);
                                instance.setDestroyCart(true);
                            }
                            instance.status.donePaymentCheck = true;
                            instance.$hub.$emit('setOrderID', null, responseData.lastInvoiceId);

                            instance.status.isPaymentDone = true;
                            instance.status.printReceiptView = true;

                            if (instance.status.autoInvoiceGenerate) {
                                setTimeout(()=>{
                                    $('#cart-payment-modal').modal('hide');
                                    instance.printReceipt();
                                })
                            }

                            instance.$emit('makePlaceOrderTrue', cartItemsToStore.tableId);
                        }
                    }
                    instance.status.hidePaymentListGetLoader = true;
                    this.$emit('returnCartChange', false);
                }
            );
        },
        offlineDataProcessing(cartItemsToStore, action) {
            let instance = this;
            if (cartItemsToStore.orderID) {
                cartItemsToStore.invoice_id = this.invoiceId;
            } else {
                if (this.invoiceId == null) {
                    cartItemsToStore.current_invoice_number = this.lastInvoiceNumber;
                    if (parseInt(this.is_cash_register_used) === 0) {
                        cartItemsToStore.invoice_id = this.invoice_prefix + this.lastInvoiceNumber + '-' + '0' + '-' + this.user.id + this.invoice_suffix;
                    } else {
                        cartItemsToStore.invoice_id = this.invoice_prefix + this.lastInvoiceNumber + '-' + this.selectedCashRegisterID + '-' + this.user.id + this.invoice_suffix;
                    }
                    this.lastInvoiceNumber = this.lastInvoiceNumber + 1;
                } else {
                    cartItemsToStore.current_invoice_number = this.lastInvoiceNumber;
                    cartItemsToStore.invoice_id = this.invoiceId;
                }
            }
            let localStorageData = localStorage.getItem('salesProduct');
            if (localStorageData != null) {
                let orderDetails = JSON.parse(localStorageData);
                if (orderDetails.length > 0) {
                    orderDetails.forEach(function (orderHoldItem, index, array) {
                        if (parseInt(orderHoldItem.orderID) === parseInt(cartItemsToStore.orderID) && orderHoldItem.orderID) {
                            array.splice(index, 1);
                        } else if (parseInt(orderHoldItem.invoice_id) === parseInt(cartItemsToStore.invoice_id) && orderHoldItem.orderID == null) {
                            array.splice(index, 1);
                        }
                    });
                }
                orderDetails.push(cartItemsToStore);
                localStorage.setItem('salesProduct', JSON.stringify(orderDetails));
            } else {
                localStorage.setItem('salesProduct', JSON.stringify([cartItemsToStore]));
            }

            instance.status.donePaymentCheck = true;
            instance.$hub.$emit('setOrderID', null);
            instance.status.isPaymentDone = true;

            instance.generateOfflineInvoice(cartItemsToStore);

            instance.$emit('getUpdatedInvoice', instance.lastInvoiceNumber);
            instance.status.printReceiptView = true;
            instance.$emit('makePlaceOrderTrue', cartItemsToStore.tableId);

            action === 'continue' ? instance.$emit('makeInvoiceIdNull', false) : instance.$emit('makeInvoiceIdNull', true);

        },

    }
}
