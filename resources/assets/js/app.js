require('./js/jquery-3.3.1.min');
require('./js/hammer.min');
require('./js/popper.min');
require('./js/bootstrap');
require('./js/lang');


const Vue = require('vue');
const _ = require('lodash');

window.moment = require('moment');
window.$cookies = require('vue-cookies');
window.printThis = require('print-this');
window.VeeValidate = require('vee-validate');

VeeValidate.Validator.extend('phoneNumber', {
    getMessage(field, args) {
        return `Enter valid phone number`;
    },

    validate(value, args) {
        //Custom regex for a phone number
        const MOBILEREG = /^(?:\+[0-9])?(?:\d{6,7}|\d{8,9}|\d{10,11}|\d{12,13}|\d{14,15})$/;
        // Check for either of these to return true
        return MOBILEREG.test(value);
    }
});
import Vuelidate from 'vuelidate';
Vue.use(VeeValidate);
Vue.use(Vuelidate);

//Popover
import Popover from 'vue-js-popover';
Vue.use(Popover);

//Shortcuts
Vue.use(require('vue-shortkey'), { prevent: ['input', 'textarea'] });

//An EventHub to share events between components
Vue.prototype.$hub = new Vue();

//Define a function to use existing laravel language
Vue.prototype.trans = (string, args) => {
    let value = _.get(window.i18n, string);

    _.eachRight(args, (paramVal, paramKey) => {
        value = _.replace(value, `:${paramKey}`, paramVal);
    });
    return value;
};

const datepickerOptions = {};

//Vue Toasted
import Toasted from 'vue-toasted';
Vue.use(Toasted);

//For success messages
let success = {
    theme: "toasted-primary",
    position: "top-right",
    duration: 4000,
};

//Register the toast with the custom message
Vue.toasted.register('success',
    (payload) => {
        // if there is a message show it with the message
        return payload.message;
    },
    success
);



//Error messages example
let errors = {
    type: 'error',
    theme: "toasted-primary",
    position: "top-right",
    duration: 4000
};

//Register the toast with the custom message
Vue.toasted.register('error',
    (payload) => {
        // if there is no message passed show default message
        if (!payload.message) {
            return "Something Went Wrong.."
        }

        // if there is a message show it with the message
        return payload.message;

    },
    errors

);

// for warning message example
let offlineToast = {
    theme: "toasted-primary",
    position: "top-right",
    className: "offline-toast-padding",
    action: {
        text: 'X',
        onClick: (e, toastObject) => {
            toastObject.goAway(0);
        }
    },
};

// register the toast with the custom message
Vue.toasted.register('offlineToast',
    (payload) => {
        // if there is a message show it with the message
        return payload.message;
    },
    offlineToast
);

//For showWarningAlertForOutOfStock
let outOfStockToast = {
    theme: "toasted-primary",
    position: "top-right",
    className: "warning-message",
    action: {
        text: 'X',
        onClick: (e, toastObject) => {
            toastObject.goAway(0);
        }
    },
};

// register the toast with the custom message
Vue.toasted.register('outOfStockToast',
    (payload) => {
        // if there is a message show it with the message
        return payload.message;
    },
    outOfStockToast
);

// for warning 
let warningToast = {
    theme: "",
    position: "top-right",
    className: "warning-message",
    duration: 4000
};
Vue.toasted.register('warningToast',
    (payload) => {
        return payload.message;
    },
    warningToast
);

Vue.use('vue-shortkey', { prevent: ['.my-class-name', 'textarea.class-of-textarea'] })

