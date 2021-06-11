<?php

class StmZoomAdminNotices
{
    /**
     * @return StmZoomAdminNotices constructor.
     */
    function __construct()
    {
        add_filter( 'wp_ajax_stm_zoom_review', array( $this, 'review' ) );

        add_filter( 'wp_ajax_stm_zoom_hide_popup', array( $this, 'hide_popup' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Add Shortcodes Tab under Zoom Settings
        add_filter( 'wpcfto_lms_field_shortcodes', function () {
            return STM_ZOOM_PATH . '/includes/additional_fields/shortcodes.php';
        });

        // Add Pro Banner under Zoom Settings
        add_action( 'wpcfto_settings_screen_stm_zoom_settings_after', function() {
            if ( ! defined('STM_ZOOM_PRO_PATH') ) {
                include STM_ZOOM_PATH . '/admin_templates/notices/pro_banner.php';
            }
        });
    }

    /**
     * Set Review Transient
     */
    public function review()
    {
        $period = MONTH_IN_SECONDS * 1000;
        if ( ! empty( $_POST[ 'skip' ] ) )
            $period = WEEK_IN_SECONDS;
        set_transient( 'stm_zoom_review', '1', $period );
    }

    /**
     * Set Hide Popup Transient
     */
    public function hide_popup()
    {
        set_transient( 'stm_zoom_hide_popup', '1', MONTH_IN_SECONDS );
        wp_send_json( 'OK' );
    }

    /**
     * Show Pro Notices
     */
    public function admin_notices()
    {
        if ( empty( get_transient( 'stm_zoom_hide_popup' ) ) && ! defined( 'STM_ZOOM_PRO_PATH' ) && ( empty( $_GET[ 'post_type' ] ) || $_GET[ 'post_type' ] !== 'stm-zoom') ) {
            include STM_ZOOM_PATH . '/admin_templates/notices/pro_popup.php';
        }

        if ( ! empty( $_GET[ 'post_type' ] ) && ($_GET[ 'post_type' ] === 'stm-zoom' || $_GET[ 'post_type' ] === 'stm-zoom-webinar') && ! defined( 'STM_ZOOM_PRO_PATH' ) ) {
            include STM_ZOOM_PATH . '/admin_templates/notices/pro_popup.php';
            include STM_ZOOM_PATH . '/admin_templates/notices/pro_notice.php';
        }

        $installed_date = get_option( 'eroom_installed', time() );
        if ( time() - (7 * 24 * 60 * 60) > $installed_date ) {
            if ( empty( get_transient( 'stm_zoom_review' ) ) ) {
                include STM_ZOOM_PATH . '/admin_templates/notices/review.php';
            }
        }
    }

}