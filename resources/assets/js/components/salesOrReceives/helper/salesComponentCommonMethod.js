export const subTotalAmount = function (
    discountOnSubtotal,
    salesOrReturnType,
    receiveOrReturnType,
    total,
    profit,
    tax,
    subTotal,
    cart,
    productTotalWithoutDiscount,
    isTaxExcluded,
    orders,
    originalSoldProductForReturn
) {
    total = 0;
    profit = 0;
    tax = 0;
    subTotal = 0;
    let adjustedDiscount = 0, returnOrder = [];

    //final subtraction for return product discount on subtotal calculation
    if ((salesOrReturnType === 'returns' || receiveOrReturnType === 'returns')) {
        returnOrder = _.last(originalSoldProductForReturn);
    }

    cart.forEach((cartItem) => {
        let calculatedPriceForSub = 0;
        productTotalWithoutDiscount += cartItem.price;

        if (cartItem.quantity > 0) {

            cartItem.calculatedPrice = cartItem.price * cartItem.quantity;

            if (cartItem.orderType !== "discount" && cartItem.discount != undefined) {

                calculatedPriceForSub = cartItem.calculatedPrice - (cartItem.calculatedPrice * cartItem.discount / 100);

            } else if (cartItem.orderType !== "discount" && cartItem.discount == undefined) {

                calculatedPriceForSub = cartItem.calculatedPrice;
            }


            if (cartItem.discount) {
                cartItem.calculatedPrice = cartItem.calculatedPrice - (cartItem.calculatedPrice * cartItem.discount / 100);
            }
            tax += (cartItem.calculatedPrice * cartItem.productTaxPercentage) / 100;

        } else if (salesOrReturnType === 'returns' || receiveOrReturnType === 'returns') {

            cartItem.calculatedPrice = cartItem.price * cartItem.quantity;
            cartItem.discount = cartItem.discount >= 0 ? cartItem.discount : 100;

            cartItem.calculatedPrice = cartItem.calculatedPrice - (cartItem.calculatedPrice * cartItem.discount / 100);
            tax += (cartItem.calculatedPrice * cartItem.productTaxPercentage) / 100;
            calculatedPriceForSub = cartItem.calculatedPrice;
        }

        total += cartItem.calculatedPrice;
        subTotal += calculatedPriceForSub;

        if (cartItem.orderType !== 'discount') {
            if (cartItem.orderType !== 'shipment') {
                profit += cartItem.calculatedPrice - (cartItem.purchase_price * cartItem.quantity);
            }
        } else if (cartItem.orderType !== 'shipment') {
            profit -= cartItem.price;
        }
    });

    let grandTotal;
    if (isTaxExcluded) {
        grandTotal = Number((total + tax).toFixed(2));
    } else grandTotal = Number((total).toFixed(2));


    //final subtraction for return product discount on subtotal calculation
    if ((salesOrReturnType === 'returns' || receiveOrReturnType === 'returns')) {

        let originalSale = originalSoldOrder(returnOrder),
            isEqualCart = parseInt(originalSale.cartWithoutDiscount.length) === parseInt(cart.length),
            isEqualCartQuantity = matchCartItemAndQuantity(originalSale.cartWithoutDiscount, cart, originalSale);

        if (isEqualCart && isEqualCartQuantity) {

            grandTotal = grandTotal + discountOnSubtotal;
            total = total + discountOnSubtotal;

        } else {

            adjustedDiscount = (originalSale.discountOnSubtotalPercentage * subTotal) / 100;
            grandTotal = originalSale.isPartial ? grandTotal + Math.abs(adjustedDiscount) : grandTotal + adjustedDiscount;
            total = total + adjustedDiscount;
        }
    }

    return {
        total: total,
        profit: profit,
        tax: tax,
        subTotal: subTotal,
        grandTotal: grandTotal,
        productTotalWithoutDiscount: productTotalWithoutDiscount,
        adjustedDiscount: adjustedDiscount,
    }
}