Vue.prototype.app_name = window.appConfig.app_name;
Vue.prototype.dateFormat = window.appConfig.dateFormat;
Vue.prototype.defaultRowSetting = window.appConfig.defaultRowSetting;
Vue.prototype.offline = window.appConfig.offline;
Vue.prototype.publicPath = window.appConfig.publicPath;
Vue.prototype.appUrl = window.appConfig.appUrl;
Vue.prototype.currencySymbol = window.appConfig.currencySymbol;
Vue.prototype.currencyPosition = window.appConfig.currencyFormat;
Vue.prototype.thousandSeparator = window.appConfig.thousandSeparator;
Vue.prototype.decimalSeparator = window.appConfig.decimalSeparator;
Vue.prototype.numDec = window.appConfig.numDec;
Vue.prototype.timeFormat = window.appConfig.timeFormat;
Vue.prototype.timezone = window.appConfig.timezone;
Vue.prototype.lowStockNotifation = window.appConfig.lowStockNotifation;
Vue.prototype.outOfStockProduct = window.appConfig.outOfStockProduct;
Vue.prototype.appVersion = window.appConfig.appVersion;
Vue.prototype.salesListDelete = window.appConfig.salesListDelete;
Vue.prototype.salesListEdit = window.appConfig.salesListEdit;
Vue.prototype.shortcutKeyInfo = window.appConfig.shortcutKeyInfo;
Vue.prototype.shortcutStatus = window.appConfig.shortcutStatus;
Vue.prototype.appLogo = window.appConfig.appLogo;
Vue.prototype.autoSendSms = window.appConfig.autoSendSms;
Vue.prototype.salesInvoiceLogo = window.appConfig.salesInvoiceLogo;
Vue.prototype.purchaseInvoiceLogo = window.appConfig.purchaseInvoiceLogo;

Vue.component('searchable-select', require('./components/helperComponent/searchable-select/Index.vue'));

//Auth components
Vue.component('common-submit-button', require('./components/commonComponents/submitButton.vue'));
Vue.component('common-input', require('./components/commonComponents/commonInput.vue'));
Vue.component('payment-input', require('./components/commonComponents/paymentInput.vue'));
Vue.component('load-more', require('./components/commonComponents/loadMoreButton.vue'));
Vue.component('login-form', require('./components/auth/Login.vue'));
Vue.component('register-form', require('./components/auth/Register.vue'));
Vue.component('email-reset', require('./components/auth/ForgotPassword.vue'));
Vue.component('reset-password', require('./components/auth/ResetPassword.vue'));
Vue.component('profile-index', require('./components/profile/ProfileIndex.vue'));
Vue.component('profile-form', require('./components/profile/ProfileForm.vue'));
Vue.component('change-password-form', require('./components/profile/ChangePassword.vue'));

//Email Template
Vue.component('email-template-list', require('./components/settings/emailTemplates/EmailTemplateIndex.vue'));
Vue.component('sms-template-list', require('./components/settings/smsTemplates/SmsTemplate.vue'));
Vue.component('email-template-list-details', require('./components/settings/emailTemplates/EmailTemplateShow.vue'));
Vue.component('sms-template-list-details', require('./components/settings/smsTemplates/SmsTemplateShow.vue'));
Vue.component('roles-index', require('./components/settings/roles/RolesIndex.vue'));
Vue.component('roles-details', require('./components/settings/roles/RolesDetails.vue'));

// Sms Setings

Vue.component('sms-setting', require('./components/settings/sms/smsSettings.vue'));


//Invite user
Vue.component('invite-user', require('./components/settings/invitation/invite.vue'));
Vue.component('user-list', require('./components/settings/invitation/userList.vue'));
Vue.component('admin-confirmation-modal', require('./components/settings/invitation/adminConfirmationModal.vue'));
Vue.component('email-setting', require('./components/settings/email/EmailSetting.vue'));
Vue.component('product-settings', require('./components/settings/product/product.vue'));
Vue.component('setting-index', require('./components/settings/SettingsIndex.vue'));
Vue.component('application-setting', require('./components/settings/application/AppSetting.vue'));

//Sales settings
Vue.component('sales-setting', require('./components/settings/sales/salesSetting.vue'));

// notification-setting
Vue.component('notification-setting', require('./components/settings/notification/notification.vue'));

// cornJob-setting
Vue.component('corn-job-setting', require('./components/settings/cornJob/cornJob.vue'));

// Datatable component
Vue.component('datatable-component', require('./components/datatable/Datatable.vue'));

