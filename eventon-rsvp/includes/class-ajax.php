<?php
/**
 * RSVP Events Ajax Handlers
 *
 * Handles AJAX requests via wp_ajax hook (both admin and front-end events)
 *
 * @author 		AJDE
 * @category 	Core
 * @package 	EventON-RS/Functions/AJAX
 * @version     2.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class evorsvp_ajax{
	public function __construct(){
		$ajax_events = array(
			
			//'the_ajax_evors_fnd'=>'evoRS_find_rsvp',			
			'the_ajax_evors_a7'=>'save_rsvp_from_eventtop',
			//'the_ajax_evors_a8'=>'find_rsvp_byuser',	
			'evors_get_rsvp_form'=>'evors_get_rsvp_form',
			'evors_find_rsvp_form'=>'evors_find_rsvp_form',	
			'the_ajax_evors'=>'save_new_rsvp',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {				
			add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, $class ) );
		}

		// AJAX only for loggedin user
		$ajax_events = array(			
			'the_ajax_evors_f4'=>'checkin_guests',
			'the_ajax_evors_a10'=>'update_rsvp_manager',
			'the_ajax_evors_f3'=>'generate_attendee_csv',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {				
			add_action( 'wp_ajax_'.  $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_'.  $ajax_event, array( $this, 'nopriv') );
		}
	}
	// no priv
		function nopriv(){
			echo json_encode( array(
				'status'=>'nopriv','content'=> __('Login Needed')
			));exit;
		}
	// checkin guests 
		function checkin_guests(){
			global $eventon_rs;

			$nonceDisabled = evo_settings_check_yn($eventon_rs->frontend->optRS, 'evors_nonce_disable');

			// verify nonce check 
			if(isset($_POST['nonce']) && !wp_verify_nonce( $_POST['nonce'], AJDE_EVCAL_BASENAME ) && !$nonceDisabled){
				echo json_encode(array('message','Invalid Nonce'));
				exit;
			}

			$rsvp_id = $_POST['rsvp_id'];
			$status = $_POST['status'];

			update_post_meta($rsvp_id, 'status',$status);
			$return_content = array(
				'status'=>'0',
				'new_status_lang'=>$eventon_rs->frontend->get_checkin_status($status),
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// Download CSV of attendance
		function generate_attendee_csv(){

			$nonceDisabled = evo_settings_check_yn(EVORS()->frontend->optRS, 'evors_nonce_disable');

			// verify nonce check 			
			if(( !$nonceDisabled && isset($_REQUEST['nonce']) && !wp_verify_nonce( $_REQUEST['nonce'], AJDE_EVCAL_BASENAME ) )
			){
				echo json_encode(array('message','Invalid Nonce!'));exit;
			}

			if( !empty($_REQUEST['e_id'])){
				EVORS()->functions->generate_csv_attendees_list($_REQUEST['e_id']);
			}else{
				echo json_encode(array('message','Event ID not provided!'));exit;
			}
		}

	// NEW RSVP from EVENTTOP
		function save_rsvp_from_eventtop(){
			$status = 0;
			$message = $content = '';
			
			global $eventon_rs;
			$front = $eventon_rs->frontend;
			
			// sanitize each posted values
			foreach($_POST as $key=>$val){
				$post[$key]= sanitize_text_field(urldecode($val));
			}

			// Load Event
				$EVENT = new EVO_Event( $post['e_id'], '', $post['repeat_interval']);
				EVORS()->frontend->load_rsvp_event($EVENT);
				$RSVP = EVORS()->frontend->RSVP;

			// pull email and name from user data
			if(!empty($post['uid'])){
				$user_info = get_userdata($post['uid']);
				if(!empty($user_info->user_email))
					$post['email']= $user_info->user_email;
				if(!empty($user_info->first_name))
					$post['first_name']= $user_info->first_name;
				if(!empty($user_info->last_name))
					$post['last_name']= $user_info->last_name;

				// other default values
				$post['count']='1';
			}			

			// check if already rsvped
			$already_rsvped = $RSVP->has_user_rsvped($post);
			if(!$already_rsvped){ // if user have not already RSVPed save the RSVP
				
				$save= $front->_form_save_rsvp($post);
				$message = ($save==7)? 
					$front->get_form_message('err7', $post['lang']): 
					$front->get_form_message('succ', $post['lang']);

				$content = $front->get_eventtop_data('', (int)$post['repeat_interval'], (int)$post['e_id']);

			}else{// already rsvped
				$message = $front->get_form_message('err8', $post['lang']);
				$status = 0;
			}
					
			$return_content = array(
				'message'=> $message,
				'status'=>(($status==7)?7:0),
				'content'=>$content
			);
			
			echo json_encode($return_content);		
			exit;
		}
	
	// GET RSVP form
		function evors_get_rsvp_form(){
			
			$args = array();
			
			foreach($_POST as $K=>$V){
				if(in_array($K, array('action'))) continue;
				if( $K == 'precap'){
					if( $V == 'na'){
						$args[$K] = 'na'; continue;
					}
					$args[$K] = !empty($V)? (int)$V: '';
					continue;
				}				

				if(in_array($K, array('e_id','uid'))){
					$args[$K] = (!empty($V)? (int)$V: '');
				}else{
					$args[$K] = (!empty($V)? addslashes($V): '');
				}
			}

			//print_r($args);

			$content = EVORS()->rsvpform->get_form($args);

			echo json_encode(array(
				'status'=>'good',
				'content'=>$content
			)); exit;
		}
		
	// SAVE a RSVP from the rsvp form - NEW /UPDATE
		function save_new_rsvp(){
			global $eventon_rs;

			$nonce = $_POST['evors_nonce'];
			$status = 0;
			$message = $save = $rsvpID = $e_id =  '';

			// form type
				$formtype = !empty($_POST['formtype']) ? $_POST['formtype']:'submit';

			// if nonce is disabled
			$nonceDisabled = (!empty($eventon_rs->frontend->optRS['evors_nonce_disable']) && 
						$eventon_rs->frontend->optRS['evors_nonce_disable'] =='yes')? true: false;

			// set lang
				if(!empty($_POST['lang']))	EVORS()->l = EVO()->lang = $_POST['lang'];
				if(isset($_POST['lang'])) EVORS()->frontend->currentlang = $_POST['lang'];

			// verify nonce check 
			if(!wp_verify_nonce( $nonce, AJDE_EVCAL_BASENAME ) && !$nonceDisabled){
				$status = 1;	$message ='Invalid Nonce';				
			}else{
				
				$front = $eventon_rs->frontend;
				
				// sanitize each posted values
					foreach($_POST as $key=>$val){
						if(empty($val)) continue;
						$post[$key]= (!is_array($val))? sanitize_text_field($val): $val;
					}

				// after process
					$e_id = !empty($post['e_id'])? $post['e_id']:false;
					$repeat_interval = !empty($post['repeat_interval'])? $post['repeat_interval']:0;
					$epmv = get_post_custom($e_id);

				// load event
					$EVENT = new EVO_Event( $e_id, '', $repeat_interval);
					EVORS()->frontend->load_rsvp_event($EVENT);
					$RSVP = EVORS()->frontend->RSVP;

					$count = isset($post['count'])? (int)$post['count']: 1;

				// if UPDATING
				if(!empty($post['rsvpid'])){
					$rsvpID = $post['rsvpid'];

					$current_rsvp_status = get_post_meta($rsvpID, 'rsvp', true);

					$proceed = true;

					// if chnaging rsvp > YES make sure there are enough spaces
					if($current_rsvp_status == 'n' && $post['rsvp'] =='y'){
						if( !$RSVP->has_space($post['count']) ) $proceed = false;
					}

					if( $proceed){
						$save= $RSVP->update_rsvp($post, $EVENT);
						$status = 0;
					// not enough spaces to change rsvp
					}else{
						$save = evo_lang('There are not enough space!');
						$status = 1;
					}
				// creating new
				}else{
					// check if already rsvped
					$already_rsvped = $RSVP->has_user_rsvped($post);
					

					// havent rsvped before
					if(!$already_rsvped){

						// check if there are spaces to rsvp
						if($RSVP->has_space( $count )){
							// pass the rsvp id for change rsvp status after submit
							$save= $front->_form_save_rsvp($post); 
													
							$rsvpID = $save;
							$status = ($save==7)? 7: 0;
						}else{
							$status = 11;
						}
						
					// user has already rsvped
					}else{ 
						$status = 8;
						$rsvpID = $already_rsvped;
					}

				}		
				$message = $save;
			}

			// RSVP CPT
				$RR = EVORS()->frontend->oneRSVP = new EVO_RSVP_CPT($rsvpID);

			// get success message HTML
				$otherdata = array('guestlist'=>'','newcount'=>'0', 'remaining'=>'0','minhap'=>'0');
				if($status==0){

					// GET the form message
					$message = EVORS()->rsvpform->form_message(
						$RSVP, 	$rsvpID, 	$formtype,	$post
					);

					// guest list information
						$otherParts = EVORS()->rsvpform->get_form_guestlist($RSVP);
						if($otherParts){
							$otherdata['guestlist'] = $otherParts['guestlist'];
							$otherdata['newcount'] = $otherParts['newcount'];
						}

					// remaining
						$otherdata['remaining'] = $RSVP->remaining_rsvp();

					// rsvp status options selection new HTML
						$_html_option_selection = EVORS()->frontend->_get_evc_html_rsvpoption($RR, $RSVP);
				}

			// update event data object with new values
				$RSVP->event->relocalize_event_data();

			// data content	
				$eventtop_content = EVORS()->frontend->get_eventtop_data($RSVP);
				$eventtop_content_your = EVORS()->frontend->get_eventtop_your_rsvp();
				$new_rsvp_text = (!empty($post['rsvp'])? 	EVORS()->frontend->get_rsvp_status($post['rsvp']):'');
					
			$return_content = array(
				// 'post'=>$_POST,
				'message'=> $message,
				'status'=>$status,
				'rsvpid'=> $rsvpID,
				'guestlist'=>$otherdata['guestlist'],
				'newcount'=>$otherdata['newcount'],
				'e_id'=> $e_id,
				'ri'=>$repeat_interval,
				'lang'=> EVORS()->frontend->currentlang,
				'data_content_eventcard'=>		EVORS()->frontend->_get_event_card_content($RSVP,$RR),
				'data_content_eventtop'=>		$eventtop_content,
				'data_content_eventtop_your'=>	$eventtop_content_your,
				'new_rsvp_text'=>$new_rsvp_text
			);
			
			echo json_encode($return_content);		
			exit;
		}

	// FIND RSVP in order to change
		function evors_find_rsvp_form(){
			global $eventon_rs;

			$rsvpid = $eventon_rs->frontend->functions->get_rsvp_post(
				$_POST['e_id'],
				(!empty($_POST['repeat_interval'])?$_POST['repeat_interval']:''),
				array(	
					'email'=>$_POST['email']
				)
			);
			
			//echo $rsvpid.'tt';
			
			if($rsvpid){
				$args = array();
				foreach(array(
					'e_id',
					'repeat_interval',
					'cap',
					'precap',
					'email',					
					'formtype',
					'incard'
				) as $key){
					$args[$key] = (!empty($_POST[$key])? $_POST[$key]: '');
				}

				$args['rsvpid'] = $rsvpid;

				$content = $eventon_rs->rsvpform->get_form($args);
				echo json_encode(array(
					'status'=>'good',
					'content'=>$content
				)); exit;
			}else{
				echo json_encode(array(
					'status'=>'bad',
				)); exit;
			}
		}
		/*function evoRS_find_rsvp(){
			global $eventon_rs;
			$front = $eventon_rs->frontend;

			$rsvp = get_post($_POST['rsvpid']);
			$post_type = get_post_type($_POST['rsvpid']);

			if($rsvp!='' && $post_type =='evo-rsvp'){
				$rsvp_meta = get_post_meta($_POST['rsvpid']);
			}else{
				$rsvp_meta = false;
			}		
			// send out results
			echo json_encode(array(
				'status'=>(($rsvp!='')? '0':'1'),			
				'content'=> $rsvp_meta,
			));		
			exit;
		}
		*/
		/*function find_rsvp_byuser(){
			$rsvp = new WP_Query(array(
				'post_type'=>'evo-rsvp',
				'meta_query' => array(
					array(
						'key'     => 'userid',
						'value'   => $_POST['uid'],
					),
					array(
						'key'     => 'e_id',
						'value'   => $_POST['eid'],
					),array(
						'key'     => 'repeat_interval',
						'value'   => $_POST['ri'],
					),
				),
			));
			$rsvpid = false;
			if($rsvp->have_posts()){
				while($rsvp->have_posts()): $rsvp->the_post();
					$rsvpid = $rsvp->post->ID;
				endwhile;
				wp_reset_postdata();

				if(!empty($rsvpid)){
					$rsvp_meta = get_post_meta($rsvpid);
					$status = 0;
				}else{
					$status = 1;
				}
			}else{
				$status = 1;
			}

			// send out results
			echo json_encode(array(
				'status'=>$status,
				'rsvpid'=> ($rsvpid? $rsvpid:''),		
				'content'=> (!empty($rsvp_meta)? $rsvp_meta: ''),
			));		
			exit;
		}
		*/

	// update RSVP Manager
		function update_rsvp_manager(){
			global $eventon_rs;
			$manager = new evors_event_manager();
			$return_content = array(
				'content'=> $manager->get_user_events($_POST['uid'])
			);
			
			echo json_encode($return_content);		
			exit;
		}

}
new evorsvp_ajax();
?>