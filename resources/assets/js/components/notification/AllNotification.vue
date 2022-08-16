<template>
    <div class="admin-layout-margin">
        <div class="row margin-fix">
            <div class="col s12 m12 l12 xl12">
                <div class="card">
                    <div class="card-content">
                        <div class="settings-card-wrapper">
                            <div class="row">
                                <div class="col s8">
                                    <h5 class="bluish-text" >{{ trans('lang.all_notifications') }}</h5>
                                    <ul class="notifications">
                                        <li v-for="item in data"><a @click.prevent="upNotify(item.id)" class="truncate" :class="{'unread-notification':  item.read_by.indexOf(profile.id)==-1}"> {{ item.event }}</a></li>
                                    </ul>
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
        props:['data','profile'],

        methods:{
            upNotify(id){
                axios.post('up-notify/'+id, {
                    read_by: this.profile.id
                })
                location.href = "/booking/"+id;
            },
        }
    }
</script>