//Datatable action components
Vue.component('customer-action-component', require('./components/datatable/CustomerActionComponent.vue'));
Vue.component('sales-report-action-component', require('./components/datatable/SalesReportActionComponent.vue'));
Vue.component('sales-list-action-component', require('./components/datatable/SalesListActionComponent.vue'));
Vue.component('shipment-list-action-component', require('./components/datatable/ShipmentListActionComponent.vue'));
Vue.component('supplier-action-component', require('./components/datatable/SupplierActionComponent.vue'));
Vue.component('email-template-action-component', require('./components/datatable/EmailTemplateTableActions.vue'));
Vue.component('sms-template-action-component', require('./components/datatable/SmsTemplateTableActions.vue'));
Vue.component('group-action-component', require('./components/datatable/GroupActionComponent.vue'));
Vue.component('product-action-component', require('./components/datatable/ProductActionTable.vue'));
Vue.component('brand-action-component', require('./components/datatable/ProductBrandActionTable.vue'));
Vue.component('product-category-action-component', require('./components/datatable/ProductCategoryActionTable.vue'));
Vue.component('product-group-action-component', require('./components/datatable/ProductGroupActionTable.vue'));
Vue.component('product-attribute-action-component', require('./components/datatable/ProductAttributeActionTable.vue'));
Vue.component('tax-action-component', require('./components/datatable/TaxActionTable.vue'));
Vue.component('product-unit-action-component', require('./components/datatable/ProductUnitActionTable.vue'));
Vue.component('payment-action-component', require('./components/datatable/PaymentSettingsActionComponent.vue'));
Vue.component('branch-action-component', require('./components/datatable/BranchSettingsTableActions.vue'));
Vue.component('cash-register-action-component', require('./components/datatable/CashRegisterActionComponent.vue'));
Vue.component('userlist-action-component', require('./components/datatable/UserListActions.vue'));
Vue.component('roles-action-component', require('./components/datatable/RolesActionComponent.vue'));
Vue.component('restaurant-table-action-component', require('./components/datatable/RestaurantTableSettingActions.vue'));
Vue.component('adjust-stock-action-component', require('./components/datatable/AdjustStockActionComponent'));

//layouts component
Vue.component('side-bar', require('./components/layouts/Sidebar.vue'));
Vue.component('nav-bar', require('./components/layouts/Navbar.vue'));
Vue.component('doughnut-chart', require('./components/charts/doughnutChart.vue'));
Vue.component('dashboard-bar-chart', require('./components/dashboard/barChart.vue'));

//Edit
Vue.component('user-line', require('./components/settings/invitation/userLineChart.vue'));
Vue.component('home-page', require('./components/home.vue'));

//Loaders
Vue.component('pre-loader', require('./components/preLoaders/preLoader.vue'));
Vue.component('update-loader', require('./components/preLoaders/updateLoader.vue'));
Vue.component('circle-loader', require('./components/preLoaders/circleLoader.vue'));
Vue.component('button-loader', require('./components/preLoaders/buttonLoader.vue'));
Vue.component('roller-loader', require('./components/preLoaders/rollerloader.vue'));

//Filters
Vue.component('date-filter', require('./components/filter/dateFilter.vue'));
Vue.component('all-notification-view', require('./components/notification/AllNotification.vue'));
Vue.component('noti-single-view', require('./components/notification/NotificationSingleView.vue'));

//Products
Vue.component('products-index', require('./components/products/Index.vue'));
Vue.component('all-products', require('./components/products/products/AllProducts.vue'));
Vue.component('product-details', require('./components/products/products/ProductDetails.vue'));
Vue.component('product-add-edit-modal', require('./components/products/products/ProductAddEditModal.vue'));
Vue.component('import-modal', require('./components/products/products/ImportModal.vue'));
Vue.component('preview-modal', require('./components/products/products/PreviewModal.vue'));
Vue.component('barcode-modal', require('./components/products/products/BarcodeModal.vue'));
Vue.component('barcode-preview-modal', require('./components/products/products/BarcodePreviewModal.vue'));
Vue.component('adjust-stock-modal', require('./components/products/products/AdjustStockModal'));

//Product category
Vue.component('product-categories', require('./components/products/category/CategorieIndex.vue'));

//Product Brand
Vue.component('product-brands', require('./components/products/brand/BrandIndex.vue'));

//Product Group
Vue.component('product-groups', require('./components/products/group/GroupIndex.vue'));

//Product attributes
Vue.component('product-attributes', require('./components/products/attribute/AttributesIndex.vue'));

