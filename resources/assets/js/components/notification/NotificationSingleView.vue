<template>
    <div class="admin-layout-margin">
        <div class="row margin-fix">
            <div class="col s12 m12 l12 xl12">
                <div class="card">
                    <!--<preloaders :loderType="preloaderType" :hideLoader="hidePreloader"></preloaders>-->
                    <div class="card-content">
                        <div class="settings-card-wrapper">
                            <div class="row margin-fix">
                                <div class="row">
                                    <div class="col s12">
                                        <!--<h5 class="bluish-text" >{{data.notiTitle}}</h5>-->
                                            <table>
                                                <thead>
                                                <tr>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                                </thead>

                                                <tbody>
                                                <tr>
                                                    <td>{{ trans('lang.product_title') }}</td>
                                                    <td v-for="lists in list">{{lists.prod_id}}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ trans('lang.stock_qty') }}</td>
                                                    <td>{{ data.qty}}</td>
                                                </tr>
                                                <!--<tr>-->
                                                    <!--<td>Booking Time</td>-->
                                                    <!--<td >-->
                                                        <!--<div v-for="booking in data.booking_time">-->
                                                            <!--{{ booking }}-->
                                                        <!--</div>-->
                                                    <!--</td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>Quantity</td>-->
                                                    <!--<td>{{ data.quantity }}</td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>Status</td>-->
                                                    <!--<td>{{ data.status }}</td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>Comment</td>-->
                                                    <!--<td>{{ data.comment }}</td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>Booking at</td>-->
                                                    <!--<td>{{ data.created_at }}</td>-->
                                                <!--</tr>-->
                                                </tbody>
                                            </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props:['data'],

        data(){
            return{
                list:[],
            }
        },

        mounted(){
            this.getObject();
        },

        methods:{
            getObject(){
                this.list = this.data;
            }
        }
    }
</script>