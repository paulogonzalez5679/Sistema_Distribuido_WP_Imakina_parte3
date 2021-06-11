<?php

class StmZoom
{
    /**
     * @return StmZoom constructor.
     */
    function __construct()
    {
        register_activation_hook( STM_ZOOM_FILE,  array( $this, 'plugin_activation_hook' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ) );

        add_action( 'wp_head', array( $this, 'head' ) );

        add_shortcode( 'stm_zoom_conference', array( $this, 'add_meeting_shortcode' ) );

        add_shortcode( 'stm_zoom_webinar', array( $this, 'add_webinar_shortcode' ) );

        add_shortcode( 'stm_zoom_conference_grid', array( $this, 'add_meeting_grid_shortcode' ) );

        add_filter( 'single_template', array( $this, 'single_zoom_template' ) );
    }

    /**
     * Plugin Activation Hook
     */
    public function plugin_activation_hook()
    {
        if ( empty( get_option( 'eroom_installed' ) ) ) {
            update_option( 'eroom_installed',  time() );
        }
    }

    /**
     * Load Single Meeting or Webinar Template
     * @param $template
     * @return bool|string
     */
    public function single_zoom_template( $template )
    {
        global $post;
        if ( $post->post_type == 'stm-zoom' ) {
            $template = get_zoom_template( 'single/main.php' );
        } elseif ( $post->post_type == 'stm-zoom-webinar' ) {
            $template = get_zoom_template( 'single/main-webinar.php' );
        }
        return $template;
    }

