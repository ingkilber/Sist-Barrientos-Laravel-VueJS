<template>
    <div class="main-layout-wrapper">
        <div class="main-layout-card">
            <div class="custom-tabs">
                <ul class="nav nav-tabs">
                    <li class="nav-item d-flex justify-content-center" :class="{'active':isSelectedTab(tab.name)}" @click.prevent="selectTab(tab.name, tab.component)"  v-if="isVisible(tab.name)" v-for="tab in tabs">
                        <a class="nav-link" href="#customers" @click.prevent="isActive = 1"> {{ trans(tab.lang) }} </a>
                    </li>
                </ul>
            </div>
            <transition name="slide-fade" mode="out-in">
                <component v-if="this.componentName" v-bind:is="this.componentName"></component>
            </transition>
        </div>
    </div>
</template> 

<script>

    export default {
        props:['customers','customer_group'],
        data() {
            return {
                selectedTab: null,
                componentName: null,
                tabs:[
                    { name:"customers",lang:"lang.customers",  component: "customers-index" },
                ],
                isVisible: function (tabName) {
                    return (this[tabName] =="1");
                },
                isSelectedTab: function (tabName) {
                    return (tabName === this.selectedTab);
                },
            }
        },
        methods: {
            selectTab: function (tabName, componentName) {
                this.selectedTab = tabName;
                this.componentName = componentName;
            },
            initSelectedTab:function(){
                var instance = this;

                this.tabs.forEach(function(tab) {
                    if(!instance.selectedTab && instance.isVisible(tab.name)){
                        instance.selectTab(tab.name, tab.component);
                    }
                });
            }
        },
        mounted(){
            this.initSelectedTab();
        }
    }
</script>