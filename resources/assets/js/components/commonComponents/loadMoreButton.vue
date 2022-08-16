<template>
    <button class="btn btn-light mobile-btn d-inline-flex" type="submit" @click.prevent="submit()" :disabled="isDisabled" >
        <div class="float-left" v-if="buttonLoader"> <roller-loader></roller-loader></div>
        <div :class="{buttonText:buttonLoader}">{{ trans('lang.load_more') }}</div>
    </button>
</template>
<script>
    export default {
        props:['buttonLoader','isDisabled'],
        data() {
            return {
            }
        },
        mounted(){

        },
        methods:{
            submit(){
                let instance=this;
                instance.$emit('submit');
            },
        }
    }
</script>
