<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.updates_setting') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content" v-if="!isPurchased">
            <form>
                <div>
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="purchase-key">{{ trans('lang.purchase_key') }}</label>
                            <input
                                    type="text"
                                    class="form-control"
                                    id="purchase-key"
                                    v-model="purchase_key"
                            />
                        </div>
                    </div>
                </div>
                <button
                        type="submit"
                        class="btn btn-primary app-color mobile-btn"
                        @click.prevent="savePurchaseKey()"
                >{{ trans('lang.save') }}
                </button>
            </form>
        </div>
        <span v-else>
             <!--old-->
            <div class="main-layout-card-content" v-if="hidePreloader!='hide'">
                <div class="text-center">
                    <div class="d-table w-100 large-loader-container">
                        <div class="d-table-cell align-middle">
                            <update-loader size="smaller-preloader"></update-loader>
                            <h5
                                    class="center-align mt-1"
                                    v-if="!installingVersion"
                            >{{ trans('lang.checking_for_updates')}}</h5>
                            <h5
                                    class="center-align mt-1"
                                    v-else
                            >{{ trans('lang.installing_version') +' '+installingVersion}}</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-layout-card-content" v-else>
                <div v-if="updates.length>0">
                    <div class="backup-reminder text-center p-3 mb-2">{{trans('lang.backup_reminder')}}</div>
                </div>
                <div>{{ trans('lang.current_version') +': '+ version }}</div>
                <div class="mt-2" v-if="updates.length>0 && permission_key == 'manage'">
                    <h6>{{trans('lang.available_updates')}}:</h6>
                    <ul>
                        <li v-for="update in updates">
                            <a
                                    href="#"
                                    @click.prevent="unzipData(update.version)"
                            >{{ trans('lang.click_to_install_version')+' '+ update.version }}</a>
                        </li>
                    </ul>
                </div>
                <div v-else class="mt-2">{{ trans('lang.no_updates_found') }}</div>
            </div>
        </span>
    </div>
</template>
<script>

    import axiosGetPost from "../../../helper/axiosGetPostCommon";

    export default {
        extends: axiosGetPost,

        props: ["permission_key"],
        data() {
            return {
                version: "",
                updates: [],
                noUpdates: "",
                preloaderType: "",
                hidePreloader: "",
                installingVersion: "",
                isPurchased: true,
                purchase_key: '',
            };
        },
        created() {
            this.checkPurchaseKey();
        },
        methods: {
            savePurchaseKey() {
                let instance = this;
                this.inputFields = {
                    purchase_key: this.purchase_key,
                };

                this.axiosPost(
                    "/purchase-key-save",
                    this.inputFields,
                    function (response) {
                        instance.hidePreloader = "hide";
                        window.location.reload();
                    },
                    function (error) {
                        instance.hidePreloader = "hide";
                    }
                );
            },
            checkPurchaseKey() {
                let instance = this;
                this.axiosGet(
                    "/check-purchase-key",
                    function (response) {
                        instance.isPurchased = response.data.isPurchased;
                        if (instance.isPurchased) {
                            instance.currentVersion();
                        }
                    },
                    function (response) {
                        instance.setPreloader("load", "hide");
                    }
                );
            },
            setPreloader(type, action) {
                this.preloaderType = type;
                this.hidePreloader = action;
            },
            currentVersion() {
                let instance = this;
                instance.setPreloader("load", "");
                this.axiosGet(
                    "/gain-update",
                    function (response) {
                        let app_version = response.data;
                        instance.version = app_version.app_version;
                        instance.downloadUpdates();
                    },
                    function (response) {
                        instance.setPreloader("load", "hide");
                    }
                );
            },
            versionUpdates() {
                let instance = this;
                this.axiosGet(
                    "/update-version-list",
                    function (response) {
                        instance.updates = response.data;
                        instance.setPreloader("load", "hide");
                    },
                    function (response) {
                        instance.setPreloader("load", "hide");
                    }
                );
            },
            downloadUpdates() {
                let instance = this;
                this.axiosGet(
                    "/curl_get_contents",
                    function (response) {
                        instance.versionUpdates();
                    },
                    function (error) {
                        instance.showErrorAlert(error.response.data.message);
                        instance.setPreloader("load", "hide");
                    }
                );
            },
            unzipData(update) {
                let instance = this;
                this.installingVersion = update;
                this.setPreloader("load", "");
                instance.axiosPost(
                    "/install-new-version/" + update,
                    {},
                    function (response) {
                        instance.setPreloader("load", "hide");
                        instance.clearLanguageCache();
                        window.location.reload();
                    },
                    function (error) {
                        instance.setPreloader("load", "hide");
                    }
                );
            },
            clearLanguageCache() {
                let instance = this;
                this.hidePreloader = "load";

                this.axiosGet("/clear-language-cache", function () {
                    instance.hidePreloader = "hide";
                });

                this.axiosGet("/storage-link", function () {
                    instance.hidePreloader = "hide";
                });
            }
        }
    };
</script>