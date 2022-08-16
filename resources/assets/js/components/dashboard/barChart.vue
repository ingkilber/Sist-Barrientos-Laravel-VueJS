<script>
    import {Bar} from 'vue-chartjs'

    export default {
        extends: Bar,
        props: ['item', 'permission'],
        data() {
            return {
                hidePreLoader: '',
            }
        },
        mounted() {
            // Overwriting base render method with actual data.
            if (this.item.profit === false) this.userSalesReport(this.item.userChartData.sales);
            else this.barChart(this.item.sales, this.item.profit);
        },
        methods:
            {
                userSalesReport(sales) {
                    this.renderChart({
                        labels: [this.trans('lang.jan'), this.trans('lang.feb'), this.trans('lang.mar'), this.trans('lang.apr'), this.trans('lang.may'), this.trans('lang.jun'), this.trans('lang.jul'), this.trans('lang.aug'), this.trans('lang.sep'), this.trans('lang.oct'), this.trans('lang.nov'), this.trans('lang.dec')],
                        datasets: [
                            {
                                label: this.trans('lang.sales'),
                                backgroundColor: '#26c6da',
                                data: sales,
                            },
                        ],
                        options: {
                            scales: {
                                yAxes: [{
                                    stacked: false
                                }],
                                xAxes: [{
                                    stacked: true
                                }]
                            },
                            legend: {
                                display: true
                            }
                        }
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                    })
                },
                barChart(sales, profit) {
                    let instance = this;
                    let dataSetArr = [];
                    if (this.permission != 'personal') {
                        dataSetArr = [
                            {
                                label: this.trans('lang.sales'),
                                backgroundColor: '#26c6da',
                                data: sales,
                            },
                            {
                                label: this.trans('lang.profit'),
                                backgroundColor: '#2879ff',
                                data: profit,
                            }
                        ];
                    } else {
                        dataSetArr = [
                            {
                                label: this.trans('lang.sales'),
                                backgroundColor: '#26c6da',
                                data: sales,
                            }
                        ];
                    }
                    instance.renderChart({
                        labels: [this.trans('lang.jan'), this.trans('lang.feb'), this.trans('lang.mar'), this.trans('lang.apr'), this.trans('lang.may'), this.trans('lang.jun'), this.trans('lang.jul'), this.trans('lang.aug'), this.trans('lang.sep'), this.trans('lang.oct'), this.trans('lang.nov'), this.trans('lang.dec')],
                        datasets: dataSetArr,
                        options: {
                            scales: {
                                yAxes: [{
                                    stacked: false
                                }],
                                xAxes: [{
                                    stacked: true
                                }]
                            },
                            legend: {
                                display: true
                            }
                        }
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                    })
                },
            }
    }
</script>