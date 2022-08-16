<template>
    <div>
        <pre-loader v-if="hidePreloader"></pre-loader>
        <div v-else>
            <div class="mb-4">
                <span class="">{{ trans('lang.enable_shortcut') }}</span>
                <div class="custom-control custom-switch d-inline-block mx-4">
                    <input type="checkbox"
                           class="custom-control-input"
                           v-model="isDisableShortcut"
                           @click="disableAllShortCuts"
                           id="customSwitch1">
                    <label class="custom-control-label cursor-pointer" for="customSwitch1"></label>
                </div>
                <button type="button"
                        class="btn float-right btn-shortcut-info"
                        data-toggle="tooltip"
                        data-placement="left"
                        :title="trans('lang.shortcut_setting_information')">
                    <i class="la la-info-circle"></i>
                </button>
            </div>
            <form id="shortcut-setup-form">
                <div :class="[isDisableShortcut ? false : 'disabled-button']">
                    <div class="form-row">
                        <!--Product Search-->
                        <div class="form-group col-12 col-md-6">
                            <label for="productSearch">{{ trans('lang.product_search') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="productSearch"
                                       class="form-control"
                                       v-model="shortcutsInfo.productSearch.shortcut_key"
                                       @keydown="showUnprintableValue(id='#productSearch', 1)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="productSearchRadio"
                                               v-model="shortcutsInfo.productSearch.status">
                                        <label class="mb-0 ml-1" for="productSearchRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.productSearch.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '1'" class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>

                        <!--Add Customer-->
                        <div class="form-group col-12 col-md-6">
                            <label for="addCustomer">{{ trans('lang.add_customer_label') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="addCustomer"
                                       class="form-control"
                                       v-model="shortcutsInfo.addCustomer.shortcut_key"
                                       @keydown="showUnprintableValue(id='#addCustomer', 2)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="addCustomerRadio"
                                               v-model="shortcutsInfo.addCustomer.status">
                                        <label class="mb-0 ml-1" for="addCustomerRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.addCustomer.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '2'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <!--Pay-->
                        <div class="form-group col-12 col-md-6">
                            <label for="pay">{{ trans('lang.pay') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="pay"
                                       class="form-control"
                                       v-model="shortcutsInfo.pay.shortcut_key"
                                       @keydown="showUnprintableValue(id='#pay', 3)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="payRadio"
                                               v-model="shortcutsInfo.pay.status">
                                        <label class="mb-0 ml-1" for="payRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.pay.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '3'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>

                        <!--Hold Card-->
                        <div class="form-group col-12 col-md-6">
                            <label for="holdCardId">{{ trans('lang.hold_card') }}</label>
                            <div class="input-group mb-1">
                                <input type="text"
                                       id="holdCardId"
                                       class="form-control"
                                       v-model="shortcutsInfo.holdCard.shortcut_key"
                                       @keydown="showUnprintableValue(id='#holdCardId', 4)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="holdCardRadio"
                                               v-model="shortcutsInfo.holdCard.status">
                                        <label class="mb-0 ml-1" for="holdCardRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.holdCard.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '4'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <!--Cancel Card Item-->
                        <div class="form-group col-12 col-md-6">
                            <label for="cancelCarditem">{{ trans('lang.cancel_card_item') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="cancelCarditem"
                                       class="form-control"
                                       v-model="shortcutsInfo.cancelCarditem.shortcut_key"
                                       @keydown="showUnprintableValue(id='#cancelCarditem', 5)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="cancelCarditemRadio"
                                               v-model="shortcutsInfo.cancelCarditem.status">
                                        <label class="mb-0 ml-1" for="cancelCarditemRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.cancelCarditem.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '5'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>

                        <!--Load Sales Page-->
                        <div class="form-group col-12 col-md-6">
                            <label for="loadSalesPage">{{ trans('lang.load_sales_page') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="loadSalesPage"
                                       class="form-control"
                                       v-model="shortcutsInfo.loadSalesPage.shortcut_key"
                                       @keydown="showUnprintableValue(id='#loadSalesPage', 6)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="loadSalesPageRadio"
                                               v-model="shortcutsInfo.loadSalesPage.status">
                                        <label class="mb-0 ml-1" for="loadSalesPageRadio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.loadSalesPage.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '6'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <!--Done Payment-->
                        <div class="form-group col-12 col-md-6">
                            <label for="donePayment1">{{ trans('lang.done_payment_label') }}</label>
                            <div class="input-group">
                                <input type="text"
                                       id="donePayment1"
                                       class="form-control"
                                       v-model="shortcutsInfo.donePayment1.shortcut_key"
                                       @keydown="showUnprintableValue(id='#donePayment1', 7)">
                                <div class="input-group-prepend">
                                    <div class="input-group-text border-left-0 rounded-right">
                                        <input type="checkbox"
                                               id="donePayment1Radio"
                                               v-model="shortcutsInfo.donePayment1.status">
                                        <label class="mb-0 ml-1" for="donePayment1Radio">Enable</label>
                                    </div>
                                </div>
                            </div>
                            <div class="heightError text-nowrap">
                                <small class="text-danger"
                                       v-show="includes(duplicateShortCutCollection, shortcutsInfo.donePayment1.shortcut_key)">
                                    {{ trans('lang.shortcut_key_must_be_unique') }}
                                </small>
                            </div>
                            <div v-if="canNotUseShortcutKey && inputFieldValue == '7'"  class="heightError text-nowrap">
                                <small class="text-danger">
                                    {{ trans('lang.use_ctrl_or_shift_for_combination_key') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!--Save Button-->
            <div class="col-12 col-md-6 pl-0">
                <div class="">
                    <button class="btn btn-primary app-color mobile-btn save_button" @click="save">
                        {{ trans('lang.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../../helper/axiosGetPostCommon';
    export default {
        extends: axiosGetPost,
        props: ["permission_key"],
        data() {
            return {
                item: {},
                shortCut: {},
                hidePreloader: false,
                productSearchId: '',
                checked: '',
                isDisableShortcut: '',
                shortcutsInfo: {},
                isShortcutsActive: 3,
                isShortcutAbleKey: '',
                canNotUseShortcutKey:false,
                duplicateShortCutCollection: [],
                inputFieldValue:'',
                includes: (array, value) => {
                    if (_.includes(array, value)) {
                        return true;
                    }
                    return false;
                },
            }
        },
        created() {
            this.getKeyboardShortcutSettingsData('/shortcut-setting-data/{id}');
        },
        mounted(){
            let instance = this;
            instance.hidePreloader = 'hide';
            this.showUnprintableValue();

            // Enable bootstrap tooltip
            $(document).ready(function() {
                $("body").tooltip({ selector: '[data-toggle=tooltip]' });
            });
        },
        watch:{
            shortcutsInfo: {
                handler(val){
                    this.duplicateShortCutCollection = [];
                },
                deep: true
            }
        },
        methods: {
            disableAllShortCuts() {
                if (this.isDisableShortcut){
                    this.saveButtonDisabled = true;
                    this.isDisableShortcut = false;
                }else{
                    this.isDisableShortcut = true;
                }
            },
            getKeyboardShortcutSettingsData(route){
                let instance = this;
                instance.hidePreloader = true;
                this.axiosGet(route,
                    function (response){
                        if (response.data.shortcutStatus == 0) {
                            instance.isDisableShortcut = false;
                        } else {
                            instance.isDisableShortcut = true;
                        }
                        instance.shortcutsInfo = response.data.shortcutSettings;
                        instance.hidePreloader = false;
                    },
                    function (error) {
                        instance.hidePreloader = false;
                    },
                );
            },
            showUnprintableValue(id, inputClickVal){
                this.keyboardAscciValueReader(id);
                let instance = this;
                $(id).on("keydown", function (event) {
                    if (event.which == 9 || event.which == 20 || event.which == 91 || event.which == 18 || event.which == 13 || event.which == 32) {
                        instance.canNotUseShortcutKey = true;
                        instance.inputClick(inputClickVal);
                    } else instance.canNotUseShortcutKey = false;
                })
            },
            save(){
                let instance = this;
                instance.hidePreloader = true;
                this.inputFields = {
                    productSearch: {
                        action_name: 'productSearch',
                        shortcut_key: instance.shortcutsInfo.productSearch.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.productSearch.status
                    },
                    holdCard: {
                        action_name: 'holdCard',
                        shortcut_key: instance.shortcutsInfo.holdCard.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.holdCard.status
                    },
                    pay: {
                        action_name: 'pay',
                        shortcut_key: instance.shortcutsInfo.pay.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.pay.status
                    },
                    addCustomer: {
                        action_name: 'addCustomer',
                        shortcut_key: instance.shortcutsInfo.addCustomer.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.addCustomer.status
                    },
                    cancelCarditem: {
                        action_name: 'cancelCarditem',
                        shortcut_key: instance.shortcutsInfo.cancelCarditem.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.cancelCarditem.status
                    },
                    loadSalesPage: {
                        action_name: 'loadSalesPage',
                        shortcut_key: instance.shortcutsInfo.loadSalesPage.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.loadSalesPage.status
                    },
                    donePayment1: {
                        action_name: 'donePayment1',
                        shortcut_key: instance.shortcutsInfo.donePayment1.shortcut_key.toLowerCase(),
                        status: instance.shortcutsInfo.donePayment1.status
                    },
                };
                let duplicates = [];
                _.forIn(this.inputFields, function (value, key) {
                    _.compact(duplicates.push(value.shortcut_key));
                });
                instance.duplicateShortCutCollection = instance.find_duplicate_in_array(duplicates);
                if(instance.duplicateShortCutCollection.length === 0 && instance.canNotUseShortcutKey === false) {
                    this.postDataMethod('/shortcuts', {
                        shortcut: this.inputFields,
                        shortcutStatus: this.isDisableShortcut
                    });
                } else {
                    instance.hidePreloader = false;
                }
            },
            postDataThenFunctionality(response) {
                let instance = this;
                instance.redirect("/profile-view");
                this.$emit("shortcut", this.isShortcutsActive);
                instance.hidePreloader = false;
            },
            postDataCatchFunctionality(error) {
                let instance = this;
                instance.hidePreloader = false;
            },
            inputClick(val){
                this.inputFieldValue = val;
            }
        }
    }
</script>
