<template>
    <div class="search-filter-dropdown">
        <div class="dropdown w-100" :class="{'disabled':data.disabled}">
            <a href="#"
               class="btn btn-filter btn-block w-100 px-3"
               :class="{'applied': value}"
               id="dropdownMenuLink"
               @click="startNavigation"
               data-toggle="dropdown"
               aria-haspopup="true"
               aria-expanded="false">
                {{showValue ? showValue : data.label}}
                <span v-if="data.visibleValue">{{ selectedValue}}</span>
                <span class="clear-icon" v-if="value" @click.prevent="clear">
                    <i class="fa fa-times"></i>
                </span>
            </a>
            <div class="dropdown-menu w-100 py-0" :class="data.listClass" aria-labelledby="dropdownMenuLink">
                <div class="btn-dropdown-close d-sm-none">
                    <span class="title">
                        {{data.label}}
                    </span>
                    <span class="back float-right" @click.prevent="closeDropDown">
                        <i class="fa fa-times"></i>
                    </span>
                </div>
                <div class="form-group form-group-with-search p-4">
                    <span class="form-control-feedback">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </span>
                    <input type="text"
                           ref="searchInput"
                           :class="'form-control '+data.listItemInputClass"
                           :placeholder="trans('lang.search_product')"
                           v-bind:value="searchValue"
                           @input="getSearchValue($event)"
                           @keydown.down="navigateDown"
                           @keydown.up="navigateUp"
                           @keydown.enter.prevent="enterSelectedValue"
                           :autofocus="startNavigation" />
                </div>
                <div class="dropdown-divider my-0"/>
                <div class="dropdown-search-result-wrapper custom-scrollbar" ref="optionList">
                    <a class="dropdown-item"
                       href="#"
                       v-for="(item,index) in allList"
                       :class="{'active':index==activeIndex, [data.listItemClass]: !isUndefined(data.listItemClass),'selected':item.id == value, 'disabled':item.disabled}"
                       @click="changeSelectedValue(item)"
                       :key="item.id+index">
                        {{item[data.listValueField]}}
                        <span v-if="item.id === value" class="check-sign float-right">
                            <i class="fa fa-check"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {InputMixin} from './mixin/InputMixin.js';
    import {FilterCloseMixin} from "./mixin/FilterCloseMixin";
    import axiosGetPost from "../../../helper/axiosGetPostCommon";
    export default {
        name: "SmartSelect",
        mixins: [InputMixin, FilterCloseMixin],
        extends: axiosGetPost,
        data() {
            return {
                selectedValue: '',
                searchValue: "",
                activeIndex: -1,
                navigationStart: false,
                dataSet : [],
                showValue: ""
            }
        },
        computed: {
            allList() {
                this.activeIndex = -1;
                if(this.data.url){
                    return this.dataSet;
                }else{
                    if (!_.isEmpty(this.searchValue)) {
                        return this.data.list.filter(item => {
                            return item[this.data.listValueField].toLowerCase().includes(this.searchValue.toLowerCase());
                        });
                    } else return this.data.list;
                }
            }
        },
        created(){
            this.getData();
        },
        methods: {
            getSearchValue($event){
                this.searchValue = $event.target.value;
                setTimeout(() => this.getData(), 1500);
            },
            changed(){
                this.$emit('input', this.selectedValue);
            },
            changeSelectedValue(value) {
                this.selectedValue = value.id;
                this.searchValue = "";
                this.showValue = value[this.data.listValueField];
                this.navigationStart = false;
                this.changed();
            },
            startNavigation() {
                this.activeIndex = -1;
                this.navigationStart = true;
                setTimeout(() => {
                    this.$refs.searchInput.focus();
                    this.$refs.optionList.scrollTop = 0;
                }, 300);
            },
            navigateUp() {
                if (this.activeIndex >= 1) {
                    this.activeIndex--;
                    // this.selectedValue = this.allList[this.activeIndex];
                    let sH = this.$refs.optionList.scrollHeight;
                    let aH = this.$refs.optionList.offsetHeight;
                    if (sH > aH) {
                        //need to adjust scroll height
                        let adS = this.activeIndex == 0 ? 0 : this.$refs.optionList.scrollTop - (sH - aH) / (this.allList.length);
                        this.$refs.optionList.scrollTo(0, adS)
                    }
                }
            },
            navigateDown() {
                if (this.activeIndex < this.allList.length - 1 && this.activeIndex > -2) {
                    this.activeIndex++;
                    // this.selectedValue = this.allList[this.activeIndex];
                    let sH = this.$refs.optionList.scrollHeight;
                    let aH = this.$refs.optionList.offsetHeight;
                    if (sH > aH && this.activeIndex > 0) {
                        //need to adjust scroll height
                        let adS = this.activeIndex == this.allList.length - 1 ? (sH - aH) : this.$refs.optionList.scrollTop + (sH - aH) / (this.allList.length);
                        this.$refs.optionList.scrollTo(0, adS)
                    }
                }
            },
            enterSelectedValue() {
                this.allList.filter((item, index) => {
                    if (index == this.activeIndex) {
                        this.changeSelectedValue(item)
                    }
                });
                this.endNavigation();
            },
            endNavigation() {
                this.activeIndex = -1;
                this.navigationStart = false;
                $(".dropdown-menu").removeClass('show');
            },
            clear(event) {
                event.stopPropagation();
                this.selectedValue = '';
                this.showValue = '';
                this.changed();
            },
            getData(){
                const url = this.data.url;
                const req = {
                    params : {
                        search : this.searchValue
                    }
                };
                axios.get(`${window.appConfig.appUrl+url}`, req)
                    .then(res => {
                        this.dataSet = res.data;
                    })
                    .catch(err => {
                        console.error(err);
                    });
            }
        }
    }
</script>