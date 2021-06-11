<div id="stm_zoom_message" class="notice notice-info ulisting-review-message">
    <p>Hey! We are so happy you choose <strong>eRoom</strong> plugin. If you enjoy using it, can we ask you to give it a <strong>5-star rating</strong>?
        Thank you for helping us out!</p>
    <p class="submit">
        <a href="https://wordpress.org/support/plugin/eroom-zoom-meetings-webinar/reviews/?filter=5#new-post" class="add_review button-primary" target="_blank">OK, you deserve it</a>
        <a href="javascript:void(0);" class="skip_review button-secondary">Nope, maybe later</a>
        <a href="javascript:void(0);" class="did_review button-secondary">I already did</a>
    </p>
</div>
<script>
    (function ($) {
        'use strict';
        $(document).ready(function () {
            $('#stm_zoom_message a').on('click', function () {
                $(this).closest('#stm_zoom_message').fadeOut();
                var skip = '';
                if($(this).hasClass('skip_review')){
                    skip = '1';
                }
                $.ajax({
                    url: stm_zoom_ajaxurl,
                    method: 'post',
                    type: 'json',
                    data: {
                        action: 'stm_zoom_review',
                        skip: skip,
                    }
                })
            });
        })
    })(jQuery);
</script>