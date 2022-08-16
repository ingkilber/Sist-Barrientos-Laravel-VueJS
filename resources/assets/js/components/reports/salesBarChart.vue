<script>
    import { Bar } from 'vue-chartjs'

    export default {
        extends: Bar,
        props:['sales','labels'],
        data(){
            return{
                hidePreLoader:'',
            }
        },
        mounted () {
            this.barChart(this.sales,this.labels);
        },
        methods:
            {
                barChart(sales,labels){
                    this.renderChart({
                        labels:labels,
                        datasets: [
                            {
                                label: this.trans('lang.sales'),
                                backgroundColor: '#26c6da',
                                data:sales,
                            },
                        ],
                        options: {
                            scales: {
                                yAxes: [{
                                    stacked: false
                                }],
                                xAxes: [ {
                                    stacked: true
                                }]
                            },
                            legend: {
                                display: true
                            }
                        }
                    },{
                        responsive: true,
                        maintainAspectRatio: false,
                    })
                }
            }
    }
</script>