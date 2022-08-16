<template>
    <div>
        <div class="main-layout-card-header-with-button">
            <div class="main-layout-card-content-wrapper p-2">
                <div class="main-layout-card-header-contents">
                    <h5 class="m-0">{{ trans('lang.available_stock') }}</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <span v-if="hideLineChartLoader"><pre-loader></pre-loader></span>
            <span v-else><stock-line-chart :stock="stock" :title="title"></stock-line-chart></span>
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
                stock: [],
                title: [],
            }
        },
        methods: {
            getStockChartData() {
                let instance = this;
                this.axiosGet('/available-stock-chart',
                    function (response) {

                        let stockChartData = response.data;

                        stockChartData.forEach(function (stockData) {
                            instance.stock.push(stockData.qtty);
                            if (stockData.attributes === 'default_variant') {
                                instance.title.push(stockData.prod_title);
                            } else {
                                instance.title.push(stockData.prod_title + " " + stockData.attributes);
                            }
                        });
                        instance.hideLineChartLoader = false;
                    },
                    function (response) {
                    },
                );
            }
        },
        created() {
            this.getStockChartData();
        }
    }
</script>