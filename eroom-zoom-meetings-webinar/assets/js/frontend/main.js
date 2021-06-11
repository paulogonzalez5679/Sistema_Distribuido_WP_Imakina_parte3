(function ($) {
    'use strict';
    $(document).ready(function () {
        $('.stm_zooom_countdown').each(function () {
            var ts = $(this).data('timer');
            ts = parseInt(ts) * 1000;
            $(this).countdown({
                timestamp: ts,
                callback: function (days, hours, minutes, seconds) {
                    var summaryTime = days + hours + minutes + seconds;
                    if (summaryTime === 0) {
                        location.reload();
                    }
                }
            });
        })
    });
})(jQuery);