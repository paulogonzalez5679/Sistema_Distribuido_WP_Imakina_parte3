<?php
/**
 * Event RSVP object
 */

class EVORS_Event{

	private $opt_rs;
	public function __construct($EVENT, $RI=0){

		if( is_numeric($EVENT)) $EVENT = new EVO_Event( $EVENT, '', $RI);

		if(!$EVENT) return;

		$this->event = $EVENT;
		$this->event_id = $EVENT->ID;
		$this->ri = $RI;	

		$this->opt_rs = EVORS()->evors_opt;

	}

// GENERAL
	function is_rsvp_active(){
		return $this->event->check_yn('evors_rsvp');
	}
	function has_space($spaces=1){

		if(!$this->event->check_yn('evors_capacity')) return true;
		if(!$this->event->get_prop('evors_capacity_count')) return true;

		if( (int)$this->event->get_prop('evors_capacity_count') >= (int)$spaces ) return true;
		return false;

	}

	// whether users can still RSVP
	function can_rsvp(){

		// if the event is cancelled then dont allow rsvping
		if($this->event->is_cancelled()) return false;

		$end_time = $this->event->get_end_time();

		$current_time = current_time('timestamp');

		// if event is past or not allowed to rsvp to past events
		if($current_time <= $end_time || ( evo_settings_check_yn($this->opt_rs, 'evors_allow_past')) ){}else{return false;}

		// if rsvp is set to close X min before expiration
		return ($this->close_rsvp_beforex())? false:true;
	}
	// check if rsvping is closed x minuted before event start time
	function close_rsvp_beforex(){
		$current_time = current_time('timestamp');
		// check if close RSVP X minuted before is set
		$close_time = $this->event->get_prop('evors_close_time');

		$closeRSVP = $close_time? (int)$close_time*60: false;

		return ($closeRSVP &&  ( ($closeRSVP+$current_time) >= $this->event->get_start_time() ) ) 
			? true: false;
	}


	function show_rsvp_count(){
		return $this->event->check_yn('evors_show_rsvp');
	}

	function inCard_form(){
		$global = evo_settings_check_yn($this->opt_rs, 'evors_incard_form');
		$event = $this->event->check_yn('_evors_incard_form');

		if($global ) return true;
		if($event) return true;
		return false;
	}

// Additional fields
	function _can_show_AF($AF_id){
		if(!$this->event) return false;
		if(!$this->event->check_yn('_evors_form_af_filter')) return true;
		if(!$this->event->get_prop('_evors_form_af_filter_val')) return true;

		$V = $this->event->get_prop('_evors_form_af_filter_val');
		$V = explode(',', str_replace(' ', '', $V));

		return (in_array($AF_id, $V)) ? true: false;
	}

	// should none of AF show?
	function _show_none_AF(){
		if(!$this->event) return false;
		if(!$this->event->check_yn('_evors_form_af_filter')) return false;
		if(!$this->event->get_prop('_evors_form_af_filter_val')) return false;

		$V = $this->event->get_prop('_evors_form_af_filter_val');
		return strpos($V, 'AFNONE') !== false? true: false;
	}

// Attendees
	function show_whoscoming(){
		return $this->event->check_yn('evors_show_whos_coming');
	}
	function show_whosnotcoming(){
		return $this->event->check_yn('_evors_show_whos_notcoming');
	}

	function can_show_guestList($currentUserRSVP){
		$show_after_rsvp = $this->event->check_yn('evors_whoscoming_after');
		return	(($show_after_rsvp && $currentUserRSVP) || !$show_after_rsvp) ? true: false;
	}
	function can_show_notcomingList($currentUserRSVP){
		$show_after_rsvp = $this->event->check_yn('_evors_whosnotcoming_after');
		return (($show_after_rsvp && $currentUserRSVP) || !$show_after_rsvp) ? true: false;
	}

// RSVP data	
	// check if capacity set
	// v 2.6.3
		function is_capacity_limit_set(){
			$setCap = $this->event->check_yn('evors_capacity');
			$setCapVal = $this->event->get_prop('evors_capacity_count');
			
			if(!$setCap && !$setCapVal) return false;

			return true;
		}


