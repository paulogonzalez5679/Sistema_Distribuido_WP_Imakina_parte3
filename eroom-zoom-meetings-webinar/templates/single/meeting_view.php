<?php
$post_id            = get_the_ID();
$post_type          = get_post_type($post_id);
$assets             = STM_ZOOM_URL . '/assets/';
$meeting_data       = get_post_meta( $post_id, 'stm_zoom_data', true );
$meeting_password   = get_post_meta( $post_id, 'stm_password', true );
$meeting_id         = '';
$settings           = get_option( 'stm_zoom_settings', array() );
$api_key            = !empty( $settings[ 'api_key' ] ) ? $settings[ 'api_key' ] : '';
$api_secret         = !empty( $settings[ 'api_secret' ] ) ? $settings[ 'api_secret' ] : '';

if ( ! empty( $meeting_data ) ) {
    $meeting_id = !empty( $meeting_data[ 'id' ] ) ? $meeting_data[ 'id' ] : '';
}

$username   = esc_attr__( 'Guest', 'eroom-zoom-meetings-webinar' );
$email      = '';

if ( is_user_logged_in() ) {
    $user       = wp_get_current_user();
    $username   = $user->user_login;
    $email      = $user->user_email;
}
?>
<!DOCTYPE html>
<head>
  <title><?php the_title(); ?></title>
  <meta charset="utf-8"/>
  <link type="text/css" rel="stylesheet" href="<?php echo $assets; ?>css/frontend/zoom/vendor.css"/>
  <meta name="format-detection" content="telephone=no">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>

<body>
<script>
    var API_KEY = '<?php echo esc_js( $api_key ); ?>';
    var leaveUrl = '<?php echo get_home_url( '/' ); ?>';
    var endpoint = '<?php echo esc_url(get_site_url()); ?>/wp-admin/admin-ajax.php?action=stm_zoom_meeting_sign';
    var meeting_id = '<?php echo esc_attr( $meeting_id ); ?>';
    var meeting_password= '<?php echo esc_attr( $meeting_password  ); ?>';
    var username= '<?php echo esc_attr( $username ); ?>';
    var email= '<?php echo esc_attr( $email ); ?>';
    var lang= 'en-US';
    var role= 0;
</script>
<script src="<?php echo esc_url( $assets ); ?>js/frontend/zoom/vendor.js"></script>
<script src="<?php echo esc_url( $assets ); ?>js/frontend/zoom/tool.js"></script>
<script src="<?php echo esc_url( $assets ); ?>js/frontend/zoom/meeting.js"></script>

</body>

</html>