//Products units
Vue.component('product-units', require('./components/products/units/ProductsUnit.vue'));

//Common add edit form for Product Category, Brand, Group, Attributes
Vue.component('variant-add-edit-common-modal', require('./components/products/addOrEditModal/VariantAddEditCommonModal.vue'));

//Product add edit form for product unit
Vue.component('product-units-add-edit-form', require('./components/products/units/AddProductsUnit.vue'));

//Sales
Vue.component('sales-or-receives-component', require('./components/salesOrReceives/SalesOrPurchase.vue'));
Vue.component('sales-list-component', require('./components/salesOrReceives/salesList/SalesList.vue'));
Vue.component('shipment-list-component', require('./components/salesOrReceives/shipment/ShipmentList.vue'));
Vue.component('cart-payment-details', require('./components/salesOrReceives/cart/PaymentDetails.vue'));
Vue.component('card-payment-details', require('./components/salesOrReceives/payment/CardPaymentDetailsModal.vue'));
Vue.component('bank-transfer-details', require('./components/salesOrReceives/payment/BankTransferDetails.vue'));
Vue.component('cart-due-payment', require('./components/salesOrReceives/cart/DuePayment.vue'));
Vue.component('sale-date-edit', require('./components/salesOrReceives/modal/SalesDateEdit.vue'));
Vue.component('cart-component', require('./components/salesOrReceives/cart/CartComponent.vue'));
Vue.component('tax-edit-modal', require('./components/salesOrReceives/modal/TaxEditModal.vue'));
Vue.component('table-selection-modal', require('./components/salesOrReceives/modal/TableSelectionModal.vue'));
Vue.component('register-info-modal', require('./components/salesOrReceives/registerInfo/RegisterInfo.vue'));
Vue.component('datatable-invoice-modal-component', require('./components/salesOrReceives/modal/DatatableInvoiceModalComponent'));

//Invoice
Vue.component('invoice', require('./components/salesOrReceives/invoice/Invoice.vue'));
Vue.component('print-receipt-component', require('./components/salesOrReceives/invoice/PrePrintReceipt.vue'));
Vue.component('invoice-template-list', require('./components/settings/invoiceTemplates/InvoiceTemplateIndex.vue'));
Vue.component('invoice-add-edit', require('./components/settings/invoiceTemplates/InvoiceTemplateCreateEditModal.vue'));
Vue.component('invoice-template-action-component', require('./components/datatable/InvoiceTemplateTableActions.vue'));

//Payment
Vue.component('payment-types', require('./components/settings/payment/PaymentTypeIndex.vue'));
Vue.component('payment-type-details', require('./components/settings/payment/PaymentTypeDetails.vue'));

//Tax
Vue.component('all-taxes', require('./components/settings/tax/TaxIndex.vue'));
Vue.component('taxes-add-edit', require('./components/settings/tax/AddEditTax.vue'));

//Branches
Vue.component('branches', require('./components/settings/branches/BranchesIndex.vue'));
Vue.component('branche-details', require('./components/settings/branches/BranchDetails.vue'));

//Shipping
Vue.component('shipping', require('./components/settings/shipping/Shipping.vue'));
Vue.component('shipping-area-add-edit', require('./components/settings/shipping/ShippingAddEdit.vue'));
Vue.component('shipping-area-action-component', require('./components/datatable/ShippingAreaActionTable.vue'));

//Confirmation-modal
Vue.component('confirmation-modal', require('./components/confirmationDeleteModal/ConfirmationModal.vue'));

//Sales Register
Vue.component('cash-register', require('./components/settings/cashRegister/CashRegisterIndex.vue'));
Vue.component('cash-register-add-edit', require('./components/settings/cashRegister/CashRegisterAddEdit.vue'));

//Invoice Settings
Vue.component('invoice-settings', require('./components/settings/invoice/InvoiceSetting.vue'));
Vue.component('purchase-invoice-settings', require('./components/settings/invoice/PurchaseInvoiceSetting.vue'));

//User Details
Vue.component('user-details', require('./components/settings/invitation/userDetails.vue'));

