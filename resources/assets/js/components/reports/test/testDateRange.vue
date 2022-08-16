<template>
    <input type="text" :id="id" :name="id" />
</template>

<script>
    export default {
        props :[
            'id'
        ],
        mounted: function(){
            let self = this;
            let input = $('input[name="'+ this.id +'"]');
            input.daterangepicker();
            input.on('apply.daterangepicker', function(ev, picker) {
                self.$emit('daterangechanged',picker);
            });
        }
    }

</script>


