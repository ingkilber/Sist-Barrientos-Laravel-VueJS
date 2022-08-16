<template>
    <div>
        <div class="main-layout-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.user_details')}}</span>
                        <span><a href="#" @click="goBack">({{trans('lang.back_page')}})</a></span>
                    </li>
                </ol>
            </nav>
            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-12 col-md-4 col-lg-4 col-xl-3 pr-md-0">
                        <div class="main-layout-card">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.user_details') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="main-layout-card-content">
                                <div class="text-center profile-image-container ">
                                    <img class="img-fluid profile-image rounded-circle avatar mb-3 user-center"
                                         :src="publicPath+'/uploads/profile/'+user.avatar" alt="" width="50%">
                                </div>
                            </div>
                            <div class="main-layout-card-content">
                                <div class="text-center user-name-text">
                                    <h5 class="m-0" v-if="user.fullName">{{ user.fullName }} </h5>
                                    <h6 class="m-0" v-if="user.email"><i  style="font-size: 20px" class="la la-envelope"></i> {{ user.email }}

                                    </h6>

                                    <h6 class="m-0" v-if="user.role_id == null && user.role_id == ''"><i  style="font-size: 20px" class="la la-eye"></i> {{ user.title }}
                                    </h6>
                                    <h6 class="m-0" v-if="user.phone_number"><i  style="font-size: 20px" class="la la-phone"></i> {{
                                        user.phone_number }} </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-lg-8 col-xl-9 pr-1">
                        <div class="main-layout-card">
                            <div class="main-layout-card-header">
                                <div class="main-layout-card-content-wrapper">
                                    <div class="main-layout-card-header-contents">
                                        <h5 class="bluish-text m-0">{{ trans('lang.user_records') }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="main-layout-card-content">
                                <span v-if="hideLineChartLoader"><pre-loader></pre-loader></span>
                                <span v-else><barchart :item="item"></barchart></span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import barchart from '../../dashboard/barChart'
    import axiosGetPost from '../../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ["user","tab_name", "route_name"],

        data() {
            return {
                id: '',
                userChartData: [],
                item:{},
                hideLineChartLoader: true,
                tabName : '',
                routeName : ''

            }
        },
        created() {
            this.getChartData('/userChartData/' + this.user.id);
        },
        mounted() {
            this.tabName = this.tab_name;
            this.routeName = this.route_name;
        },
        components: {
            barchart,
        },
        methods: {
            getActiveAttributeModal(isActive) {
                this.isActiveAttributeModal = isActive;
            },
            getChartData(route) {
                let instance = this;
                instance.axiosGet(route,
                    function (response) {
                        instance.userChartData = response.data;
                        instance.item = {
                            userChartData: instance.userChartData,
                            profit:false
                        };
                        instance.hideLineChartLoader = false;
                    },
                    function (response) {
                    },
                );
            },
            goBack() {
                let instance = this;
                instance.redirect(`/${this.routeName}?tab_name=${this.tabName}&&route_name=${this.routeName}`);
            }
        },
    }

</script>
