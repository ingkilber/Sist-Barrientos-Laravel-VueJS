<script>
    import { Doughnut } from 'vue-chartjs';

    export default {
        extends: Doughnut,
        data(){
            return{
                item:[],
            }
        },
        mounted() {
            this.getData();
        },
        methods:{
            setChartLoader: function(e) {
                this.$emit('setChartLoader', e);
            },
            renderDoughnutChart(serviceName,serviceData){
                this.renderChart({
                    datasets: [{
                        data: serviceData,
                        backgroundColor: [
                            'rgba(41, 121, 255, 1)',
                            'rgba(38, 198, 218, 1)',
                            'rgba(116, 96, 238, 1)',
                            'rgba(247, 247, 247, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: serviceName,
                },{responsive: true, maintainAspectRatio: false, cutoutPercentage: 80})
            },
            getData(){
                axios.get('/dashBoard').then(response => {
                    this.item = response.data;
                    this.setChartLoader(false);
                    this.renderDoughnutChart(this.item.serviceName,this.item.serviceCount)
                }).then(function(){

                });
            }
        },
    }
</script>
