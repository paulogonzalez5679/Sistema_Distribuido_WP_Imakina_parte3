<div class="zoom_pro_notice">
    <div class="free">
        <img src="<?php echo STM_ZOOM_URL; ?>/assets/images/zoom_icon.png" width="40"/>
        <div class="zoom_title">eRoom</div>
        <div class="zoom_subtitle">Free version</div>
    </div>
    <div class="pro">
        <a href="#" target="_blank">Pro features</a>
    </div>
</div>
<script>
    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('#stm_zoom_pro_popup').hide();
            $('.zoom_pro_notice .pro a').on('click', function (e) {
                e.preventDefault();
                $('#stm_zoom_pro_popup').fadeIn();
            })
        })
    })(jQuery);
</script>