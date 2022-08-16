<script>
    import { Line } from 'vue-chartjs'

    export default {
        extends: Line,
        props:['lineChartData'],
        data(){
            return{
            }
        },
        mounted () {
            this.chartRender(this.lineChartData.profit,this.lineChartData.days);
        },
        created(){

        },
        methods:
            {
                chartRender(profit,days){
                    this.renderChart({
                        datasets: [{
                            label: this.trans('lang.profit'),
                            data:profit,
                            backgroundColor: [
                                'rgba(41, 121, 255, 1)',
                                'rgba(38, 198, 218, 1)',
                                'rgba(116, 96, 238, 1)',
                                'rgba(247, 247, 247, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                        }],
                        labels:days,
                    },{
                        responsive: true,
                        maintainAspectRatio: false,

                    })
                }
            },
    }

</script>