<?php
/**
 * helper fnctions for calendar
 *
 * @class 		evo_cal_help
 * @version		3.0.6
 * @package		EventON/Classes
 * @category	Class
 * @author 		AJDE
 */

class evo_cal_help {
	public $opt1 = array();
	public $ordered_eventcard_fields = array();
	public function __construct(){
		$this->opt1 = EVO()->calendar->evopt1;
		// /$this->options = get_option('evcal_options_evcal_1');
	}
	
	// return classes array as a string
		function get_eventinclasses($atts){
			 
			$classnames[] = (!empty($atts['img_thumb_src']) && !empty($atts['show_et_ft_img']) && $atts['show_et_ft_img']=='yes')? 'hasFtIMG':'';

			$classnames[] = ($atts['event_type']!='nr')? 'event_repeat':null;	
			$classnames[] = $atts['event_description_trigger'];

			$classnames[] = (!empty($atts['existing_classes']['__featured']) && $atts['existing_classes']['__featured'])? 'featured_event':null;
			$classnames[] = (!empty($atts['existing_classes']['_cancel']) && $atts['existing_classes']['_cancel'])? 'cancel_event':null;
			$classnames[] = (!empty($atts['existing_classes']['_completed']) && $atts['existing_classes']['_completed'])? 'completed-event':null;

			$classnames[] = ($atts['monthlong'])? 'month_long':null;
			$classnames[] = ($atts['yearlong'])? 'year_long':null;

			
			// filter through existing class and remove true false values
				$existingClasses = array();
				if(is_array($atts)){
					foreach($atts['existing_classes'] as $field=>$value){
						//if($field==0 || $field ==1) continue;
						$existingClasses[$field]= $value;
					}
				}

			$classnames = array_merge($classnames, $existingClasses);
			$classnames = array_filter($classnames);

			return implode(' ',  $classnames);
		}

	function implode($array=''){
		if(empty($array))
			return '';

		return implode(' ', $array);
	}

	function get_attrs($array){
		if(empty($array)) return;

		$output = '';
		$array = array_filter($array);

		foreach($array as $key=>$value){
			if($key=='style' && !empty($value)){
				$output .= 'style="'. implode("", $value).'" ';
			}elseif($key=='rest'){
				$output .= implode(" ", $value);
			}else{
				if(is_array($value)) $value = json_encode($value);
				if( $key == 'data-j'){
					$output .= $key."='".$value."'";
				}else{
					$output .= $key.'="'.$value.'" ';
				}				
			}
		}

		return $output;
	}

	function evo_meta($field, $array, $type=''){
		switch($type){
			case 'tf':
				return (!empty($array[$field]) && $array[$field][0]=='yes')? true: false;
			break;
			case 'yn':
				return (!empty($array[$field]) && $array[$field][0]=='yes')? 'yes': 'no';
			break;
			case 'null':
				return (!empty($array[$field]) )? $array[$field][0]: null;
			break;
			default;
				return (!empty($array[$field]))? true: false;
			break;
		}		
	}

	// ORDERED EventCard Fields
		function eventcard_sort($array){

			$ordered_fields = $this->ordered_eventcard_fields;

			if($ordered_fields && is_array($ordered_fields) && sizeof($ordered_fields)>0){
				$A = array();
				foreach($ordered_fields as $F){
					if(!array_key_exists($F, $array) ) continue;
					$A[$F] = $array[$F];
				}
				return $A;
			}
			$A = $array;
			return apply_filters('evo_eventcard_array_after_sorted', $A, $array);			
		}

		// return ordered event card fields array or empty array
		function _get_eventCard_box_order($opt){

			if(!is_array($opt)) return false;
			if(!isset($opt['evoCard_order'])) return false;

			$evoCard_order = $opt['evoCard_order'];
			$correct_order = (!empty($evoCard_order))? 	explode(',',$evoCard_order): false;

			$ordered_fields = array();

			if($correct_order){
				$evoCard_hide = (!empty($opt['evoCard_hide']))? explode(',',$opt['evoCard_hide']): false;

				// each saved order item
				foreach($correct_order as $box){
					if( $evoCard_hide && in_array($box, $evoCard_hide) ) continue;
					$ordered_fields[] = $box;
				}

				$this->ordered_eventcard_fields = $ordered_fields;
				return $ordered_fields;				
			}

			$this->ordered_eventcard_fields = $ordered_fields;
			return $ordered_fields;
		}

		function _is_card_field($field_var){
			if(count($this->ordered_eventcard_fields)==0) return true;
			if(in_array($field_var, $this->ordered_eventcard_fields)) return true;
			return false;
		}