	// Internationalization rsvp status yes, no, maybe
	public function trans_rsvp_status($status, $lang=''){
		if(empty($status)) return;

		$opt2 = $this->opt2;
		$_sta = array(
			'y'=>array('Yes', 'evoRSL_003'),
			'n'=>array('No', 'evoRSL_005'),
			'm'=>array('Maybe', 'evoRSL_004'),
		);

		$lang = (!empty($lang))? $lang : (!empty(EVO()->lang)? EVO()->lang: 'L1');
		return EVORS()->lang($_sta[$status][1], $_sta[$status][0], $lang);
	}

	// return remaining rsvp capacity for event
	function remaining_rsvp(){
		//echo 'tt';
		// get already RSVP-ed count
		$yes = $this->event->get_prop('_rsvp_yes')? $this->event->get_prop('_rsvp_yes'): 0 ;
		$maybe = $this->event->get_prop('_rsvp_maybe')? $this->event->get_prop('_rsvp_maybe'): 0 ;

		$output = 'nocap';

		// if capacity limit set for rsvp 
		if($this->event->check_yn('evors_capacity')){

			// if capacity calculated per each repeat instance
			if($this->is_ri_count_active()){		
				$ri_capacity = $this->event->get_prop('ri_capacity_rs');			
				$ri_count = $this->event->get_prop('ri_count_rs');	
				$ri = $this->ri; 

				if(empty($ri_capacity[$ri])){
					$output = '0';
				}else{ 

					// if count not saved
					if(empty($ri_count)){
						$this->update_ri_count($this->event->ID, $ri, 'y', $yes);
						$this->update_ri_count($this->event->ID, $ri, 'm', $maybe);
					}	
					$count = (!empty($ri_count))? (!empty($ri_count[$ri]['y'])? $ri_count[$ri]['y']:0)+
						(!empty($ri_count[$ri]['m'])? $ri_count[$ri]['m']:0)
						:($yes+$maybe);

					$output = $ri_capacity[$ri] - $count;
					$output = ($output == 0)? '0':$output;
				}
			
			// not repeating event
			}elseif( $this->event->get_prop('evors_capacity_count')	){
				$capacity = (int)$this->event->get_prop('evors_capacity_count');
				$remaining =  $capacity - ( $yes + $maybe);
				$output = ($remaining>0)? $remaining: '0';
			}

		}

		return apply_filters('evors_remain_rsvp_output',$output, $this);
	}

	// check if there is capacity available to rsvp for event
		function has_space_to_rsvp(){
			$remaining_rsvp = $this->remaining_rsvp();

			if($remaining_rsvp == 'nocap') return true;
			return $remaining_rsvp >0? true: false;
		}

	function is_ri_count_active(){
		 return (
			$this->event->check_yn('evors_capacity')
			&& $this->event->check_yn('_manage_repeat_cap_rs')
			&& $this->event->is_repeating_event()
			&& $this->event->get_prop('ri_capacity_rs')
		)? true:false;
	}
	function get_ri_count($rsvp){
		$ri_count = $this->event->get_prop('ri_count_rs');
		//print_r($ri_count);
		if(!$ri_count) return 0;
		if(!isset($ri_count[$this->ri])) return 0;
		if(!isset($ri_count[$this->ri][$rsvp])) return 0;
		return $ri_count[$this->ri][$rsvp];
	}

	// GET rsvp remaining count RI or not
	function get_ri_remaining_count($rsvp, $ricount){
		$openCount = (int)$this->get_ri_count($rsvp);
		return $ricount - $openCount;
	}

	function get_rsvp_count($rsvp){
		if($this->is_ri_count_active()){
			return $this->get_ri_count($rsvp);
		}else{
			$r_ar = EVORS()->rsvp_array[$rsvp];
			return $this->event->get_prop('_rsvp_'.$r_ar)? 
				$this->event->get_prop('_rsvp_'.$r_ar): 0;
		}
	}

