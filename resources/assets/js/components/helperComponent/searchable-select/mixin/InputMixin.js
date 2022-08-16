export const InputMixin = {
    props: {
        data: {
            default: null
        },
        value: {}
    },
    data() {
        return {
            fieldValue: '',
            inputFieldId: '',
        }
    },
    computed: {
        // name() {
        //     return this.$parent.name;
        // },
        listeners() {
            return {
                ...this.$listeners,
                input: event => {
                    if (this.data.type == 'file') {
                        const file = event.target.files[0];
                        this.fieldValue = file;
                        this.$emit('input', this.fieldValue);
                    } else
                        this.$emit('input', event.target.value);
                }
            }
        }
    },
    created() {
        this.inputFieldId = this.data.id ? this.data.id : this.data.name;
    },
};
