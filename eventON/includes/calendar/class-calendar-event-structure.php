<?php
/**
* Calendar single event's html structure 
* @version 3.0.7
*/

class EVO_Cal_Event_Structure{
	private $EVENT;
	private $timezone = '';
	private $timezone_data = array();
	
	public function __construct($EVENT=''){

		$this->timezone_data = array(
			'__f'=>'YYYY-MM-DD h:mm:a',
			'__t'=> evo_lang('View in my time')
		);

		if(!empty($EVENT)) $this->EVENT = $EVENT;

		$this->timezone = get_option('gmt_offset', 0);
	}


	// HTML EventTop
	function get_event_top($array, $EventData, $eventtop_fields, $evOPT, $evOPT2){
			
		$EVENT = $this->EVENT;
		$OT = '';
		$_additions = apply_filters('evo_eventtop_adds' , array());

		$is_array_eventtop_fields = is_array($eventtop_fields)? true:false;

		extract($EventData);

		if(!is_array($array)) return $OT;

		EVO()->cal->set_cur('evcal_1');


		foreach($array as $element =>$elm){

			if(!is_array($elm)) continue;


			// convert to an object
			$object = new stdClass();
			foreach ($elm as $key => $value){
				$object->$key = $value;
			}

			$boxname = (in_array($element, $_additions))? $element: null;

			switch($element){
				case has_filter("eventon_eventtop_{$boxname}"):
					$helpers = array(
						'evOPT'=>$evOPT,
						'evoOPT2'=>$evOPT2,
					);

					$OT.= apply_filters("eventon_eventtop_{$boxname}", $object, $helpers, $EVENT);	
				break;
				case 'ft_img':
					$url = !empty($object->url_med)? $object->url_med: $object->url;
					//$url = !empty($object->url_full)? $object->url_full[0]: $url;
					$url = apply_filters('eventon_eventtop_image_url', $url);
					$OT.= "<span class='ev_ftImg' data-img='".(!empty($object->url_full)? $object->url_full: '')."' data-thumb='".$url."' style='background-image:url(\"".$url."\")'></span>";
				break;
				case 'day_block':

					if(!empty($object->include) && !$object->include) break;
					if(!is_array( $object->start )) break;
					
					$OT.="<span class='evcal_cblock ".( $year_long?'yrl ':null).( $month_long?'mnl ':null)."' data-bgcolor='".$color."' data-smon='".$object->start['F']."' data-syr='".$object->start['Y']."'>";
					
					// include dayname if allowed via settings
					$daynameS = $daynameE = '';
					if( is_array($eventtop_fields) && in_array('dayname', $eventtop_fields)){
						$daynameS = (!empty($object->html['start']['day'])? $object->html['start']['day']:'');
						$daynameE = (!empty($object->html['end']['day'])? $object->html['end']['day']:'');
					}

					$time_data = apply_filters('evo_eventtop_dates', array(
						'start'=>array(
							'year'=> 	($object->show_start_year=='yes'? $object->html['start']['year']:''),	
							'day'=>		$daynameS,
							'date'=> 	(!empty($object->html['start']['date'])?$object->html['start']['date']:''),
							'month'=>  	(!empty($object->html['start']['month'])?$object->html['start']['month']:''),
							'time'=>  	(!empty($object->html['start']['time'])?$object->html['start']['time']:''),
						),
						'end'=>array(
							'year'=> 	(($object->show_end_year=='yes' && !empty($object->html['end']['year']) )? $object->html['end']['year']:''),	
							'day'=>		$daynameE,
							'date'=> 	(!empty($object->html['end']['date'])?$object->html['end']['date']:''),
							'month'=> 	(!empty($object->html['end']['month'])? $object->html['end']['month']:''),
							'time'=> 	(!empty($object->html['end']['time'])? $object->html['end']['time']:''),
						),
					), $object->show_start_year, $object );

					$class_add = '';
					foreach($time_data as $type=>$data){					
						$end_content = '';
						foreach($data as $field=>$value){
							if(empty($value)) continue;
							$end_content .= "<em class='{$field}'>{$value}</em>";
						}

						if($type == 'end' && empty($data['year']) && empty($data['month']) && empty($data['date']) && !empty($data['time'])){
							$class_add = 'only_time';
						}
						if(empty($end_content)) continue;
						$OT .= "<span class='evo_{$type} {$class_add}'>";
						$OT .= $end_content;
						$OT .= "</span>";
					}
								
					$OT.= "<em class='clear'></em>";
					$OT .= "</span>";

				break;

				// title section of the event top
				case 'titles':
					$show_widget_eventtops = (!empty($evOPT['evo_widget_eventtop']) && $evOPT['evo_widget_eventtop']=='yes')? '':'hide_eventtopdata ';
					
					// location attributes
					$event_location_variables = '';
					if(!empty($location_name) && (!empty($location_address) || !empty($location_latlng))){
						$LL = !empty($location_latlng)? $location_latlng:false;

						if(!empty($location_address)) $event_location_variables .= ' data-location_address="'.$location_address.'" ';
						$event_location_variables .= ($LL)? 'data-location_type="lonlat"': 'data-location_type="address"';
						$event_location_variables .= ' data-location_name="'.$location_name.'"';
						if(isset($location_url))	$event_location_variables .= ' data-location_url="'.$location_url.'"';
						$event_location_variables .= ' data-location_status="true"';

						if( $LL){
							$event_location_variables .= ' data-latlng="'.$LL.'"';
						}
					}

					$OT.= "<span class='evcal_desc evo_info ".$show_widget_eventtops. ( $year_long?'yrl ':null).( $month_long?'mnl ':null)."' {$event_location_variables} >";
					
					// above title inserts
					$OT.= "<span class='evo_above_title'>";
						
						// live now virtual event
						if($EVENT && $EVENT->is_virtual() && !$EVENT->is_cancelled() && $EVENT->is_event_live_now() && !EVO()->cal->check_yn('evo_hide_live') ){
							$OT.= "<span class='evo_live_now' title='".( evo_lang('Live Now')  )."'></span>";
						}

						$OT .= apply_filters("eventon_eventtop_abovetitle", '', $object, $EVENT);
							
						if( $_status && $_status != 'scheduled'){							
							$OT.= "<span class='evo_event_headers canceled {$_status}'>".( $EVENT->get_event_status_lang()  ). "</span>";
						}
						if(!empty($featured) && $featured){
							$OT.= "<span class='evo_event_headers featured'>".( evo_lang('Featured','', $evOPT2)  )."</span>";
						}

						// virtual event
						if( $EVENT && $EVENT->is_virtual() ){							
							$OT.= "<span class='evo_event_headers vir'>".( evo_lang('Virtual Event')  )."</span>";
						}

					$OT.="</span>";

					// event edit button
						$editBTN = '';
						if( current_user_can('manage_options') && !empty($evOPT['evo_showeditevent']) && $evOPT['evo_showeditevent']=='yes')
							$editBTN = "<i href='".get_edit_post_link($EVENT->ID)."' class='editEventBtnET fa fa-pencil'></i>";
					
					$OT.= "<span class='evcal_desc2 evcal_event_title' itemprop='name'>". apply_filters('eventon_eventtop_maintitle',$EVENT->get_title() ) . $editBTN."</span>";
					
					// below title inserts
					$OT.= "<span class='evo_below_title'>";
						if($ST = $EVENT->get_subtitle()){
							$OT.= "<span class='evcal_event_subtitle' >" . apply_filters('eventon_eventtop_subtitle' , $ST) ."</span>";
						}

						// event status reason 
						if( $reason = $EVENT->get_status_reason()){
							$OT.= '<span class="status_reason">'. $reason .'</span>';
						}

					$OT.="</span>";
				break;

				case 'belowtitle':

					if(!$object->include) break;

					$OT.= "<span class='evcal_desc_info' >";

					// time
					if($is_array_eventtop_fields && in_array('time', $eventtop_fields) && isset($object->html)){
						$timezone_text = (!empty($object->timezone)? ' <em class="evo_etop_timezone">'.$object->timezone. '</em>':null);

						//print_r($object);
						$tzo = $tzo_box = '';

						if( !empty($object->_evo_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1')){

							extract( $this->timezone_data);
							$help = new evo_helper();

							$start_unix = explode('-', $object->event_times);

							$tzo = $help->get_timezone_offset( $object->_evo_tz,  $start_unix[0]);

							
							if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1')){
								$timezone_text .= "<span class='evo_tz'>(". $help->get_timezone_gmt( $object->_evo_tz,  $start_unix[0]) .")</span>";
							}

							$tzo_box = "<em class='evcal_tz_time evo_mytime tzo_trig' title='". evo_lang('My Time') ."' data-tzo='{$tzo}' data-tform='{$__f}' data-times='{$object->event_times}' ><i class='fa fa-globe-americas'></i> <b>{$__t}</b></em>";							
						}

						$OT.= "<em class='evcal_time evo_tz_time'>". apply_filters('evoeventtop_belowtitle_datetime', $object->html['html_fromto'], $object->html, $object) . $timezone_text ."</em> ";

						// local time
						if( !empty($object->_evo_tz)){
							$OT.= $tzo_box;
						}

						// manual timezone text
						if( empty($object->_evo_tz)) $OT.= "<em class='evcal_local_time' data-s='{$event_start_unix}' data-e='{$event_end_unix}' data-tz='". $EVENT->get_prop('_evo_tz') ."'></em>";
					}
					
					
					// location information
					if($is_array_eventtop_fields){
						// location name
						$LOCname = (in_array('locationame',$eventtop_fields) && !empty($location_name) )? $location_name: false;

						// location address
						$LOCadd = (in_array('location',$eventtop_fields) && !empty($location_address))? stripslashes($location_address): false;

						if($LOCname || $LOCadd){
							$OT.= '<em class="evcal_location" '.( !empty($location_latlng)? ' data-latlng="'.$location_latlng.'"':null ).' data-add_str="'.$LOCadd.'">'.($LOCname? '<em class="event_location_name">'.$LOCname.'</em>':'').
								( ($LOCname && $LOCadd)?', ':'').
								$LOCadd.'</em>';
						}
					}

					$OT.="</span>";
					$OT.="<span class='evcal_desc3'>";

					//organizer
						if($object->fields_ && in_array('organizer',$object->fields) && !empty($organizer) && isset($organizer->name)){
							$OT.="<span class='evcal_oganizer'>
								<em><i>".( eventon_get_custom_language( $evOPT2,'evcal_evcard_org', 'Event Organized By')  ).':</i></em>
								<em>'.$organizer->name."</em>
								</span>";
						}
					//event type
					if($object->tax)
						$OT.= $object->tax;

					// event tags
					if($is_array_eventtop_fields && in_array('tags',$eventtop_fields) && !empty($object->tags) ){
						$OT.="<span class='evo_event_tags'>
							<em><i>".eventon_get_custom_language( $evOPT2,'evo_lang_eventtags', 'Event Tags')."</i></em>";

						$count = count($object->tags);
						$i = 1;
						foreach($object->tags as $tag){
							$OT.="<em data-tagid='{$tag->term_id}'>{$tag->name}".( ($count==$i)?'':',')."</em>";
							$i++;
						}
						$OT.="</span>";
					}

					// custom fields
					if(!empty($object->cmf_data) && is_array($object->cmf_data) && count($object->cmf_data)>0){

						foreach($object->cmf_data as $f=>$v){

							// user loggedin visibility restriction
							if( !empty($v['login_needed_message']) ) continue;

							// user role restriction validation
							if( ($v['visibility_type'] =='admin' && !current_user_can( 'manage_options' ) ) ||
								($v['visibility_type'] =='loggedin' && !is_user_logged_in() && empty($v['login_needed_message']))
							) continue;

							// make sure this is custom field is set to show on eventtop
							if($is_array_eventtop_fields && in_array('cmd'.$f, $eventtop_fields) 
							&& !empty($v['value']) ){


								// custom icon
								$icon_string = '';
								if( !empty($v['imgurl']) && !empty($evOPT['evo_eventtop_customfield_icons']) && $evOPT['evo_eventtop_customfield_icons']=='yes'){
									$icon_string ='<i class="fa '. $v['imgurl'] .'"></i>'; 
								}

								if( $v['type'] == 'button'){									
									$OT.= "<span><em class='evcal_cmd evocmd_button' data-href='". ($v['valueL'] ). "' data-target='". ($v['_target']). "'>" . $icon_string .$v['value']."</em></span>";
								
								}elseif( $v['type'] == 'textarea'){

								}else{	

									$OT.= "<span><em class='evcal_cmd'>". $icon_string . "<i>".  $v['field_name'].':</i></em><em>'. $v['value'] ."</em>
										</span>";									
								}

							}

						}
					}

					// event progress bar
						if( !EVO()->cal->check_yn('evo_eventtop_progress_hide','evcal_1')  && $EVENT->is_event_live_now() && !$EVENT->is_cancelled()
							&& !$EVENT->echeck_yn('hide_progress')
						){
							$SC = EVO()->calendar->shortcode_args;

							$livenow_bar_sc = isset($SC['livenow_bar']) ? $SC['livenow_bar'] : 'yes';
							
							// check if shortcode livenow_bar is set to hide live bar
							if($livenow_bar_sc == 'yes'):

							$OT.= "<span class='evo_event_progress' >";

							$OT.= "<span class='evo_ep_pre'>". evo_lang('Live Now') ."</span>";

							$now =  current_time('timestamp');
							$times = explode('-', $object->event_times);
							$duration = $times[1] - $times[0];
							$gap = $times[1] - $now;

							$perc = $duration == 0? 0: ($now - $times[0]) / $duration;
							$perc = (int)( $perc*100);

							// action on expire
							
							$exp_act = $nonce = '';
							if( isset($SC['cal_now']) && $SC['cal_now'] == 'yes'){
								$exp_act = 'runajax_refresh_now_cal';
								$nonce = wp_create_nonce('evo_calendar_now');
							}

							$OT.= "<span class='evo_ep_bar'><b style='width:{$perc}%'></b></span>";
							$OT.= "<span class='evo_ep_time evo_countdowner' data-gap='{$gap}' data-dur='{$duration}' data-exp_act='". $exp_act ."' data-n='{$nonce}' data-t='". evo_lang('Time Left')."'></span>";

							$OT.= "</span>";

							endif;

						}

				break;

				case 'close1':
					$OT.="</span>";// span.evcal_desc3
				break;

				case 'close2':
					$OT.= "</span>";// span.evcal_desc 
					$OT.="<em class='clear'></em>";
				break;
			}
		}	

		return $OT;
	}



