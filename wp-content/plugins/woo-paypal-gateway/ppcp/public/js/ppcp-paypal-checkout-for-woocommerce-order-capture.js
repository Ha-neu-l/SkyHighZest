(function ($) {
    'use strict';
    $(function () {
        if ($('#ship-to-different-address-checkbox').length) {
            $('#ship-to-different-address-checkbox').prop('checked', true);
        }
        if ($('#place_order').length) {
            $('html, body').animate({
                scrollTop: ($('#place_order').offset().top - 500)
            }, 1000);

        }

        setTimeout(function () {
            $('#place_order').show();
        }, 1200);



    });
})(jQuery);