const originalSoldOrder = (returnOrder) => {

    const tempOriginalSale = {
        subtotal: 0,
        isPartial: false,
        discountOnSubtotal: 0,
    };

    returnOrder.cart.map((item) => {

        if (item.orderType !== 'discount') {

            item.discount = item.discount >= 0 ? item.discount : 100;
            tempOriginalSale.originalSoldSubTotal = item.original_sold_sub_total;

            if (item.returnType === 'partial') {

                tempOriginalSale.subtotal += item.price - ((item.price * item.discount) / 100);
                tempOriginalSale.isPartial = true;

            } else {
                tempOriginalSale.subtotal += item.calculatedPrice - (item.calculatedPrice * item.discount / 100);
            }

        } else {

            tempOriginalSale.discountOnSubtotal = item.price;
        }
    })

    tempOriginalSale.cart = returnOrder.cart;
    tempOriginalSale.cartWithoutDiscount = returnOrder.cart.filter((item) => item.orderType === 'sales');

    if (tempOriginalSale.isPartial) {
        tempOriginalSale.discountOnSubtotalPercentage = (tempOriginalSale.discountOnSubtotal * 100) / tempOriginalSale.originalSoldSubTotal;

    } else {
        tempOriginalSale.discountOnSubtotalPercentage = (tempOriginalSale.discountOnSubtotal * 100) / tempOriginalSale.subtotal;
    }

    return tempOriginalSale;
}

const matchCartItemAndQuantity = (originalSoldCart, currentCart, originalSale) => {

    let result = true;

    for (let i = 0; i < currentCart.length; i++) {
        let variants = originalSoldCart.find(item => (Number(item.variantID) === Number(currentCart[i].variantID)))
        if (Number(variants.quantity) === Number(currentCart[i].quantity)) {
            if (originalSale.isPartial) {
                result = false
                break;
            }
        } else {
            result = false;
            break;
        }
    }

    return result;
}

/*
* setCartItemsToCookieOrDB
* */

export const cartItemsToCookie = function (flag = 0, object, appVersion) {
    let cookieName = appVersion + "-user-" + object.user.id + "-" + object.order_type + "-cart",
        cookieObject = {
            'cart': object.cart,
            'customer': object.selectedCustomer,
            'branch': object.selectedSearchBranch,
            'orderID': object.orderID,
            'orderIdInternalTransfer': object.orderIdInternalTransfer,
            'discount': object.discount,
            'overAllDiscount': object.overAllDiscount,
            'lastInvoiceNumber': object.lastInvoiceNumber,
            'addShipping': object.addShipping,
        };
    if (!window.$cookies.isKey(cookieName)) {
        window.$cookies.set(cookieName, cookieObject, "4m");
    } else {
        if (parseInt(flag) === 0) {
            let cookieValue = window.$cookies.get(cookieName);
            let cookieCart = cookieValue.cart;

            cookieCart.forEach(function (cookieCartItem, index, array) {
                if (cookieCartItem.showItemCollapse) {
                    array[index].showItemCollapse = false;
                }
            });
            object.cart = cookieCart;
            object.selectedCustomer = cookieValue.customer;
            if (cookieValue.branch != undefined && object.salesOrReceivingType === 'internal') {

                if (parseInt(cookieValue.branch.length) === 0) {
                    object.isSelectedBranch = true;
                } else {
                    object.selectedSearchBranch = cookieValue.branch;
                    object.isSelectedBranch = false;
                }
            } else {
                object.isSelectedBranch = true;
            }
            object.discount = cookieValue.discount;
            object.newDiscount = cookieValue.discount;
            object.overAllDiscount = cookieValue.overAllDiscount;
            object.newOverAllDiscount = cookieValue.overAllDiscount;
            object.orderID = cookieValue.orderID;
            if (object.selectedCustomer.length == 1) {
                object.customerNotAdded = false;
            }
        } else {
            window.$cookies.set(cookieName, cookieObject, "4m");
        }
    }
    return {
        selectedCustomer: object.selectedCustomer,
        isSelectedBranch: object.isSelectedBranch,
        discount: object.discount,
        newDiscount: Number(object.newDiscount),
        overAllDiscount: Number(object.overAllDiscount),
        orderID: object.orderID,
        orderIdInternalTransfer: object.orderIdInternalTransfer,
        customerNotAdded: object.customerNotAdded,
        newOverAllDiscount: Number(object.newOverAllDiscount),
        cart: object.cart,
        selectedSearchBranch: object.selectedSearchBranch,
        addShipping: object.addShipping,
    }
}

/*
* Delete Cart From cookie
* */
export const deleteCartItemsFromCookieOrDB = function (user, order_type, appVersion) {
    window.$cookies.remove(appVersion + "-user-" + user.id + "-" + order_type + "-cart");
}