	// EventCard HTML
	function get_event_card($array, $EventData, $evOPT, $evoOPT2){
		// INIT
			$EVENT = $this->EVENT;
			$ED = $EventData;
			$evoOPT2 = (!empty($evoOPT2))? $evoOPT2: '';
			
			$OT ='';
			$count = 1;
			$items = count($array);	

			extract($EventData);
			
			// close button
			$close = "<div class='evcal_evdata_row evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";

			// additional fields array 
			$_additions = apply_filters('evo_eventcard_adds' , array());

	
		// FOR each
		foreach($array as $box_f=>$box){


			$end = ($count == $items)? $close: null;
			$end_row_class = ($count == $items)? ' lastrow': null;
			
			// convert to an object
				$object = new stdClass();
				foreach ($box as $key => $value){
					$object->$key = $value;
				}
			
			$boxname = (in_array($box_f, $_additions))? $box_f: null;

			
			// each eventcard type
			switch($box_f){

				// addition
					case has_filter("eventon_eventCard_{$boxname}"):

						//print_r($boxname);					
						$helpers = array(
							'evOPT'=>$evOPT,
							'evoOPT2'=>$evoOPT2,
							'end_row_class'=>$end_row_class,
							'end'=>$end,
						);

						$OT.= apply_filters("eventon_eventCard_{$boxname}", $object, $helpers, $EVENT);							
					break;
					
				// Event Details
					case 'eventdetails':	
						
						$more_code=''; $evo_more_active_class = '';

						// check if character length of description is longer than X size
						if( !empty($evOPT['evo_morelass']) && $evOPT['evo_morelass']!='yes' && (strlen($object->fulltext) )>600 ){
							$more_code = 
								"<p class='eventon_shad_p' style='padding:5px 0 0; margin:0'>
									<span class='evcal_btn evo_btn_secondary evobtn_details_show_more' content='less'>
										<span class='ev_more_text' data-txt='".evo_lang_get('evcal_lang_less','less')."'>".evo_lang_get('evcal_lang_more','more')."</span><span class='ev_more_arrow ard'></span>
									</span>
								</p>";
							$evo_more_active_class = 'shorter_desc';
						}

						$iconHTML = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_001', 'fa-align-justify',$evOPT )."'></i></span>";

						$_full_event_details = stripslashes( $object->fulltext );

						
						$OT.="<div class='evo_metarow_details evorow evcal_evdata_row evcal_event_details".$end_row_class."'>
								".$object->excerpt.$iconHTML."
								
								<div class='evcal_evdata_cell ".$evo_more_active_class."'>
									<div class='eventon_full_description'>
										<h3 class='padb5 evo_h3'>".$iconHTML . evo_lang_get('evcal_evcard_details','Event Details')."</h3>
										<div class='eventon_desc_in' itemprop='description'>
										". 

										apply_filters('evo_eventcard_details',EVO()->frontend->filter_evo_content( $_full_event_details )) 

										."</div>";
										
										// pluggable inside event details
										do_action('eventon_eventcard_event_details');

										$OT .= $more_code;

										$OT.="<div class='clear'></div>
									</div>
								</div>
							</div>";
									
					break;

				// TIME and LOCATION
					case 'timelocation':
						$iconTime = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_002', 'fa-clock-o',$evOPT )."'></i></span>";
						$iconLoc = "<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_003', 'fa-map-marker',$evOPT )."'></i></span>";

						// time for event card
						$timezone = (!empty($object->timezone)? ' <em class="evo_eventcard_tiemzone">'. $object->timezone.'</em>':null);
						$evc_time_text = "<span class='evo_eventcard_time_t'>". apply_filters('evo_eventcard_time', $object->timetext. $timezone, $object) . "</span>";


						// if timezone selected
						if( !empty($object->_evo_tz) && EVO()->cal->check_yn('evo_show_localtime','evcal_1')){

							extract( $this->timezone_data);
							$help = new evo_helper();

							$start_unix = explode('-', $object->event_times);
							$tzo = $help->get_timezone_offset( $object->_evo_tz,  $start_unix[0]);

							if( !EVO()->cal->check_yn('evo_gmt_hide','evcal_1')){
								$evc_time_text .= "<span class='evo_tz'>(". $help->get_timezone_gmt( $object->_evo_tz ) .")</span>";
							}

							$evc_time_text .= "<span class='evo_mytime tzo_trig' title='". evo_lang('My Time') ."' data-tzo='{$tzo}' data-tform='{$__f}' data-times='{$object->event_times}'><i class='fa fa-globe-americas'></i> <b>{$__t}</b></span>";
						}

						if(!empty($ED['location_name']) || !empty($ED['location_address'])){
							
							$locationLink = (!empty($location_link))? '<a target="'. ($location_link_target=='yes'? '_blank':'') .'" href="'. evo_format_link($location_link).'">':false;
							
							$OT.= 
							"<div class='evcal_evdata_row evorow_2b evo_metarow_time_location evorow ".$end_row_class." '>
								<div class='evorow_b evorow_b1 evo_time'>
									{$iconTime}
									<div class='evcal_evdata_cell'>							
										<h3 class='evo_h3'>".$iconTime . evo_lang_get('evcal_lang_time','Time')."</h3>
										<p>".$evc_time_text. "</p>
									</div>
								</div>
								<div class='evorow_b evo_location'>
									{$iconLoc}
									<div class='evcal_evdata_cell' data-loc_tax_id='{$ED['location_term_id']}'>";

									if( $location_hide){
										$OT.= "<h3 class='evo_h3'>".$iconLoc. evo_lang_get('evcal_lang_location','Location'). "</h3>";
										$OT .= "<p class='evo_location_name'>". EVO()->calendar->helper->get_field_login_message() . "</p>";
									}else{
										
										$OT.= "<h3 class='evo_h3'>".$iconLoc.($locationLink? $locationLink:''). evo_lang_get('evcal_lang_location','Location').($locationLink?'</a>':'')."</h3>";

										if( !empty($location_name) && !$EVENT->check_yn('evcal_hide_locname') )
											$OT.= "<p class='evo_location_name'>". $locationLink. $location_name . ($locationLink? '</a>':'') ."</p>";

										// for virtual location
										if( $location_type == 'virtual'){
											if( $locationLink) 
												$OT.= "<p class='evo_virtual_location_url'>" . evo_lang('URL:'). $locationLink . ' '. $location_link."</a></p>";
										}else{

											if(!empty($location_address)){
												$OT .= "<p class='evo_location_address'>". $locationLink . stripslashes($location_address) . ($locationLink? '</a>':'') ."</p>";
											}
											
										}											
									}
									$OT.= "</div>
								</div>
								
							</div>";
							
						}else{
						// time only
							
							$OT.="<div class='evo_metarow_time evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
								{$iconTime}
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>".$iconTime . evo_lang_get('evcal_lang_time','Time')."</h3><p>".$evc_time_text."</p>
								</div>
							</div>";						
						}
						
					break;

				// REPEAT SERIES
					case 'repeats':
						$OT.="<div class='evo_metarow_repeats evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
								<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_repeats', 'fa-repeat',$evOPT )."'></i></span>
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_lang_repeats','Future Event Times in this Repeating Event Series')."</h3>
									<p class='evo_repeat_series_dates ".($object->clickable?'clickable':'')."'' data-click='".$object->clickable."' data-event_url='".$object->event_permalink."'>";

							$datetime = new evo_datetime();

							// allow for custom date time format passing to repeat event times
							$repeat_start_time_format = apply_filters('evo_eventcard_repeatseries_start_dtformat','');
							$repeat_end_time_format = apply_filters('evo_eventcard_repeatseries_end_dtformat','');
							
							foreach($object->future_intervals as $key=>$interval){
								$OT .= "<span data-repeat='{$key}' data-l='". $EVENT->get_permalink($key,$EVENT->l) ."' class='evo_repeat_series_date'>"; 
								

								if($EVENT->is_year_long()){
									$OT .= date('Y', $interval[0]);
								}elseif( $EVENT->is_month_long()){

									$OT .= $EVENT->get_readable_formatted_date( $interval[0], 'F, Y');
									
								}else{

									$OT .= $EVENT->get_readable_formatted_date( $interval[0], $repeat_start_time_format);

									if( $object->showendtime && !empty($interval[1])){

										$OT .= ' - '.$EVENT->get_readable_formatted_date( $interval[1], $repeat_end_time_format);
									}

								}
								
								
								$OT.= "</span>";
							}

						$OT.="</p></div></div>";
					break;

				// Location Image
					case 'locImg':

						if(empty($location_img_id)) break;
						$img_src = wp_get_attachment_image_src($location_img_id,'full');
						if(empty($img_src)) break;

						$fullheight = (int)EVO()->calendar->get_opt1_prop('evo_locimgheight',400);

						if(!empty($img_src)){
							
							// text over location image
							$inside = $inner = '';
							if(!empty($location_name) && $EVENT->check_yn('evcal_name_over_img') ){

								if(!empty($location_address))	$inner .= '<span style="padding-bottom:10px">'. stripslashes($location_address) .'</span>';
								if(!empty($location_description)) $inner .= '<span class="location_description">'.$location_description.'</span>';
								$inside = "<p class='evoLOCtxt'><span class='evo_loc_text_title'>{$location_name}</span>{$inner}</p>";
							}
							$OT.="<div class='evcal_evdata_row evo_metarow_locImg evorow ".( !empty($inside)?'tvi':null)."' style='height:{$fullheight}px; background-image:url(".$img_src[0].")' id='".$location_img_id."_locimg' >{$inside}</div>";
						}
					break;

				// GOOGLE map
					case 'gmap':	

						if( $location_type != 'virtual')	
							$OT.="<div class='evcal_evdata_row evo_metarow_gmap evorow evcal_gmaps ".$object->id."_gmap' id='".$object->id."_gmap' style='max-width:none'></div>";
					break;
				
				// Featured image
					case 'ftimage':
						
						$__hoverclass = (!empty($object->hovereffect) && $object->hovereffect!='yes')? ' evo_imghover':null;
						$__noclickclass = (!empty($object->clickeffect) && $object->clickeffect=='yes')? ' evo_noclick':null;
						$__zoom_cursor = (!empty($evOPT['evo_ftim_mag']) && $evOPT['evo_ftim_mag']=='yes')? ' evo_imgCursor':null;

						ob_start();
						// if set to direct image
						if(!empty($evOPT['evo_ftimg_height_sty']) && $evOPT['evo_ftimg_height_sty']=='direct'){
							// ALT Text for the image
								$alt = !empty($object->img_id)? get_post_meta($object->img_id,'_wp_attachment_image_alt', true):false;
								$alt = !empty($alt)? 'alt="'.$alt.'"': '';
							echo "<div class='evo_metarow_directimg evcal_evdata_row'><img class='evo_event_main_img' src='{$object->img}' {$alt}/></div>";
						}else{

							// make sure image array object passed
							if( $object->main_image && is_array($object->main_image)){

								$main_image = $object->main_image;

								$height = !empty($object->img[2])? $object->img[2]:'';
								$width = !empty($object->img[1])? $object->img[1]:'';
								echo "<div class='evo_metarow_fimg evorow evcal_evdata_img evcal_evdata_row ".$end_row_class.$__hoverclass.$__zoom_cursor.$__noclickclass."' data-imgheight='". $main_image['full_h'] ."' data-imgwidth='". $main_image['full_w'] ."'  style='background-image: url(\"".$object->img."\")' data-imgstyle='".$object->ftimg_sty."' data-minheight='".$object->min_height."' data-status=''></div>";
							}
						}

						// additional images
							$adds = $EVENT->get_prop('_evo_images');

							if( apply_filters('evo_eventcard_additional_images', $adds, $object, $EVENT) ){

								//print_r($object);
								echo "<div class='evcal_evdata_row evo_event_images'>";

								echo "<span class='evo_event_more_img select'><img title='' src='{$object->img}' data-f='{$object->img}'/></span>";
								

								$imgs = explode(',', $adds);
								$imgs = array_filter($imgs);

								$x = 1;
								foreach($imgs as $img){

									$caption = get_post_field('post_excerpt',$img);
									$thumb = wp_get_attachment_image_src($img);
									$full = wp_get_attachment_image_src($img,'full');

									echo "<span class='evo_event_more_img '><img title='{$caption}' src='{$thumb[0]}' data-f='{$full[0]}' data-h='{$full[2]}' data-w='{$full[1]}'/></span>";
									$x++;
								}
								echo "</div>";
							}

							do_action('evo_eventcard_ftimg_end', $object, $EVENT);

						$OT .= ob_get_clean();
						
					break;
				
				// event organizer
					case 'organizer':					
						$evcal_evcard_org = eventon_get_custom_language($evoOPT2, 'evcal_evcard_org','Organizer');

						if(empty($ED['organizer_term'])) break;
						
						$img_src = (!empty($organizer_img_id)? 
							wp_get_attachment_image_src($organizer_img_id,'medium'): null);

						$newdinwow = (!empty($organizer_link_target) && $organizer_link_target=='yes')? 'target="_blank"':'';

						// organizer name text openinnewwindow
							if(!empty($organizer_link)){							
								$orgNAME = "<span class='evo_card_organizer_name_t'><a ".( $newdinwow )." href='" . 
									evo_format_link($organizer_link) . "'>".$organizer_name."</a></span>";
							}else{
								$orgNAME = "<span class='evo_card_organizer_name_t'>".$organizer_name."</span>";
							}	

						
						$OT.= "<div class='evo_metarow_organizer evorow evcal_evdata_row evcal_evrow_sm ".$end_row_class."'>
								<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_004', 'fa-headphones',$evOPT )."'></i></span>
								<div class='evcal_evdata_cell'>							
									<h3 class='evo_h3'>".$evcal_evcard_org."</h3>
									".(!empty($img_src)? 
										"<p class='evo_data_val evo_card_organizer_image'><img src='{$img_src[0]}'/></p>":null)."
									<div class='evo_card_organizer'>";

									$description = !empty($organizer_description) ? stripslashes($organizer_description): false;

									$org_data = "<p class='evo_data_val evo_card_organizer_name'>
										".$orgNAME.
										( $description? "<span class='evo_card_organizer_description'>".$description."</span>":'').
										(!empty($organizer_contact)? 
										"<span class='evo_card_organizer_contact'>". stripslashes($organizer_contact). "</span>":null)."
										".(!empty($organizer_address)? 
										"<span class='evo_card_organizer_address'>". stripslashes($organizer_address). "</span>":null)."
										</p>";

									$OT .= apply_filters('evo_organizer_event_card', $org_data, $ED, $organizer_term_id);

									$OT .= "</div><div class='clear'></div>							
								</div>
							</div>";
						
					break;
				
				// get directions
					case 'getdirection':
						
						$_lang_1 = evo_lang_get('evcalL_getdir_placeholder','Type your address to get directions');
						$_lang_2 = evo_lang_get('evcalL_getdir_title','Click here to get directions');

						$_from_address = false;
						if(!empty($location_address)) $_from_address = $location_address;
						if(!empty($location_getdir_latlng) && $location_getdir_latlng =='yes' && !empty($location_latlng)){
							$_from_address = $location_latlng;
						}

						if(!$_from_address) break;
						
						$OT.="<div class='evo_metarow_getDr evorow evcal_evdata_row evcal_evrow_sm getdirections'>
							<form action='https://maps.google.com/maps' method='get' target='_blank'>
							<input type='hidden' name='daddr' value=\"{$_from_address}\"/> 
							<p><input class='evoInput' type='text' name='saddr' placeholder='{$_lang_1}' value=''/>
							<button type='submit' class='evcal_evdata_icons evcalicon_9' title='{$_lang_2}'><i class='fa ".get_eventON_icon('evcal__fai_008a', 'fa-road',$evOPT )."'></i></button>
							</p></form>
						</div>";
						
					break;
						
				// learnmore ICS and close button
					case 'learnmoreICS':						
						// Initial 
							$opt = EVO()->frontend->evo_options;

							$__ics_url =admin_url('admin-ajax.php').'?action=eventon_ics_download&amp;event_id='.$EVENT->ID.'&amp;ri='.$EVENT->ri;

							$O = (object)array(
								'location_name'=> !empty($location_name)?$location_name:'',
								'location_address'=> !empty($location_address)?$location_address:'',
								'etitle'=> $event_title,
								'excerpt'=> $event_excerpt_txt
							);

							
							$__googlecal_link = $EVENT->get_addto_googlecal_link(
								$O->location_name,
								$O->location_address
							);


						// which options to show for add to calendar
							$addCaloptions = !empty($evOPT['evo_addtocal'])? $evOPT['evo_addtocal']: 'all';
							$addCalContent = '';

						// add to cal section
							switch($addCaloptions){
								case 'ics':
									$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>";
								break;
								case 'gcal':
									$addCalContent = "<a href='{$__googlecal_link}' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
								break;
								case 'all':
									$addCalContent = "<a href='{$__ics_url}' class='evo_ics_nCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addics','Add to your calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calncal','Calendar')."</a>".
										"<a href='{$__googlecal_link}' target='_blank' class='evo_ics_gCal' title='".eventon_get_custom_language($evoOPT2, 'evcal_evcard_addgcal','Add to google calendar')."'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_calgcal','GoogleCal')."</a>";
								break;
							}
						
						// learn more link with pluggability
						$learnmore_link = !empty($EVENT->get_prop('evcal_lmlink'))? apply_filters('evo_learnmore_link', $EVENT->get_prop('evcal_lmlink'), $object): false;
						$learnmore_target = ($EVENT->get_prop('evcal_lmlink_target')  && $EVENT->get_prop('evcal_lmlink_target')=='yes')? 'target="_blank"':null;

						// learn more and ICS
						if( $learnmore_link && $addCaloptions!='none'){
							
							ob_start();					
							?>
							<div class='evcal_evdata_row evorow_2b evo_metarow_learnMICS evorow <?php echo $end_row_class;?>'>						
								<?php 
								echo apply_filters('evo_eventcard_learnmore_link_html_pre', 
								"<a class='evorow_b evorow_b1 evo_metarow_learnmore evo_clik_row' href='". $learnmore_link ."' ". $learnmore_target.">", 
								$learnmore_link, $object );
								?><span class='evorow_box1' >
										<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT );?>'></i></span>
										<div class='evcal_evdata_cell'>
											<h3 class='evo_h3'><?php echo eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More');?></h3>
										</div>
									</span>
								</a>						
								<div class='evorow_b evo_ics evo_metarow_ICS evo_clik_row' >
									<div class=''>
										<span class="evcal_evdata_icons"><i class="fa fa-calendar"></i></span>
										<div class='evcal_evdata_cell'>
											<h3 class='evo_h3'><?php echo $addCalContent;?></h3>	
										</div>
									</div>
								</div>
							</div>
							<?php
							$OT.= ob_get_clean();
						
						// only learn more
						}else if( $learnmore_link ){
							$OT.="<div class='evo_metarow_learnM evo_metarow_learnmore evorow'>
								<a class='evcal_evdata_row evo_clik_row ' href='".$learnmore_link."' ".$learnmore_target.">
									<span class='evcal_evdata_icons'><i class='fa ".get_eventON_icon('evcal__fai_006', 'fa-link',$evOPT )."'></i></span>
									<h3 class='evo_h3'>".eventon_get_custom_language($evoOPT2, 'evcal_evcard_learnmore2','Learn More')."</h3>
								</a>
								</div>";

						// only ICS
						}else if($addCaloptions!='none'){

							ob_start();
							//echo get_option('gmt_offset', 0).'ttt';
							?>
							<div class='evo_metarow_ICS evorow evcal_evdata_row'>
								<span class="evcal_evdata_icons"><i class="fa fa-calendar"></i></span>
								<div class='evcal_evdata_cell'>
									<p><?php echo $addCalContent;?></p>	
								</div>
							</div>
							<?php
							$OT.= ob_get_clean();
						}
					
