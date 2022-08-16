export const FilterCloseMixin = {
    created() {
        this.preventDropdownMenuCloseOnInsideClick();
    },
    methods:{
        closeDropDown() {
            $(".dropdown-menu").removeClass('show');
        },
        preventDropdownMenuCloseOnInsideClick() {
            $(document).on('click.bs.dropdown.data-api', '.dropdown.keep-inside-clicks-open', function (e) {
                e.stopPropagation();
            });
        }
    }

}
