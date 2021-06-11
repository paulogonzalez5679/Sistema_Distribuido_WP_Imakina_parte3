<?php
/**
 *
 *	EventON Now Calendar Content
 *	@version 3.0
 */

class Evo_Calendar_Now{

	public function process_a( $A){
		$defA = array(
			'eventtop_style'=>'2',
			'cal_now'=>'yes'
		);

		$this->A = array_merge($defA, $A);
	}

	public function get_cal($A = array()){

		$this->process_a($A); 
		
		ob_start();

		$this->print_shell_header();
	
		$this->get_body( $A);

		$this->print_shell_footer( $A);

		return ob_get_clean();
	}
	public function get_body( $delay = false ){

		$A = $this->A;

		$DD = new DateTime();
		$DD->setTimezone( EVO()->calendar->timezone0 );
		$DD->setTimestamp( EVO()->calendar->current_time );	

		$plus = $delay? 1:0;

		$A['focus_start_date_range'] = $DD->format('U') + $plus;
		$A['focus_end_date_range'] = $DD->format('U') + $plus;

		$A = EVO()->calendar->process_arguments( $A);	

		?>

		<div class='evo_eventon_now'>
			<h3><?php echo evo_lang('Events Happening Now');?></h3>

			<?php

			// now
				$now_event_ids = array();
				$event_list_array = EVO()->calendar->evo_get_wp_events_array( );	

				// events present
				if( count($event_list_array) >0 ){
					$event_data = EVO()->calendar->generate_event_data(
						$event_list_array, 	
						$A['focus_start_date_range']
					);

					$header_args = array(
						'external'=> true,
						'_classes_calendar'=> '',
						'initial_ajax_loading_html'=> false,
						'date_header'=> false,
					);

					echo EVO()->calendar->body->get_calendar_header($header_args);		

					foreach( $event_data as $ED){
						$now_event_ids[] = $ED['event_id'];
						echo $ED['content'];
					}

					echo EVO()->calendar->body->get_calendar_footer( true);

				// no events
				}else{
					echo "<p class='evo_eventon_no_events_now'>". evo_lang('No Events at the Moment')."</p>";
				}

			?>

		</div>

		<div class='evo_eventon_now_next'>
			
			<?php
			
			// up next
				$A = $this->A;
				
				$DD->setTimestamp( EVO()->calendar->current_time );	
				$A['focus_start_date_range'] = $DD->format('U');
				$DD->modify('+12 months');
				$A['focus_end_date_range'] = $DD->format('U');

				$A = EVO()->calendar->process_arguments( $A);	
				$event_list_array = EVO()->calendar->evo_get_wp_events_array( array( 'post__not_in'=> $now_event_ids) );

				// if there are events in the next 12 months
				if( count($event_list_array) > 0){
					$next_event_start_unix = $event_list_array[0]['event_start_unix'];
					$next_events = array( $event_list_array[0]);
					
					$event_data = EVO()->calendar->generate_event_data(
						$next_events, 	
						$A['focus_start_date_range']
					);

					$gap = $next_event_start_unix - EVO()->calendar->current_time;
					$nonce = wp_create_nonce('evo_calendar_now');

					echo "<h3>". evo_lang('Coming up Next in') ." <span class='evo_countdowner' data-gap='{$gap}' data-t='' data-exp_act='runajax_refresh_now_cal' data-n='{$nonce}'></span></h3>";

					$header_args = array(
						'external'=> true,
						'_classes_calendar'=> '',
						'initial_ajax_loading_html'=> false,
						'date_header'=> false,
					);

					echo EVO()->calendar->body->get_calendar_header($header_args);		

					foreach( $event_data as $ED){
						$now_event_ids[] = $ED['event_id'];
						echo $ED['content'];
					}

					echo EVO()->calendar->body->get_calendar_footer( true);

				}

			?>

		</div>

		<?php
		
	}
	public function print_shell_header(){		
		?><div class='evo_eventon_live_now_section'><?php
	}

	public function print_shell_footer( ){
		$d = json_encode($this->A);
		?><div class='evo_data' data-d='<?php echo $d;?>'></div></div><?php
	}		
}