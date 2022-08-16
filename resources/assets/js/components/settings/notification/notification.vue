<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.notification') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="hidePreLoader!='hide'" />
            <form v-else>
                <div class="table-responsive notification-setting-wrapper">
                    <table class="table mb-0">
                        <tr>
                            <td class="text-left pl-0">
                                <div class="custom-control custom-switch d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="customSwitch1"
                                        v-model="lowStockNotification"
                                        @click="managerNotification"
                                        :disabled="permission_key != 'manage'"
                                    />
                                    <label
                                        class="custom-control-label cursor-pointer"
                                        for="customSwitch1"
                                    />
                                </div>
                            </td>
                            <td class="text-left">{{trans('lang.notify_manager_for_low_stock')}}</td>
                            <td class="text-right pr-0 d-none">
                                <div v-if="permission_key == 'manage'">
                                    <a
                                        href
                                        class="btn action-button-notification"
                                        data-toggle="modal"
                                        data-target="#time-add-edit-modal"
                                    >
                                        <i class="fa fa-clock-o fa-2x" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
        </div>

        <!--Time Add Edit Modal-->
        <div
            class="modal fade"
            id="time-add-edit-modal"
            tabindex="-1"
            role="dialog"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered short-modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-layout-header">
                        <div class="row">
                            <div class="col-10">
                                <h4 class="m-0">{{ trans('lang.add_notification_time') }}</h4>
                            </div>
                            <div class="col-2 text-right">
                                <button
                                    type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    aria-label="Close"
                                    @click.prevent
                                >
                                    <i class="la la-close icon-modal-cross" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="modal-layout-content">
                        <pre-loader v-if="!timeModalPreloader" />
                        <form v-else>
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label for="time">{{ trans('lang.notification_time') }}</label>
                                    <timeselector
                                        class="form-control"
                                        id="time"
                                        v-model="selectedTime"
                                    />
                                    <div class="heightError">
                                        <small
                                            class="text-danger"
                                            v-show="errors.has('time')"
                                        >{{ errors.first('time') }}</small>
                                    </div>
                                </div>
                            </div>
                            <button
                                v-if="permission_key == 'manage'"
                                type="submit"
                                class="btn btn-primary app-color mobile-btn"
                                @click.prevent="notificationSettingUpdate()"
                            >{{ trans('lang.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axiosGetPost from "../../../helper/axiosGetPostCommon";
import Timeselector from "vue-timeselector";

export default {
    extends: axiosGetPost,
    components: {
        Timeselector
    },
    props: ["permission_key"],
    data() {
        return {
            hidePreLoader: "",
            selectedTime: "",
            lowStockNotification: false,
            timeModalPreloader: true
        };
    },
    created() {
        this.getNotificationSettings();
    },
    mounted() {},
    methods: {
        managerNotification() {
            let instance = this;
            instance.hidePreLoader = "";
            this.axiosPost(
                "/low-stock-notification-setting-save",
                {
                    low_stock_notification: this.lowStockNotification
                },
                function(response) {
                    window.location.reload();
                },
                function(error) {
                    instance.showErrorAlert(error.data.message);
                    instance.hidePreLoader = "hide";
                }
            );
        },
        getNotificationSettings() {
            let instance = this;
            instance.timeModalPreloader = false;
            instance.axiosGet(
                "/notification-setting",
                function(response) {
                    let defaultTime = response.data.notificationTime;
                    let date = new Date(defaultTime);
                    instance.selectedTime = date;
                    instance.lowStockNotification = parseInt(
                        response.data.lowStockNotification
                    );
                    instance.hidePreLoader = "hide";
                    instance.timeModalPreloader = true;
                },
                function(error) {
                    instance.hidePreLoader = "hide";
                    instance.timeModalPreloader = true;
                }
            );
        },
        notificationSettingUpdate() {
            this.$validator.validateAll().then(result => {
                if (result) {
                    let instance = this;
                    instance.timeModalPreloader = false;
                    this.axiosPost(
                        "/notification-setting-save",
                        {
                            notification_time: this.selectedTime
                        },
                        function(response) {
                            instance.getNotificationSettings();
                            $("#time-add-edit-modal").modal("hide");
                            instance.timeModalPreloader = false;
                        },
                        function(error) {
                            instance.timeModalPreloader = false;
                            instance.showErrorAlert(error.data.message);
                        }
                    );
                }
            });
        }
    }
};
</script>