					break;

				// Related Events @+2.8
					case 'relatedEvents':
						$events = $EVENT->get_prop('ev_releated');
						$events = !empty($events)? json_decode($events, true): false;

						if($events && is_array($events)){
							
							ob_start();
							?>
								<div class='evo_metarow_rel_events evorow evcal_evdata_row'>
									<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_relev', 'fa-calendar-plus',$evOPT );?>'></i></span>
									<div class='evcal_evdata_cell'>
										<h3 class='evo_h3'><?php echo evo_lang('Related Events');?></h3>
										<div class='evcal_cell_rel_events'>
										<?php
										foreach($events as $I=>$N){
											$id = explode('-', $I);
											$EE = new EVO_Event($id[0]);
											$x = isset($id[1])? $id[1]:'0';
											$time = $EE->get_formatted_smart_time($x);
											
											echo "<a href='". $EE->get_permalink($x). "'><b>{$N}</b><em>{$time}</em></a>";
										}
										?>
										</div>
									</div>
								</div>
							<?php
							$OT.= ob_get_clean();

						}
					break;
				
				// Virtual Event
					case 'virtual':
						if($EVENT->is_virtual() && !$EVENT->is_cancelled()):
							ob_start();
							?>
							<div class='evo_metarow_virtual evorow evcal_evdata_row'>
								<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_vir', 'fa-globe',$evOPT );?>'></i></span>
								<div class='evcal_evdata_cell'>
									<h3 class='evo_h3'><?php echo evo_lang('Virtual Event Details');?></h3>
									<div class='evcal_cell'>
										<?php 

