<?php

class StmZoomPostTypes
{
    /**
     * @return StmZoomPostTypes constructor.
     */
    function __construct()
    {
        add_action( 'init', array( $this, 'stm_zoom_register_post_type' ), 10 );

        if ( is_admin() )
            self::stm_zoom_metaboxes();

        add_action( 'add_meta_boxes', array( $this, 'stm_zoom_add_custom_box' ) );

        add_action( 'save_post', array( $this, 'update_meeting' ), 10 );

        add_filter( 'wp_ajax_stm_zoom_sync_meetings_webinars', array( $this, 'stm_zoom_sync_meetings_webinars' ) );

        add_action( 'bookit_appointment_status_changed', array( $this, 'stm_zoom_bookit_edit_add_meeting' ), 100, 1 );

        add_action( 'bookit_appointment_updated', array( $this, 'stm_zoom_bookit_edit_add_meeting' ), 100, 1 );

        add_action( 'save_post', array( $this, 'change_date_if_empty' ), 100, 1 );

        add_action( 'wp_ajax_stm_zoom_meeting_sign', array( $this, 'generate_signature' ));
    
        add_action( 'wp_ajax_nopriv_stm_zoom_meeting_sign', array( $this, 'generate_signature' ));
    }

	/**
	 * Generate Signature
	 */
    public function  generate_signature (){
    
        $request =  file_get_contents('php://input');;
        $request = json_decode( $request);
        $api_key = $request->api_key;
        $meeting_number = $request->meetingNumber;
        $role = $request->role;
        $settings           = get_option( 'stm_zoom_settings', array() );
        $api_secret = !empty( $settings[ 'api_secret' ] ) ? $settings[ 'api_secret' ] : '';
      
        $time = time() * 1000 - 30000;
        
        $data = base64_encode($api_key . $meeting_number . $time . $role);
        
        $hash = hash_hmac('sha256', $data, $api_secret, true);
        
        $_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode($hash);
        
        $res = rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
        $results = array($res);
         echo  wp_json_encode( $results);
        wp_die();
    }

	/**
	 * @param $post_id
	 */
    public function change_date_if_empty($post_id)
    {
        $post_type = ! empty( $_POST[ 'post_type' ] ) ? sanitize_text_field( $_POST[ 'post_type' ] ) : '';
  
        if ( empty( $post_type ) ) {
            $post_type = get_post_type( $post_id );
        }
  
        if ( $post_type === 'stm-zoom' || $post_type === 'stm-zoom-webinar' ) {
            $start_date = ! empty( $_POST[ 'stm_date' ] ) ? apply_filters('eroom_sanitize_stm_date', $_POST['stm_date']) : '';
            $timezone   = ! empty( $_POST[ 'stm_timezone' ] ) ? sanitize_text_field( $_POST[ 'stm_timezone' ] ) : '';
            $start_date = $this->current_date($start_date, $timezone);

            update_post_meta($post_id, 'stm_date', $start_date);
        }

    }