	function update_ri_count($event_id, $ri, $rsvp_status, $count){
		$ri_count = $this->event->get_prop('ri_count_rs');
		$ri_count = $ri_count? $ri_count: array();
		$ri_count[$ri][$rsvp_status] = $count;

		$this->event->set_prop('ri_count_rs',$ri_count);
	}

	// return total RSVP count for an event
		function total_rsvp_counts(){
			$rsvp_count = array('y'=>0,'n'=>0,'m'=>0);

			if($this->event->get_prop('_rsvp_yes')) $rsvp_count['y']= $this->event->get_prop('_rsvp_yes');
			if($this->event->get_prop('_rsvp_no')) $rsvp_count['n']= $this->event->get_prop('_rsvp_no');
			if($this->event->get_prop('_rsvp_maybe')) $rsvp_count['m']= $this->event->get_prop('_rsvp_maybe');
			
			return $rsvp_count;
		}


	// check if max rsvp per instance set and return the max value
	function is_per_rsvp_max_set(){
		if(!$this->event->check_yn('evors_max_active')) return false;
		return $this->event->get_prop('evors_max_count') ? $this->event->get_prop('evors_max_count') : 'na';
	}

	// return total capacity for events adjusted for repeat intervals
		function get_total_adjusted_capacity(){
			//$epmv = (!empty($epmv))? $epmv: get_post_meta($eid);

			$setCap = $this->event->check_yn('evors_capacity');
			$setCapVal = $this->event->get_prop('evors_capacity_count');
			$managRIcap = $this->event->check_yn('_manage_repeat_cap_rs');
			$riCap = $this->event->get_prop('ri_capacity_rs');

			$ri = $this->ri;	

			// if managing capacity per each ri
			if($managRIcap && $riCap){
				return !empty($riCap[$ri])? $riCap[$ri]:0;
			// if total capacity limit for event
			}elseif($setCap && $setCapVal){
				return ($setCapVal)? $setCapVal: 0;
			}else{
				return 0;
			}
			
		}	

	// Adjust Event RSVP data	
		
