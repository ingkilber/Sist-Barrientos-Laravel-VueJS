<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.application_settings') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="hidePreloader!='hide'"></pre-loader>
            <form v-else>
                <div class="mb-3">
                    <div class="form-row">
                        <h6 class="col">{{ trans('lang.general_settings') }}</h6>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="app-name">{{ trans('lang.app_name') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                id="app-name"
                                v-model="item.app_name"
                            />
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label>{{ trans('lang.change_app_logo') }}</label>
                            <div class="custom-file">
                                <input
                                    type="file"
                                    class="custom-file-input"
                                    id="app-logo"
                                    accept="image/*"
                                    @change="appLogo"
                                />
                                <label
                                    class="custom-file-label text-truncate"
                                    for="app-logo"
                                >{{ trans('lang.image_only') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="rows-per-table">{{ trans('lang.rows_per_table') }}</label>
                            <select
                                id="rows-per-table"
                                v-model="item.max_row_per_table"
                                class="custom-select"
                            >
                                <option disabled selected>{{ trans('lang.choose_one') }}</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label>{{ trans('lang.change_background_image') }}</label>
                            <div class="custom-file">
                                <input
                                    type="file"
                                    class="custom-file-input"
                                    id="background-image"
                                    accept="image/*"
                                    @change="backgroundImage"
                                />
                                <label
                                    class="custom-file-label text-truncate"
                                    for="background-image"
                                >{{ trans('lang.image_only') }}</label>
                            </div>
                        </div>
                    </div>

                   <!-- <div class="form-row">
                        <div class="form-group col-md-12">
                            <label>{{ trans('lang.offline_mode') }}</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    v-model="item.offline_mode"
                                    type="radio"
                                    id="offline_mode_enable"
                                    value="1"
                                />
                                <label
                                    for="offline_mode_enable"
                                    class="radio-button-label"
                                >{{ trans('lang.enable') }}</label>
                                <input
                                    class="form-check-input"
                                    v-model="item.offline_mode"
                                    type="radio"
                                    id="offline_mode_dissable"
                                    value="0"
                                />
                                <label
                                    for="offline_mode_enable"
                                    class="radio-button-label"
                                >{{ trans('lang.disable') }}</label>
                            </div>
                        </div>
                    </div>-->

                </div>
                <div class="mb-3">
                    <div class="form-row">
                        <h6 class="col">{{ trans('lang.date_time_settings') }}</h6>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label>{{ trans('lang.time_format') }}</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    v-model="item.time_format"
                                    type="radio"
                                    name="time-format"
                                    id="time-format-1"
                                    value="24h"
                                />
                                <label
                                    for="time-format-1"
                                    class="radio-button-label"
                                >{{ trans('lang.24h') }}</label>
                                <input
                                    class="form-check-input"
                                    v-model="item.time_format"
                                    type="radio"
                                    name="time-format"
                                    id="time-format-2"
                                    value="12h"
                                />
                                <label
                                    for="time-format-2"
                                    class="radio-button-label"
                                >{{ trans('lang.12h') }}</label>
                            </div>
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label for="date-format">{{ trans('lang.date-format') }}</label>
                            <select
                                id="date-format"
                                v-model="item.date_format"
                                class="custom-select"
                                data-live-search="true"
                            >
                                <option value disabled>{{ trans('lang.choose_one') }}</option>
                                <option value="d/m/Y">{{ trans('lang.dd/mm/yyyy') }}</option>
                                <option value="m/d/Y">{{ trans('lang.mm/dd/yyyy') }}</option>
                                <option value="Y/m/d">{{ trans('lang.yyyy/mm/dd') }}</option>
                                <option value="d-m-Y">{{ trans('lang.dd-mm-yyyy') }}</option>
                                <option value="m-d-Y">{{ trans('lang.mm-dd-yyyy') }}</option>
                                <option value="Y-m-d">{{ trans('lang.yyyy-mm-dd') }}</option>
                                <option value="d.m.Y">{{ trans('lang.dd_mm_yyyy') }}</option>
                                <option value="m.d.Y">{{ trans('lang.mm_dd_yyyy') }}</option>
                                <option value="Y.m.d">{{ trans('lang.yyyy_mm_dd') }}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label>{{ trans('lang.timezone_title') }}</label>
                            <select
                                id="timezone"
                                v-model="item.time_zone"
                                class="custom-select"
                                data-live-search="true"
                            >
                                <option value disabled>{{ trans('lang.choose_one') }}</option>
                                <option
                                    v-for="singleItem in timezones"
                                    :value="singleItem"
                                >{{ singleItem }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-row">
                        <h6 class="col">{{ trans('lang.currency_settings') }}</h6>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="currency-symbol">{{ trans('lang.currency_symbol') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                id="currency-symbol"
                                v-model="item.currency_symbol"
                                value="$"
                            />
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label for="currency-position">{{ trans('lang.currency_position') }}</label>
                            <select
                                id="currency-position"
                                v-model="item.currency_format"
                                class="custom-select"
                            >
                                <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                                <option value="left">$0.00</option>
                                <option value="right">0.00$</option>
                                <option value="left-space">$ 0.00</option>
                                <option value="right-space">0.00 $</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="decimal-separator">{{ trans('lang.decimal_separator') }}</label>
                            <select
                                id="decimal-separator"
                                name="thousand-separator"
                                class="custom-select"
                                v-model="item.decimal_separator"
                            >
                                <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                                <option value=".">100.11</option>
                                <option value=",">100,11</option>
                            </select>
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label for="thousand-separator">{{ trans('lang.thousand_separator') }}</label>
                            <select
                                id="thousand-separator"
                                name="thousand-separator"
                                class="custom-select"
                                v-model="item.thousand_separator"
                            >
                                <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                                <option value="space">1 00 000</option>
                                <option value=",">1,00,000</option>
                                <option value=".">1.00.000</option>
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label>{{ trans('lang.number_of_decimal') }}</label>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    v-model="item.number_of_decimal"
                                    type="radio"
                                    id="number-of-decimal-0"
                                    value="0"
                                />
                                <label for="number-of-decimal-0" class="radio-button-label">0</label>
                                <input
                                    class="form-check-input"
                                    v-model="item.number_of_decimal"
                                    type="radio"
                                    id="number-of-decimal-2"
                                    value="2"
                                />
                                <label for="number-of-decimal-2" class="radio-button-label">2</label>
                                <input
                                        class="form-check-input"
                                        v-model="item.number_of_decimal"
                                        type="radio"
                                        id="number-of-decimal-3"
                                        value="3"
                                />
                                <label for="number-of-decimal-3" class="radio-button-label">3</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-row">
                        <h6 class="col">{{ trans('lang.language_settings') }}</h6>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="language">{{ trans('lang.preferred_language') }}</label>
                            <select
                                id="language"
                                class="custom-select"
                                v-model="item.language_setting"
                            >
                                <option value disabled selected>{{ trans('lang.choose_one') }}</option>
                                <option
                                    v-for="language in languages"
                                    :value="language"
                                >{{ language.charAt(0).toUpperCase() + language.slice(1) }}</option>
                            </select>
                        </div>
                        <div class="form-group offset-md-1 col-md-5">
                            <label>{{ trans('lang.clear_language_cache') }}</label>
                            <button
                                type="submit"
                                class="btn btn-light"
                                @click.prevent="clearLanguageCache()"
                            >{{ trans('lang.clear_language_cache') }}</button>
                        </div>
                    </div>
                </div>
                <button
                    v-if="permission_key == 'manage'"
                    type="submit"
                    class="btn btn-primary app-color mobile-btn"
                    @click.prevent="appSettingsUpdate()"
                >{{ trans('lang.save') }}</button>
            </form>
        </div>
    </div>
</template>

<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";

export default {
    extends: axiosGetPost,
    props: ["permission_key", "list_data"],
    data() {
        return {
            item: {},
            backgroundImageName: "",
            is_disabled: false,
            languages: [],
            hidePreloader: "",
            timezones: []
        };
    },
    mounted() {
        let instance = this;
        this.getBasicSettingData();
        if (this.list_data != undefined) {
            this.timezones = JSON.parse(this.list_data);
        }
        $(document).ready(function() {
            $(window).keydown(function(event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    },
    //methods
    methods: {
        getBasicSettingData() {
            let instance = this;
            this.axiosGet(
                "/basic-setting-data",
                function(response) {
                    instance.item = response.data.basicData;
                    instance.languages = response.data.language;
                    instance.hidePreloader = "hide";
                },
                function(response) {
                    instance.hidePreloader = "hide";
                }
            );
        },
        appLogo(event) {
            let fileName = event.target.files[0].name;
            $("#app-logo")
                .next(".custom-file-label")
                .html(fileName);

            let input = event.target;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = e => {
                    this.item.app_logo = e.target.result;
                };
                this.app_logo = input.files[0].name;
                reader.readAsDataURL(input.files[0]);
            } else {
                this.item.app_logo = "";
            }
        },

        backgroundImage(event) {
            let fileName = event.target.files[0].name;
            $("#background-image")
                .next(".custom-file-label")
                .html(fileName);

            let input = event.target;
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = e => {
                    this.backgroundImage = e.target.result;
                };
                this.backgroundImageName = input.files[0].name;
                reader.readAsDataURL(input.files[0]);
            } else {
                this.backgroundImage = "";
            }
        },

        clearLanguageCache() {
            let instance = this;
            this.hidePreloader = "load";
            this.axiosGet("/clear-language-cache", function() {
                instance.showSuccessAlert(
                    instance.trans("lang.the_language_cache_has_been_removed")
                );
                instance.hidePreloader = "hide";
                window.location.reload();
            });
        },

        appSettingsUpdate() {
            let instance = this;
            instance.hidePreloader = "load";

            this.inputFields = {
                time_format: this.item.time_format,
                date_format: this.item.date_format,
                currency_symbol: this.item.currency_symbol,
                currency_format: this.item.currency_format,
                thousand_separator: this.item.thousand_separator,
                decimal_separator: this.item.decimal_separator,
                number_of_decimal: this.item.number_of_decimal,
                language_setting: this.item.language_setting,
                max_row_per_table: this.item.max_row_per_table,
                app_name: this.item.app_name,
                app_logo: this.item.app_logo,
                background_image: this.backgroundImage,
                offline_mode: this.item.offline_mode,
                time_zone: this.item.time_zone
            };
            this.axiosPost(
                "/basic-setting",
                this.inputFields,
                function() {
                    instance.hidePreloader = "hide";
                    window.location.reload();
                },
                function() {
                    instance.hidePreloader = "hide";
                }
            );
        }
    }
};
</script>