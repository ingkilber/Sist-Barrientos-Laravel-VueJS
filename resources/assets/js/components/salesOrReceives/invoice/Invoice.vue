<template>
    <div style="display:none">
        <div id="cart-print">
            <span v-html="rawHtml"></span>
        </div>
    </div>
</template>
<script>


import axiosGetPost from '../../../helper/axiosGetPostCommon';
import AppFunction from "../../../js/AppFuntion";

export default {
    extends: axiosGetPost,
    props: [
        'printInvoice',
        'rawHtml',
    ],
    data() {
        return {
            printOptions: {
                printable: 'cart-print',
                type: 'html',
                maxWidth: '',
                scanStyles: false,
                css: [
                    AppFunction.getAppUrl('css/app.css'),
                ],
            },
        };
    },

    watch: {
        printInvoice: function (newVal) {
            if (newVal) {
                this.printReceipt();
            }
        }
    },
    methods: {
        printReceipt() {
            //ref: https://printjs.crabbly.com/#documentation
            printJS(this.printOptions)
            this.$emit('resetGetInvoice', false);
        },
    }
}
</script>