		// adjust rsvp count
		function adjust_ri_count( $rsvp_status, $adjust='reduce'){
			$ri_count = $this->event->get_prop('ri_count_rs');
			$ri_count = !empty($ri_count)? $ri_count: array();

			$ri = $this->ri;				

			// if data already exist 
			if(sizeof($ri_count)>0 && !empty($ri_count[$ri][$rsvp_status])){
				$old_count = (int)$ri_count[$ri][$rsvp_status];
				$new_count = $adjust=='reduce'? $old_count-1: $old_count+1;
				$ri_count[$ri][$rsvp_status] = $new_count;
			}else{// 
				$new_count = $adjust=='reduce'? 0: 1;
				$ri_count[$ri][$rsvp_status] = $count;
			}
			
			$this->event->set_prop('ri_count_rs', $ri_count, true, true);
		}

// GUESTS - list
	function GET_rsvp_list($rsvp_type = 'normal'){
		
		$ri = $this->ri;
		$ri_count_active = $this->is_ri_count_active();
		$guestsAR = array('y'=>array(),'m'=>array(),'n'=>array());

		$metaKey = (!empty(EVORS()->evors_opt['evors_orderby']) && EVORS()->evors_opt['evors_orderby']=='fn')? 'first_name':'last_name';

		$wp_args = array(
			'posts_per_page'=>-1,
			'post_type' => 'evo-rsvp',
			'meta_query' => apply_filters('evors_guest_list_metaquery', array(
				array('key' => 'e_id','value' => $this->event_id)
			)),
			'meta_key'=>$metaKey,
			'orderby'=>array('meta_value'=>'DESC','title'=>'ASC')
		);

		//print_r($wp_args);

		$guests = new WP_Query( $wp_args );

		if($guests->have_posts()):
			while( $guests->have_posts() ): $guests->the_post();
				$_id = get_the_ID();

				$RR = new EVO_RSVP_CPT($_id);
				$pmv = $RR->pmv;

				// only allow normal RSVP guests
				if($rsvp_type == 'normal' && !in_array($RR->checkin_status(), array('check-in', 'checked'))) continue;

				$rsvp = $RR->get_rsvp_status();
				$e_id = $RR->event_id();
				$_ri = $RR->repeat_interval();

				if(!$rsvp) continue;
				if(!$e_id || $e_id!= $this->event_id) continue;

				if(empty($pmv['email'])) continue;

				if(							
					$ri == 'all' || 
					(!$_ri && $ri == '0') ||
					($_ri==$ri)
				){
					$lastName = isset($pmv['last_name'])? $pmv['last_name'][0]:'';
					$firstName = isset($pmv['first_name'])? $pmv['first_name'][0]:'';
					$guestsAR[$rsvp][$_id] = array(
						'fname'=> ( $RR->first_name()? $RR->first_name():''),
						'lname'=> ( $RR->last_name()? $RR->last_name():''),
						'name'=> $lastName.(!empty($lastName)?', ':'').$firstName,
						'email'=> $pmv['email'][0],
						'phone'=> (!empty($pmv['phone'])?$pmv['phone'][0]:''),
						'status'=> $RR->checkin_status(),
						'count'=>$pmv['count'][0],						
						'userid'=>  (!empty($pmv['uid'])? $pmv['uid'][0]: (!empty($pmv['userid'])? $pmv['userid'][0]: 'na')),
						'names'=>  (!empty($pmv['names'])? unserialize($pmv['names'][0]) :'na'),
						'rsvpid'=>  $_id
					);
				}

			endwhile;
		endif;
		wp_reset_postdata();
		return array('y'=>$guestsAR['y'], 'm'=>$guestsAR['m'], 'n'=>$guestsAR['n']);
	}

// Change RSVP
	function show_change_rsvp($currentUserRSVP){
		// if set in settings to hide change rsvp buttons
		if(!empty($this->opt_rs['evors_hide_change']) && $this->opt_rs['evors_hide_change'] == 'yes') return false;

		$optRS = $this->opt_rs;

		if(empty($optRS['evors_onlylog_chg']) || 
			(!empty($optRS['evors_onlylog_chg']) && $optRS['evors_onlylog_chg']=='no') ||
			(!empty($optRS['evors_onlylog_chg']) && $optRS['evors_onlylog_chg']=='yes' && is_user_logged_in() && 
				(	empty($optRS['evors_change_hidden']) || 
					(!empty($optRS['evors_change_hidden']) && $optRS['evors_change_hidden']=='no') ||
					(!empty($optRS['evors_change_hidden']) && $optRS['evors_change_hidden']=='yes' && $currentUserRSVP)
				)
			)
		){return true;}else{return false;}
	}

// User RSVP
		function current_user_id(){
			return get_current_user_id();
		}
		function get_current_userid(){
			if(is_user_logged_in()){
				global $current_user;
				wp_get_current_user();
				return $current_user->ID;
			}else{
				return false;
			}
		}
	// get rsvp CPT ID
		function get_rsvp_id(){

			$other_rsvp_id = apply_filters('evors_rsvp_byauthor', false, $this);
			if($other_rsvp_id !== false) return $other_rsvp_id;

			// user not loggedin 
			if(!is_user_logged_in()) return false;

			$rsvp = $this->get_rsvp_by_author( $this->get_current_user_id() );

			if(!$rsvp) return false;
			return $rsvp[0]->ID;

		}

		// GET RSVP by user ID
		function get_rsvp_by_author($uid){
			if(!$uid) return false;

			$II = new WP_Query(array(
				'posts_per_page'=>1,
				'post_type'=>'evo-rsvp',
				'meta_query'=>array(
					array(	'key'	=> 'e_id','value'	=> $this->event->ID	),
					array(	'key'	=> 'repeat_interval','value'	=> $this->ri	),
					array(	'key'	=> 'userid','value'	=> $uid	),
					array(	'key'	=> 'rsvp','compare'	=> 'EXISTS'	),
				)
			));

			if(!$II->have_posts()) return  false;

			return $II->posts;
		}