	// get repeating intervals for the event
		function get_ri_for_event($event_){
			return (!empty($event_['event_repeat_interval'])? 
				$event_['event_repeat_interval']: 
				( !empty($_GET['ri'])? (int)$_GET['ri']: 0) );
		}

	// get event type #1 font awesome icon
		function get_tax_icon($tax, $term_id, $opt){

			if(!empty($opt['evcal_hide_filter_icons']) && $opt['evcal_hide_filter_icons']=='yes') return false;

			$icon_str = false;
			if($tax == 'event_type'){
				$term_meta = get_option( "evo_et_taxonomy_$term_id" ); 
				if( !empty($term_meta['et_icon']) )
					$icon_str = '<i class="fa '. $term_meta['et_icon']  .'"></i>';
			}
			return $icon_str;
		}

	// get all event default values
	// updated 2.8
		function get_calendar_defaults(){
			$options = EVO()->calendar->evopt1;
			$SC = EVO()->calendar->shortcode_args;

			$defaults = array();

			$defaults['ux_val'] = !empty($SC['ux_val'])? $SC['ux_val']: false;
			$defaults['hide_end_time'] = (!empty($SC['hide_end_time']) && $SC['hide_end_time']=='yes' )? true: false;
			$defaults['ft_event_priority'] = (!empty($SC['ft_event_priority']) && $SC['ft_event_priority']=='yes' )? true: false;
			$defaults['eventcard_open'] = evo_settings_check_yn($SC,'evc_open');

			// SCHEMA
				$show_schema = EVO()->cal->check_yn('evo_schema')? false: true;
				if(EVO()->calendar->__calendar_type =='single' && EVO()->cal->get_prop('evcal_schema_disable_section') =='single' && !$show_schema)
					$show_schema = true;

				$defaults['show_schema'] = $show_schema;

				$show_jsonld = EVO()->cal->check_yn('evo_remove_jsonld')? false:true;						
				if(EVO()->calendar->__calendar_type =='single' && EVO()->cal->get_prop('evo_remove_jsonld_section') =='single' && !$show_jsonld)
					$show_jsonld = true;

				$defaults['show_jsonld'] = $show_jsonld;

			// default event image
				if(EVO()->cal->check_yn('evcal_default_event_image_set') && !empty($options['evcal_default_event_image']) ){
					$defaults['image'] = $options['evcal_default_event_image'];
				}

			// default event color
				$defaults['color'] = (!empty($options['evcal_hexcode']))? '#'.$options['evcal_hexcode']:'#4bb5d8';
			// event top fields
				$defaults['eventtop_fields'] = (!empty($options['evcal_top_fields']))? $options['evcal_top_fields']:null;

			// check if single events addon active
				$defaults['single_addon']  = true;		
				$defaults['user_loggedin'] = is_user_logged_in();


			$defaults['start_of_week'] = get_option('start_of_week');
			$defaults['hide_arrows'] = EVO()->cal->check_yn('evcal_arrow_hide');
			$defaults['wp_date_format'] = evo_convert_php_moment(EVO()->calendar->date_format);
			$defaults['wp_time_format'] = evo_convert_php_moment( EVO()->calendar->time_format );
			$defaults['utc_offset'] = get_option('gmt_offset');

			// google maps
			$defaults['google_maps_load'] = EVO()->calendar->google_maps_load;
				
			return apply_filters('evo_calendar_defaults',$defaults, $options, $SC);
		}


	// return the login message with button for fields that require login
		function get_field_login_message(){
			global $wp;
			$options_1 = $this->opt1 ;
			$current_url = home_url(add_query_arg(array(),$wp->request));

			$link = wp_login_url($current_url);

			if(!empty($options_1['evo_login_link']))
				$link = $options_1['evo_login_link'];

			return sprintf("%s <a href='%s' class='evcal_btn'>%s</a>", evo_lang('Login required to see the information') , $link, evo_lang('Login'));
		}

	// run special character encoding
		function htmlspecialchars_decode($data){
			return ( evo_settings_check_yn($this->opt1, 'evo_dis_icshtmldecode'))? 
				$data:
				htmlspecialchars_decode($data);
		}	


