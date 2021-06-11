<?php
/**
 * Virtual Events Meta box content
 * @ 2.9.2
 */
?>

<div class='evcal_data_block_style1 event_virtual_settings'>
	<div class='evcal_db_data'>

	<p class='yesno_row evo single_main_yesno_field'>
		<?php 	
		echo $ajde->wp_admin->html_yesnobtn(array(
			'id'=>		'_virtual', 
			'var'=>		$EVENT->get_prop('_virtual'),
			'input'=>	true,
			'attr'=>	array('afterstatement'=>'evo_virtual_details')
		));
		?>						
		<label class='single_yn_label' for='_virtual'><?php _e('This is a virtual (online) event', 'eventon')?></label>
	</p>

	<?php 	

	$vir_type = $EVENT->virtual_type();		
	$show_err = false;

	if( !$EVENT->get_prop('_vir_url')) $show_err = true;

	$vir_link_txt = __('Zoom Meeting URL to join','eventon');
	$vir_pass_txt = __('Zoom Password','eventon');
	$vir_o = '';
	?>

	<div id='evo_virtual_details' class='evo_edit_field_box Xevo_metabox_secondary' style='display:<?php echo $EVENT->check_yn('_virtual')?'block':'none';?>'>
		
		<?php if($show_err):?>
			<p style='background-color: #ff7837;color: #fff;padding: 3px 15px;border-radius: 20px;margin: 0 0 15px;'><?php _e('Required Information Missing to Enable Virtual Events','eventon');?>!</p>
		<?php endif;?>

		<p class='row' style='padding-bottom: 15px;'>
			<label><?php _e('Virtual Event Boradcasting Method','eventon');?></label>
			<span style='display: flex'>
			<select name='_virtual_type' class='evo_eventedit_virtual_event'>
				<?php foreach(array(
					'zoom'=> array(
						__('Zoom','eventon'),
						__('Zoom Meeting URL to join','eventon'),
						__('Zoom Password','eventon'),
					),
					'youtube_live'=>array(
						__('Youtube Live','eventon'),
						__('Youtube Channel ID','eventon'),
						__('Optional Access Pass Information','eventon'),
						__('Find channel ID from https://www.youtube.com/account_advanced','eventon')
					),
					'youtube_private'=> array(
						__('Youtube Private Recorded Event','eventon'),
						__('Youtube Video URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
					'google_meet'=>array(
						__('Google Meet','eventon'),
						__('Google Meet URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
					'jitsi'=>array(
						__('Jit.si','eventon'),
						__('Jit.si meet URL','eventon'),
						__('Optional Password','eventon'),
					),
					'facebook_live'=>array(
						__('Facebook Live','eventon'),
						__('Facebook Live Video URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
					'periscope'=>array(
						__('Periscope','eventon'),
						__('Periscope Video URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
					'other_live'=>array(
						__('Other Live Stream','eventon'),
						__('Live Event URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
					'other_recorded'=>array(
						__('Other Pre-Recorded Video of Event','eventon'),
						__('Recorded Event Video URL','eventon'),
						__('Optional Access Pass Information','eventon'),
					),
				) as $F=>$V){
					if($vir_type == $F){
						$vir_link_txt = $V[1];
						$vir_pass_txt = $V[2];
						if(isset($V[3])) $vir_o = $V[3];
					}
					echo "<option value='{$F}' ". ($vir_type ==$F ? 'selected="selected"':'') ." data-l='{$V[1]}' data-p='{$V[2]}' data-o='". (isset($V[3])? $V[3]:''). "'>{$V[0]}</option>";
				}?>
			</select>											
		</p>

		<?php
		$EVENT->localize_edata('_evoz');
		?>

		<div class='zoom_connect' style='display:<?php echo $vir_type=='zoom'?'block':'none';?>; margin-bottom: 15px'>
			<?php if( $zid = $EVENT->get_eprop('_evoz_mtg_id')):?>
				<span class='evo_btn trig_zoom ajde_popup_trig' data-popc='evo_gen_lightbox' data-t='Zoom Meeting Settings' data-eid='<?php echo $EVENT->ID;?>' style='margin-right: 10px'><?php _e('Open Zoom Meeting Settings','eventon');?></span>
				</span>
			<?php else:?>
				<span class='evo_btn trig_zoom ajde_popup_trig' data-popc='evo_gen_lightbox' data-t='Create Zoom Meeting' data-eid='<?php echo $EVENT->ID;?>' style='margin-right: 10px'><?php _e('Create Zoom Meeting','eventon');?></span>
				</span>
			<?php endif;?>
			</span>
		</div>


		<p class='row vir_link'>
			<label><?php echo $vir_link_txt;?>*</label>
			<input name='_vir_url' value='<?php echo $EVENT->get_prop('_vir_url');?>' type='text' style='width:100%'/>
			<em><?php echo ($vir_o) ? $vir_o:''?></em>
		</p>

		<div class='evo_edit_field_box' style='background-color: #e0e0e0;' >
			<p style='font-size: 16px;'><b><?php _e('Other Information','eventon');?></b></p>
			<p class='row vir_pass'>
				<label><?php echo $vir_pass_txt?></label>
				<input name='_vir_pass' value='<?php echo $EVENT->get_prop('_vir_pass');?>' type='text' style='width:100%'/>
			</p>										
			<p class='row'>
				<label><?php _e('(Optional) Embed Event Video Code','eventon');?></label>
				<textarea name='_vir_embed' style='width:100%'><?php echo $EVENT->get_prop('_vir_embed');?></textarea>
			</p>	
			<p class='row'>
				<label><?php _e('(Optional) Other Additional Event Access Details','eventon');?></label>
				<input name='_vir_other' value='<?php echo $EVENT->get_prop('_vir_other');?>' type='text' type='text' style='width:100%'/>
			</p>
		</div>
		<p class='row' style='padding-bottom: 15px;'>
			<label><?php _e('When to show the above virtual event information on event card','eventon');?><?php echo $ajde->wp_admin->tooltips( 'This will set when to show the virtual event link and access information on the event card.','eventon');?></label>
			<select name='_vir_show'>
				<?php foreach( apply_filters('evo_vir_show', array(
					'always'=>__('Always','eventon'),
					'10800'=>__('3 Hours before the event start','eventon'),	
					'7200'=>__('2 Hours before the event start','eventon'),	
					'3600'=>__('1 Hour before the event start','eventon'),	
					'1800'=>__('30 Minutes before the event start','eventon'),	
				)) as $F=>$V){
					echo "<option value='{$F}' ". ($EVENT->get_prop('_vir_show') ==$F ? 'selected="selected"':'') .">{$V}</option>";
				}?>
			</select>
		</p>
		<p class='yesno_row evo '>
			<?php 	
			echo $ajde->wp_admin->html_yesnobtn(array(
				'id'=>		'_vir_hide', 
				'var'=>		$EVENT->get_prop('_vir_hide'),
				'input'=>	true,
				'label'=> __('Hide above access information when the event is live', 'eventon')
			));
			?>
		</p>
		<p class='yesno_row evo '>
			<?php 	
			echo $ajde->wp_admin->html_yesnobtn(array(
				'id'=>		'_vir_nohiding', 
				'var'=>		$EVENT->get_prop('_vir_nohiding'),
				'input'=>	true,
				'label'=> 	__('Disable redirecting and hiding virtual event link', 'eventon'),
				'guide'=> __('Enabling this will show virtual event link without hiding it behind a redirect url.','eventon')
			));
			?>			
		</p>

		<div class='evo_edit_field_box' style='background-color: #e0e0e0;' >
			<p style='font-size: 16px;'><b><?php _e('Optional After Event Information','eventon');?></b></p>
			<p class='row '>
				<label><?php _e('Content to show after event has taken place','eventon'); ?></label>
				<textarea name='_vir_after_content' style='width:100%'><?php echo $EVENT->get_prop('_vir_after_content');?></textarea>
			</p>										
			<p class='row'>
				<label><?php _e('When to show the above content on eventcard','eventon');?></label>
				<select name='_vir_after_content_when'>
					<?php 
					foreach( apply_filters('evo_vir_after_content_show',array(					
						'event_end'=>__('After event end time is passed','eventon'),
						'3600'=>__('1 Hour after the event has ended','eventon'),	
						'86400'=>__('1 Day after the event has ended','eventon'),	
					)) as $F=>$V){
						echo "<option value='{$F}' ". ($EVENT->get_prop('_vir_after_content_when') ==$F ? 'selected="selected"':'') .">{$V}</option>";
					}?>
				</select>
			</p>
		</div>

		<?php do_action('evo_editevent_vir_options', $EVENT);?>
		
		<p style='padding-top: 15px;'><em>
			<b><?php _e('Other Recommendations','eventon');?></b><br/><?php _e('Set Event Status value to "Moved Online" so proper schema data will be added to event to help search engines identify event type.','eventon');?>
			<br/><?php echo sprintf( wp_kses( __('You can use <a href="%s">EventON Tickets</a> or <a href="%s">RSVP addon</a> to show virtual event access information after customers have purchased or RSVPed to event.','eventon'), array('a'=> array('href'=>array()) )  ), esc_url('https://www.myeventon.com/addons/event-tickets/'), esc_url('https://www.myeventon.com/addons/rsvp-events/') ) ;?></em></p>
	</div>
	</div>									
</div>