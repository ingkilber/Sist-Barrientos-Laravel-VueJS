<?php

namespace App\Http\Controllers\API;

use App\Helpers\FileHandler;
use App\Http\Controllers\API\Sales\Traits\SaleHelper;
use App\Libraries\AllSettingFormat;
use App\Libraries\SmsHelper;
use App\Libraries\Permissions;
use App\Libraries\Email;
use App\Libraries\searchHelper;
use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\CashRegisterLog;
use App\Models\CustomerGroup;
use App\Models\EmailTemplate;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderItems;
use App\Models\Payments;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\ShortcutKey;
use App\Models\Tax;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\RestaurantTable;
use App\Models\SmsTemplate;

use App\Models\ShippingInformation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use PDF;
use Config;
use Milon\Barcode\DNS1D;

class SalesController extends Controller
{
    use SaleHelper, FileHandler;

    private $paymentController;

    public function __construct()
    {
        $this->paymentController = new PaymentController;
    }

    public function permissionCheck()
    {
        return new Permissions;
    }

    public function salesView()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);

        $paymentTypes = $this->paymentController->getData();
        $autoInvoice = $this->paymentController->getAutoInvoice();
        $customer = Customer::getCustomerDetails();

        $customerGroup = CustomerGroup::allData();
        $cashRegisterID = $this->getCashRegisterID();
        $salesReturnStatus = Setting::getSettingValue('sales_return_status')->setting_value;
        $salesType = Setting::getSaleOrReceivingType('sales_type');
        $invoiceData = $this->invoiceData();
        $userID = auth()->id();
        $currentBranch = Setting::currentBranch($userID);
        $holdOrders = $this->getHoldOrder();
        $restaurantTables = RestaurantTable::all();

        $defaultInvoiceTemplate = InvoiceTemplate::getDefaultTemplate();
        $bookedTables = Order::getBookedTables();
        $output = [
            'currentBranch' => $allSettings->getCurrentBranch(),
            'totalBranch' => $totalBranch,
            'currentCashRegister' => $cashRegisterID,
            'salesReturnStatus' => $salesReturnStatus,
            'salesType' => $salesType,
            'branches' => $getBranch,
            'autoInvoice' => $autoInvoice['autoInvoice'],
            'paymentTypes' => $paymentTypes,
            'customer' => $customer,
            'customerGroup' => $customerGroup,
            'invoicePrefix' => $invoiceData['prefix'],
            'invoiceSuffix' => $invoiceData['suffix'],
            'lastInvoiceNum' => $invoiceData['lastInvoiceNum'],
            'appName' => '',
            'isBranchSelected' => false,
            'product' => null,
            'shortcutKeyCollection' => null,
            'holdOrders' => $holdOrders,
            'defaultInvoiceTemplateForSales' => $defaultInvoiceTemplate['sales_invoice']['invoice_template'],
            'restaurantTables' => $restaurantTables,
            'bookedTables' => $bookedTables,
        ];


        if ($currentBranch != null) {
//            $product = $this->getProduct('sales', $currentBranch->setting_value);
            $output['isBranchSelected'] = true;
//            $output['product'] = null;
//            $output['shortcutKeyCollection'] = $product['shortcutKeyCollection'];
        }
        return view('sales.SalesIndex', $output);
    }

    public function invoiceData()
    {
        $invoicePrefix = Setting::getSettingValue('invoice_prefix')->setting_value;
        $invoiceSuffix = Setting::getSettingValue('invoice_suffix')->setting_value;
        $lastInvoiceNum = Setting::getSettingValue('last_invoice_number')->setting_value;

        return ['prefix' => $invoicePrefix, 'suffix' => $invoiceSuffix, 'lastInvoiceNum' => $lastInvoiceNum];
    }

    public function getRegisterAmount($id)
    {
        return CashRegisterLog::getRegisterAmount($id);
    }

    public function getCashRegisterID()
    {
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $currentBranchID = 0;

        if ($currentBranch) {
            $currentBranchID = $currentBranch->setting_value;
        }

        $cashRegisterID = CashRegisterLog::getRegistersLog($currentBranchID, $userID);

        if ($cashRegisterID) {
            return $cashRegisterID = CashRegister::getCashRegisters($cashRegisterID->cash_register_id);
        } else {
            return $cashRegisterID = null;
        }
    }

    public function purchaseView()
    {
        $allSettings = new AllSettingFormat;
        $BranchController = new BranchController;
        $getBranch = $BranchController->index();
        $totalBranch = sizeof($getBranch);

        $paymentTypes = $this->paymentController->getData();
        $autoInvoice = $this->paymentController->getAutoInvoice();
        $supplier = Supplier::all();
        $cashRegisterID = $this->getCashRegisterID();
        $receivingType = Setting::getSaleOrReceivingType('receiving_type');
        $invoiceData = $this->invoiceData();
        $userID = Auth::user()->id;
        $currentBranch = Setting::currentBranch($userID);
        $defaultInvoiceTemplate = InvoiceTemplate::getDefaultTemplate();
        $purchaseReturnStatus = Setting::getSettingValue('purchase_return_status')->setting_value;
        $output = [
            'currentBranch' => $allSettings->getCurrentBranch(),
            'totalBranch' => $totalBranch,
            'currentCashRegister' => $cashRegisterID,
            'receivingType' => $receivingType,
            'branches' => $getBranch,
            'paymentTypes' => $paymentTypes,
            'purchaseReturnStatus' => $purchaseReturnStatus,
            'autoInvoice' => $autoInvoice['autoInvoice'],
            'supplier' => $supplier,
            'invoicePrefix' => $invoiceData['prefix'],
            'invoiceSuffix' => $invoiceData['suffix'],
            'lastInvoiceNum' => $invoiceData['lastInvoiceNum'],
            'appName' => '',
            'isBranchSelected' => false,
            'product' => null,
            'shortcutKeyCollection' => null,
            'defaultInvoiceTemplateForReceives' => $defaultInvoiceTemplate['receive_invoice']['invoice_template']
        ];
        if ($currentBranch != null) {
//            $product = $this->getProduct('receiving', $currentBranch->setting_value);
            $output['isBranchSelected'] = true;
//            $output['product'] = $product['products'];
//            $output['shortcutKeyCollection'] = $product['shortcutKeyCollection'];
        }
        return view('receives.ReceivesIndex', $output);
    }

    public function getReturnProduct(Request $request)
    {
        $orderId = $request->orderId;
        $receivingType = $request->receivingType;
        $orderType = $receivingType ? 'receiving' : 'sales';

        $orderItems = Order::searchOrders($orderId, $orderType);

        foreach ($orderItems as $rowOrderItem) {

            $rowOrderItem->cart = OrderItems::getOrderItemsDetails($rowOrderItem->orderID);
            $getReturnProduct = Order::getReturnProduct($rowOrderItem->cart[0]['invoiceReturnId'], $orderType);


            $return_products = optional($getReturnProduct)->groupBy('variant_id')->map(function ($items) {
                $item = $items->first();
                $item->quantity = $items->sum('quantity');
                return $item;
            });

            $modified_cart = $rowOrderItem->cart->map(function ($cart) use ($return_products) {
                $return_product = $return_products->where('variant_id', $cart->variantID)->first();
                $cart->quantity = optional($cart)->quantity + optional($return_product)->quantity;
                return $cart;
            });


            //$rowOrderItem->cart = OrderItems::getAll(['invoice_id','price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);
            foreach ($modified_cart as $rowItem) {
                if ($rowItem->taxID) {
                    $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                } else {
                    $rowItem->productTaxPercentage = 0;
                }

                $rowItem->returnType = $rowOrderItem['return_type'];


                if ($rowItem->variantID != null) {
                    $firstVariant = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID);
                    $rowItem->variantTitle = $firstVariant ? $firstVariant->variant_title : "";

                    $firstProductTitle = Product::getFirst('title', 'id', $rowItem->productID);
                    $rowItem->productTitle = $firstProductTitle ? $firstProductTitle->title : "";
                }

                $rowItem->showItemCollapse = false;
                $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
            }

            if ($rowOrderItem->customer != null) {
                $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id'], 'id', $rowOrderItem->customer);
                $rowOrderItem->customer->customer_group_discount = 0;
            }
        }

        return $orderItems;
    }

    public function setSalesReturnsType(Request $request)
    {
        $salesReturnType = $request->salesOrReturnType;
        Setting::updateSetting('sales_return_status', $salesReturnType);
    }

    public function setPurchaseReturnsType(Request $request)
    {
        $purchaseReturnType = $request->purchaseOrReturnType;
        Setting::updateSetting('purchase_return_status', $purchaseReturnType);
    }

    public function getProductNew(Request $request)
    {
        $shortcutSettings = $this->getShortcutSettings();
        $options = $this->optionShaper($request);

        $products = Product::getAllProducts($options);
        $productVariants = Product::getProductVariantsList(
            $options['branchId'],
            $options['orderType'],
            $options['onlyInStockProducts'],
            $request->rowLimit ? $products : null
        );

        return [
            'products' => $products,
            'count' => $products->count(),
            'barcodeResultValue' => null,
            'shortcutSettings' => $shortcutSettings,
            'variants' => $productVariants,
            'total_products' => Product::query()->count('id')
        ];
    }


    public function getProduct($orderType, $currentBranch)
    {

        $outOfStock = 0;
        $shortcutSettings = $this->getShortcutSettings();

        $data = Product::index([
            'products.id as productID', 'products.title', 'products.taxable',
            'products.tax_type', 'products.tax_id', 'products.imageURL as productImage',
            'products.branch_id'
        ]);

        foreach ($data as $rowData) {

            if ($rowData->taxable == 0) {
                $rowData->taxPercentage = 0;
            } else {

                if ($rowData->tax_type == 'default') {

                    $branchTax = Branch::getFirst('*', 'id', $currentBranch);
                    if ($branchTax->taxable == 0) {
                        $rowData->taxPercentage = 0;
                    } else {

                        if ($branchTax->is_default == 0) {
                            $taxID = $branchTax->tax_id;
                        } else {
                            $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                        }

                        $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                    }
                } else {
                    $rowData->taxPercentage = Tax::getFirst('percentage', 'id', $rowData->tax_id)->percentage;
                }
            }

            $productVariant = ProductVariant::getProductVariant($rowData->productID, $orderType, $outOfStock);

            foreach ($productVariant as $rowProductVariant) {
                $rowProductVariant->attribute_values = explode(',', $rowProductVariant->attribute_values);
                $rowProductVariant->availableQuantity = OrderItems::availableQuantity($rowProductVariant->id);
            }

            $attribute_name = [];
            $attribute_id = ProductAttributeValue::attributeValues($rowData->productID);

            foreach ($attribute_id as $key => $rowAttributeId) {
                $attribute_name[$key] = ProductAttribute::getFirst('name', 'id', $rowAttributeId->attribute_id)->name;
            }

            //$rowData->variants = $productVariant;
            $rowData->attributeName = $attribute_name;
        }

        return [
            'products' => $data,
            'shortcutKeyCollection' => $shortcutSettings,
        ];
    }

    public function barCodeSearch($searchValueForBarCode, $orderType)
    {
        $barCodeSearch = ProductVariant::searchProduct($searchValueForBarCode, $orderType);

        if ($barCodeSearch) {
            $barCodeSearch->cartItemNote = '';
            $barCodeSearch->discount = 0;
            $barCodeSearch->quantity = 1;
            $barCodeSearch->showItemCollapse = false;
            $barCodeSearch->discountType = '%';
            $barCodeSearch->calculatedPrice = $barCodeSearch->price;

            if ($barCodeSearch->taxable == 0) {
                $barCodeSearch->productTaxPercentage = 0;
            } else {
                if ($barCodeSearch->tax_type == 'default') {
                    $branchTax = Branch::getFirst('is_default', 'id', $barCodeSearch->branch_id)->is_default;

                    if ($branchTax == 0) {
                        $taxID = Branch::getFirst('tax_id', 'id', $barCodeSearch->branch_id)->tax_id;
                    } else {
                        $taxID = Tax::getFirst('id', 'is_default', 1)->id;
                    }
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $taxID)->percentage;
                } else {
                    $barCodeSearch->productTaxPercentage = Tax::getFirst('percentage', 'id', $barCodeSearch->taxID)->percentage;
                }
            }
        }

        unset($barCodeSearch->tax_type);
        unset($barCodeSearch->taxable);
        return $barCodeSearch;
    }

    public function setBranch(Request $request)
    {
        $allSetting = new AllSettingFormat;
        $authID = Auth::user('id')->id;
        $branchID = $request->branchID;
        $orderType = $request->orderType;
        $currentBranch = $allSetting->getCurrentBranch();
//        $products = $this->getProduct($orderType, $branchID);
//        $products = $this->getProductNew($request);
        if ($currentBranch) {
            Setting::updateCurrentBranch($authID, $branchID);
        } else {
            Setting::store([
                'setting_name' => 'current_branch',
                'setting_value' => $branchID,
                'setting_type' => 'user',
                'user_id' => $authID,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
//        return ['products' => []];
    }

    public function updateVariantPurchasePrice($data)
    {
        $updatableData = [
            'purchase_price' => $data['price']
        ];
        ProductVariant::updateData($data['variantID'], $updatableData);
    }

    public function updateVariantSalesPrice($data)
    {
        $updatableData = [
            'selling_price' => $data['price']
        ];
        ProductVariant::updateData($data['variantID'], $updatableData);
    }

    public function salesStore(Request $request)
    {
        $requested_id = collect($request->cart)->pluck('productID');
        $cashRegister = $request->cashRagisterId;
        $allSettings = new AllSettingFormat;
        $userId = Auth::id();
        $userBranchId = Setting::getFirst('*', 'user_id', $userId)->setting_value;
        $date = Carbon::now()->toDateString();
        $orderType = $request->orderType;
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderStatus = $request->status;
        $createdBy = Auth::user()->id;
        $carts = $request->cart;
        $id = $request->customer ? $request->customer['id'] : null;
        $subTotal = $request->subTotal;
        $tax = $request->tax;
        $allDiscount = $request->discount; //percentage discount on entire sell
        $grandTotal = $request->grandTotal;
        $payment = $request->payments;
        $orderID = $request->orderID;
        $orderIdInternalTransfer = $request->orderIdInternalTransfer;
        $transferBranch = $request->transferBranch;
        $transferBranchName = $request->transferBranchName;
        $dueAmount = 0;
        $time = $request->time;
        $restaurantTableId = $request->tableId;
        $variantTitle = '';
        $message = [];
        $orderData1 = [];
        $allOrderData1 = [];
        $totalProductInThisInvoice = 0;
        $totalReturnedProduct = 0;
        $totalCartProduct = 0;
        $returnProductProfit = 0;


        /*Section for Return Order product in cart*/
        if ($request->salesOrReturnType == 'returns') {
            $orderItemProductCount = 0;
            //checking cart with discount
            foreach ($carts as $cart) if ($cart['orderType'] == 'sales') $orderItemProductCount++;

            $salingProfit = Order::getFirst(['id', 'profit'], 'invoice_id', $request->cart[0]['invoiceReturnId']);

            $returnInvoiceProfit = Order::getReturnProductProfit($carts[0]['invoiceReturnId']);

            if ($returnInvoiceProfit->profit == null) $returnInvoiceProfit->profit = 0;

            $productsSoldFirstCount = OrderItems::countRecord('order_id', $salingProfit->id);
        }
        /*Section for Return Order product in cart ends*/

        $totalCartProduct = (collect($carts)->sum('quantity')) * (-1);


        if ($grandTotal < 0) {

            $orderDetailsInformation = Order::getOrderInformation($carts[0]['invoiceReturnId'], $orderType);

            if (count($orderDetailsInformation) > 0) $returnProductProfit = $orderDetailsInformation[0]->profit; //checking profit while return product

            $totalProductInThisInvoice = (collect($orderDetailsInformation)->sum('quantity')) * (-1);

            $diffQuantity = $orderDetailsInformation
                ->whereIn('product_id', $requested_id)
                ->filter(function ($order_item) use ($request) {
                    $item = collect($request->cart)->first(function ($item) use ($order_item) {
                        return $item["productID"] == $order_item->product_id;
                    });
                    $order_item->quantity_difference = $item["quantity"] - $order_item->quantity;
                    return $item["quantity"] > $order_item->quantity;
                });

            $orderData1 = $orderDetailsInformation->whereNotIn('product_id', $requested_id);
            $allOrderData1 = collect($orderData1)->merge($diffQuantity)->map(function ($order) {
                $item["product_id"] = $order->product_id;
                $item["variant_id"] = $order->variant_id;
                if ($item["quantity"] = $order->quantity_difference == null) {
                    $item["quantity"] = $order->quantity * -1;
                } else {
                    $item["quantity"] = $order->quantity_difference;
                }
                return $item;
            });
        }


        $outOfStock = Setting::getSettingValue('out_of_stock_products')->setting_value;
        $checkAvailableQuantity = 'false';

        foreach ($request->cart as $cart) {
            $availableQuantityCheck = OrderItems::checkAvailableQuantity($cart['variantID']);

            if ($cart['orderType'] !== 'discount') {
                $outOfStockVariantTitle = $cart['variantTitle'] ? ' (' . $cart['variantTitle'] . ') ' : ' ' . $cart['variantTitle'] . ' ';
                if ($outOfStock == 1 && $request->orderType == 'sales' && $request->status == 'done' && $cart['quantity'] > $availableQuantityCheck && $cart['quantity'] > 0) {
                    $checkAvailableQuantity = 'true';
                    if ($availableQuantityCheck <= 0) {
                        array_push($message, $cart['productTitle'] . $outOfStockVariantTitle . trans('lang.is_out_of_stock') . ' ' . trans('lang.please_remove_from_cart'));
                    } else {
                        array_push($message, $cart['productTitle'] . $outOfStockVariantTitle . trans('lang.is_out_of_stock') . ' ' . trans('lang.available_quantity') . '' . $availableQuantityCheck . '.');
                    }
                }
            }


            if ($orderType == 'receiving'
                || $salesOrReceivingType == 'internal'
                || $salesOrReceivingType == 'internal-transfer') {
                $this->updateVariantPurchasePrice($cart);
            } else {
                $this->updateVariantSalesPrice($cart);
            }
        }

        if ($checkAvailableQuantity == 'true') {

            $response = [
                'checkAvailableQuantity' => $checkAvailableQuantity,
                'message' => $message
            ];
            return response()->json($response, 200);
        }
        $lastInvoiceNumber = Setting::getSettingValue('last_invoice_number')->setting_value;
        $purchaseLastInvoiceNumber = Setting::getSettingValue('purchase_last_invoice_number')->setting_value;

        $profit = $request->profit == null ? 0 : $request->profit;

        $invoiceFixes = $allSettings->getInvoiceFixes();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;
        } else {
            $cashRegisterID = null;
        }

        if ($allDiscount == null) {
            $allDiscount = 0;
        }

        if (!empty($payment)) {
            foreach ($payment as $key => $value) {
                if ($value['paymentType'] == 'credit') {
                    $dueAmount = floatval($value['paid']);
                }
            }
        }
        if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID) || ($orderStatus == 'hold' && !$orderID)) {
            $orderData = array();
            $orderData['date'] = $date;
            $orderData['sales_note'] = $request->salesNote;
            $orderData['all_discount'] = $allDiscount;
            $orderData['sub_total'] = $subTotal;
            $orderData['total_tax'] = $tax;
            $orderData['due_amount'] = $dueAmount;
            $orderData['total'] = $grandTotal;
            $orderData['type'] = $salesOrReceivingType;
            $orderData['profit'] = $profit;
            $orderData['status'] = $orderStatus;
            $orderData['table_id'] = $restaurantTableId;

            if ($orderData['total'] >= 0 || $salesOrReceivingType == "internal-transfer") {
                $orderData['order_type'] = $orderType;
            } else {
                //return product section

                $getReturnProduct = Order::getReturnProduct($carts[0]['invoiceReturnId'], $orderType);

                $totalReturnedProduct = collect($getReturnProduct)->sum('quantity');

                if (count($allOrderData1) > 0) {
                    if ($orderType == 'receiving') {
                        $totalProductInThisInvoice = $totalProductInThisInvoice * -1;
                    }
                    if ($totalProductInThisInvoice - $totalReturnedProduct == $totalCartProduct) {
                        $returnType = 'fully';
                    } else {
                        $returnType = 'partial';
                    }
                } else {
                    $returnType = 'fully';
                }

                $orderData['order_type'] = $orderType;

                $orderData['type'] = $salesOrReceivingType;
                $orderData['returned_invoice'] = $carts[0]['invoiceReturnId'];

                /*profit calculation for return product*/
                if ($request->salesOrReturnType == 'returns') {
                    if ($returnType == 'fully') {
                        $orderData['profit'] = ($returnProductProfit - $returnInvoiceProfit->profit) * (-1);
                    } else {
                        $availableProfit = $salingProfit->profit - $returnInvoiceProfit->profit;
                        $orderData['profit'] = $availableProfit / $productsSoldFirstCount * (-1);
                    }
                } else {
                    //for purchase return
                    $orderData['profit'] = 0;
                }
            }

            if ($salesOrReceivingType == 'internal' || $salesOrReceivingType == 'internal-transfer') {
                $orderData['transfer_branch_id'] = $transferBranch;
            }

            $orderType === 'sales' ? $orderData['customer_id'] = $id : $orderData['supplier_id'] = $id;

            $orderData['created_by'] = $createdBy;
            $orderData['branch_id'] = $userBranchId;
            $orderData['created_at'] = Carbon::parse($time);

            if ($orderData['table_id']) {
                RestaurantTable::updateTableStatus($orderData['table_id'], 'booked');
            }


            $orderLastId = Order::store($orderData);


            if ($salesOrReceivingType == 'internal-transfer') {
                $orderIdInternalTransfer = $this->insertInternalTransfer($orderData, $transferBranch, $userBranchId, $invoiceFixes, $purchaseLastInvoiceNumber);
            }

            if ($orderLastId->total < 0 && $orderLastId->status == 'done') {
                Order::updateOrderType($carts[0]['invoiceReturnId'], $returnType, $orderType);
            }


            if ($request->shippingAreaId != null && $orderStatus == 'done') {
                $setShippingInfo = $this->storeShippingInformation($request, $orderLastId->id);
            }

            $orderID = $orderLastId->id;

            if ($orderLastId->order_type == 'sales') {
                Order::updateData($orderID, ['invoice_id' => $invoiceFixes['prefix'] . $lastInvoiceNumber . $invoiceFixes['suffix']]);
                $lastInvoiceNumber += 1;

                $lastUpdatedInvoice = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;
                if ($lastInvoiceNumber > $lastUpdatedInvoice) {
                    Setting::updateSetting('last_invoice_number', $lastInvoiceNumber);
                }
            } else {
                Order::updateData($orderID, ['invoice_id' => $invoiceFixes['purchasePrefix'] . $purchaseLastInvoiceNumber . $invoiceFixes['purchaseSuffix']]);
                $purchaseLastInvoiceNumber += 1;

                $purchaseLastUpdatedInvoice = Setting::where('setting_name', 'purchase_last_invoice_number')->first()->setting_value;
                if ($purchaseLastInvoiceNumber > $purchaseLastUpdatedInvoice) {
                    Setting::updateSetting('purchase_last_invoice_number', $purchaseLastInvoiceNumber);
                }
            }

        } else {
            $orders = array();
            $orders['date'] = $date;
            $orders['sales_note'] = $request->salesNote;
            $orders['order_type'] = $orderType;
            $orders['all_discount'] = $allDiscount;
            $orders['sub_total'] = $subTotal;
            $orders['total_tax'] = $tax;
            $orders['total'] = $grandTotal;
            $orders['type'] = $salesOrReceivingType;
            $orders['status'] = $orderStatus;
            $orders['table_id'] = $restaurantTableId;
            $orders['due_amount'] = $dueAmount;

            if ($orders['total'] < 0) {
                $getReturnProduct = Order::getReturnProduct($carts[0]['invoiceReturnId'], $orderType);
                $totalReturnedProduct = collect($getReturnProduct)->sum('quantity');

                if ($orderType == 'receiving') {
                    $totalProductInThisInvoice = $totalProductInThisInvoice * -1;
                }

                Order::updateOrderType(
                    $carts[0]['invoiceReturnId'],
                    $totalProductInThisInvoice - $totalReturnedProduct == $totalCartProduct ? 'fully' : 'partial',
                    $orderType
                );

                $orders['order_type'] = $orderType;
            }

            if ($salesOrReceivingType == 'internal') {
                $orders['transfer_branch_id'] = $transferBranch;
            }


            $orderType == 'sales' ? $orders['customer_id'] = $id : $orders['supplier_id'] = $id;

            $orders['created_by'] = $createdBy;

            if ($orders['table_id']) {
                RestaurantTable::updateTableStatus($orders['table_id'], 'available');
            }

            Order::updateData($request->orderID, $orders);

            if ($request->shippingAreaId != null && $orderStatus == 'done') {
                $setShippingInfo = $this->storeShippingInformation($request, $request->orderID);
            }
            if ($salesOrReceivingType == 'internal-transfer') {
                $orders['order_type'] = 'receiving';
                Order::updateData($request->orderIdInternalTransfer, $orders);
            }
        }
        $orderItems = [];
        $orderItemsInternalTransfer = [];

        foreach ($carts as $cart) {
            $orderType == 'sales' ? $quantity = -$cart['quantity'] : $quantity = $cart['quantity'];

            if (!array_key_exists('discount', $cart) || $cart['discount'] == null) {
                $cart['discount'] = 0;
            }

            array_push($orderItems, [
                'product_id' => $cart['productID'],
                'variant_id' => $cart['variantID'],
                'type' => $cart['orderType'],
                'quantity' => $quantity,
                'price' => $cart['price'],
                'discount' => $cart['discount'],
                'sub_total' => $cart["calculatedPrice"],
                'tax_id' => $cart['taxID'],
                'order_id' => $orderID,
                'note' => $cart['cartItemNote'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // for update isNotify product_variant
            if (isset($cart['variantID'])) {
                ProductVariant::removeBranchFromIsNotify($cart['variantID'], $request->branchId);
            }

            if ($salesOrReceivingType == 'internal-transfer') {
                $quantity = $cart['quantity'];
                array_push($orderItemsInternalTransfer, [
                    'product_id' => $cart['productID'],
                    'variant_id' => $cart['variantID'],
                    'type' => 'receiving',
                    'quantity' => $quantity,
                    'price' => $cart['price'],
                    'discount' => $cart['discount'],
                    'sub_total' => $cart["calculatedPrice"],
                    'tax_id' => $cart['taxID'],
                    'order_id' => $orderIdInternalTransfer,
                    'note' => $cart['cartItemNote'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if ($orderStatus != 'hold') {
            if (sizeof($payment) > 0) {
                $paymentArray = [];
                $paymentArrayInternal = [];

                foreach ($payment as $rowPayment) {
                    array_push($paymentArray, ['date' => $date, 'paid' => $rowPayment['paid'], 'exchange' => $rowPayment['exchange'], 'payment_method' => $rowPayment['paymentID'], 'options' => serialize($rowPayment['options']), 'order_id' => $orderID, 'cash_register_id' => $cashRegisterID, 'is_active' => $rowPayment['is_active'], 'created_at' => $rowPayment['PaymentTime']]);
                }
                foreach ($payment as $rowPayment) {
                    array_push($paymentArrayInternal, ['date' => $date, 'paid' => $rowPayment['paid'], 'exchange' => $rowPayment['exchange'], 'payment_method' => $rowPayment['paymentID'], 'options' => serialize($rowPayment['options']), 'order_id' => $orderIdInternalTransfer, 'cash_register_id' => $cashRegisterID, 'is_active' => $rowPayment['is_active'], 'created_at' => $rowPayment['PaymentTime']]);
                }

                if (($orderStatus == 'done' && !$orderID) || ($orderStatus == 'pending' && !$orderID)) {
                    Payments::insertData($paymentArray);
                } else {
                    Payments::deleteRecord('order_id', $request->orderID);
                    Payments::insertData($paymentArray);
                    if ($salesOrReceivingType == 'internal-transfer') {
                        Payments::deleteRecord('order_id', $orderIdInternalTransfer);
                        Payments::insertData($paymentArrayInternal);
                    }
                }
            }
        }

        if ($orderType == 'sales') {
            $invoiceId = $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'];
        } else $invoiceId = $invoiceFixes['purchasePrefix'] . $invoiceFixes['purchaseLastInvoiceNumber'] . $invoiceFixes['purchaseSuffix'];

        if (($orderStatus == 'done' && $orderID == null)) {
            OrderItems::insertData($orderItems);
            $response = [
                'invoiceID' => $invoiceId,
            ];
            return $response;
        } else if (($orderStatus == 'pending' && $orderID == null)) {

            OrderItems::insertData($orderItems);
            $response = [
                'orderID' => $orderID,
                'orderIdInternalTransfer' => $orderIdInternalTransfer
            ];

            return $response;

        } else {
            OrderItems::deleteRecord('order_id', $request->orderID);
            OrderItems::insertData($orderItems);
            if ($salesOrReceivingType == 'internal-transfer') {
                OrderItems::insertData($orderItemsInternalTransfer);
            }
            if ($orderType == 'sales') {
                $invoiceId = $invoiceFixes['prefix'] . $invoiceFixes['lastInvoiceNumber'] . $invoiceFixes['suffix'];
                $lastInvoiceId = Setting::getSettingValue('last_invoice_number')->setting_value;
            } else {
                $invoiceId = $invoiceFixes['purchasePrefix'] . $invoiceFixes['purchaseLastInvoiceNumber'] . $invoiceFixes['purchaseSuffix'];
                $lastInvoiceId = Setting::getSettingValue('purchase_last_invoice_number')->setting_value;
            }

            if ($orderStatus == 'done') {
                // send customer invoice
                try {
                    $invoiceTemplateEmail = new InvoiceTemplateController();
                    $invoiceTemplateData = $invoiceTemplateEmail->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegister, $orderType, 'email');

                    $autoEmailReceive = Setting::getSettingValue('auto_email_receive')->setting_value;
                    $orderDetails = Order::orderDetails($orderID, $cashRegister);
                    // Sms receive to customer
                    $autoSmsReceive = Setting::getSettingValue('sms_recive_to_customer')->setting_value;


                    if ($orderDetails->customer_id) {
                        $orderCustomer = Customer::getOne($orderDetails->customer_id);

                        if ($autoSmsReceive == 1 && $orderCustomer->phone_number) {

                            $this->autoCustomerSmsSend($orderCustomer->first_name, $orderCustomer->last_name, $orderCustomer->phone_number, $orderDetails->invoice_id, $orderDetails->total);

                        }

                        if ($autoEmailReceive == 1 && $orderCustomer->email) {

                            $content = EmailTemplate::query()->select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_invoice')->first();

                            $subject = $content->template_subject;
                            $text = $content->custom_content ?  $content->custom_content : $content->default_content;

                            $mailText = str_replace('{first_name}', $orderCustomer->first_name, str_replace('{invoice_id}', $orderDetails->invoice_id, str_replace('{app_name}', Config::get('app_name'), $text)));

                            $this->sendPdf($invoiceTemplateData['data'], $orderID, $cashRegister, $mailText, $orderCustomer->email, $subject);

                        }
                    }
                } catch (\Exception $e) {
                    return $e;
                }

                $invoiceTemplate = new InvoiceTemplateController();

                $templateData = $invoiceTemplate->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegister, $orderType, 'receipt');


                $response = [
                    'orderID' => $orderID,
                    'orderIdInternalTransfer' => $orderIdInternalTransfer,
                    'invoiceID' => $invoiceId,
                    'message' => Lang::get('lang.payment_done_successfully'),
                    'invoiceTemplate' => $templateData,
                    'lastInvoiceId' => $lastInvoiceId,
                ];

                return $response;
            } else {
                $response = [
                    'orderID' => $orderID,
                    'orderIdInternalTransfer' => $orderIdInternalTransfer,
                    'invoiceID' => $invoiceId,
                    'message' => Lang::get('lang.payment_done_successfully'),
                    'lastInvoiceId' => $lastInvoiceId,
                ];

                return $response;
            }
        }
    }

    // auto sms

    protected function autoCustomerSmsSend($firstName, $lastName, $phoneNumber, $invoiceId, $total)
    {
        try {
            // Sms Template
            $smsText = SmsTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_sms')->first();
            if ($smsText->custom_content) {
                $text = $smsText->custom_content;
            } else {
                $text = $smsText->default_content;
            }
            $sendSmsText = str_replace('{first_name}', $firstName, str_replace('{last_name}', $lastName, str_replace('{invoice_id}', $invoiceId, str_replace('{total}', $total, str_replace('{app_name}', Config::get('app_name'), $text)))));
            $this->autoSmsSend($phoneNumber, $sendSmsText);
        } catch (\Exception $e) {
            $response = [
                'message' => Lang::get('lang.phone_number_wrong')
            ];

            return response()->json($response, 201);
        }
    }

    // Button sms

    public function customerSendSms(Request $request)
    {
        try {
            if ($request->id && $request->phone_number) {

                $smsText = SmsTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_sms')->first();
                if ($smsText->custom_content) {
                    $text = $smsText->custom_content;
                } else {
                    $text = $smsText->default_content;
                }
                $sendSmsText = str_replace('{first_name}', $request->first_name, str_replace('{last_name}', $request->last_name, str_replace('{invoice_id}', $request->invoiceId, str_replace('{total}', $request->subTotal, str_replace('{app_name}', Config::get('app_name'), $text)))));

                $this->autoSmsSend($request->phone_number, $sendSmsText);

                $response = [
                    'message' => Lang::get('lang.successfully_sms')
                ];

                return response()->json($response, 200);

            }
        } catch (\Exception $e) {
            $response = [
                'message' => Lang::get('lang.phone_number_wrong')
            ];

            return response()->json($response, 201);
        }


    }

    // auto sms receive customer
    protected function autoSmsSend($phone_number, $sendSmsText)
    {
        $send = SmsHelper::sendSms($phone_number, $sendSmsText);
        return $send;
    }

    public function saveDueAmount(Request $request)
    {

        $data = $request->cartItemsToStore;

        $orderId = $data['rowData']['id'];
        $paymentType = $data['paymentType'];
        $date = Carbon::now()->toDateString();
        $payments = $data['payments'];
        $cashRegisterID = null;
        $output = null;

        $allSettings = new AllSettingFormat;
        $userId = Auth::id();

        if ($allSettings->getCurrentBranch()->is_cash_register == 1) {
            $cashRegisterID = $this->getCashRegisterID()->id;
        } else {
            $cashRegisterID = null;
        }

        $deleteRow = Payments::destroyByOrderAndType($orderId, $paymentType);

        if (isset($payments)) {
            $paymentArray = [];
            $due = 0;
            foreach ($payments as $rowPayment) {
                array_push(
                    $paymentArray,
                    [
                        'date' => $date,
                        'paid' => $rowPayment['paid'],
                        'exchange' => $rowPayment['exchange'],
                        'payment_method' => $rowPayment['paymentID'],
                        'options' => serialize($rowPayment['options']),
                        'order_id' => $orderId,
                        'cash_register_id' => $cashRegisterID,
                        'created_at' => $rowPayment['PaymentTime']
                    ]
                );

                if ($rowPayment['paymentType'] == 'credit') {
                    $due = $rowPayment['paid'];
                }
            }
            $updateData = [
                'due_amount' => $due
            ];
            Order::updateData($orderId, $updateData);
            if (isset($paymentArray)) {
                $output = Payments::insertData($paymentArray);
            }
        }

        if ($output) {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.payment_done_successfully')
            ];
        } else {
            return [
                'orderID' => $orderId,
                'message' => Lang::get('lang.something_went_wrong')
            ];
        }
    }

    public function salesCancel(Request $request)
    {
        $orderId = $request->orderID;
        $orderIdInternalTransfer = $request->orderIdInternalTransfer;

        if (Order::checkExists('id', $orderId)) {
            Order::updateData($orderId, ['status' => 'cancelled']);
        }
        if ($orderIdInternalTransfer != null) {
            if (Order::checkExists('id', $orderIdInternalTransfer)) {
                Order::updateData($orderIdInternalTransfer, ['status' => 'cancelled']);
            }
        }
    }

    public function getPaymentsAndDetails(Request $request)
    {
        $orderId = $request->orderID;
        $payments = [];

        if ($orderId) {
            $payments = Payments::getAll('*', 'order_id', $orderId);
        }

        return $payments;
    }

    public function customerList(Request $request)
    {
        $searchValue = $request->customerSearchValue;

        if ($request->orderType == 'sales') {

            return Customer::customerData($searchValue);
        } else {
            return Supplier::supplierData($searchValue);
        }
    }

    public function getHoldOrder()
    {
        $orderHoldItems = Order::getHoldOrders();

        //check if it return empty
        if (count($orderHoldItems) > 0) {
            foreach ($orderHoldItems as $rowOrderItem) {

                $allOrderItems = OrderItems::getAll(['price', 'discount', 'product_id as productID', 'type as orderType', 'tax_id as taxID', 'quantity', 'variant_id as variantID', 'note as cartItemNote'], 'order_id', $rowOrderItem->orderID);

                //check if it return empty
                if (count($allOrderItems) > 0) {
                    $rowOrderItem->cart = $allOrderItems;
                    foreach ($rowOrderItem->cart as $rowItem) {

                        if ($rowItem->taxID) {
                            $rowItem->productTaxPercentage = Tax::getFirst('percentage', 'id', $rowItem->taxID)->percentage;
                        } else {
                            $rowItem->productTaxPercentage = 0;
                        }

                        if ($rowItem->variantID != null) {

                            $rowItem->variantTitle = ProductVariant::getFirst('variant_title', 'id', $rowItem->variantID)->variant_title;
                            $rowItem->productTitle = Product::getFirst('title', 'id', $rowItem->productID)->title;
                        }

                        $rowItem->quantity = abs($rowItem->quantity);
                        $rowItem->showItemCollapse = false;
                        $rowItem->calculatedPrice = $rowItem->quantity * $rowItem->price;
                    }

                    //time as per settings
                    $rowOrderItem->time = Carbon::parse($rowOrderItem->date)->format('H:i:s');

                    if ($rowOrderItem->customer != null) {
                        $rowOrderItem->customer = Customer::getFirst(['first_name', 'last_name', 'email', 'id'], 'id', $rowOrderItem->customer);
                        $rowOrderItem->customer->customer_group_discount = 0;
                    }
                }
            }
        }

        return $orderHoldItems;
    }

    public function sendPdf($templateData, $orderID, $cashRegister, $mailText, $email, $subject)
    {
        try {

            $allSettingFormat = new AllSettingFormat();
            $order = $this->formatOrdersDetails($orderID, $cashRegister);
            $order->due = $allSettingFormat->getCurrencySeparator($order->due);
            $orderItems = $this->formatOrdersItems($orderID);
            $appName = Config::get('app_name');
            $invoiceLogo = Config::get('invoiceLogo');
            $fileNameToStore = 'Gain-' . $order->invoice_id . '.pdf';

            $pdf = PDF::loadView('invoice.invoiceTemplate',
                compact('templateData', 'orderItems', 'order', 'appName', 'invoiceLogo')
            );

            $content = $pdf->download()->getOriginalContent();
            Storage::put('public/pdf/'.$fileNameToStore,$content);

            $emailSend = new Email;
            $emailSend->sendEmail($mailText, $email, $subject, $fileNameToStore);

            unlink(public_path('/storage/pdf/' . $fileNameToStore));

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function formatOrdersDetails($orderID, $cashRegister)
    {

        $orderDetails = Order::getInvoiceData($orderID, $cashRegister);

        $allSettingFormat = new AllSettingFormat();
        $orderDetails->due = $orderDetails->total - $orderDetails->paid;

        $orderDetails->paid = $allSettingFormat->getCurrencySeparator($orderDetails->paid);
        $orderDetails->total = $allSettingFormat->getCurrencySeparator($orderDetails->total);
        $orderDetails->sub_total = $allSettingFormat->getCurrencySeparator($orderDetails->sub_total);
        $orderDetails->change = $allSettingFormat->getCurrencySeparator($orderDetails->change);
        $orderDetails->date = $allSettingFormat->getDate($orderDetails->date);


        if ($orderDetails->change == null) {
            $orderDetails->change = 0;
        }

        return $orderDetails;
    }

    public static function formatOrdersItems($orderID)
    {
        $orderItems = OrderItems::getOrderDetails($orderID, true);
        $allSettingFormat = new AllSettingFormat();
        foreach ($orderItems as $item) {
            if ($item->type == 'discount') {
                $item->price = null;
                $item->quantity = null;
                $item->discount = null;
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            } else {
                $item->discount = $item->discount . '%';
                $item->price = $allSettingFormat->getCurrencySeparator($item->price);
                $item->total = $allSettingFormat->getCurrencySeparator($item->sub_total);
            }
        }

        return $orderItems;
    }

    public function setSalesReceivingType(Request $request)
    {
        $salesOrReceivingType = $request->salesOrReceivingType;
        $orderType = $request->orderType;
        Setting::saveSalesOrReceivingType($salesOrReceivingType, $orderType);
    }

    public function saleListDelete($id)
    {
        $delete = Order::salesListDelete($id);
        if ($delete == 0) {
            $response = [
                'message' => Lang::get('lang.sales_list_small') . ' ' . Lang::get('lang.successfully_deleted')
            ];

            return response()->json($response, 200);
        }
    }

    public static function saleListUpdate(Request $request, $id)
    {

        $date = Carbon::parse($request->editedSalesDate)->format('Y-m-d');
        $shippingStatus = $request->shippingStatus;
        $update = Order::where('id', '=', $id)
            ->update(['date' => $date]);
        $updateShipping = ShippingInformation::where('order_id', '=', $id)
            ->update(['status' => $shippingStatus]);

        if ($update == 1 && $updateShipping == 1) {
            $response = [
                'message' => Lang::get('lang.sales_date') . ' ' . Lang::get('lang.successfully_updated')
            ];

            return response()->json($response, 200);
        }
    }

    public function offlineSalesStore(Request $request)
    {
        $numberOfOrdersPlaced = count($request->all());
        $count = 0;
        //DB::beginTransaction();
        foreach ($request->all() as $singleOrder) {

            $dueAmount = 0;
            $customerId = 0;

            if ($singleOrder['isCashRegisterBranch'] == true) {
                $cashRegisterID = $singleOrder['cashRagisterId'];
            } else $cashRegisterID = null;

            //profit
            if ($singleOrder['profit'] == null) $profit = 0;
            else $profit = $singleOrder['profit'];

            //discount
            $allDiscount = 0;

            if (array_key_exists('discount', $singleOrder) && $singleOrder['discount'] != null) {
                $allDiscount = $singleOrder['discount'];
            }

            //due
            if ($singleOrder['status'] == 'done') {
                if (!empty($singleOrder['payments'])) {
                    foreach ($singleOrder['payments'] as $key => $value) {
                        if ($value['paymentType'] == 'credit') {
                            $dueAmount = floatval($value['paid']);
                        }
                    }
                }
            }
            $orderData = array();

            //customer id / supplier id
            if ($singleOrder['orderType'] == 'sales') {
                if (array_key_exists('customer', $singleOrder) && $singleOrder['customer'] != null) {
                    if (array_key_exists('id', $singleOrder['customer'])) {
                        $orderData['customer_id'] = $singleOrder['customer']['id'];
                        $customerId = $orderData['customer_id'];
                    } else {
                        $orderData['customer_id'] = Customer::getInsertedId($singleOrder['customer']);
                        $customerId = $orderData['customer_id'];
                    }
                } else {
                    $orderData['customer_id'] = null;
                }
            } else {
                if (array_key_exists('customer', $singleOrder) && $singleOrder['customer'] != null) {
                    if (array_key_exists('id', $singleOrder['customer'])) {
                        $orderData['supplier_id'] = $singleOrder['customer']['id'];
                    } else {
                        $orderData['supplier_id'] = Supplier::getInsertedId($singleOrder['customer']);
                    }
                } else {
                    $orderData['supplier_id'] = null;
                }
            }
            if (array_key_exists('transferBranch', $singleOrder)) $orderData['transfer_branch_id'] = $singleOrder['transferBranch'];

            $orderData['date'] = Carbon::parse($singleOrder['date']);
            $orderData['order_type'] = $singleOrder['orderType'];
            $orderData['sales_note'] = $singleOrder['salesNote'];
            $orderData['sub_total'] = $singleOrder['subTotal'];
            $orderData['total_tax'] = $singleOrder['tax'];
            $orderData['due_amount'] = $dueAmount;
            $orderData['total'] = $singleOrder['grandTotal'];
            $orderData['type'] = $singleOrder['salesOrReceivingType'];
            $orderData['profit'] = $profit;
            $orderData['status'] = $singleOrder['status'];
            $orderData['all_discount'] = $allDiscount;
            $orderData['table_id'] = $singleOrder['tableId'];


            if (array_key_exists('selectedBranchID', $singleOrder)) $orderData['branch_id'] = $singleOrder['selectedBranchID'];
            if (array_key_exists('invoice_id', $singleOrder)) $orderData['invoice_id'] = $singleOrder['invoice_id'];
            if (isset($singleOrder['invoice_id'])) $invoiceIdExistOrNot = Order::getIdOfExisted('invoice_id', $singleOrder['invoice_id']);

            $orderData['created_by'] = $singleOrder['createdBy'];
            $orderData['created_at'] = Carbon::parse($singleOrder['time']);


            if ($singleOrder['orderID'] != null) {
                Order::updateData($singleOrder['orderID'], $orderData);
                $orderID = $singleOrder['orderID'];
            } else {
                $orderLastId = Order::store($orderData);
                $orderID = $orderLastId->id;
            }

            $lastUpdatedInvoice = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;

            if (empty($invoiceIdExistOrNot) && array_key_exists('current_invoice_number', $singleOrder)) {
                if ($singleOrder['current_invoice_number'] > $lastUpdatedInvoice) {
                    Setting::updateSetting('last_invoice_number', $singleOrder['current_invoice_number']);
                }
            }

            $orderItems = [];
            //cart data insert in order_items
            foreach ($singleOrder['cart'] as $cart) {

                if ($singleOrder['orderType'] == 'sales') $quantity = -$cart['quantity'];
                else $quantity = $cart['quantity'];

                if ($singleOrder['orderType'] == 'receiving'
                    || $singleOrder['salesOrReceivingType'] == 'internal'
                    || $singleOrder['salesOrReceivingType'] == 'internal-transfer') {
                    $this->updateVariantPurchasePrice($cart);
                } else {
                    $this->updateVariantSalesPrice($cart);
                }
                if (!array_key_exists('discount', $cart) || $cart['discount'] == null) $cart['discount'] = 0;

                array_push($orderItems, [
                    'product_id' => $cart['productID'],
                    'variant_id' => $cart['variantID'],
                    'type' => $cart['orderType'],
                    'quantity' => $quantity,
                    'price' => $cart['price'],
                    'discount' => $cart['discount'],
                    'sub_total' => $cart["calculatedPrice"],
                    'tax_id' => $cart['taxID'],
                    'order_id' => $orderID,
                    'note' => $cart['cartItemNote'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // payment items insert in payment table
            if ($singleOrder['status'] == 'done') {
                if (sizeof($singleOrder['payments']) > 0) {
                    $paymentArray = [];

                    foreach ($singleOrder['payments'] as $rowPayment) {
                        array_push($paymentArray, [
                            'date' => Carbon::parse($singleOrder['date']),
                            'paid' => $rowPayment['paid'],
                            'exchange' => $rowPayment['exchange'],
                            'payment_method' => $rowPayment['paymentID'],
                            'options' => serialize($rowPayment['options']),
                            'order_id' => $orderID,
                            'cash_register_id' => $cashRegisterID,
                            'is_active' => $rowPayment['is_active'],
                            'created_at' => $rowPayment['PaymentTime']
                        ]);
                    }

                    Payments::insertData($paymentArray);
                }
            }
            if ($singleOrder['status'] == 'done' || $singleOrder['status'] == 'cancelled') {
                if ($singleOrder['orderID'] != null) OrderItems::deleteRecord('order_id', $singleOrder['orderID']);
                OrderItems::insertData($orderItems);

                //Send email and generate invoice
                try {
                    $salesOrReceivingType = $singleOrder['salesOrReceivingType'];
                    $transferBranchName = $request->transferBranchName;

                    $invoiceTemplateEmail = new InvoiceTemplateController();
                    $invoiceTemplateData = $invoiceTemplateEmail->getInvoiceTemplateToPrint($orderID, $salesOrReceivingType, $transferBranchName, $cashRegisterID, $singleOrder['orderType'], 'email');

                    $autoEmailReceive = Setting::getSettingValue('auto_email_receive')->setting_value;

                    if ($customerId) {
                        $orderCustomer = Customer::getOne($customerId);

                        if ($autoEmailReceive == 1 && $orderCustomer->email) {

                            $content = EmailTemplate::select('template_subject', 'default_content', 'custom_content')->where('template_type', 'pos_invoice')->first();
                            $subject = $content->template_subject;

                            if ($content->custom_content) $text = $content->custom_content;
                            else $text = $content->default_content;

                            $mailText = str_replace('{first_name}', $orderCustomer->first_name, str_replace('{invoice_id}', $singleOrder['invoice_id'], str_replace('{app_name}', Config::get('app_name'), $text)));


                            $this->sendPdf($invoiceTemplateData['data'], $orderID, $cashRegisterID, $mailText, $orderCustomer->email, $subject);
                        }
                    }
                } catch (\Exception $e) {
                }
                $count++;
            } elseif ($singleOrder['status'] == 'hold') {
                if ($singleOrder['orderID'] != null) OrderItems::deleteRecord('order_id', $singleOrder['orderID']);
                OrderItems::insertData($orderItems);
                $count++;
            } else {
                $count--;
            }
        }

        $lastInvoiceNumber = Setting::where('setting_name', 'last_invoice_number')->first()->setting_value;

        if ($numberOfOrdersPlaced == $count) {
            //DB::commit();
            $response = [
                'message' => Lang::get('lang.sync_complete_your_all_sales_now_up_to_date'),
                'lastInvoiceNumber' => $lastInvoiceNumber
            ];
            return response()->json($response, 201);
        } else {
            //DB::rollback();
            $response = [
                'message' => Lang::get('lang.something_went_wrong'),
            ];
            return response()->json($response, 400);
        }
    }

    public function salesListGetData(Request $request, $id)
    {
        if ($request->columnKey) $columnName = $request->columnKey;
        if ($request->rowLimit) $limit = $request->rowLimit;
        $filtersData = $request->filtersData;
        $searchValue = searchHelper::inputSearch($request->searchValue);
        $requestType = $request->reqType;
        $due = OrderItems::salesListItems($id, $filtersData, $searchValue, $request->columnSortedBy, $limit, $request->rowOffset, $columnName, $requestType);

        if (empty($requestType)) $dueData = $due['data'];
        else $dueData = $due;

        if (empty($requestType)) {
            $dueItems = $this->calculateDues($dueData);
            $arrayCount = $dueItems['count'];
            $totalCount = count($due['allData']);
            $dueData[$arrayCount] = [
                'invoice_id' => Lang::get('lang.total'),
                'item_purchased' => $dueItems['netItem'],
                'tax' => $dueItems['netTax'],
                'discount' => $dueItems['discount'],
                'total' => $dueItems['netTotal'],
                'due_amount' => $dueItems['netDueAmount'],
            ];
            $grandCalculation = $this->calculateDues($due['allData']);
            $dueData[$arrayCount + 1] = [
                'invoice_id' => Lang::get('lang.grand_total'),
                'item_purchased' => $grandCalculation['netItem'],
                'tax' => $grandCalculation['netTax'],
                'discount' => $grandCalculation['discount'],
                'total' => $grandCalculation['netTotal'],
                'due_amount' => $dueItems['netDueAmount']
            ];

            return ['datarows' => $dueData, 'count' => $totalCount];

        } else {

            $this->calculateDues($dueData);
            return ['datarows' => $dueData];
        }
    }

    public function calculateDues($dueData)
    {
        $netTotal = 0;
        $netTax = 0;
        $netItem = 0;
        $arrayCount = 0;
        $netDiscount = 0;
        $netDueAmount = 0;

        foreach ($dueData as $rowData) {
            if ($rowData->type == 'customer') {
                $rowData->type = Lang::get('lang.customer');
            } else {
                $rowData->type = Lang::get('lang.internal_sales');
                $rowData->customer = $rowData->transfer_branch_name;
            }
            if ($rowData->due_amount > 0) {
                $rowData->payment_status = Lang::get('lang.due');;
            } else {
                $rowData->payment_status = Lang::get('lang.paid');
            }
            if ($rowData->customer == '') $rowData->customer = Lang::get('lang.walk_in_customer');
            $netTax += $rowData->tax;
            $netTotal += $rowData->total;
            $netItem += $rowData->item_purchased;
            $netDiscount += $rowData->discount;
            $netDueAmount += $rowData->due_amount;
            $arrayCount++;
        }

        return [
            'netTotal' => $netTotal,
            'netTax' => $netTax,
            'netItem' => $netItem,
            'discount' => $netDiscount,
            'count' => $arrayCount,
            'netDueAmount' => $netDueAmount
        ];
    }

    public function storeShippingInformation($request, $orderID)
    {
        $ShippingData = array();
        $ShippingData['shipping_area_id'] = $request->shippingAreaId;
        $ShippingData['price'] = $request->shippingPrice;
        $ShippingData['shipping_address'] = $request->shippingAreaSddress;
        $ShippingData['order_id'] = $orderID;
        $ShippingData['branch_id'] = $request->branchId;

        ShippingInformation::store($ShippingData);
    }

    public function insertInternalTransfer($orderData, $transferBranch, $userBranchId, $invoiceFixes, $purchaseLastInvoiceNumber)
    {
        $orderData['order_type'] = 'receiving';
        $orderData['branch_id'] = $transferBranch;
        $orderData['transfer_branch_id'] = $userBranchId;
        $orderLastIdForInternalTransfer = Order::store($orderData);
        $orderIdInternalTransfer = $orderLastIdForInternalTransfer->id;

        Order::updateData($orderIdInternalTransfer, ['invoice_id' => $invoiceFixes['purchasePrefix'] . $purchaseLastInvoiceNumber . $invoiceFixes['purchaseSuffix']]);
        $purchaseLastInvoiceNumber += 1;

        $purchaseLastUpdatedInvoice = Setting::where('setting_name', 'purchase_last_invoice_number')->first()->setting_value;
        if ($purchaseLastInvoiceNumber > $purchaseLastUpdatedInvoice) {
            Setting::updateSetting('purchase_last_invoice_number', $purchaseLastInvoiceNumber);
        }

        return $orderIdInternalTransfer;
    }

}
