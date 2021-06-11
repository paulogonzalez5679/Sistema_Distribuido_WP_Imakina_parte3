(function ($) {
    'use strict';
    $(document).ready(function () {
        var classes = [
            'post-type-stm-zoom-webinar',
        ];

        if ($('body').is("." + classes.join(', .'))) {
            $('#adminmenu > li').removeClass('wp-has-current-submenu wp-menu-open');

            $('#toplevel_page_stm_zoom')
                .addClass('wp-has-current-submenu wp-menu-open')
                .removeClass('wp-not-current-submenu');

            $('.toplevel_page_stm_zoom')
                .addClass('wp-has-current-submenu')
                .removeClass('wp-not-current-submenu');
        }
    })
})(jQuery);