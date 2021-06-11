<?php if(!empty($_GET['show_meeting'])): ?>
    <?php include get_zoom_template('single/meeting_view.php'); ?>
<?php else: ?>
<?php get_header(); ?>
<?php echo do_shortcode('[stm_zoom_webinar post_id="'. get_the_ID() . '" hide_content_before_start=""]'); ?>
<?php get_footer(); ?>
<?php endif; ?>