	// time functions
		function time_since($old_time, $new_time){
	        $since = $new_time - $old_time;
	        // array of time period chunks
	        $chunks = array(
	            /* translators: 1: The number of years in an interval of time. */
	            array( 60 * 60 * 24 * 365, _n_noop( '%s year', '%s years', 'wp-crontrol' ) ),
	            /* translators: 1: The number of months in an interval of time. */
	            array( 60 * 60 * 24 * 30, _n_noop( '%s month', '%s months', 'wp-crontrol' ) ),
	            /* translators: 1: The number of weeks in an interval of time. */
	            array( 60 * 60 * 24 * 7, _n_noop( '%s week', '%s weeks', 'wp-crontrol' ) ),
	            /* translators: 1: The number of days in an interval of time. */
	            array( 60 * 60 * 24, _n_noop( '%s day', '%s days', 'wp-crontrol' ) ),
	            /* translators: 1: The number of hours in an interval of time. */
	            array( 60 * 60, _n_noop( '%s hour', '%s hours', 'wp-crontrol' ) ),
	            /* translators: 1: The number of minutes in an interval of time. */
	            array( 60, _n_noop( '%s minute', '%s minutes', 'wp-crontrol' ) ),
	            /* translators: 1: The number of seconds in an interval of time. */
	            array( 1, _n_noop( '%s second', '%s seconds', 'wp-crontrol' ) ),
	        );

	        if ( $since <= 0 ) {
	            return __( 'now', 'wp-crontrol' );
	        }

	        // we only want to output two chunks of time here, eg:
	        // x years, xx months
	        // x days, xx hours
	        // so there's only two bits of calculation below:

	        // step one: the first chunk
	        for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
	            $seconds = $chunks[ $i ][0];
	            $name = $chunks[ $i ][1];

	            // finding the biggest chunk (if the chunk fits, break)
	            if ( ( $count = floor( $since / $seconds ) ) != 0 ) {
	                break;
	            }
	        }

	        // set output var
	        $output = sprintf( translate_nooped_plural( $name, $count, 'wp-crontrol' ), $count );

	        // step two: the second chunk
	        if ( $i + 1 < $j ) {
	            $seconds2 = $chunks[ $i + 1 ][0];
	            $name2 = $chunks[ $i + 1 ][1];

	            if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) {
	                // add to output var
	                $output .= ' ' . sprintf( translate_nooped_plural( $name2, $count2, 'wp-crontrol' ), $count2 );
	            }
	        }

	        return $output;
	    }


	// wpdb based event post meta retrieval
	// @since 2.5.5
		function event_meta($event_id, $fields){
			global $wpdb;

			$fields_str = '';
			$select = '';

			asort($fields);

			$len = count($fields); $i=1;
			foreach($fields as $field){
				$fields_str .= "'{$field}". ($i==$len? "'":"',");
				$select .= "MT.meta_value AS {$field}" . ($i==$len? "":",");
				$i++;
			}

			//print_r($fields_str);
	        $sql = "SELECT MT.meta_value
	            FROM $wpdb->postmeta AS MT
	            WHERE MT.meta_key IN ({$fields_str}) 
	            AND MT.post_id='{$event_id}' ORDER BY MT.meta_key DESC";
			$results = $wpdb->get_results( $sql);

			if(!$results && count($results) ==0) return false;

			//print_r($sql);
			//print_r($fields);

			$output = array();
			foreach($results as $index=>$result){
				$output[ $fields[$index]] = maybe_unserialize($result->meta_value);
			}
	        //print_r($output);
			return $output;

		}

	// use this to save multiple event post meta values with one data base query 
	// @since 2.5.6
	    function update_event_meta($event_id, $fields){
	        // check required values
	        $event_id = absint($event_id); if(!$event_id) return false;
	        $table = _get_meta_table('post');   if(!$table) return false;


	        $values = array();
	        foreach($fields as $meta_key=>$meta_value){
	            $meta_key = wp_unslash($meta_key);
	            $meta_value = maybe_serialize(wp_unslash($meta_value));

	            $values[] = "('{$meta_key}','{$meta_value}','{$event_id}')";
	        }

	        $values = implode(',', $values);

	        global $wpdb;

	        $res = $wpdb->update(
	            $table,
	            array(
	                'meta_value'=>'yes'
	            ),
	            array(
	                'meta_key'=>'_evoto_block_assoc'
	            )
	        );

	        /*$results = $wpdb->query(  
	            "INSERT INTO $wpdb->postmeta (meta_key, meta_value, post_id)
	            VALUES ('_evoto_block_assoc','yes','1840') 
	            ON DUPLICATE KEY UPDATE meta_key=VALUES(meta_key), meta_value=VALUES(meta_value)");

	        echo $wpdb->show_errors(); 
	        echo $wpdb->print_error();
	        */

	    }

	// timezone offset


}