										$show_details = false;										

										$vir_show = $EVENT->get_prop('_vir_show');
										if(!$vir_show || $vir_show == 'always'){
											$show_details = true;
										}else{
											$time_to_event = $EVENT->seconds_to_start_event();
											if($time_to_event && $time_to_event <= (int)$vir_show) $show_details = true;
										}

										$is_event_past = $EVENT->is_past_event();

										if($live_now = $EVENT->is_event_live_now()){
											echo "<span class='evo_live_now'><b></b>" . evo_lang('Live Now') ."</span>";

											if($EVENT->check_yn('_vir_hide')){
												$show_details = false;
												echo "<p>". evo_lang('Event has already started and the access to the event is closed') . "!</p>";
											}else{
												$show_details = true;
											}
										}

										// hide information for past events
										if($is_event_past) $show_details = false;

										do_action('evo_eventcard_virtual_after_livenow', $EVENT);

										
										if(apply_filters('evo_eventcard_vir_details_bool', $show_details, $EVENT, $live_now, $is_event_past)){
											$vir_type = $EVENT->virtual_type();
											echo "<p class='evo_vir_link'>
												<a target='_blank' href='". $EVENT->virtual_url() ."' class='evcal_btn'>". evo_lang('Join the Event Now') ."</a>";
											if($v_pass = $EVENT->get_prop('_vir_pass'))
												echo "<span class='evo_vir_pass'>". evo_lang('Password'). ' <b>' . $v_pass ."</b></span>";
											echo "</p>";

											if($embed = $EVENT->get_prop('_vir_embed')){
												echo $embed;
											}

											// other event access details
											if($v_other = $EVENT->get_prop('_vir_other')){
												echo "<h4 class='evo_h4' style='margin-top:10px;'>". evo_lang('Other Access Information') ."</h4>";
												echo "<p class='evo_vir_other'>". $v_other ."</p>";
											}
										}else{
										// event details will not show untill time
											if($is_event_past){
												
												if($after_content = $EVENT->is_vir_after_content() ){
													echo $after_content;
												}else{
													echo "<p>". evo_lang('Event has already taken place') . "!</p>";
												}
												
											
											}elseif(!$live_now) {
												// event is in the future and not live right now

												echo "<p>". apply_filters('evo_eventcard_vir_txt_cur', evo_lang('Event access information coming soon, Please check back again closer to event start time'), $EVENT, $live_now) . "!</p>";
											}
										}
										?>	
									</div>
								</div>
							</div>
							<?php
							$OT.= ob_get_clean();
						endif;
					break;