	// rsvp status of user
		function get_user_rsvp_status($userid){
			$rsvp_data = $this->event->get_prop('evors_data');

			if(empty($rsvp_data)){
				return false;
			}else{
				$_ri = ($this->ri==0)? '0': $this->ri;
				if(!isset($rsvp_data[$userid])) return false;
				if(!isset($rsvp_data[$userid][$_ri])) return false;
				return $rsvp_data[$userid][$_ri];
			}
		}

		function get_loggedin_user_rsvp_status(){
			if(!is_user_logged_in()) return false;

			return $this->get_user_rsvp_status( get_current_user_id() ); 
		}

		function get_current_user_id(){
			if(!is_user_logged_in()) return false;

			$I = get_current_user_id();

			if($I == 0) return false;
			return $I;
		}

		// if user loggedin req to rsvp and if user is indeed logged in
		function user_need_login_to_rsvp(){

			if($this->event->check_yn('evors_only_loggedin') && !is_user_logged_in()) return false;
			return true;

		}

		// if user role restrictions enabled and if current loggedin user have the permission to rsvp
		function can_user_rsvp(){

			if(!is_user_logged_in()) return false;

			$allow_only_loggedin = !empty($this->opt_rs['evors_onlylogu']) && $this->opt_rs['evors_onlylogu']=='yes'? true:false;

			// if everyone can rsvp
			if(!$allow_only_loggedin) return true;

			$roles = !empty($this->opt_rs['evors_rsvp_roles']) ? $this->opt_rs['evors_rsvp_roles']: false;

			// if specific user roles were not set but user is loggedin 
			if(!$roles) return true;

			// check if current user is in the speficied user role
			if($roles){
				$user = wp_get_current_user();
				
				foreach($user->roles as $role){
					if(in_array($role, $roles)){
						return true;
					}
				}
			}
			return false;
		}

		function has_user_rsvped($post){
			$rsvped = new WP_Query( array(
				'posts_per_page'=>-1,
				'post_type' => 'evo-rsvp',
				'meta_query' => array(
					array('key' => 'email','value' => $post['email']),
					array('key' => 'e_id','value' => $this->event->ID),
					array('key' => 'repeat_interval','value' => $this->ri),
				),
			));
			return ($rsvped->have_posts())? $rsvped->post->ID: false;
		}

		function save_user_rsvp_status($userid, $rsvp_status){
			$rsvp_data = $this->event->get_prop('evors_data');

			if(empty($rsvp_data)) $rsvp_data = array();

			$rsvp_data[$userid][$this->ri] = $rsvp_status;
			$this->event->set_prop('evors_data', $rsvp_data, true, true);
		}

