<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper p-2">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.sales_statistics') }}</h5>
                </div>
            </div>
        </div>
        <div class="line-header">
            <div class="form-row">
                <div class="col-12 col-sm-4">
                    <label>{{ trans('lang.employee') }}</label>
                    <div class="input-field">
                        <div class="sales-filter-menu">
                            <select id="employee" v-model="user" @change="onChange($event)" class="custom-select">
                                <option value="">{{ trans('lang.all') }}</option>
                                <option v-for="user in users" :value="user.id">
                                    {{ user.first_name+' '+user.last_name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <label>{{ trans('lang.branch') }}</label>
                    <div class="input-field">
                        <div class="sales-filter-menu">
                            <select id="branch" v-model="branch" @change="onChange($event)" class="custom-select">
                                <option value="">{{ trans('lang.all') }}</option>
                                <option v-for="branch in branches" :value="branch.id">{{ branch.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <label>{{ trans('lang.time_period') }}</label>
                    <div class="input-field">
                        <div class="sales-filter-menu">
                            <select id="year" v-model="duration" @change="onChange($event)" class="custom-select">
                                <option value="this_year">{{ trans('lang.this_year') }}</option>
                                <option value="last_year">{{ trans('lang.last_year') }}</option>
                                <option value="this_month">{{ trans('lang.this_month') }}</option>
                                <option value="last_month">{{ trans('lang.last_month') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <span v-if="hideLineChartLoader"><pre-loader></pre-loader></span>
            <span v-else><sales-bar-chart :sales="sales" :labels="labels"></sales-bar-chart></span>
        </div>
    </div>
</template>

<script>

    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        data() {
            return {
                hideLineChartLoader: true,
                sales: [],
                salesFilterData: [],
                labels: [],
                dailyData: [],
                branches: [],
                users: [],
                user: '',
                branch: '',
                duration: ''
            }
        },
        methods: {
            getSalesChartData() {
                let instance = this;
                instance.salesFilterData = {
                    user: this.user,
                    branch: this.branch,
                    duration: this.duration,
                };
                instance.axiosPost('/yearly-sales-chart', {
                        filterData: instance.salesFilterData
                    },
                    function (response) {
                        instance.salesChartData(response.data)
                    },
                );
            },
            onChange(event) {
                this.year = event.target.value;
                this.hideLineChartLoader = true;
                this.getSalesChartData();
            },
            salesChartData(data) {
                let instance = this;
                instance.duration = data.salesFilterData['duration'];

                if (data.salesFilterData['user'] !== null) instance.user = data.salesFilterData['user'];
                if (data.salesFilterData['branch'] !== null) instance.branch = data.salesFilterData['branch'];

                if (instance.duration === 'this_year' || instance.duration === 'last_year') {

                    instance.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    instance.sales = data.salesChartData;
                } else {

                    let days = data.days, salesData = data.salesChartData, i, obj;
                    instance.labels = [];
                    instance.sales = [];
                    for (i = 0; i < days; i++) {

                        obj = salesData.find(sales => sales.day - 1 === i);

                        obj ?  instance.sales.push(obj.sales) :  instance.sales.push(0);

                        instance.labels.push(i + 1);
                    }
                }
                instance.hideLineChartLoader = false;
            },
            getSupportingData() {
                let instance = this;
                this.axiosGet('/branch-user',
                    function (response) {
                        instance.users = response.data.user;
                        instance.branches = response.data.branch;
                    },
                );
            }
        },
        created() {
            this.getSalesChartData();
            this.getSupportingData();
        }
    }
</script>