				// health guidance
					case 'health':


						if( !$EVENT->check_yn('_health')) break;

						$EVENT->localize_edata();

						ob_start();
						?>
						<div class='evo_metarow_health evorow evcal_evdata_row'>
							<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_health', 'fa-heartbeat',$evOPT );?>'></i></span>
							<div class='evcal_evdata_cell'>
								<h3 class='evo_h3'><?php echo evo_lang('Health Guidelines for this Event');?></h3>
								<div class='evcal_cell'>
									<div class='evo_card_health_boxes'>
									<?php
									foreach(array(
										'_health_mask'=> array('svg','mask', evo_lang('Masks Required')),
										'_health_temp'=> array('i','thermometer-half', evo_lang('Temperature Checked At Entrance')),
										'_health_pdis'=> array('svg','distance', evo_lang('Physical Distance Maintained')),
										'_health_san'=> array('i','clinic-medical', evo_lang('Event Area Sanitized')),
										'_health_out'=> array('i','tree', evo_lang('Outdoor Event')),
									) as $k=>$v){

										if(!$EVENT->echeck_yn( $k )) continue;
										
										echo "<div class='evo_health_b'>";
									 	if($v[0]=='svg')
											echo "<svg class='evo_svg_icon' enable-background='new 0 0 20 20' height='20' viewBox='0 0 512 512' width='20' xmlns='http://www.w3.org/2000/svg'>". EVO()->elements->svg->get_icon_path( $v[1]) ."</svg>";
										if($v[0]=='i') echo "<i class='fa fa-{$v[1]}'></i>";
										echo "<span>". $v[2] ."</span>
										</div>";

									}

