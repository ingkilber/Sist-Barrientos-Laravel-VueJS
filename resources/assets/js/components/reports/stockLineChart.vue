<script>
    import { Line } from 'vue-chartjs'

    export default {
        extends: Line,
        props:['stock','title'],
        data(){
            return{
            }
        },
        mounted () {
            this.chartRender(this.stock,this.title);
        },
        created(){

        },
        methods:
            {
                chartRender(stock,title){
                    this.renderChart({
                        datasets: [{
                            label: 'Available Stock',
                            data:stock,
                            backgroundColor: [
                                'rgba(41, 121, 255, 1)',
                                'rgba(38, 198, 218, 1)',
                                'rgba(116, 96, 238, 1)',
                                'rgba(247, 247, 247, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                        }],
                        labels:title,
                    },{
                        responsive: true,
                        maintainAspectRatio: false,

                    })
                }
            },
    }

</script>