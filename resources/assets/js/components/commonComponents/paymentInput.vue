<template>
    <input type="text" :id="id" class="form-control" v-model="value" @keyup="checkData" :disabled="salesOrReturnType=='returns'"
           onkeydown="return event.keyCode == 69 ? false : true">
</template>
<script>
    const {unformat} = require('number-currency-format');
    import axiosGetPost from '../../helper/axiosGetPostCommon';

    export default {
        extends: axiosGetPost,
        props: ['id', 'inputValue', 'index', 'salesOrReturnType'],

        data() {
            return {
                value: '',
                previousValue: '',
            }
        },
        watch: {
            value: function (newVal) {
                this.processData(newVal);
            },
            inputValue: function (newValue) {

                this.editData();
            },
        },
        created() {
            this.editData();
        },
        methods: {
            editData() {
                let instance = this;
                if (this.inputValue) {
                    this.value = this.inputValue;
                }
            },
            checkData(value) {
                let formattedNumber = '';
                if (this.value && this.value != this.currencySymbol && this.value != '-') {
                    this.value = this.value[0] + this.value.slice(1).split("-").join("");
                    if (this.value != '-') {
                        formattedNumber = unformat(this.value, {
                            thousandSeparator: this.thousandSeparator,
                            decimalSeparator: this.decimalSeparator,
                        });
                    }
                }

                this.$emit('input', formattedNumber, this.index, this.value);
            },

            processData(newVal) {
                let allowChar, check, checkDecimalSep;

                if (this.thousandSeparator === "space") this.thousandSeparator = ' ';

                allowChar = new RegExp("[^0-9-\b" + this.thousandSeparator + this.decimalSeparator + "\b]", "is");

                this.$set(this, 'value', newVal.toString().replace(allowChar, ''));
                check = this.value.substring(0, 1);

                if (check == this.decimalSeparator || check == this.thousandSeparator) this.value = this.value.substring(1);

                let currentChar = newVal.toString().slice(-1);
                if (currentChar == this.thousandSeparator || currentChar == this.currencySymbol || currentChar == this.decimalSeparator) {

                    checkDecimalSep = newVal.toString().split(this.decimalSeparator).length - 1;

                    if (checkDecimalSep > 1) {
                        this.value = this.value.substring(0, this.value.length - 1);
                    }
                }
            }
        }
    }

</script>