									?>

									</div>

									<?php if($EVENT->get_eprop('_health_other')):?>
									<div class='evo_health_b ehb_other'>
										<span class='evo_health_bo_title'>
											<i class='fa fa-laptop-medical'></i><span><?php evo_lang_e('Other Health Guidelines');?></span>
										</span>
										<span class='evo_health_bo_data'><?php echo $EVENT->get_eprop('_health_other');?></span>
									</div>
									<?php endif;?>
										

								</div>
							</div>
						</div>
						<?php 	$OT.= ob_get_clean();


					break;

				// paypal link
						case 'paypal':
							$ev_txt = $EVENT->get_prop('evcal_paypal_text');
							$text = ($ev_txt)? $ev_txt: evo_lang_get('evcal_evcard_tix1','Buy ticket via Paypal');

							$currency = !empty($evOPT['evcal_pp_cur'])? $evOPT['evcal_pp_cur']: false;
							$email = ($EVENT->get_prop('evcal_paypal_email')? $EVENT->get_prop('evcal_paypal_email'): $evOPT['evcal_pp_email']);

							if($currency && $email):
								$_event_time = $EVENT->get_formatted_smart_time();							
								
								ob_start();
							?>
							<div class='evo_metarow_paypal evorow evcal_evdata_row evo_paypal'>
									<span class='evcal_evdata_icons'><i class='fa <?php echo get_eventON_icon('evcal__fai_007', 'fa-ticket',$evOPT );?>'></i></span>
									<div class='evcal_evdata_cell'>
										<p style='padding-bottom:5px;'><?php echo $text;?></p>
										<form target="_blank" name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post">
											<input type="hidden" name="cmd" value="_xclick">
											<input type="hidden" name="business" value="<?php echo $email;?>">
											<input type="hidden" name="currency_code" value="<?php echo $currency;?>">
											<input type="hidden" name="item_name" value="<?php echo $EVENT->post_title.' '.$_event_time;?>">
											<input type="hidden" name="amount" value="<?php echo $EVENT->get_prop('evcal_paypal_item_price');?>">
											<input type='submit' class='evcal_btn' value='<?php echo evo_lang_get('evcal_evcard_btn1','Buy Now');?>'/>
										</form>										
									</div></div>							
							<?php $OT.= ob_get_clean();
							endif;

