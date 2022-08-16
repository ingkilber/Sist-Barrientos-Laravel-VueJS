<script>
    import { Doughnut } from 'vue-chartjs'

    export default {
        extends: Doughnut,
        data(){
            return{
                dataValue:[],
                dataLabel:[],
                item:[]
            }
        },
        mounted () {
        },
        created(){

            axios.get('/donoughData').then(response => {
                this.item = response.data;
                this.chartRender(this.item.serviceCount,this.item.serviceName);

            }).then(function () {

            });

        },
        methods:
        {
            chartRender(serviceCount,serviceName)
            {
                this.renderChart({
                        datasets: [{
                            data:this.item.serviceCount,
                            backgroundColor: [
                                'rgba(41, 121, 255, 1)',
                                'rgba(38, 198, 218, 1)',
                                'rgba(116, 96, 238, 1)',
                                'rgba(247, 247, 247, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                        }],
                        labels:this.item.serviceName, options: {
                            scales: {
                                yAxes: [{
                                    stacked: true
                                }],
                                xAxes: [ {
                                    stacked: true
                                }]
                            },
                            legend: {
                                display: true
                            }
                        }
                    },
                    {responsive: true,
                        maintainAspectRatio: false,
                        cutoutPercentage: 70})
            }
        }
    }
</script>