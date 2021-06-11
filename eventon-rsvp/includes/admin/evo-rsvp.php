<?php
/**
 * Admin functions for the evo-rsvp post type
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	eventON/Admin/evo-rsvp
 * @version     1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function _evors_remove_box(){
	remove_post_type_support('evo-rsvp', 'title');
    remove_post_type_support('evo-rsvp', 'editor');
}
add_action("admin_init", "_evors_remove_box");

// columns for evo-rsvp
	function evoRS_edit_event_columns( $existing_columns ) {
		
		// GET event type custom names
		
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) )
			$existing_columns = array();

		unset( $existing_columns['title'], $existing_columns['comments'], $existing_columns['date'] );

		$columns = array();
		$columns["cb"] = "<input type=\"checkbox\" />";	

		$columns['rsvp_'] = __( 'Status', 'evors' );
		$columns['rsvp'] = __( 'RSVP', 'evors' );
		$columns['rsvp_status'] = __( 'RSVP Status', 'evors' );

		$columns["event"] = __( 'Event', 'evors' );
		$columns["count"] = __( 'Count', 'evors' );
		$columns["updates"] = __( 'Email Updates', 'evors' );

		
		//$columns["date"] = __( 'Date', 'evors' );

		return array_merge( $columns, $existing_columns );
	}
	add_filter( 'manage_edit-evo-rsvp_columns', 'evoRS_edit_event_columns' );

// field values
	function evoRS_custom_event_columns( $column ) {
	global $post;

	//if ( empty( $ajde_events ) || $ajde_events->id != $post->ID )
		//$ajde_events = get_product( $post );

	$opt = get_option('evcal_options_evcal_2');

	$RSVP = new EVO_RSVP_CPT($post->ID);

	$meta = $RSVP->pmv;

	switch ($column) {		
		case "name_":
			
			$fname =get_post_meta($post->ID, 'first_name', true);
			$lname =get_post_meta($post->ID, 'last_name', true);
			
			echo $fname.' '.$lname;		
				
		break;
		case "rsvp_status":	
			
			$rsvp_status = $RSVP->checkin_status();			

			// if RVP status is NO disable checking
			if( $RSVP->get_rsvp_status() == 'n'){
				echo "<p class='evors_status_list {$rsvp_status} ' data-rsvp_id='".$post->ID."' data-status='{$rsvp_status}' data-nonce='". wp_create_nonce(AJDE_EVCAL_BASENAME) ."'>". evo_lang('Not Coming') ."</p>";
				break;
			}
			

			$rsvp_status = apply_filters('evors_admin_cpt_column_rsvp_status', EVORS()->frontend->get_checkin_status($rsvp_status), $post->ID);

			$checkable = in_array($rsvp_status, array('checked','check-in'))? true:false;

			echo "<p class='evors_status_list {$rsvp_status} ". ($checkable? 'evors_trig_checkin':'')."' data-rsvp_id='".$post->ID."' data-status='{$rsvp_status}' data-nonce='". wp_create_nonce(AJDE_EVCAL_BASENAME) ."'>".$rsvp_status."</p>";
				
		break;
		case "event":
			
			if($RSVP->event_id()){
				$edit_link = get_edit_post_link( $RSVP->event_id() );
				$title = get_the_title($RSVP->event_id());
				
				echo $title? '<strong><a class="row-title" href="'.$edit_link.'">' . $title.'</a>':
					"<span>"._e('Event No longer exists!','evors')."</span>";
			}else{ 
				echo '--';
			}

		break;
		case "count":
			$count = (!empty($meta['count']))? $meta['count'][0]: null;

			if($count){				
				echo ($count=='1')? 'Just Me': $count;
			}else{ echo 'Just Me';}

		break;
		case "rsvp_":
			$rsvp = get_post_meta($post->ID, 'rsvp', true);
			
			switch($rsvp){
				case 'y': echo '<p class="y">YES</p>'; break;
				case 'm': echo '<p class="m">MAYBE</p>'; break;
				case 'n': echo '<p class="n">NO</p>'; break;
			}
			do_action('evors_admin_cpt_column_rsvp_', $post->ID);
		break;
		case "rsvp":
			$edit_link = get_edit_post_link( $post->ID );
			$_email = (!empty($meta['email']))? '<a href="mailto:'.$meta['email'][0].'">'.$meta['email'][0].'</a>': null;
			echo "<strong><a class='row-title' href='".$edit_link."'>#{$post->ID}</a></strong> by ".( !empty($meta['first_name'])? $meta['first_name'][0]:'')." ".(!empty($meta['last_name'])? $meta['last_name'][0]:'')." ".$_email;
			echo "<br/><i class='at'>at ".$post->post_date."</i>";
			//echo get_post_meta($post->ID, 'rsvp', true);
			?>
			<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
			<?php
		break;
		case "updates":
			$U = get_post_meta($post->ID, 'updates', true);
			echo $U? $U: __('no','evors');
		break;	
	}
}
add_action('manage_evo-rsvp_posts_custom_column', 'evoRS_custom_event_columns', 2 );


// COLUMN Sorting
	/** Make events columns sortable */
	function evors_rsvp_sort($columns) {
		$custom = array(
			'rsvp_'		=> 'rsvp_',
			'rsvp_status'		=> 'rsvp_status',
			'count'		=> 'count',
		);
		return wp_parse_args( $custom, $columns );
	}

	add_filter( 'manage_edit-evo-rsvp_sortable_columns', 'evors_rsvp_sort');


/** Event column orderby */
	function evors_editorder_columns( $vars ) {
		if (isset( $vars['orderby'] )) :
			if ( 'rsvp_' == $vars['orderby'] ) :
				$vars = array_merge( $vars, array(
					'meta_key' 	=> 'rsvp',
					'orderby' 	=> 'meta_value'
				) );
			endif;
			if ( 'rsvp_status' == $vars['orderby'] ) :
				$vars = array_merge( $vars, array(
					'meta_key' 	=> 'status',
					'orderby' 	=> 'meta_value'
				) );
			endif;
			if ( 'count' == $vars['orderby'] ) :
				$vars = array_merge( $vars, array(
					'meta_key' 	=> 'count',
					'orderby' 	=> 'meta_value'
				) );
			endif;
			
		endif;

		return $vars;
	}

	add_filter( 'request', 'evors_editorder_columns' );

