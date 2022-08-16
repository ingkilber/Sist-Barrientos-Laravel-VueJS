(function ($) {

    $.fn.responsiveTabs = function ($tabContentSelector, options) {

        if (!options) var options = {};

        var self = this,
            dropdownElement = options.dropdownElement ? options.dropdownElement : '.tab-dropdown',
            dropdownMenuElement = options.dropdownMenuElement ? options.dropdownMenuElement : '.js-tab-dropdown',
            $dropdownSetector = $(dropdownElement),
            $dropdownMenuSelector = $(dropdownMenuElement),
            totalWidth = $(self).width() - 150,
            tabs = self.children('a.nav-link'),
            tabWidth = 0;

        //Removed previous appended item
        $dropdownMenuSelector.html('');

        $.each(tabs, function (index, value) {

            var $this = $(this),
                tabItemWidth = $this.outerWidth(),
                href = $this.attr('href'),
                display = 'block';

            tabWidth += tabItemWidth;

            if (totalWidth < tabWidth) {
                display = 'none';
                $dropdownSetector.show();

                //Drop-down menu append
                var hasActiveClass = $this.hasClass('active'),
                    tabName = $this.text(),
                    activeClass = '';

                if (hasActiveClass) activeClass = 'active';

                $dropdownMenuSelector.append('<a class="dropdown-item js-dropdown-item ' + activeClass + '" href="' + href + '">' + tabName + '</a>');

                if (activeClass) resetTabName($(self), tabName);

            } else {

                $dropdownSetector.hide();
                resetTabName($(self), 'More');
            }

            $this.css({display: display});

            //Selected tab as selected tab content
            var activeTabContentId = $tabContentSelector.find('.active').attr('id');
            if (href === '#' + activeTabContentId) $this.addClass('active');
        });

        setTimeout(function () {
            $('.js-dropdown-item').click(function (e) {
                var $tabSelector = $(e.target);

                $('.js-dropdown-item').removeClass('active');
                $tabSelector.addClass('active');

                resetTabName($(self), $tabSelector.text());
            });
        });
    };
})($);

//Reset responsive tab name
var resetTabName = function ($selector, tabName) {

    $selector.find(".tab-more-text").text(tabName);

    if (tabName === 'More') {
        $selector.find(".tab-more-text").parent().removeClass('active');
    } else {
        $selector.find(".tab-more-text").parent().addClass('active');
    }
}