		// trash rsvp data for a user
		function trash_user_rsvp($userid){
			$rsvp_data = $this->event->get_prop('evors_data');

			if(empty($rsvp_data)) return;
			if(empty($rsvp_data[$userid][$this->ri])) return;

			unset($rsvp_data[$userid][$this->ri]);
			$this->event->set_prop('evors_data', $rsvp_data, true, true);
		}

// Add new RSVP 
	function add_new($data=''){
		
		$title = 'RSVP '.date('M d Y @ h:i:sa', time());

		$helper = new evo_helper();
		$N = $helper->create_posts(array(
			'post_title'   => $title,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'evo-rsvp',
            'post_name'    => sanitize_title($title),
		));

		if($N){
			// if post data set to pass to the new rsvp post
			if(!empty($data)){
				$R = new EVO_RSVP_CPT($N);
				$data['status'] = 'created';
				foreach($data as $d=>$dd){
					$R->set_prop($d, $dd);
				}
			}

			// return post ID
			return $N;
		}else{
			$this->log('couldnt create post'); return false;
		}
	}

// Update existing RSVP
	function update_rsvp($post, $EVENT){

		if(!isset($post['rsvpid'])) return false;

		$RR = new EVO_RSVP_CPT( $post['rsvpid'] );

		// NOTE - if rsvp status changed
			if(  $RR->get_rsvp_status() && $RR->get_rsvp_status() != $post['rsvp']){
				$RR->create_note('Changed RSVP status to "'. EVORS()->frontend->get_rsvp_status($post['rsvp']) .'"');
			}

		// pluggable proceed check
			$proceed = apply_filters('evors_rsvp_updated_before',true, $post, $RR, $EVENT);
			if($proceed !== true) return $proceed;

		// update each fields
			foreach($post as $field=>$value){
				if(in_array($field, array( 'action','evors_nonce','_wp_http_referer','formtype','lang','rsvp_type','invite_status','status'))) continue;

				if($field=='names' && !empty($post['names'])){
					$value = array_unique(array_filter($post['names']));
				}

				$RR->set_prop($field, $value);
			}

		// update usermeta
			if(isset($post['uid']) && isset($post['e_id'])){
				$this->save_user_rsvp_status($post['uid'],  $post['rsvp']);
			}

		// EMAILIN
			$send_emails = false;

			// enable sending emails if rsvp status changed
			if(!empty($post['original_status']) && $post['original_status'] != $post['rsvp'])
					$send_emails = true;

			if($send_emails){
				$post['rsvp_id'] = $post['rsvpid'];
				$post['emailtype'] = 'update';

				// if rsvp status changed to NO
				if($post['rsvp'] == 'n'){
					$post['notice_message'] = evo_lang('You have successfully changed the RSVP status');
					EVORS()->email->send_email($post,'attendee_notification');
				}

				// if RSVP status changed to YES
				if($post['rsvp'] == 'y'){
					EVORS()->email->send_email($post,'confirmation');
				}

				// notice admin of this update
				$post['notice_message'] = evo_lang('Attendee has changed the RSVP status.');
				EVORS()->email->send_email($post, 'notification');
			}

		// pluggable action
			do_action('evors_rsvp_updated',$post, $RR, $EVENT);

		// sync count
			$this->sync_rsvp_count( );

		return true;
	}

// SYNC Values
// - run when admin ajax call for sync count
	function sync_rsvp_count(){
		// check if repeat interval RSVP active
		$is_ri_count_active = $this->is_ri_count_active();
		$event_id = $this->event->ID;

		$ri_count = array();
		$rsvp_count = array('y'=>0,'n'=>0,'m'=>0);

		$Rs = new WP_Query( array(
			'posts_per_page'=>-1,
			'post_type' => 'evo-rsvp',
			'post_status'=>'publish',
			'meta_query' => array(
				array('key' => 'e_id','value' => $event_id)
			)
		));
		if($Rs->found_posts>0){
			while($Rs->have_posts()): $Rs->the_post();

				$RR = new EVO_RSVP_CPT($Rs->post->ID);
				$rsvpPMV = $RR->pmv;

				// skip not normal guests 
				if( !$RR->checkin_status_normal()) continue;				

				$rsvp_status = $RR->get_rsvp_status();

				$count = $RR->count();				

				$rsvp_count[$rsvp_status] = !empty($rsvp_count[$rsvp_status])? $rsvp_count[$rsvp_status]+$count: $count;

				// for repeat event RSVP cap
				if($is_ri_count_active){
					$ri = $RR->repeat_interval();
					$ri_count[$ri][$rsvp_status] = !empty($ri_count[$ri][$rsvp_status])? $ri_count[$ri][$rsvp_status]+$count: $count;
				}

			endwhile;

			// if event is managing cap per repeat
			$this->event->set_prop('ri_count_rs', $ri_count );

		}


		// update the RSVP counts
		update_post_meta($event_id,'_rsvp_yes', $rsvp_count['y'] );
		update_post_meta($event_id,'_rsvp_no', $rsvp_count['n'] );
		update_post_meta($event_id,'_rsvp_maybe', $rsvp_count['m'] );

		wp_reset_postdata();			

		return $rsvp_count;		
	}

}