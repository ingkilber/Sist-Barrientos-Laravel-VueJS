<template>
    <div class="main-layout-card">
        <div class="main-layout-card-header">
            <div class="main-layout-card-content-wrapper">
                <div class="main-layout-card-header-contents">
                    <h5 class="bluish-text m-0">{{ trans('lang.corn_job') }}</h5>
                </div>
            </div>
        </div>
        <div class="main-layout-card-content">
            <pre-loader v-if="!hidePreLoader"></pre-loader>
            <table v-else class="table custom-table-responsive mb-0">
                <tr>
                    <td width="25%" class="border-top-0 pl-0 pt-0">{{ trans('lang.corn_job_link') }}</td>
                    <td width="75%" class="pr-0 pt-0 border-top-0">
                        <p class="bg-light p-2 mb-0">
                            <span>{{cronJobUrl}}</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td width="25%" class="pl-0">{{ trans('lang.last_corn_job_run') }}</td>
                    <td width="75%" class="pr-0">
                        <p v-if="lastRunTime != ''" class="bg-light p-2 mb-0">
                            <span>{{lastRunTime}}</span>
                        </p>
                        <p v-else class="mb-0">
                            <span class="badge badge-danger p-2">{{trans('lang.naver') }}</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="pl-0" width="25%">{{ trans('lang.recommended_execution_interval') }}</td>
                    <td class="pr-0" width="75%">{{ trans('lang.daily_one') }}</td>
                </tr>
                <tr>
                    <td
                        class="pl-0 border-bottom-0"
                        width="25%"
                    >{{ trans('lang.cpanel_corn_job_command') }}</td>
                    <td class="pr-0 border-bottom-0" width="75%">
                        <p class="bg-light p-2">
                            <span>wget {{cronJobUrl}}</span>
                        </p>or
                        <p class="bg-light p-2 mb-0">
                            <span>wget -q -0- {{cronJobUrl}}</span>
                        </p>
                    </td>
                </tr>
            </table>
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
            hidePreLoader: true,
            lastRunTime: "",
            cronJobUrl: this.appUrl + "/corn-job"
        };
    },

    created() {
        this.getNotificationSettings();
    },

    methods: {
        getNotificationSettings() {
            let instance = this;
            instance.hidePreLoader = false;
            instance.axiosGet(
                "/corn-log-last-obj",
                function(response) {
                    if (response.data != "") {
                        let date = new Date(response.data.created_at);
                        instance.lastRunTime = date;
                    }
                    instance.hidePreLoader = true;
                },
                function(error) {
                    instance.hidePreLoader = true;
                }
            );
        }
    }
};
</script>