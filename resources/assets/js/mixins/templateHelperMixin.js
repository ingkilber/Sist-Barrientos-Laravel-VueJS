export default {
    mounted() {
        this.$nextTick(() => {
            // Prevent dropdown menu from closing inside click
            $(document).on('click.bs.dropdown.data-api', '.dropdown.keep-inside-clicks-open', function (e) {
                e.stopPropagation();
            });
        });
    }
};