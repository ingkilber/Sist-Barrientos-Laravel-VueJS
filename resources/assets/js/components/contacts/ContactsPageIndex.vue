<!--suppress ALL -->
<template>
    <div>
        <div class="main-layout-wrapper" v-shortkey="loadSales" @shortkey="globalShortcutMethod()">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent m-0">
                    <li class="breadcrumb-item">
                        <span>{{trans('lang.contacts')}}</span>
                    </li>
                </ol>
            </nav>
            <div class="main-layout-card">
                <div class="custom-tabs">
                    <ul class="nav nav-tabs">
                        <li class="nav-item d-flex justify-content-center" :class="{'active':isSelectedTab(tab.name)}"
                            @click.prevent="selectTab(tab.name, tab.component)" v-if="isVisible(tab.name)"
                            v-for="tab in tabs">
                            <a class="nav-link" href="#customers" @click.prevent="isActive = 1"> {{ trans(tab.lang)
                                }} </a>
                        </li>
                    </ul>
                </div>
                <transition name="slide-fade" mode="out-in">
                    <component v-if="this.componentName" v-bind:is="this.componentName"
                               :permission="permission"></component>
                </transition>
            </div>
        </div>
    </div>
</template>
<script>
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['customers', 'suppliers', 'customer_group','tab_name','route_name'],
        data() {
            return {
                shortcuts: '',
                selectedTab: null,
                permission: '',
                componentName: null,
                loadSales: [],

                tabs: [
                    {name: "customers", lang: "lang.customers", component: "customers-index"},
                    {name: "customer_group", lang: "lang.customer_groups_dt", component: "groups-index"},
                    {name: "suppliers", lang: "lang.suppliers", component: "suppliers-page-index"},
                    {name: "shortcuts", lang: "lang.shortcuts", component: "shortcuts-setting"},

                ],
                isVisible: function (tabName) {
                    return (this[tabName] === 'manage' || this[tabName] === 'read_only');
                },
                isSelectedTab: function (tabName) {
                    return (tabName === this.selectedTab);
                },
            }
        },
        methods: {
            selectTab: function (tabName, componentName) {
                this.permission = this[tabName];
                this.selectedTab = tabName;
                this.componentName = componentName;
            },
            initSelectedTab: function () {
                var instance = this;

                this.tabs.forEach(function (tab) {
                    if (!instance.selectedTab && instance.isVisible(tab.name)) {
                        instance.selectTab(tab.name, tab.component);
                    }
                });
            }
        },
        mounted() {
            this.initSelectedTab();
            this.loadSales = this.shortCutKeyConversion();

            if (this.tab_name){
                var instance = this;
                this.tabs.forEach(function(tab) {
                    if(tab.name == instance.tab_name){
                        instance.selectTab(tab.name, tab.component);
                    }
                });
            }
        }
    }
</script>