    /**
     * Enqueue Frontend Styles & Scripts
     */
    public function frontend_enqueue()
    {
        wp_enqueue_script( 'stm_jquery.countdown', STM_ZOOM_URL . '/assets/js/frontend/jquery.countdown.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
        wp_enqueue_script( 'stm_zoom_main', STM_ZOOM_URL . '/assets/js/frontend/main.js', array( 'jquery' ), STM_ZOOM_VERSION, true );
        wp_enqueue_style( 'stm_zoom_main', STM_ZOOM_URL . '/assets/css/frontend/main.css', false, STM_ZOOM_VERSION );
        wp_enqueue_style( 'linear', STM_ZOOM_URL . '/wp-custom-fields-theme-options/assets/linearicons/linear.css', array(), STM_ZOOM_VERSION );
    }

    /**
     * Define Frontend Translation Variables
     */
    public function head()
    {
        ?>
        <script>
            var daysStr = "<?php esc_html_e( 'Days', 'eroom-zoom-meetings-webinar' ); ?>";
            var hoursStr = "<?php esc_html_e( 'Hours', 'eroom-zoom-meetings-webinar' ); ?>";
            var minutesStr = "<?php esc_html_e( 'Minutes', 'eroom-zoom-meetings-webinar' ); ?>";
            var secondsStr = "<?php esc_html_e( 'Seconds', 'eroom-zoom-meetings-webinar' ); ?>";
        </script>
        <?php
    }

    /**
     * Add Meeting Shortcode
     * @param $atts
     * @return string
     */
    public function add_meeting_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            'post_id' => '',
            'hide_content_before_start' => ''
        ), $atts );
        $content = '';
        $hide_content_before_start = '';
        if ( ! empty( $atts[ 'hide_content_before_start' ] ) ) {
            $hide_content_before_start = '1';
        }
        if ( ! empty( $atts[ 'post_id' ] ) ) {
            $content = self::add_zoom_content( $atts[ 'post_id' ], $hide_content_before_start );
        }
        return $content;
    }

    /**
     * Add Webinar Shortcode
     * @param $atts
     * @return string
     */
    public function add_webinar_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            'post_id' => '',
            'hide_content_before_start' => ''
        ), $atts );
        $content = '';
        $hide_content_before_start = '';
        if ( ! empty( $atts[ 'hide_content_before_start' ] ) ) {
            $hide_content_before_start = '1';
        }
        if ( ! empty( $atts[ 'post_id' ] ) ) {
            $content = self::add_zoom_content( $atts[ 'post_id' ], $hide_content_before_start, true );
        }
        return $content;
    }

    /**
     * Add Meetings Grid Shortcode
     * @param $atts
     * @return string
     */
    public function add_meeting_grid_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            'count' => '',
            'post_type' => '',
            'per_row' => '',
        ), $atts );

        if ( !empty( $atts[ 'count' ] ) ) {
            $count = intval( $atts[ 'count' ] );
        } else {
            $count = 3;
        }

        $per_row = !empty( $atts[ 'per_row' ] ) ? intval( $atts[ 'per_row' ] ) : '';

        $post_type = 'stm-zoom';

        if ( ! empty( $atts[ 'post_type' ] ) ) {
            if ( $atts[ 'post_type' ] != 'product' ) {
                $post_type = $atts[ 'post_type' ];
            } elseif ( class_exists( 'StmZoomPro' ) ) {
                $post_type = 'product';
            }
        }

        $args = array(
            'posts_per_page' => $count,
            'post_type' => $post_type,
        );

        if ( $post_type === 'product' ) {
            $args = array(
                'posts_per_page' => $count,
                'post_type' => 'product',
                'meta_query' => array(
                    array(
                        'key' => '_meeting_id',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            );
        }

        ob_start();

        $q = new WP_Query( $args );

        if ( $q->have_posts() ) {
            $users = StmZoom::stm_zoom_get_users();
            ?>
            <div class="stm_zoom_grid_container">
                <div class="stm_zoom_grid per_row_<?php echo esc_attr( $per_row ); ?>">
                    <?php
                    while ( $q->have_posts() ) {
                        $q->the_post();
                        $path = get_zoom_template('loop/single-meeting.php');
                        include $path;
                    }
                    ?>
                </div>
            </div>
            <?php
        }

        wp_reset_postdata();

        $content = ob_get_clean();

        return $content;
    }

    /**
     * Zoom Meeting Content
     * @param $post_id
     * @param string $hide_content_before_start
     * @return string
     */
    public static function add_zoom_content( $post_id, $hide_content_before_start = '', $webinar = false )
    {
        $content = '';
        if ( ! empty( $post_id ) ) {
            $post_id        = intval( $post_id );
            $meeting_data   = self::meeting_time_data( $post_id );
            if ( !empty( $meeting_data ) && ! empty( $meeting_data[ 'meeting_start' ] ) && ! empty( $meeting_data[ 'meeting_date' ] ) ) {
                $meeting_start = $meeting_data[ 'meeting_start' ];
                $meeting_date = $meeting_data[ 'meeting_date' ];
                $is_started = $meeting_data[ 'is_started' ];
                if ( ! $is_started ) {
                    $content = self::countdown( $meeting_date, false,  $webinar );
                    if ( empty( $hide_content_before_start ) ) {
                        $content .= self::zoom_content( $post_id, $meeting_start, $webinar );
                    }
                } else {
                    $content = self::zoom_content( $post_id, $meeting_start, $webinar );
                }
            }
        }
        return $content;
    }

    /**
     * Collect Meeting Data
     * @param $post_id
     * @return array|bool
     */
    public static function meeting_time_data( $post_id )
    {
        if ( empty( $post_id ) )
            return false;

        $r = array();
        $post_id        = intval( $post_id );
        $start_date     = get_post_meta( $post_id, 'stm_date', true );
        $start_time     = get_post_meta( $post_id, 'stm_time', true );
        $timezone       = get_post_meta( $post_id, 'stm_timezone', true );
        $meeting_start  = strtotime( 'today', ( apply_filters('eroom_sanitize_stm_date', $start_date ) / 1000 ) );

        if ( ! empty( $start_time ) ) {
            $time = explode( ':', $start_time );
            if ( is_array( $time ) and count( $time ) === 2 ) {
                $meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
            }
        }

        $meeting_start = date( 'Y-m-d H:i:s', $meeting_start );

        if ( empty( $timezone ) ) {
            $timezone = 'UTC';
        }

        $meeting_date   = new DateTime( $meeting_start, new DateTimeZone( $timezone ) );
        $meeting_date   = $meeting_date->format( 'U' );
        $is_started     = ( $meeting_date > time() ) ? false : true;

        $r[ 'meeting_start' ]   = $meeting_start;
        $r[ 'meeting_date' ]    = $meeting_date;
        $r[ 'is_started' ]      = $is_started;

        return $r;
    }

    /**
     * Meeting Countdown
     * @param string $time
     * @param bool $hide_title
     * @return string
     */
    public static function countdown( $time = '', $hide_title = false, $webinar = false )
    {
        if ( ! empty( $time ) ) {
            $countdown = '<div class="zoom_countdown_wrap">';
            if ( ! $hide_title ) {
                $title = ( $webinar ) ? esc_html__('Webinar starts in', 'eroom-zoom-meetings-webinar') : esc_html__('Meeting starts in', 'eroom-zoom-meetings-webinar') ;
                $countdown .= '<h2 class="countdown_title">' . $title . '</h2>';
            }
            $countdown .= '<div class="stm_zooom_countdown" data-timer="' . esc_attr( $time ) . '"></div></div>';

            return $countdown;
        }
    }

    /**
     * Zoom Meeting Content Template
     * @param $post_id
     * @param $meeting_start
     * @return string
     */
    public static function zoom_content( $post_id, $meeting_start, $webinar = false )
    {
        if ( ! empty( $post_id ) ) {
            $zoom_data = get_post_meta( $post_id, 'stm_zoom_data', true );
            if ( ! empty( $zoom_data ) && ! empty( $zoom_data[ 'id' ] ) ) {
                $meeting_id = sanitize_text_field( $zoom_data[ 'id' ] );
                $title      = get_the_title( $post_id );
                $agenda     = get_post_meta( $post_id, 'stm_agenda', true );
                $password   = get_post_meta( $post_id, 'stm_password', true );

                ob_start();
                ?>
                <div class="stm_zoom_content">
                    <?php if ( has_post_thumbnail( $post_id ) ) { ?>
                        <div class="zoom_image">
                            <?php echo get_the_post_thumbnail( $post_id, 'large' ); ?>
                        </div>
                    <?php } ?>
                    <div class="zoom_info">
                        <h2><?php esc_html_e( $title ); ?></h2>
                        <?php if ( !empty( $meeting_start ) ) { ?>
                            <div class="date">
                                <span><?php echo ( $webinar ) ? esc_html_e( 'Webinar date', 'eroom-zoom-meetings-webinar' ) : esc_html_e( 'Meeting date', 'eroom-zoom-meetings-webinar' ); ?> </span>
                                <b>
                                    <?php
                                  
                                    $date_format    = get_option( 'date_format', 'd M Y H:i' );
                                    $time_format    = get_option( 'time_format', 'H:i' );
                                    $format         = $date_format . ' ' . $time_format;
                                    $date           = strtotime( $meeting_start );
                                    $date           = date_i18n($format, $date );
                                    esc_html_e( $date );
                                   
                                    ?>
                                </b>
                            </div>
                        <?php } ?>
                        <?php if ( !empty( $password ) ) { ?>
                            <div class="password">
                                <span><?php esc_html_e( 'Password: ', 'eroom-zoom-meetings-webinar' ); ?></span>
                                <span class="value"><?php esc_html_e( $password ); ?></span>
                            </div>
                        <?php } ?>
                        <a href="<?php echo add_query_arg( array( 'show_meeting' => '1' ), get_permalink( $post_id ) ); ?>" class="btn stm-join-btn join_in_menu" target="_blank">
                            <?php esc_html_e( 'Join in browser', 'eroom-zoom-meetings-webinar' ); ?>
                        </a>
                        <a href="https://zoom.us/j/<?php echo esc_attr( $meeting_id ); ?>" class="btn stm-join-btn outline" target="_blank">
                            <?php esc_html_e( 'Join in zoom app', 'eroom-zoom-meetings-webinar' ); ?>
                        </a>
                    </div>
                    <div class="zoom_description">
                        <?php if ( ! empty( $agenda ) ) { ?>
                            <div class="agenda">
                                <?php echo wp_kses_post( $agenda ); ?>
                            </div>
                        <?php } ?>
                        <div id="zmmtg-root"></div>
                        <div id="aria-notify-area"></div>
                    </div>
                </div>
                <?php
                $content = ob_get_clean();

                return $content;
            }
        }
    }

    /**
     * Get Zoom Users from Zoom API
     * @return array
     */
    public static function stm_zoom_get_users()
    {
        $users      = get_transient( 'stm_zoom_users' );
        $settings   = get_option( 'stm_zoom_settings', array() );

        if ( empty( $users ) ) {
            $api_key    = ( ! empty( $settings[ 'api_key' ] ) ) ? $settings[ 'api_key' ] : '';
            $api_secret = ( ! empty( $settings[ 'api_secret' ] ) ) ? $settings[ 'api_secret' ] : '';
            if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
                $users_data = new \Zoom\Endpoint\Users( $api_key, $api_secret );
                $users_list = $users_data->userlist();
                if ( ! empty( $users_list ) && ! empty( $users_list[ 'users' ] ) ) {
                    $users = $users_list[ 'users' ];
                    set_transient( 'stm_zoom_users', $users, 36000 );
                }
            } else {
                $users = array();
            }
        }
        return $users;
    }

    /**
     * Get Zoom Users
     * @return array
     */
    public static function get_users_options()
    {
        $users = self::stm_zoom_get_users();
        if ( ! empty( $users ) ) {
            foreach ( $users as $user ) {
                $first_name         = $user[ 'first_name' ];
                $last_name          = $user[ 'last_name' ];
                $email              = $user[ 'email' ];
                $id                 = $user[ 'id' ];
                $user_list[ $id ]   = $first_name . ' ' . $last_name . ' (' . $email . ')';
            }
        } else {
            return array();
        }
        return $user_list;
    }

    /**
     * Get Users for Autocomplete
     * @return array
     */
    static function get_autocomplete_users_options()
    {
        $users  = self::get_users_options();
        $result = array();
        foreach ( $users as $id => $user ) {
            $result[] = array(
                'id' => $id,
                'title' => $user,
                'post_type' => ''
            );
        }
        return $result;
    }
}