//Reports
Vue.component('reports-index', require('./components/reports/index.vue'));
Vue.component('sales-reports', require('./components/reports/salesReports.vue'));
Vue.component('sales-details', require('./components/reports/salesDetails.vue'));
Vue.component('payment-reports', require('./components/reports/paymentReports.vue'));
Vue.component('payment-summary-reports', require('./components/reports/paymentSummary.vue'));
Vue.component('sales-summary-reports', require('./components/reports/salesSummaryReports.vue'));
Vue.component('receiving-reports', require('./components/reports/receivingReports/receviengsReports.vue'));
Vue.component('cash-register-log-reports', require('./components/reports/cashRegisterLogReports.vue'));
Vue.component('inventory-reports', require('./components/reports/inventoryReports.vue'));
Vue.component('receiving-summary-reports', require('./components/reports/receivingReports/receivingSummaryReports.vue'));
Vue.component('sales-reports-details', require('./components/reports/salesReportsDetails.vue'));
Vue.component('payment-reports', require('./components/reports/paymentReports.vue'));
Vue.component('yearly-sales-chart', require('./components/reports/salesChart.vue'));
Vue.component('sales-bar-chart', require('./components/reports/salesBarChart.vue'));
Vue.component('available-stock-chart', require('./components/reports/availableStock.vue'));
Vue.component('stock-line-chart', require('./components/reports/stockLineChart.vue'));
Vue.component('export-data', require('./components/reports/exportData.vue'));
Vue.component('available-tax-report', require('./components/reports/taxReport.vue'));
Vue.component('profit-loss-report', require('./components/reports/profitLossReport.vue'));
Vue.component('customers-summary-reports', require('./components/reports/customersSummaryReports.vue'));
Vue.component('suppliers-summary-reports', require('./components/reports/suppliersSummaryReports.vue'));
Vue.component('sales-and-purchase-reports', require('./components/reports/salesAndPurchaseReports.vue'));
Vue.component('adjust-stock-reports', require('./components/reports/adjustStockReport.vue'));
Vue.component('shipment-reports', require('./components/reports/shipmentReport.vue'));

//Shortcuts
Vue.component('shortcuts-setting', require('./components/settings/shortcuts/shortcuts.vue'));

//Updates
Vue.component('updates-setting', require('./components/settings/updates/Updates'));

//Tables
Vue.component('tables-setting', require('./components/settings/table/tableIndex'));
Vue.component('tables-details', require('./components/settings/table/tableDetails'));

// Adjust stock setting
Vue.component('adjust-stock-setting', require('./components/settings/adjustStock/AdjustStockIndex'));
Vue.component('adjust-stock-details', require('./components/settings/adjustStock/AdjustStockDetails'));

//Contacts
Vue.component('contacts-page-index', require('./components/contacts/ContactsPageIndex.vue'));

//Customers Component
Vue.component('customers-page-index', require('./components/contacts/customersTab/CustomersPageIndex.vue'));
Vue.component('customers-index', require('./components/contacts/customersTab/CustomersTabIndex.vue'));
Vue.component('customer-create-edit', require('./components/contacts/customersTab/CustomerAddEditFrom.vue'));
Vue.component('customer-details', require('./components/contacts/customersTab/CustomersDetails.vue'));

//Groups Component
Vue.component('groups-index', require('./components/contacts/groupsTab/GroupsTabIndex.vue'));
Vue.component('group-create-edit', require('./components/contacts/groupsTab/GroupAddEditFrom.vue'));

//Suppliers
Vue.component('suppliers-page-index', require('./components/contacts/suppliersTab/SuppliersPageIndex.vue'));
Vue.component('supplier-create-edit', require('./components/contacts/suppliersTab/SupplierAddEditModal.vue'));
Vue.component('supplier-details', require('./components/contacts/suppliersTab/SuppliersDetails.vue'));

//Vue.component('supplier-index',require('./components/Suppliers/suppliersTabIndex.vue'));
Vue.component('dashboard', require('./components/dashboard/dashboard.vue'));

// Todo list
Vue.component('user-todo-list', require('./components/todolist/TodoListComponent'));

//Install
Vue.component('app-install-wizard', require('./components/installer/Installer'));

Vue.component('app-database-wizard', require('./components/installer/DatabaseWizard'));

Window.app = new Vue({
    el: '#app',
    data: {
        msg: 'Hello Vue JS'
    }
});

