<?php
/**
 * EVO_Shortcodes class.
 *
 * @class 		EVO_Shortcodes
 * @version		2.4
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class EVO_Shortcodes {
	public function __construct(){
		// regular shortcodes
		add_shortcode('add_ajde_evcal',array($this,'eventon_show_calendar'));	// for eventon ver < 2.0.8	
		add_shortcode('add_eventon',array($this,'eventon_show_calendar'));
		add_shortcode('add_eventon_list',array($this,'events_list'));		
		add_shortcode('add_eventon_tabs',array($this,'eventon_tabs'));	
		add_shortcode('add_single_eventon', array($this,'single_event_box'));	
		add_shortcode('add_eventon_now', array($this,'eventon_now'));	
		add_shortcode('test_eventon_shortcode', array($this,'test_shortcode'));	
	}	
	
	// Testing shortcode
		public function test_shortcode(){
			return "<p>-- Shortcode content generation is working fine --</p>";
		}

	// MAIN CALENDAR
		function eventon_show_calendar($A){
			if(empty($A) || !is_array($A)) $A = array();
			
			$A['number_of_months'] = '1';
			$A['sep_month'] = 'no';

			return EVO()->calendar->_get_initial_calendar($A );
		}

	// NOW Events
		public function eventon_now($A){
			if(empty($A) || !is_array($A)) $A = array();

			$calnow = new Evo_Calendar_Now();
			return $calnow->get_cal( $A);
		}

	// BASIC EVENT LIST
		public function events_list($atts){
			
			if(empty($atts) || !is_array($atts)) $atts = array();
			
			ob_start();	
			
			$atts['calendar_type'] = 'list';
			
			echo EVO()->evo_generator->generate_events_list($atts);	

			return ob_get_clean();	
		}
	
	// Tab view for eventon calendar
		function eventon_tabs($atts){
			$defaults = array(
				'tab1'=>'Calendar View',
				'tab1shortcode'=>'add_eventon'
			);
			$args = array_merge($defaults, $atts);

			ob_start();
			echo "<div class='evo_tab_view'>";
			echo "<div class='evo_tab_holder'><span class='evo_tabs'>";
			for($x=1; $x<=4; $x++){
				if(empty($args['tab'.$x]) || empty($args['tab'.$x.'shortcode'])) continue;

				echo "<p class='evo_tab ". ($x==1? 'selected':'')."' data-tab='tab_".'tab'.$x."'>".$args['tab'.$x]."</p>";
			}
			echo "</span></div>";

			echo "<div class='evo_tab_container'>";
			for($x=1; $x<=4; $x++){
				if(empty($args['tab'.$x]) || empty($args['tab'.$x.'shortcode'])) continue;

				echo "<div class='evo_tab_section ". ($x==1?'visible':'') ." tab_".'tab'.$x."'>";
				$shortcode = '['. $args['tab'.$x.'shortcode'] . ']';
				
				echo do_shortcode($shortcode);
				echo "</div>";
			}
			echo "</div>";
			return ob_get_clean();
		}

	

	// single events
		function single_event_box($atts){
			EVO()->frontend->load_evo_scripts_styles();		
			
			add_filter('eventon_shortcode_defaults', array($this,'shortcode_defaults_single_event'), 10, 1);
			
			EVO()->calendar->process_arguments( $atts);	
			$args = EVO()->calendar->shortcode_args;

			//print_r($args);
			
			if(empty($args['id'])) return false; // when the id value was not passed


			// user interaction for this event box
				$ev_uxval = 4; // default open as event page
				$external_url = '';
				if( $args['open_as_popup']=='yes' || $args['ev_uxval']==3){
					$ev_uxval = 3;
					$args['show_exp_evc'] = 'no';// override expended event card
				}elseif(  $args['ev_uxval']=='X'){
					$ev_uxval = 'X';
				}elseif(  $args['ev_uxval']=='2' && !empty($args['ext_url'])){// external link
					$ev_uxval = '2';
					$external_url = $args['ext_url'];
				}elseif(  $args['ev_uxval']=='1' ){// slidedown
					$ev_uxval = 1;
				}

				// update calendar ux_val to 4 so eventcard HTML content will not load on eventbox
				if( ($ev_uxval==3 && $args['show_exp_evc']!='no') || $ev_uxval==1){}else{
					EVO()->evo_generator->process_arguments(array('ux_val'=>4));	
				}
					

			EVO()->evo_generator->is_eventcard_hide_forcer= true;
			$opt = EVO()->evo_generator->evopt1;

				// google map variables
				$evcal_gmap_format = ($opt['evcal_gmap_format']!='')?$opt['evcal_gmap_format']:'roadmap';	
				$evcal_gmap_zooml = ($opt['evcal_gmap_zoomlevel']!='')?$opt['evcal_gmap_zoomlevel']:'12';	
					
				$evcal_gmap_scrollw = (!empty($opt['evcal_gmap_scroll']) && $opt['evcal_gmap_scroll']=='yes')?'false':'true';				
			// get individual event content from calendar generator function
				$modified_event_ux = ($args['show_exp_evc']=='yes'  )? null: 4;
				$event = EVO()->evo_generator->get_single_event_data(
					$args['id'], 
					$args['lang'],
					$args['repeat_interval'],
					$args
				);
			
			// other event box variables
			$ev_excerpt = ($args['show_excerpt']=='yes')? "data-excerpt='1'":null;
			$ev_expand = ($args['show_exp_evc']=='yes')? "data-expanded='1'":null;
				
			$SC = array(
				'excerpt'=>$args['show_excerpt'],
				'expanded'=>$args['show_exp_evc'],	
				'ux_val'=>$ev_uxval,	
				'exturl'=>$external_url,	
				'mapscroll'=>$evcal_gmap_scrollw,	
				'mapformat'=>$evcal_gmap_format,	
				'mapzoom'=>$evcal_gmap_zooml,	
				'maps_load' => (EVO()->calendar->google_maps_load ? 'yes':'no')
			);

			// RTL
				$rtl_on = EVO()->cal->check_yn('evo_rtl','evcal_1');

			// Calendar Class Names
				$__cal_classes = array('ajde_evcal_calendar','eventon_single_event','eventon_event');

				$boxCal = (!empty($args['tiles']) && $args['tiles'] =='yes')?'boxy':null;

				if($boxCal == 'boxy'){
					$__cal_classes[] = 'boxy';
					$__cal_classes[] = 'box_1'; // 1 tile box
				}else{

				}
					
				// tile design
				if(!empty($args['tile_style']) && $args['tile_style'] !='0'){
					$__cal_classes[] = 'boxstyle'.$args['tile_style'];
				}else{
					if($boxCal == 'boxy') $__cal_classes[] = 'boxstyle0';
				}


				if( $rtl_on) $__cal_classes[] = 'evortl';

				$_cal_classes_string = implode(' ', $__cal_classes);

			
			ob_start();
				
			echo "<div class='{$_cal_classes_string}' >";
			echo "<div class='evo-data' ".$ev_excerpt." ".$ev_expand." data-ux_val='{$ev_uxval}' data-exturl='{$external_url}' data-mapscroll='".$evcal_gmap_scrollw."' data-mapformat='".$evcal_gmap_format."' data-mapzoom='".$evcal_gmap_zooml."' ></div><div class='evo_cal_data' data-sc='". json_encode($SC)."'></div>";
			echo "<div id='evcal_list' class='eventon_events_list ".($ev_uxval=='X'?'noaction':null)."'>";
			echo (isset($event) && isset($event[0]) ) ? $event[0]['content']: __('Missing Event Data','eventon');
			echo "</div></div>";
				
			
			return ob_get_clean();
		}

		// add new default shortcode arguments
		function shortcode_defaults_single_event($arr){			
			return array_merge($arr, array(
				'id'=>0,
				'show_excerpt'=>'no',
				'show_exp_evc'=>'no',
				'open_as_popup'=>'no',
				'ev_uxval'=>4,
				'repeat_interval'=>0,
				'ext_url'=>''
			));			
		}
}