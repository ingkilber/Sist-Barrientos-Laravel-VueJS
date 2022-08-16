<template>
    <div>
        <div class="main-layout-wrapper" v-shortkey="loadSales" @shortkey="globalShortcutMethod()">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.dashboard')}}</span>
                    </li>
                </ol>
            </nav>
            <div class="cardContainer">
                <!--top section cards starts-->
                <div class="cardLayer">
                    <div class="row mr-0">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 layer1">
                            <div class="card shadow-sm card1">
                                <div class="card-body">
                                    <div class="parent">
                                        <div class="child1">
                                            <i class="la la-cart-plus"></i>
                                        </div>
                                        <div class="child2">
                                            <h3 v-if="maxLength<9">{{dbInfo.todaySales}}</h3>
                                            <h4 v-else-if="maxLength<11">{{dbInfo.todaySales}}</h4>
                                            <h5 v-else-if="maxLength<14">{{dbInfo.todaySales}}</h5>
                                            <h6 v-else>{{dbInfo.todaySales}}</h6>
                                            <p>{{ trans('lang.today_sell') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 layer2">
                            <div class="card shadow-sm card2">
                                <div class="card-body">
                                    <div class="parent">
                                        <div class="child1">
                                            <i class="la la-cart-plus"></i>
                                        </div>
                                        <div class="child2">
                                            <h3 v-if="maxLength<9">{{dbInfo.monthlySale}}</h3>
                                            <h4 v-else-if="maxLength<11">{{dbInfo.monthlySale}}</h4>
                                            <h5 v-else-if="maxLength<14">{{dbInfo.monthlySale}}</h5>
                                            <h6 v-else>{{dbInfo.monthlySale}}</h6>
                                            <p>{{ trans('lang.last_thirty_days_sales') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-4 layer3">
                            <div class="card shadow-sm card3">
                                <div class="card-body">
                                    <div class="parent">
                                        <div class="child1">
                                            <i class="la la-cart-plus"></i>
                                        </div>
                                        <div class="child2">
                                            <h3 v-if="maxLength<9">{{dbInfo.totalSale}}</h3>
                                            <h4 v-else-if="maxLength<11">{{dbInfo.totalSale}}</h4>
                                            <h5 v-else-if="maxLength<14">{{dbInfo.totalSale}}</h5>
                                            <h6 v-else>{{dbInfo.totalSale}}</h6>
                                            <p>{{ trans('lang.total_sales') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--top section cards ends-->
                <!--graph section starts-->
                
                <div class="graphLayer">
                    <div class="row mr-0" id="container">
                        <div class="col-12 col-lg-8 layer1" :class="{'col-12 col-lg-12 layer1': see_profit_permission == 'personal'}">
                            <div class="card shadow-sm card1" id="left-column">
                                <div class="barheader" v-if="see_profit_permission == 'personal'">
                                    <span>{{ trans('lang.sales_overview') }}</span>
                                    <h6 class="subtitle">{{ trans('lang.chart_sub_title') }}</h6>
                                </div>
                                <div class="barheader" v-else>
                                    <span>{{ trans('lang.bar_chart_title') }}</span>
                                    <h6 class="subtitle">{{ trans('lang.chart_sub_title') }}</h6>
                                </div>
                                <hr>
                                <div class="card-body">
                                    <span v-if="hideChartLoader"><pre-loader></pre-loader></span>
                                    <span v-else> <barchart :item="item" :permission="see_profit_permission"></barchart></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 layerDoughnut" v-show="see_profit_permission != 'personal'">
                            <div>
                                <div class="card shadow-sm card3" id="right-column">
                                    <div class="barheader">
                                        <span>{{ trans('lang.doughnut_chart_title') }}</span>
                                        <h6 class="subtitle">{{ trans('lang.line_chart_sub_title') }}</h6>
                                    </div>
                                    <hr>
                                    <div class="card-body">
                                        <span v-if="hideChartLoader"><pre-loader></pre-loader></span>
                                        <span v-else><linechart :lineChartData="lineChartData"></linechart></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--graph section ends-->
                <!--bottom section cards strats-->
                <div class="bottomLayer" v-show="see_profit_permission != 'personal'">
                    <div class="row mr-0 card-deck dashboard-bottom">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 layer1 card shadow-sm card1">
                            <div class="card-body">
                                <div class="parent">
                                    <div class="child1">
                                        <h3><i class="la la-money"></i> {{dbInfo.todayProfit}}</h3>
                                    </div>
                                </div>
                                <div class="child2">
                                    <h5>{{ trans('lang.today_profit') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 layer2 card shadow-sm card2">
                            <div class="card-body">
                                <div class="parent">
                                    <div class="child1">
                                        <h3><i class="la la-money"></i> {{dbInfo.monthlyProfit}}</h3>
                                    </div>
                                </div>
                                <div class="child2">
                                    <h5>{{ trans('lang.last_thirty_days_profit') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 layer3 card shadow-sm card3">
                            <div class="card-body">
                                <div class="parent">
                                    <div class="child1">
                                        <h3><i class="la la-money"></i> {{dbInfo.totalProfit}}</h3>
                                    </div>
                                </div>
                                <div class="child2">
                                    <h5>{{ trans('lang.total_profit') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    import barchart from './barChart'
    import linechart from './lineChart'
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        props: ["hidePreloader", "see_profit_permission"],
        extends: axiosGetPost,
        data() {
            return {
                dbInfo: {},
                item: [],
                lineChartData: [],
                hideChartLoader: true,
                maxLength : 0,
                loadSales:[],
            }
        },
        components: {
            barchart,
            linechart
        },
        methods: {
            getDashboardData(route) {
                let instance = this,
                    dataLength = '';
                this.axiosGet(route,
                    function (response) {
                        let dashboardData = response.data;
                        instance.dbInfo = dashboardData.basicData;
                        _.forIn(instance.dbInfo, function(value, key) {
                            dataLength = value.toString().length;
                            if (dataLength>instance.maxLength) instance.maxLength = dataLength;
                        });

                        // sales
                        instance.dbInfo.todaySales = instance.numberFormat(instance.dbInfo.todaySales);
                        instance.dbInfo.monthlySale = instance.numberFormat(instance.dbInfo.monthlySale);
                        instance.dbInfo.totalSale = instance.numberFormat(instance.dbInfo.totalSale);
                        instance.dbInfo.totalReturn = instance.numberFormat(instance.dbInfo.totalReturn);
                        // profit
                        instance.dbInfo.todayProfit = instance.numberFormat(instance.dbInfo.todayProfit);
                        instance.dbInfo.monthlyProfit = instance.numberFormat(instance.dbInfo.monthlyProfit);
                        instance.dbInfo.totalProfit = instance.numberFormat(instance.dbInfo.totalProfit);

                        instance.item = dashboardData.barChartData;
                        instance.lineChartData = dashboardData.lineChartData;
                        instance.hideChartLoader = false;
                    },
                    function (response) {
                    },
                );
            },
        },
        created() {
            this.getDashboardData('/dashboard-data');
            this.loadSales = this.shortCutKeyConversion();
        }
    }
</script>