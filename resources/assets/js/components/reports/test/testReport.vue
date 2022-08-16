<template>
    <div>
        <label>{{dateRange}}</label>

        <br/>
        <date-range-picker id="dfdfd" v-on:dateRangeChanged="onDateRangeChanged"></date-range-picker>
    </div>



</template>

<script>
    export default {
        data: {
            dateRange: "ciao",
            startRange: "",
            endRange: ""
        },
        methods:{
            onDateRangeChanged: function(picker){
                this.dateRange = picker.startDate + " - " + picker.endDate;
            }
        }
    }
</script>
