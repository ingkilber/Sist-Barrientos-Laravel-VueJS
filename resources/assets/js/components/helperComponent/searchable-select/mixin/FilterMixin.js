import {FilterCloseMixin} from "./FilterCloseMixin";

export const FilterMixin = {
    mixins: [FilterCloseMixin],
    props: {
        filterKey: {
            type: String
        }
    },
    data() {
        return {
            isApply: false,
            value: null
        }
    },
    methods: {
        clear() {
            this.isApply = false;
            this.returnValue(this.value);
        },
        applyFilter(v) {
            this.isApply = true;
            this.returnValue(this.value);
            $(".dropdown-menu").removeClass('show');
        },
        returnValue(value) {
            this.$emit('get-value', {'key': this.filterKey, 'value': value})
        }
    }
}
