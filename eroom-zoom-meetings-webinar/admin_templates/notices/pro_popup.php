<div class="stm_zoom_pro_popup <?php if(!empty(get_transient('stm_zoom_hide_popup'))) echo 'hidden'; ?>" id="stm_zoom_pro_popup">
    <div class="zoom_overlay"></div>
    <div class="zoom_popup">
        <div class="zoom_close"></div>
        <div class="popup_title">
            <h2>Purchasable Meetings</h2>
        </div>
        <div class="popup_subtitle">
            No extra tools are required, everything is at your hand. Easily sell your meetings online on the website.
        </div>
        <div class="popup_image">
            <img src="<?php echo STM_ZOOM_URL . '/assets/images/popup_image.png'; ?>" />
        </div>
        <div class="popup_footer">
            <div class="text"><a href="https://stylemixthemes.com/zoom-meetings-webinar-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020" target="_blank">Get eRoom PRO</a> today and stay connected anywhere at any time!</div>
            <a href="https://stylemixthemes.com/zoom-meetings-webinar-plugin/?utm_source=admin&utm_medium=promo&utm_campaign=2020" class="pro_button" target="_blank">
                More Details
            </a>
        </div>
    </div>
</div>
<script>
    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('.stm_zoom_pro_popup .zoom_overlay, .stm_zoom_pro_popup .zoom_close').on('click', function (e) {
                e.preventDefault();
                $(this).closest('.stm_zoom_pro_popup').fadeOut();
                $.ajax({
                    url: stm_zoom_ajaxurl,
                    method: 'post',
                    type: 'json',
                    data: {
                        action: 'stm_zoom_hide_popup',
                        hide: true,
                    }
                })
            });
        })
    })(jQuery);
</script>