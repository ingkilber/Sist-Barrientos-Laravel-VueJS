<template>
    <div>
        <div class="modal-layout-header">
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-10">
                        <h4 class="m-0" v-if="id">{{ trans('lang.edit_role') }}</h4>
                        <h4 class="m-0" v-else>{{ trans('lang.add_role') }}</h4>
                    </div>
                    <div class="col-2 text-right">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click.prevent="">
                            <i class="la la-close icon-modal-cross"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-layout-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>
            <form class="form-row" v-else>
                <div class="form-group col-md-12">
                    <label for="role-title">{{ trans('lang.role_title') }}</label>
                    <input v-validate="'required'" name="title" id="role-title" class="form-control" type="text"
                           v-model="title">
                    <small class="text-danger" v-show="errors.has('title')">{{ errors.first('title') }}</small>
                </div>
                <div class="form-group col-md-12">

                    <h6 class="m-0">{{trans('lang.settings')}}</h6>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-1" v-model="check"
                               value="can_edit_application_setting" name="can_edit_application_setting"/>
                        <label class="custom-control-label" for="setting-role-1">{{
                            trans('lang.can_manage_application_settings') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-2" v-model="check"
                               value="can_see_application_settings" name="can_see_application_settings"/>
                        <label class="custom-control-label" for="setting-role-2">{{
                            trans('lang.can_see_application_settings') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sales-setting-role-1" v-model="check"
                               value="can_manage_sales_setting" name="can_manage_sales_setting"/>
                        <label class="custom-control-label" for="sales-setting-role-1">{{
                            trans('lang.can_manage_sales_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sales-setting-role-2" v-model="check"
                               value="can_see_sales_setting" name="can_see_sales_setting"/>
                        <label class="custom-control-label" for="sales-setting-role-2">{{
                            trans('lang.can_see_sales_setting') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-3" v-model="check"
                               value="can_edit_email_setting" name="can_edit_email_setting"/>
                        <label class="custom-control-label" for="setting-role-3">{{
                            trans('lang.can_manage_email_settings') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-4" v-model="check"
                               value="can_see_email_settings" name="can_see_email_settings"/>
                        <label class="custom-control-label" for="setting-role-4">{{
                            trans('lang.can_see_email_settings') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="email-role-5" v-model="check"
                               value="can_edit_email_template" name="can_edit_email_template"/>
                        <label class="custom-control-label" for="email-role-5">{{
                            trans('lang.can_manage_email_templates') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="email-role-32" v-model="check"
                               value="can_see_email_templates" name="can_see_email_templates"/>
                        <label class="custom-control-label" for="email-role-32">{{
                            trans('lang.can_see_email_templates') }}</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="sms-role-33" v-model="check"
                               value="can_manage_sms_setting" name="can_manage_sms_setting"/>
                        <label class="custom-control-label" for="sms-role-33">{{
                            trans('lang.can_manage_sms_setting') }}</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="sms-role-34" v-model="check"
                               value="can_see_sms_setting" name="can_see_sms_setting"/>
                        <label class="custom-control-label" for="sms-role-34">{{
                            trans('lang.can_see_sms_setting') }}</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-6" v-model="check"
                               value="can_manage_branches" name="can_manage_branches"/>
                        <label class="custom-control-label" for="setting-role-6">{{ trans('lang.can_manage_branches')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-7" v-model="check"
                               value="can_see_branches" name="can_see_branches"/>
                        <label class="custom-control-label" for="setting-role-7">{{ trans('lang.can_see_branches')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-8" v-model="check"
                               value="can_manage_payment_settings" name="can_manage_payment_settings"/>
                        <label class="custom-control-label" for="setting-role-8">{{
                            trans('lang.can_manage_payment_settings') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-9" v-model="check"
                               value="can_see_payment_settings" name="can_see_payment_settings"/>
                        <label class="custom-control-label" for="setting-role-9">{{
                            trans('lang.can_see_payment_settings') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-10" v-model="check"
                               value="can_manage_tax_settings" name="can_manage_tax_settings"/>
                        <label class="custom-control-label" for="setting-role-10">{{
                            trans('lang.can_manage_tax_settings') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-11" v-model="check"
                               value="can_see_tax_settings" name="can_see_tax_settings"/>
                        <label class="custom-control-label" for="setting-role-11">{{
                            trans('lang.can_see_tax_settings') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-12" v-model="check"
                               value="can_manage_invoice_setting" name="can_manage_invoice_setting"/>
                        <label class="custom-control-label" for="setting-role-12">{{
                            trans('lang.can_manage_invoice_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-13" v-model="check"
                               value="can_see_invoice_setting" name="can_see_invoice_setting"/>
                        <label class="custom-control-label" for="setting-role-13">{{
                            trans('lang.can_see_invoice_setting') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-14" v-model="check"
                               value="can_manage_roles" name="can_manage_roles"/>
                        <label class="custom-control-label" for="setting-role-14">{{ trans('lang.can_manage_roles')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-15" v-model="check"
                               value="can_see_roles" name="can_see_roles"/>
                        <label class="custom-control-label" for="setting-role-15">{{ trans('lang.can_see_roles')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-16" v-model="check"
                               value="can_manage_cash_registers" name="can_manage_cash_registers"/>
                        <label class="custom-control-label" for="setting-role-16">{{
                            trans('lang.can_manage_cash_registers') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-17" v-model="check"
                               value="can_see_cash_registers" name="can_see_cash_registers"/>
                        <label class="custom-control-label" for="setting-role-17">{{
                            trans('lang.can_see_cash_registers') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-18" v-model="check"
                               value="can_manage_users" name="can_manage_users"/>
                        <label class="custom-control-label" for="setting-role-18">{{ trans('lang.can_manage_users')
                            }} &nbsp; <i class="la la-warning text-danger"></i>  &nbsp;<span class="text-danger" >{{ trans('lang.it_can_be_dangerous')}}</span> </label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-19" v-model="check"
                               value="can_see_users" name="can_see_users"/>
                        <label class="custom-control-label" for="setting-role-19">{{ trans('lang.can_see_users')}}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-20" v-model="check"
                               value="can_see_users_details" name="can_see_users_details"/>
                        <label class="custom-control-label" for="setting-role-20">{{
                            trans('lang.can_see_users_details') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-21" v-model="check"
                               value="can_manage_product_setting" name="can_manage_product_setting"/>
                        <label class="custom-control-label" for="setting-role-21">{{
                            trans('lang.can_manage_product_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-22" v-model="check"
                               value="can_see_product_setting" name="can_see_product_setting"/>
                        <label class="custom-control-label" for="setting-role-22">{{
                            trans('lang.can_see_product_setting') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-23" v-model="check"
                               value="can_manage_adjust_stock" name="can_manage_adjust_stock"/>
                        <label class="custom-control-label" for="setting-role-23">{{
                            trans('lang.can_manage_adjust_stock') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-24" v-model="check"
                               value="can_see_adjust_stock_settings" name="can_see_adjust_stock_settings"/>
                        <label class="custom-control-label" for="setting-role-24">{{
                            trans('lang.can_see_adjust_stock_settings') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-25" v-model="check"
                               value="can_manage_updates_setting" name="can_manage_updates_setting"/>
                        <label class="custom-control-label" for="setting-role-25">{{
                            trans('lang.can_manage_updates_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-26" v-model="check"
                               value="can_see_updates_setting" name="can_see_updates_setting"/>
                        <label class="custom-control-label" for="setting-role-26">{{
                            trans('lang.can_see_updates_setting') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-27" v-model="check"
                               value="can_manage_table_setting" name="can_manage_table_setting"/>
                        <label class="custom-control-label" for="setting-role-27">{{
                            trans('lang.can_manage_table_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-28" v-model="check"
                               value="can_see_table_setting" name="can_see_table_setting"/>
                        <label class="custom-control-label" for="setting-role-28">{{
                            trans('lang.can_see_table_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-29" v-model="check"
                               value="can_manage_invoice_template_setting" name="can_manage_invoice_template_setting"/>
                        <label class="custom-control-label" for="setting-role-29">{{
                            trans('lang.can_manage_invoice_template_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-30" v-model="check"
                               value="can_see_invoice_template_setting" name="can_see_invoice_template_setting"/>
                        <label class="custom-control-label" for="setting-role-30">{{
                            trans('lang.can_see_invoice_template_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-31" v-model="check"
                               value="can_manage_notification_setting" name="can_manage_notification_setting"/>
                        <label class="custom-control-label" for="setting-role-31">{{
                            trans('lang.can_manage_notification_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-32" v-model="check"
                               value="can_see_notification_setting" name="can_see_notification_setting"/>
                        <label class="custom-control-label" for="setting-role-32">{{
                            trans('lang.can_see_notification_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="setting-role-33" v-model="check"
                               value="can_see_corn_job_setting" name="can_see_corn_job_setting"/>
                        <label class="custom-control-label" for="setting-role-33">{{
                            trans('lang.can_see_corn_job_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="invoice-setting-1" v-model="check"
                               value="can_manage_purchase_invoice_setting" name="can_manage_purchase_invoice_setting"/>
                        <label class="custom-control-label" for="invoice-setting-1">{{
                            trans('lang.can_manage_purchase_invoice_setting') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="invoice-setting-2" v-model="check"
                               value="can_see_purchase_invoice_setting" name="can_see_purchase_invoice_setting"/>
                        <label class="custom-control-label" for="invoice-setting-2">{{
                            trans('lang.can_see_purchase_invoice_setting') }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="shipping-area-setting-1" v-model="check"
                               value="can_manage_shipping_area" name="can_manage_shipping_area"/>
                        <label class="custom-control-label" for="shipping-area-setting-1">{{
                            trans('lang.can_manage_shipping_area') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="shipping-area-setting-2" v-model="check"
                               value="can_see_shipping_area" name="can_see_shipping_area"/>
                        <label class="custom-control-label" for="shipping-area-setting-2">{{
                            trans('lang.can_see_shipping_area') }}</label> <br>
                    </div>

                </div>
                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.products')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-1" v-model="check"
                               value="can_manage_products" name="can_manage_products"/>
                        <label class="custom-control-label" for="products-role-1">{{ trans('lang.can_manage_products')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-2" v-model="check"
                               value="can_see_products" name="can_see_products"/>
                        <label class="custom-control-label" for="products-role-2">{{ trans('lang.can_see_products')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-3" v-model="check"
                               value="can_manage_categories" name="can_manage_categories"/>
                        <label class="custom-control-label" for="products-role-3">{{ trans('lang.can_manage_categories')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-4" v-model="check"
                               value="can_see_categories" name="can_see_categories"/>
                        <label class="custom-control-label" for="products-role-4">{{ trans('lang.can_see_categories')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-5" v-model="check"
                               value="can_manage_brands" name="can_manage_brands"/>
                        <label class="custom-control-label" for="products-role-5">{{ trans('lang.can_manage_brands')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-6" v-model="check"
                               value="can_see_brands" name="can_see_brands"/>
                        <label class="custom-control-label" for="products-role-6">{{ trans('lang.can_see_brands')
                            }}</label> <br>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-7" v-model="check"
                               value="can_manage_groups" name="can_manage_groups"/>
                        <label class="custom-control-label" for="products-role-7">{{ trans('lang.can_manage_groups')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-8" v-model="check"
                               value="can_see_groups" name="can_see_groups"/>
                        <label class="custom-control-label" for="products-role-8">{{ trans('lang.can_see_groups')
                            }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-9" v-model="check"
                               value="can_manage_variant_attribute" name="can_manage_variant_attribute"/>
                        <label class="custom-control-label" for="products-role-9">{{
                            trans('lang.can_manage_variant_attribute') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-10" v-model="check"
                               value="can_see_variant_attribute" name="can_see_variant_attribute"/>
                        <label class="custom-control-label" for="products-role-10">{{
                            trans('lang.can_see_variant_attribute') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-11" v-model="check"
                               value="can_manage_units" name="can_manage_units"/>
                        <label class="custom-control-label" for="products-role-11">{{
                            trans('lang.can_manage_units') }}</label> <br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="products-role-12" v-model="check"
                               value="can_see_units" name="can_see_units"/>
                        <label class="custom-control-label" for="products-role-12">{{
                            trans('lang.can_see_units') }}</label> <br>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.sales')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="sales-role-1" v-model="check"
                               value="can_manage_sales" name="can_manage_sales"/>
                        <label class="custom-control-label" for="sales-role-1">{{ trans('lang.can_manage_sales')
                            }}</label><br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="sales-role-2" v-model="check"
                               value="can_manage_sales_price" name="can_manage_sales_price"/>
                        <label class="custom-control-label" for="sales-role-2">{{ trans('lang.can_manage_sales_price')
                            }}</label><br>
                    </div>
                </div>

                <div class="form-group <col>-md-12">
                    <h6 class="m-0">{{trans('lang.suppliers')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="supplier-role-1" v-model="check"
                               value="can_add_suppliers" name="can_add_suppliers"/>
                        <label class="custom-control-label" for="supplier-role-1">{{ trans('lang.can_manage_suppliers')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="supplier-role-2" v-model="check"
                               value="can_see_suppliers" name="can_see_suppliers"/>
                        <label class="custom-control-label" for="supplier-role-2">{{ trans('lang.can_see_suppliers')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="supplier-role-3" v-model="check"
                               value="can_see_supplier_details" name="can_see_supplier_details"/>
                        <label class="custom-control-label" for="supplier-role-3">{{ trans('lang.can_see_supplier_details')
                            }}</label>
                    </div>

                </div>

                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.receives')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="receives-role-1" v-model="check"
                               value="can_manage_receives" name="can_manage_receives"/>
                        <label class="custom-control-label" for="receives-role-1">{{ trans('lang.can_manage_receives')
                            }}</label><br>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.customers')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customer-role-1" v-model="check"
                               value="can_manage_customers" name="can_manage_customers"/>
                        <label class="custom-control-label" for="customer-role-1">{{ trans('lang.can_manage_customers')
                            }}</label><br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customer-role-2" v-model="check"
                               value="can_see_customers" name="can_see_customers"/>
                        <label class="custom-control-label" for="customer-role-2">{{ trans('lang.can_see_customers')
                            }}</label><br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customer-role-3" v-model="check"
                               value="can_manage_customer_groups" name="can_manage_customer_groups"/>
                        <label class="custom-control-label" for="customer-role-3">{{
                            trans('lang.can_manage_customer_groups') }}</label><br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customer-role-4" v-model="check"
                               value="can_see_customer_groups" name="can_manage_customer_groups"/>
                        <label class="custom-control-label" for="customer-role-4">{{
                            trans('lang.can_see_customer_groups') }}</label><br>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customer-role-5" v-model="check"
                               value="can_see_customer_details" name="can_see_customer_details"/>
                        <label class="custom-control-label" for="customer-role-5">{{
                            trans('lang.can_see_customer_details') }}</label><br>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.cash_register')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="cash-role-1" v-model="check"
                               value="can_close_others_cash_register" name="can_close_others_cash_register"/>
                        <label class="custom-control-label" for="cash-role-1">{{
                            trans('lang.can_close_others_cash_register') }}</label><br>
                    </div>
                </div>

                <div class="form-group col-md-12">
                    <h6 class="m-0">{{trans('lang.reports')}}</h6>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-1" v-model="check"
                               value="can_see_sales_reports" name="can_see_sales_reports"/>
                        <label class="custom-control-label" for="report-role-1">{{ trans('lang.can_see_sales_reports')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-2" v-model="check"
                               value="can_see_personal_sales_reports" name="can_see_personal_sales_reports"/>
                        <label class="custom-control-label" for="report-role-2">{{ trans('lang.can_see_personal_sales_reports')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-3" v-model="check"
                               value="can_see_sales_summary_reports" name="can_see_sales_summary_reports"/>
                        <label class="custom-control-label" for="report-role-3">{{
                            trans('lang.can_see_sales_summary_reports')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-4" v-model="check"
                               value="can_see_receiving_reports" name="can_see_receiving_reports"/>
                        <label class="custom-control-label" for="report-role-4">{{
                            trans('lang.can_see_receiving_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                    <input class="custom-control-input" type="checkbox" id="report-role-5" v-model="check"
                           value="can_see_personal_receiving_reports" name="can_see_personal_receiving_reports"/>
                    <label class="custom-control-label" for="report-role-5">{{
                        trans('lang.can_see_personal_receiving_reports') }}</label>
                </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-6" v-model="check"
                               value="can_see_receiving_summary_reports" name="can_see_receiving_summary_reports"/>
                        <label class="custom-control-label" for="report-role-6">{{
                            trans('lang.can_see_receiving_summary_reports') }}</label>
                    </div>

                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-7" v-model="check"
                               value="can_see_register_reports" name="can_see_register_reports"/>
                        <label class="custom-control-label" for="report-role-7">{{
                            trans('lang.can_see_register_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-8" v-model="check"
                               value="can_see_inventory_reports" name="can_see_inventory_reports"/>
                        <label class="custom-control-label" for="report-role-8">{{
                            trans('lang.can_see_inventory_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-9" v-model="check"
                               value="can_see_payment_reports" name="can_see_payment_reports"/>
                        <label class="custom-control-label" for="report-role-9">{{
                            trans('lang.can_see_payment_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-10" v-model="check"
                               value="can_see_sales_payment_reports" name="can_see_sales_payment_reports"/>
                        <label class="custom-control-label" for="report-role-10">{{
                            trans('lang.can_see_sales_payment_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-11" v-model="check"
                               value="can_see_payment_summary_reports" name="can_see_payment_summary_reports"/>
                        <label class="custom-control-label" for="report-role-11">{{
                            trans('lang.can_see_payment_summary_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-12" v-model="check"
                               value="can_see_sales_statistics_reports" name="can_see_sales_statistics_reports"/>
                        <label class="custom-control-label" for="report-role-12">{{
                            trans('lang.can_see_sales_statistics_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-13" v-model="check"
                               value="can_see_tax_reports" name="can_see_tax_reports"/>
                        <label class="custom-control-label" for="report-role-13">{{
                            trans('lang.can_see_tax_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-14" v-model="check"
                               value="can_see_profit_loss_reports" name="can_see_profit_loss_reports"/>
                        <label class="custom-control-label" for="report-role-14">{{
                            trans('lang.can_see_profit_loss_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-15" v-model="check"
                               value="can_see_customer_summary_reports" name="can_see_customer_summary_reports"/>
                        <label class="custom-control-label" for="report-role-15">{{
                            trans('lang.can_see_customer_summary_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-16" v-model="check"
                               value="can_see_supplier_summary_reports" name="can_see_supplier_summary_reports"/>
                        <label class="custom-control-label" for="report-role-16">{{
                            trans('lang.can_see_supplier_summary_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-17" v-model="check"
                               value="can_see_sales_and_purchase_reports" name="can_see_sales_and_purchase_reports"/>
                        <label class="custom-control-label" for="report-role-17">{{
                            trans('lang.can_see_sales_and_purchase_reports') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-18" v-model="check"
                               value="can_see_adjust_stock" name="can_see_adjust_stock"/>
                        <label class="custom-control-label" for="report-role-18">{{
                            trans('lang.can_see_adjust_stock') }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-19" v-model="check"
                               value="can_see_report_action" name="can_see_report_action"/>
                        <label class="custom-control-label" for="report-role-19">{{ trans('lang.can_see_report_action')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-20" v-model="check"
                               value="can_see_sales_details_reports" name="can_see_sales_details_reports"/>
                        <label class="custom-control-label" for="report-role-20">{{ trans('lang.can_see_sales_details_reports')
                            }}</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="report-role-21" v-model="check"
                               value="can_see_shipment_report" name="can_see_shipment_report"/>
                        <label class="custom-control-label" for="report-role-21">{{ trans('lang.can_see_shipment_report')
                            }}</label>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn app-color mobile-btn" :disabled="errors.any()" type="submit"
                            @click.prevent="save()">{{ trans('lang.save') }}
                    </button>
                    <button class="btn cancel-btn mobile-btn" data-dismiss="modal" @click.prevent="">{{
                        trans('lang.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        props: ['id', 'modalID'],
        extends: axiosGetPost,
        data() {
            return {

                title: '',
                check: [],
            }
        },

        created() {

            if (this.id) {
                this.getRoleData('/role-permissions/' + this.id);
            }
        },

        methods: {
            save() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.inputFields = {

                            title: this.title,
                            permissions: this.check,
                        };

                        if (this.id) {
                            this.postDataMethod('/add-role/' + this.id, this.inputFields);
                        }
                        else {
                            this.postDataMethod('/add-role', this.inputFields);
                        }
                    }
                });
            },

            getRoleData(route) {

                let instance = this;
                this.setPreLoader(false);
                this.axiosGet(route,
                    function (response) {
                        instance.title = response.data.title;
                        instance.check = response.data.permissions;
                        instance.setPreLoader(true);
                    },
                    function (response) {
                        instance.setPreLoader(true);
                    },
                );
            },
            postDataThenFunctionality(response) {
                $(this.modalID).modal('hide');
                this.$hub.$emit('reloadDataTable');
            },
            postDataCatchFunctionality(error) {
                this.hidePreLoader = false;
                this.showErrorAlert(error.data.message);
            },
        }
    }
</script>