    /**
     * Registering Custom Post Type
     */
    public function stm_zoom_register_post_type()
    {
        $meeting_args = array(
            'labels' => array(
                'name'                  => esc_html__( 'Meetings', 'eroom-zoom-meetings-webinar' ),
                'singular_name'         => esc_html__( 'Meeting', 'eroom-zoom-meetings-webinar' ),
                'add_new'               => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
                'add_new_item'          => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
                'edit_item'             => esc_html__( 'Edit meeting', 'eroom-zoom-meetings-webinar' ),
                'new_item'              => esc_html__( 'New meeting', 'eroom-zoom-meetings-webinar' ),
                'view_item'             => esc_html__( 'View meeting', 'eroom-zoom-meetings-webinar' ),
                'search_items'          => esc_html__( 'Search meeting', 'eroom-zoom-meetings-webinar' ),
                'not_found'             => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
                'not_found_in_trash'    => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
                'menu_name'             => esc_html__( 'Meetings', 'eroom-zoom-meetings-webinar' ),
            ),
            'public'                => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => true,
            'show_ui'               => true,
            'show_in_menu'          => 'stm_zoom',
            'capability_type'       => 'post',
            'supports'              => array( 'title', 'author', 'thumbnail' ),
        );

        register_post_type( 'stm-zoom', $meeting_args ); // Calling Register Post Type

        $webinar_args = array(
            'labels' => array(
                'name' => esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
                'singular_name' => esc_html__( 'Webinar', 'eroom-zoom-meetings-webinar' ),
                'add_new' => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
                'add_new_item' => esc_html__( 'Add new', 'eroom-zoom-meetings-webinar' ),
                'edit_item' => esc_html__( 'Edit webinar', 'eroom-zoom-meetings-webinar' ),
                'new_item' => esc_html__( 'New webinar', 'eroom-zoom-meetings-webinar' ),
                'view_item' => esc_html__( 'View webinar', 'eroom-zoom-meetings-webinar' ),
                'search_items' => esc_html__( 'Search webinar', 'eroom-zoom-meetings-webinar' ),
                'not_found' => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
                'not_found_in_trash' => esc_html__( 'Not found', 'eroom-zoom-meetings-webinar' ),
                'menu_name' => esc_html__( 'Webinars', 'eroom-zoom-meetings-webinar' ),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=stm-zoom-webinar',
            'capability_type' => 'post',
            'supports' => array( 'title', 'author', 'thumbnail' ),
        );

        register_post_type( 'stm-zoom-webinar', $webinar_args ); // Calling Register Post Type
    }

    /**
     * STM Zoom Post Type Settings - Post Meta Box & Fields
     */
    public function stm_zoom_metaboxes()
    {
        // Register Meta Boxes
        add_filter( 'stm_wpcfto_boxes', function( $boxes ) {

            // Meeting Meta Box
            $boxes[ 'stm_zoom_meeting' ] = array(
                'post_type' => array( 'stm-zoom' ),
                'label'     => esc_html__( 'Post settings', 'eroom-zoom-meetings-webinar' ),
            );

            // Webinar Meta Box
            $boxes[ 'stm_zoom_webinar' ] = array(
                'post_type' => array( 'stm-zoom-webinar' ),
                'label'     => esc_html__( 'Post settings', 'eroom-zoom-meetings-webinar' ),
            );

            return $boxes;
        } );

        // Register Webinar Meta Box Fields
        add_filter( 'stm_wpcfto_fields', function( $fields ) {

            $fields[ 'stm_zoom_meeting' ] = array(

                'tab_1' => array(
                    'name' => esc_html__( 'Meeting settings', 'eroom-zoom-meetings-webinar' ),
                    'fields' => array(
                        'stm_agenda'    => array(
                            'type'      => 'textarea',
                            'label'     => esc_html__( 'Meeting agenda', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_host' => array(
                            'type'      => 'select',
                            'label'     => esc_html__( 'Meeting host', 'eroom-zoom-meetings-webinar' ),
                            'options'   => StmZoom::get_users_options()
                        ),
                        'stm_date' => array(
                            'type'      => 'date',
                            'label'     => esc_html__( 'Meeting date', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_time' => array(
                            'type'      => 'time',
                            'label'     => esc_html__( 'Meeting time', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_timezone' => array(
                            'type'      => 'select',
                            'label'     => esc_html__( 'Meeting timezone', 'eroom-zoom-meetings-webinar' ),
                            'options'   => stm_zoom_get_timezone_options(),
                            'value'     => get_current_timezone()
                        ),
                        'stm_duration' => array(
                            'type'      => 'number',
                            'label'     => esc_html__( 'Meeting duration (in min)', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_password' => array(
                            'type'      => 'text',
                            'label'     => esc_html__( 'Meeting password', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_waiting_room' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Waiting room', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_join_before_host' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Join Before Host', 'eroom-zoom-meetings-webinar' ),
                            'dependency' => array(
                                'key' => 'stm_waiting_room',
                                'value' => 'empty'
                            )
                        ),
                        'stm_host_join_start' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Host video', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_start_after_participants' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Participants video', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_mute_participants' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Mute Participants upon entry', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_enforce_login' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Enforce Login', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_alternative_hosts' => array(
                            'type'      => 'autocomplete',
                            'label'     => esc_html__( 'Alternative hosts', 'eroom-zoom-meetings-webinar' ),
                            'post_type' => array()
                        ),
                    )
                ),

            );

            $fields[ 'stm_zoom_webinar' ] = array(

                'tab_1' => array(
                    'name' => esc_html__( 'Webinar settings', 'eroom-zoom-meetings-webinar' ),
                    'fields' => array(
                        'stm_agenda'    => array(
                            'type'      => 'textarea',
                            'label'     => esc_html__( 'Webinar agenda', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_host' => array(
                            'type'      => 'select',
                            'label'     => esc_html__( 'Webinar host', 'eroom-zoom-meetings-webinar' ),
                            'options'   => StmZoom::get_users_options()
                        ),
                        'stm_date' => array(
                            'type'      => 'date',
                            'label'     => esc_html__( 'Webinar date', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_time' => array(
                            'type'      => 'time',
                            'label'     => esc_html__( 'Webinar time', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_timezone' => array(
                            'type'      => 'select',
                            'label'     => esc_html__( 'Webinar timezone', 'eroom-zoom-meetings-webinar' ),
                            'options'   => stm_zoom_get_timezone_options(),
                            'value'     => get_current_timezone()
                        ),
                        'stm_duration' => array(
                            'type'      => 'number',
                            'label'     => esc_html__( 'Webinar duration (in min)', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_password' => array(
                            'type'      => 'text',
                            'label'     => esc_html__( 'Webinar password', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_waiting_room' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Waiting room', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_join_before_host' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Join Before Host', 'eroom-zoom-meetings-webinar' ),
                            'dependency' => array(
                                'key' => 'stm_waiting_room',
                                'value' => 'empty'
                            )
                        ),
                        'stm_host_join_start' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Host video', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_start_after_participants' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Participants video', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_mute_participants' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Mute Participants upon entry', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_enforce_login' => array(
                            'type'      => 'checkbox',
                            'label'     => esc_html__( 'Enforce Login', 'eroom-zoom-meetings-webinar' ),
                        ),
                        'stm_alternative_hosts' => array(
                            'type'      => 'autocomplete',
                            'label'     => esc_html__( 'Alternative hosts', 'eroom-zoom-meetings-webinar' ),
                            'post_type' => array()
                        ),
                    )
                ),

            );

            return $fields;
        } );
    }

    /**
     * Adding STM Zoom Post Type Custom Box - Zoom meeting info
     */
    public function stm_zoom_add_custom_box()
    {
        // Meeting Meta Box for Shortcode
        $meeting_screens = array( 'stm-zoom' );
        add_meta_box( 'stm_zoom_info', 'Zoom meeting info', 'StmZoomPostTypes::meeting_info_template', $meeting_screens, 'side', 'high' );

        // Webinar Meta Box for Shortcode
        $webinar_screens = array( 'stm-zoom-webinar' );
        add_meta_box( 'stm_zoom_webinar_info', 'Zoom webinar info', 'StmZoomPostTypes::webinar_info_template', $webinar_screens, 'side', 'high' );
    }

    /**
     * Meeting shortcode template
     * @param $post
     * @param $meta
     */
    public static function meeting_info_template( $post, $meta )
    {
        $meeting_data = get_post_meta( $post->ID, 'stm_zoom_data', true );
        if ( ! empty( $meeting_data ) && ! empty( $meeting_data[ 'id' ] ) ) {
            echo '<p>';
            echo esc_html__( 'Meeting shortcode', 'eroom-zoom-meetings-webinar' );
            echo '</p><p>';
            echo '<b>[stm_zoom_conference post_id="' . $post->ID . '" hide_content_before_start=""]</b>';
            echo '</p>';
        }
    }

    /**
     * Webinar shortcode template
     * @param $post
     * @param $meta
     */
    public static function webinar_info_template( $post, $meta )
    {
        $webinar_data = get_post_meta( $post->ID, 'stm_zoom_data', true );
        if ( ! empty( $webinar_data ) && ! empty( $webinar_data[ 'id' ] ) ) {
            echo '<p>';
            echo esc_html__( 'Webinar shortcode', 'eroom-zoom-meetings-webinar' );
            echo '</p><p>';
            echo '<b>[stm_zoom_webinar post_id="' . $post->ID . '" hide_content_before_start=""]</b>';
            echo '</p>';
        } elseif ( ! empty( $webinar_data ) && ! empty( $webinar_data[ 'message' ] ) ) {
            echo '<p><strong style="color: #f00;">';
            echo apply_filters('stm_zoom_escape_output', $webinar_data[ 'message' ]);
            echo '</strong>';
        }
    }

	/**
	 * Zoom & Bookit Integration
	 * @param $appointment_id
	 */
    public function stm_zoom_bookit_edit_add_meeting( $appointment_id )
    {
	    $settings = get_option( 'stm_zoom_settings', array() );
    	if ( defined('BOOKIT_VERSION') && !empty( $settings['bookit_integration'] ) && $settings['bookit_integration'] ) {
		    $appointment        = \Bookit\Classes\Database\Appointments::get('id', $appointment_id);
		    $appointment_posts  = get_posts( [
			    'post_type'     => 'stm-zoom',
			    'numberposts'   => 1,
			    'meta_key'      => 'appointment_id',
			    'meta_value'    => $appointment_id,
		    ] );

		    if ( $appointment->status != \Bookit\Classes\Database\Appointments::$approved ) {
			    if ( ! empty( $appointment_posts ) && ! empty( $appointment_posts[0] ) ) {
				    wp_delete_post( intval($appointment_posts[0]->ID) );
			    }
		    	return;
		    }

		    $customer       = \Bookit\Classes\Database\Customers::get('id', $appointment->customer_id);
		    $staff          = \Bookit\Classes\Database\Staff::get('id', $appointment->staff_id);
		    $service        = \Bookit\Classes\Database\Services::get('id', $appointment->service_id);
		    $hosts          = StmZoom::stm_zoom_get_users();
		    $host_id        = '';

		    if ( ! empty( $hosts ) ) {
			    foreach ( $hosts as $host ) {
			    	if ( $host['email'] == $staff->email ) {
					    $host_id = $host['id'];
				    }
			    }
			    if ( empty($host_id) ) {
				    $host_id = $hosts[0]['id'];
			    }
		    }

		    $meeting = [
			    'post_title'    => sprintf( __( 'Appointment #%s - %s', 'eroom-zoom-meetings-webinar' ), $appointment->id, $service->title ),
			    'post_type'     => 'stm-zoom',
			    'post_status'   => 'publish',
			    'post_author'   => (!empty($customer->wp_user_id)) ? $customer->wp_user_id : 1,
			    'meta_input'    => [
				    'appointment_id'    => $appointment_id,
				    'stm_agenda'        => sprintf(
				    	__( 'Customer: %s, %s, %s. Payment via %s: %s', 'eroom-zoom-meetings-webinar' ),
					    $customer->full_name,
					    $customer->phone,
					    $customer->email,
					    $appointment->payment_method,
					    $appointment->payment_status
				    ),
				    'stm_host'          => $host_id,
				    'stm_date'          => $appointment->date_timestamp * 1000,
				    'stm_time'          => date('H:i', $appointment->start_time),
				    'stm_timezone'      => get_current_timezone(),
				    'stm_duration'      => intval(abs($appointment->start_time - $appointment->end_time) / 60)
			    ]
		    ];

		    /**
		     * Create / Update Post
		     */
		    if ( ! empty( $appointment_posts ) && ! empty( $appointment_posts[0] ) ) {
			    $meeting['ID']  = $appointment_posts[0]->ID;
			    $post_id        = wp_update_post( $meeting );
			    update_post_meta($post_id, 'stm_date', abs($appointment->date_timestamp * 1000));
		    } else {
			    $post_id            = wp_insert_post( $meeting );
			    update_post_meta($post_id, 'stm_date', abs($appointment->date_timestamp * 1000));
		    }

		    /**
		     * Create / Update Zoom Meeting
		     */
		    if ( ! empty( $post_id ) ) {
			    $api_key    = ! empty( $settings[ 'api_key' ] ) ? $settings[ 'api_key' ] : '';
			    $api_secret = ! empty( $settings[ 'api_secret' ] ) ? $settings[ 'api_secret' ] : '';

			    $host_id        = sanitize_text_field( $host_id );
			    $title          = sanitize_text_field( $meeting['post_title'] );
			    $agenda         = sanitize_text_field( $meeting['meta_input']['stm_agenda'] );
			    $start_date     = apply_filters('eroom_sanitize_stm_date', $meeting['meta_input']['stm_date']);
			    $start_time     = apply_filters('eroom_sanitize_stm_date', $meeting['meta_input']['stm_time']);
			    $timezone       = get_current_timezone();
			    $duration       = intval( $meeting['meta_input']['stm_duration'] );

			    $meeting_start = strtotime( 'today', ( ( $start_date ) / 1000 ) );
			    if ( ! empty( $start_time ) ) {
				    $time = explode( ':', $start_time );
				    if ( is_array( $time ) and count( $time ) === 2 ) {
					    $meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
				    }
			    }
			    $meeting_start = date( 'Y-m-d\TH:i:s', $meeting_start );
			    $data = array(
				    'topic'         => $title,
				    'type'          => 2,
				    'start_time'    => $meeting_start,
				    'agenda'        => $agenda,
				    'timezone'      => $timezone,
				    'duration'      => $duration
			    );
			    $meeting_data = get_post_meta( $post_id, 'stm_zoom_data', true );

			    if ( ! empty( $api_key ) && ! empty( $api_secret ) && ! empty( $host_id ) ) {
				    remove_action( 'save_post', array( $this, 'update_meeting' ), 10 );
				    remove_action( 'save_post', array( $this, 'change_date_if_empty' ), 10 );

				    $zoom_endpoint = new \Zoom\Endpoint\Meetings( $api_key, $api_secret );

				    if ( empty( $meeting_data[ 'id' ] ) ) {
					    $new_meeting    = $zoom_endpoint->create( $host_id, $data );
					    $meeting_id     = $new_meeting[ 'id' ];

					    update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );

					    do_action( 'stm_zoom_after_create_meeting', $post_id );
				    } else {
					    $meeting_id     = $meeting_data[ 'id' ];

					    $zoom_endpoint->update( $meeting_id, $data );

					    do_action( 'stm_zoom_after_update_meeting', $post_id );
				    }

				    if ( ! empty( $customer->email ) ) {
					    $message = sprintf( esc_html__( 'Hello, your meeting will begin at: %s, %s', 'eroom-zoom-meetings-webinar' ), $meeting['meta_input']['stm_time'], date('F j, Y', $appointment->date_timestamp) ) . '<br>';
					    $message .= esc_html__( 'Your meeting url: ', 'eroom-zoom-meetings-webinar' );
					    $message .= '<a href="https://zoom.us/j/' . esc_attr( $meeting_id ) . '" >' . esc_html( 'https://zoom.us/j/' . $meeting_id ) . '</a><br>';

					    $headers[]  = 'Content-Type: text/html; charset=UTF-8';

					    wp_mail( $customer->email, sprintf( esc_html__( 'Meeting Notification: %s', 'eroom-zoom-meetings-webinar' ), $title ), $message, $headers );
				    }

			    }
		    }

	    }
    }

    /**
     * Customize Update Meeting & Webinar Post Type data
     * @param $post_id
     */
    public function update_meeting( $post_id )
    {
        $post_type = ! empty( $_POST[ 'post_type' ] ) ? sanitize_text_field( $_POST[ 'post_type' ] ) : '';

        if ( empty( $post_type ) ) {
            $post_type = get_post_type( $post_id );
        }

        if ( $post_type === 'stm-zoom' || $post_type === 'stm-zoom-webinar' ) {
            $settings   = get_option( 'stm_zoom_settings', array() );
            $api_key    = ! empty( $settings[ 'api_key' ] ) ? $settings[ 'api_key' ] : '';
            $api_secret = ! empty( $settings[ 'api_secret' ] ) ? $settings[ 'api_secret' ] : '';

            $host_id                    = ! empty( $_POST[ 'stm_host' ] ) ? sanitize_text_field( $_POST[ 'stm_host' ] ) : '';
            $title                      = ! empty( $_POST[ 'post_title' ] ) ? sanitize_text_field( $_POST[ 'post_title' ] ) : '';
            $agenda                     = ! empty( $_POST[ 'stm_agenda' ] ) ? sanitize_text_field( $_POST[ 'stm_agenda' ] ) : '';
            $start_date                 = ! empty( $_POST[ 'stm_date' ] ) ? apply_filters('eroom_sanitize_stm_date', $_POST['stm_date']) : '';
            $start_time                 = ! empty( $_POST[ 'stm_time' ] ) ? sanitize_text_field( $_POST[ 'stm_time' ] ) : '';
            $timezone                   = ! empty( $_POST[ 'stm_timezone' ] ) ? sanitize_text_field( $_POST[ 'stm_timezone' ] ) : '';
            $duration                   = ! empty( $_POST[ 'stm_duration' ] ) ? intval( $_POST[ 'stm_duration' ] ) : 60;
            $password                   = ! empty( $_POST[ 'stm_password' ] ) ? sanitize_text_field( $_POST[ 'stm_password' ] ) : '';
            $waiting_room               = ! empty( $_POST[ 'stm_waiting_room' ] ) ? true : false;
            $join_before_host           = ! empty( $_POST[ 'stm_join_before_host' ] ) ? true : false;
            $host_join_start            = ! empty( $_POST[ 'stm_host_join_start' ] ) ? true : false;
            $start_after_participantst  = ! empty( $_POST[ 'stm_start_after_participants' ] ) ? true : false;
            $mute_participants          = ! empty( $_POST[ 'stm_mute_participants' ] ) ? true : false;
            $enforce_login              = ! empty( $_POST[ 'stm_enforce_login' ] ) ? true : false;
    
            $start_date = $this->current_date($start_date, $timezone);
            
            $alternative_hosts = '';
            if ( ! empty( $_POST[ 'stm_alternative_hosts' ] ) ) {
                $alternative_hosts = sanitize_text_field( $_POST[ 'stm_alternative_hosts' ] );
            }
            if ( is_array( $alternative_hosts ) && ! empty( $alternative_hosts ) ) {
                $alternative_hosts = implode( ',', $alternative_hosts );
            }
      
            $meeting_start = strtotime( 'today', ( ( $start_date ) / 1000 ) );
            if ( ! empty( $start_time ) ) {
                $time = explode( ':', $start_time );
                if ( is_array( $time ) and count( $time ) === 2 ) {
                    $meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
                }
            }
            $meeting_start = date( 'Y-m-d\TH:i:s', $meeting_start );
            $data = array(
                'topic'         => $title,
                'type'          => 2,
                'start_time'    => $meeting_start,
                'agenda'        => $agenda,
                'timezone'      => $timezone,
                'duration'      => $duration,
                'password'      => $password,
                'settings'      => array(
                    'waiting_room'              => $waiting_room,
                    'join_before_host'          => $join_before_host,
                    'host_video'                => $host_join_start,
                    'participant_video'         => $start_after_participantst,
                    'mute_upon_entry'           => $mute_participants,
                    'meeting_authentication'    => $enforce_login,
                    'alternative_hosts'         => $alternative_hosts,
                )
            );
            $meeting_data = get_post_meta( $post_id, 'stm_zoom_data', true );

            if ( ! empty( $api_key ) && ! empty( $api_secret ) && ! empty( $host_id ) ) {
                remove_action( 'save_post', array( $this, 'update_meeting' ), 10 );

                if ( $post_type === 'stm-zoom' ) {
                    $zoom_endpoint = new \Zoom\Endpoint\Meetings( $api_key, $api_secret );
                } elseif ( $post_type === 'stm-zoom-webinar' ) {
                    $zoom_endpoint = new \Zoom\Endpoint\Webinars( $api_key, $api_secret );
                }

                if ( empty( $meeting_data[ 'id' ] ) ) {
                    $new_meeting = $zoom_endpoint->create( $host_id, $data );

                    update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );

                    do_action( 'stm_zoom_after_create_meeting', $post_id );
                } else {
                    $meeting_id = $meeting_data[ 'id' ];

                    $zoom_endpoint->update( $meeting_id, $data );

                    do_action( 'stm_zoom_after_update_meeting', $post_id );
                }
            }
        }
    }

    /**
     * Synchronize Zoom Meetings and Webinars
     */
    public function stm_zoom_sync_meetings_webinars()
    {
        $post_type = 'stm-zoom';
        if ( ! empty( $_POST[ 'zoom_type' ] ) )
            $post_type = $_POST[ 'zoom_type' ];

        $settings       = get_option( 'stm_zoom_settings', array() );
        $api_key        = ! empty( $settings[ 'api_key' ] ) ? $settings[ 'api_key' ] : '';
        $api_secret     = ! empty( $settings[ 'api_secret' ] ) ? $settings[ 'api_secret' ] : '';
        $meeting_ids    = [];
        $zoom_type      = 'meetings';

        if ( ! empty( $api_key ) && ! empty( $api_secret ) ) {
            // Send Meetings / Webinars to Zoom Service.
            if ( $post_type === 'stm-zoom' ) {
                $zoom_endpoint  = new \Zoom\Endpoint\Meetings( $api_key, $api_secret );
            } elseif ( $post_type === 'stm-zoom-webinar' ) {
                $zoom_endpoint  = new \Zoom\Endpoint\Webinars( $api_key, $api_secret );
                $zoom_type      = 'webinars';
            }

            $args = array(
                'numberposts' => -1,
                'post_type' => $post_type
            );
            $zoom_posts = get_posts( $args );

            foreach ( $zoom_posts as $post ) {
                $post_id                    = $post->ID;
                $meeting_data               = get_post_meta($post_id, 'stm_zoom_data', true);
                $title                      = sanitize_text_field($post->post_title);
                $agenda                     = sanitize_text_field(get_post_meta($post_id, 'stm_agenda', true));
                $start_date                 = !empty(get_post_meta($post_id, 'stm_date', true)) ? intval(get_post_meta($post_id, 'stm_date', true)) : '';
                $start_time                 = sanitize_text_field(get_post_meta($post_id, 'stm_time', true));
                $timezone                   = sanitize_text_field(get_post_meta($post_id, 'stm_timezone', true));
                $duration                   = !empty(get_post_meta($post_id, 'stm_duration', true)) ? intval(get_post_meta($post_id, 'stm_duration', true)) : 60;
                $password                   = sanitize_text_field(get_post_meta($post_id, 'stm_password', true));
                $waiting_room               = !empty(get_post_meta($post_id, 'stm_waiting_room', true)) ? true : false;
                $join_before_host           = !empty(get_post_meta($post_id, 'stm_join_before_host', true)) ? true : false;
                $host_join_start            = !empty(get_post_meta($post_id, 'stm_host_join_start', true)) ? true : false;
                $start_after_participantst  = !empty(get_post_meta($post_id, 'stm_start_after_participants', true)) ? true : false;
                $mute_participants          = !empty(get_post_meta($post_id, 'stm_mute_participants', true)) ? true : false;
                $enforce_login              = !empty(get_post_meta($post_id, 'stm_enforce_login', true)) ? true : false;
                $host_id                    = sanitize_text_field(get_post_meta($post_id, 'stm_host', true));
                $alternative_hosts          = sanitize_text_field(get_post_meta($post_id, 'stm_alternative_hosts', true));

                if ( is_array( $alternative_hosts ) && ! empty( $alternative_hosts ) ) {
                    $alternative_hosts = implode(',', $alternative_hosts);
                }

                $meeting_start = strtotime('today', (intval($start_date) / 1000));
                if ( ! empty( $start_time ) ) {
                    $time = explode(':', $start_time);
                    if ( is_array($time) and count($time) === 2 ) {
                        $meeting_start = strtotime("+{$time[0]} hours +{$time[1]} minutes", $meeting_start);
                    }
                }
                $meeting_start = date('Y-m-d\TH:i:s', $meeting_start);

                $data = array(
                    'topic'         => $title,
                    'type'          => 2,
                    'start_time'    => $meeting_start,
                    'agenda'        => $agenda,
                    'timezone'      => $timezone,
                    'duration'      => $duration,
                    'password'      => $password,
                    'settings'      => array(
                        'waiting_room'              => $waiting_room,
                        'join_before_host'          => $join_before_host,
                        'host_video'                => $host_join_start,
                        'participant_video'         => $start_after_participantst,
                        'mute_upon_entry'           => $mute_participants,
                        'meeting_authentication'    => $enforce_login,
                        'alternative_hosts'         => $alternative_hosts,
                    )
                );

                if ( empty( $meeting_data[ 'id' ] ) ) {
                    $new_meeting = $zoom_endpoint->create( $host_id, $data );

                    $meeting_ids[] = $new_meeting['id'];

                    update_post_meta( $post_id, 'stm_zoom_data', $new_meeting );

                    do_action( 'stm_zoom_after_create_meeting', $post_id );
                } else {
                    $meeting_id = $meeting_data[ 'id' ];

                    $zoom_endpoint->update( $meeting_id, $data );

                    $meeting_ids[] = $meeting_data[ 'id' ];

                    do_action( 'stm_zoom_after_update_meeting', $post_id );
                }
            }

            wp_reset_postdata();

            // Get Meetings / Webinars from Zoom Service.
            $zoom_meetings = $zoom_endpoint->meetings_list( 'me', [ 'page_size' => 100 ] );

            if ( ! empty( $zoom_meetings[ $zoom_type ] ) ) {
                foreach ( $zoom_meetings[ $zoom_type ] as $meeting ) {
                    if ( in_array( $meeting[ 'id' ], $meeting_ids ) )
                        continue;

                    $zoom_meeting = $zoom_endpoint->meeting( $meeting[ 'id' ] );

                    $meeting = array(
                        'post_title'    => wp_strip_all_tags( $meeting[ 'topic' ] ),
                        'post_status'   => 'publish',
                        'post_type'     => $post_type,
                    );

                    $new_post_id = wp_insert_post( $meeting );

                    $stm_time = new DateTime( $zoom_meeting[ 'start_time' ], new DateTimeZone( 'UTC' ) );
                    $stm_time->setTimezone( new DateTimeZone( $zoom_meeting[ 'timezone' ] ) );

                    update_post_meta( $new_post_id, 'stm_zoom_data', $zoom_meeting );
                    update_post_meta( $new_post_id, 'stm_agenda', $zoom_meeting[ 'agenda' ] );
                    update_post_meta( $new_post_id, 'stm_date', intval( strtotime( date( 'Y-m-d 00:00:00', strtotime( $zoom_meeting[ 'start_time' ] ) ) ) * 1000) );
                    update_post_meta( $new_post_id, 'stm_time', $stm_time->format( 'H:i' ) );
                    update_post_meta( $new_post_id, 'stm_timezone', $zoom_meeting[ 'timezone' ] );
                    update_post_meta( $new_post_id, 'stm_duration', $zoom_meeting[ 'duration' ] );
                    update_post_meta( $new_post_id, 'stm_host', $zoom_meeting[ 'host_id' ] );
                    update_post_meta( $new_post_id, 'stm_alternative_hosts', $zoom_meeting[ 'settings' ][ 'alternative_hosts' ] );
                    update_post_meta( $new_post_id, 'stm_password', $zoom_meeting[ 'password' ] );
                    update_post_meta( $new_post_id, 'stm_waiting_room', $zoom_meeting[ 'settings' ][ 'waiting_room' ] );
                    update_post_meta( $new_post_id, 'stm_join_before_host', $zoom_meeting[ 'settings' ][ 'join_before_host' ] );
                    update_post_meta( $new_post_id, 'stm_host_join_start', $zoom_meeting[ 'settings' ][ 'host_video' ] );
                    update_post_meta( $new_post_id, 'stm_start_after_participants', $zoom_meeting[ 'settings' ][ 'participant_video' ] );
                    update_post_meta( $new_post_id, 'stm_mute_participants', $zoom_meeting[ 'settings' ][ 'mute_upon_entry' ] );
                    update_post_meta( $new_post_id, 'stm_enforce_login', $zoom_meeting[ 'settings' ][ 'enforce_login' ] );
                }
            }

            wp_send_json( 'done' );
        } else {
            wp_send_json( 'Please set your Zoom API keys.' );
        }
    }
    
    public function current_date($start_date, $timezone)
    {
        if ( empty($start_date) ) {
            $start_date = strtotime('today') . "000";
        }
        
        return $start_date;
    }

}