						break;
				
			}// end switch

			// for custom meta data fields
				if(!empty($object->x) && $box_f == 'customfield'.$object->x){
					$i18n_name = eventon_get_custom_language($evoOPT2,'evcal_cmd_'.$object->x , $evOPT['evcal_ec_f'.$object->x.'a1']);

					// user role restriction access validation
					if( ($object->visibility_type=='admin' && !current_user_can( 'manage_options' ) ) ||
						($object->visibility_type=='loggedin' && !is_user_logged_in() && empty($object->login_needed_message))
					) continue;

					$OT .="<div class='evo_metarow_cusF{$object->x} evorow evcal_evdata_row evcal_evrow_sm '>
							<span class='evcal_evdata_custometa_icons'><i class='fa ".$object->imgurl."'></i></span>
							<div class='evcal_evdata_cell'>							
								<h3 class='evo_h3'>".$i18n_name."</h3>";

						// if visible only to loggedin users and user is not logged in
						if( !empty($object->login_needed_message)){
							$OT .="<div class='evo_custom_content evo_data_val'>". $object->login_needed_message . "</div>";
						}else{
							if($object->type=='button'){
								$_target = (!empty($object->_target) && $object->_target=='yes')? 'target="_blank"':null;
								$OT .="<a href='".$object->valueL."' {$_target} class='evcal_btn evo_cusmeta_btn'>".$object->value."</a>";
							}else{
								$OT .="<div class='evo_custom_content evo_data_val'>". 
								(  EVO()->frontend->filter_evo_content($object->value) )."</div>";
							}
						}
					
					$OT .="</div></div>";
				}
			
			$count++;
		
		}// end foreach

		// the end of card closing button

		$OT .= "<div class='evo_card_row_end evcal_close' title='".eventon_get_custom_language($evoOPT2, 'evcal_lang_close','Close')."'></div>";
		
		return $OT;
		
	}	

	// SEO Schema data
		function get_schema($EventData, $_eventcard){
			extract($EventData);
			$EVENT = $this->EVENT;

			//print_r($EventData);

			$__scheme_data = '<div class="evo_event_schema" style="display:none" >';

			$tz = strpos($this->timezone, '-') === false? '+'. $this->timezone : $this->timezone;


			// Start time 
				$_schema_starttime = $_schema_endtime = '';
				if(is_array($start_date_data))
					$_schema_starttime = $start_date_data['Y'].'-'.$start_date_data['n'].'-'.$start_date_data['j'].( !$EVENT->is_all_day()? 'T'.$start_date_data['H'].':'.$start_date_data['i']. $tz. ':00' :'');
				if(is_array($end_date_data))
					$_schema_endtime = $end_date_data['Y'].'-'.$end_date_data['n'].'-'.$end_date_data['j']. ( !$EVENT->is_all_day()? 'T'.$end_date_data['H'].':'.$end_date_data['i'].$tz. ':00':'');

			// Event Status
				$ES = array(
					'cancelled'=>'https://schema.org/EventCancelled',
					'movedonline'=>'https://schema.org/EventMovedOnline',
					'postponed'=>'https://schema.org/EventPostponed',
					'rescheduled'=>'https://schema.org/EventRescheduled',
				);

				$_ES = isset($ES[$_status])? $ES[$_status]: 'https://schema.org/EventScheduled';

			
			// Event details				
				$__schema_desc = !empty($event_excerpt_txt)? $event_excerpt_txt : (isset($EVENT->post_title)? '"'.$EVENT->post_title.'"':'');
				if(!empty($event_details)) $__schema_desc = $event_details;
				$__schema_desc = str_replace("'","'", $__schema_desc);
				$__schema_desc = str_replace('"',"'", $__schema_desc);
				$__schema_desc = preg_replace( "/\r|\n/", " ", $__schema_desc );

			// attendence mode
				$_AM = 'https://schema.org/OfflineEventAttendanceMode';
			
			if(!empty($schema) && $schema){	
				// for each schema custom values
				foreach(apply_filters('evo_event_schema',array(
					'url'=>array(
						'type'=>'a',
						'attr'=>'href',
						'attrcontent'=> $EVENT->get_permalink()
					),					
					'image'=>array(
						'type'=>'meta',
						'content'=> (!empty($img_src) &&!empty($img_src)? $img_src:'')
					),					
					'startDate'=>array(
						'type'=>'meta',
						'content'=> $_schema_starttime
					),
					'endDate'=>array(
						'type'=>'meta',
						'content'=> $_schema_endtime
					),
					'eventStatus'=>array(
						'type'=>'meta',
						'content'=>  $_ES
					),
				),$EVENT, $EVENT->ID) as $key=>$value){
					$__scheme_data .= "<".(!empty($value['type'])?$value['type']:'meta') ." itemprop='{$key}' ".(!empty($value['content'])? 'content="'.$value['content'].'"':'') ." ". ( !empty($value['attr'])? $value['attr']."='". $value['attrcontent']."'":'');

					if(!empty($value['itemtype'])) $__scheme_data .= ' itemscope itemtype="'.$value['itemtype'].'"';
					
					$__scheme_data .= ($value['type'] =='meta')? "/>": ">";
					$__scheme_data .= (!empty($value['html'])?$value['html']:'');
					$__scheme_data .= (isset($value['type']) && $value['type'] == 'meta')? '': 
						( isset($value['type'])? "</".$value['type'] .">" :'' ); 
				}
				
				// location data
					if( !empty($location_type) && $location_type =='virtual'){
						$__scheme_data .= '<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/VirtualLocation">';
						if(!empty($location_link)) $__scheme_data .= '<span itemprop="url">'.$location_link.'</span>';
						$__scheme_data .= "</item>";

						$_AM = 'https://schema.org/OnlineEventAttendanceMode';
						
					}

					if(!empty($location_address)){

						$__scheme_data .= '<item style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">'. ( !empty($location_name)? '<span itemprop="name">'.$location_name.'</span>':'').'<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><item itemprop="streetAddress">'. stripslashes($location_address) .'</item></span></item>';

						if( $location_type =='virtual') 
							$_AM = 'https://schema.org/MixedEventAttendanceMode';
					}

					$__scheme_data .= '<item style="display:none" itemprop="eventAttendanceMode" itemscope itemtype="'.$_AM.'"></item>';

				// offer data
					if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
						$__scheme_data .= '<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
					        <div class="event-price" itemprop="price" content="'.$EVENT->get_prop('_seo_offer_price').'">'.$EVENT->get_prop('_seo_offer_price').'</div>
					        <meta itemprop="priceCurrency" content="'.$EVENT->get_prop('_seo_offer_currency').'">
					        <meta itemprop="url" content="'.$EVENT->get_permalink().'">
					        <meta itemprop="availability" content="http://schema.org/InStock">
					        <meta itemprop="validFrom" content="'.$_schema_starttime.'">
					    </div>';
					}

			    // organizer data
				    if(!empty($organizer) && isset($organizer->name)){
					    $__scheme_data .= '<div itemprop="organizer" itemscope="" itemtype="http://schema.org/Organization">
					    	<meta itemprop="name" content="'.$organizer->name.'">
					    	'. (!empty($organizer_link)? '<meta itemprop="url" content="'.$organizer_link.'">':'').
					    '</div>';
					}

				// performer data using organizer data
					if( $EVENT->get_prop('evo_event_org_as_perf') && !empty($organizer) && isset($organizer->name)){
						 $__scheme_data .= '<div itemprop="performer" itemscope="" itemtype="http://schema.org/Person">
					    	<meta itemprop="name" content="'.$organizer->name.'">
					    </div>';
					}
			}else{
				$__scheme_data .= '<a href="'.$event_permalink.'"></a>';
			}

			// JSON LD
			if(!empty($schema_jsonld) && $schema_jsonld){
				$__scheme_data .= '<script type="application/ld+json">';				

				// event status
				$_schema_eventstatus = ',"eventStatus":"'. $_ES .'"';

				// location
					$_schema_location = ''; 
					
					if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
						$_schema_location .= ',"location":';
					}

					if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address))
						$_schema_location .= '[';

					if(!empty($location_type) && $location_type == 'virtual'){
						$_schema_location .= '{"@type":"VirtualLocation"';
						if(!empty($location_link)) $_schema_location .= ',"url":"'.$location_link.'"';
						$_schema_location .= '}';
					}
					if(!empty($location_address)){

						if(!empty($location_type) && $location_type == 'virtual')
							$_schema_location .= ',';

						$_name = !empty($location_name)? '"name":"'.$location_name.'",':'';
						$location_name = str_replace('"', "", $location_name);
						$_schema_location .= '{"@type":"Place",'.$_name.'"address":{"@type": "PostalAddress","streetAddress":"'. str_replace("\,",",", stripslashes($location_address) ).'"}}';
					}
					if(!empty($location_type) && $location_type == 'virtual' || !empty($location_address)){
						$_schema_location .= ']';
					}

				// organizer 
					$_schema_performer = $_schema_organizer = '';
					if(!empty($organizer) && isset($organizer->name)){
						$_schema_organizer = ',"organizer":{"@type":"Organization","name":"'.$organizer->name.'"'. 
							( !empty($organizer_link)? ',"url":"'.$organizer_link.'"':'').
							'}';				
					}

				// perfomer data using organizer
					if( $EVENT->get_prop('evo_event_org_as_perf') && !empty($organizer) && isset($organizer->name) ){
						$_schema_performer = ',"performer":{"@type":"Person","name":"'.$organizer->name.'"}';
					}

				// offers field
					$_schema_offers = '';
					if( $EVENT->get_prop('_seo_offer_price') && $EVENT->get_prop('_seo_offer_currency')){
						$_schema_offers = ',"offers":{"@type":"Offer","price":"'. $EVENT->get_prop('_seo_offer_price') .'","priceCurrency":"'.$EVENT->get_prop('_seo_offer_currency').'","availability":"http://schema.org/InStock","validFrom":"'.$_schema_starttime.'","url":"'.$EVENT->get_permalink().'"}';
					}

				$__scheme_data .= 
					'{"@context": "http://schema.org","@type": "Event",
					"@id": "event_'. $EVENT->get_event_uniqid().'",
					"eventAttendanceMode":"'. $_AM .'",
					"name": '.(isset($EVENT->post_title)? '"'.htmlspecialchars( $EVENT->post_title, ENT_QUOTES ) .'"' :'').',
					"url": "'. $EVENT->get_permalink() .'",
					"startDate": "'.$_schema_starttime.'",
					"endDate": "'.$_schema_endtime.'",
					"image":'.(!empty($img_src) &&!empty($img_src)? '"'.$img_src.'"':'""').', 
					"description":"'.$__schema_desc.'"'.
				  	$_schema_location.
				  	$_schema_organizer.
				  	$_schema_performer.
				  	$_schema_offers.
				  	$_schema_eventstatus.
				  	apply_filters('eventon_event_json_schema_adds', '', $EVENT, $EVENT->ID).
				'}';
				$__scheme_data .= "</script>";
			}
			$__scheme_data .= "</div>";

			return $__scheme_data;
		}
}
