<script>
    import { Line } from 'vue-chartjs';

    export default {
        data(){
            return{
                item:[],
            }
        },
        extends: Line,
        mounted() {
            this.getData();
        },
        methods:{
            setChartLoader: function(e) {
                this.$emit('setChartLoader', e);
            },
            renderLineChart(lineArray){
                this.renderChart({
                    labels: [this.trans('lang.january'), this.trans('lang.february'), this.trans('lang.march'), this.trans('lang.april'), this.trans('lang.may'), this.trans('lang.june'), this.trans('lang.july'), this.trans('lang.august'), this.trans('lang.september'), this.trans('lang.october'), this.trans('lang.november'), this.trans('lang.december')],
                    datasets: [
                        {
                            label: 'Data One',
                            backgroundColor: 'rgba(38, 199, 219, 1)',
                            data: lineArray
                        }
                    ]
                }, {responsive: true, maintainAspectRatio: false})
            },
            getData(){
                axios.get('/dashBoard').then(response => {
                    this.item = response.data;
                    this.setChartLoader(false);
                    this.renderLineChart(this.item.monthlyArray)
                }).then(function(){

                });
            }
        }
    }
</script>
