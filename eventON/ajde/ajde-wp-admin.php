<?php
/**
 * AJDE wp-admin all the other required parts for wp-admin
 * @version 2.8
 */

if(class_exists('ajde_wp_admin')) return;

class ajde_wp_admin{
	public $content = '';
	public function __construct(){}
	

	// date time selector
		function print_date_time_selector($A){
			EVO()->elements->print_date_time_selector( $A );			
		}

	// ONLY time selector
		function print_time_selector($A){			
			EVO()->elements->print_time_selector( $A );
		}

		function _get_date_picker_data(){
			return EVO()->elements->_get_date_picker_data();
		}
		function _print_date_picker_values(){	
			EVO()->elements->_print_date_picker_values();
		}

	// icon selector
		function icons(){
			include_once( AJDE_EVCAL_PATH.'/assets/fonts/fa_fonts.php' );
			ob_start();?>			
			<div class='ajde_fa_icons_selector'>
				<div class="fai_in">
					<ul class="faicon_ul">
					<?php
					// $font_ passed from incldued font awesome file above
					if(!empty($font_)){
						foreach($font_ as $fa){
							echo "<li><i data-name='".$fa."' class='fa ".$fa."' title='{$fa}'></i></li>";
						}
					}
					?>						
					</ul>
				</div>
			</div>
			<?php return ob_get_clean();
		}
		function get_font_icons_data(){
			include_once( AJDE_EVCAL_PATH.'/assets/fonts/fa_fonts.php' );
			return $font_;
		}

	// Options panel for custom posts
		function options_panel($fields, $PMV){

			global $ajde;
			$ajde->load_colorpicker();

			ob_start();

			echo "<div class='ajde_options_panel'>";
			foreach($fields as $field){
				$VAL = (!empty($field['id']) && !empty($PMV[$field['id']]))? $PMV[$field['id']][0]:false;
				$DEFAULT = (!empty($field['default']) && !empty($PMV[$field['default']]))? $PMV[$field['default']][0]:false;
				$TOOLTIP = !empty($field['tooltip'])? $this->tooltips($field['tooltip']):false;

				switch ($field['type']) {
					case 'note':
						echo "<p>{$field['content']}</p>";
					break;	
					case 'text':
						$DEF = !empty($field['default'])? $field['default']:'';
						echo "<p><label>{$field['label']}{$TOOLTIP}</label><input name='{$field['id']}' value='{$VAL}' placeholder='{$DEF}'/></p>";
					break;	
					case 'textarea':
						$content = $VAL? stripcslashes($VAL): 
							( !empty($field['default'])? $field['default']:'');
						echo "<p><label>{$field['label']}{$TOOLTIP}</label><textarea name='{$field['id']}'>{$content}</textarea></p>";
					break;
					case 'image':
						$image = ''; 
						
						echo "<p><label>{$field['label']}{$TOOLTIP}</label></p>";
						$preview_img_size = (empty($field['preview_img_size']))?'medium': $field['preview_img_size'];
						echo '<span class="custom_default_image" style="display:none">'.$image.'</span>';  
						if ($VAL) { $image = wp_get_attachment_image_src($VAL, $preview_img_size); $image = $image[0]; } 
						
						$img_code = (empty($image))? "<p class='custom_no_preview_img'><i>No Image Selected</i></p><img src='' style='display:none' class='custom_preview_image' />"
							: '<p class="custom_no_preview_img" style="display:none"><i>No Image Selected</i></p><img src="'.$image.'" class="custom_preview_image" alt="" />';
						
						echo '<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.$VAL.'" /> 
							'.$img_code.'<br /> 
		                    <input class="custom_upload_image_button button" type="button" value="Choose Image" /> 
		                    <small> <a href="#" class="custom_clear_image_button">Remove Image</a></small> 
		                    <br clear="all" />';
					break;
					case 'color':
						$DEF = (!empty($field['default'])? $field['default']:'3d3d3d');
						$color = $VAL? $VAL: $DEF;
						echo "<p class='row_color'><label>{$field['label']}{$TOOLTIP}</label><em>
							<span id='{$field['id']}' class='colorselector' style='background-color:#{$color}' hex='{$color}'></span>
							<input type='hidden' name='{$field['id']}' data-default='{$DEF}'/>
						</em></p>";
					break;
					case 'wysiwyg':
						echo "<p><label>{$field['label']}{$TOOLTIP}</label></p>";
						$content = $VAL? stripcslashes($VAL): 
							( !empty($field['default'])? $field['default']:'');
						wp_editor($content, $field['id']);
					
					break;
					case 'select':
						if(empty($field['options'])) break;
						echo "<p><label>{$field['label']}</label> <select name='{$field['id']}'>";
						foreach($field['options'] as $sfield=>$sval){							
							echo "<option value='{$sfield}' ".($VAL==$sfield?'selected="selected"':'').">{$sval}</option>";
						}
						echo "</select>{$TOOLTIP}</p>";
					break;
					case 'yesno':
						echo "<p id='ajde_field_{$field['id']}'>".$this->html_yesnobtn(array('label'=>$field['label'],'input'=>true, 'default'=>$DEFAULT,
							'abs'=>'yes',
							'attr'=> (!empty($field['attr'])? $field['attr']:''),
							'var'=>$VAL,
							'id'=>$field['id'], 
							))."{$TOOLTIP}</p>";
					break;
					case 'beginafterstatement':	
						$yesno_val = (!empty($PMV[$field['val']]))? $PMV[$field['val']][0]:'no';
						echo "<div id='{$field['id']}' class='ajde_options_inner' style='display:".(($yesno_val=='yes')?'block':'none')."'>";
					break;
					case 'endafterstatement':
						echo "</div>";
					break;
					// for show if select
					case 'beginShowIf':
						$showIf = (!empty($PMV[$field['varname']]))? $PMV[$field['varname']][0]:false;
						$classes = implode(' ', $field['values']);

						echo "<div class='ajdeShowIf {$classes} {$field['varname']}' class='ajde_options_inner' style='display:".(($showIf && in_array($showIf, $field['values']))?'block':'none')."'>";
					break;
					case 'endShowIf':
						echo "</div>";
					break;
				}
			}
			echo "</div>";
			echo "<div id='ajde_clr_picker' class='cp cp-default' style='display:none; position:absolute; z-index:99;'></div>";

			return ob_get_clean();
			

		}
		
	// wp admin tables
		function start_table_header($id, $column_headers, $args=''){ 

			$defaults = array(
				'classes'=>'',
				'display'=>'table'
			);
			$args = !empty($args)? array_merge($defaults, $args): $defaults;
			?>
			<table id="<?php echo $id;?>" class='evo_admin_table <?php echo !empty($args['classes'])? implode(' ',$args['classes']):'';?>' style='display:<?php echo $args['display'];?>'>
				<thead width="100%">
					<tr>
						<?php
						foreach($column_headers as $key=>$value){
							// width for column
							$width = (!empty($args['width'][$key]))? 'width="'.$args['width'][$key].'px"':'';
							echo "<th id='{$key}' class='column column-{$key}' {$width}>".$value."</th>";
						}
						?>
					</tr>
				</thead>
				<tbody id='list_items' width="100%">
			<?php
		}
		function table_row($data='', $args=''){
			$defaults = array(
				'classes'=>'',
				'tr_classes'=>'',
				'tr_attr'=>'',
				'colspan'=>'none'
			);
			$args = !empty($args) ?array_merge($defaults, $args): $defaults;

			// attrs
				$tr_attr = '';
				if(!empty($args['tr_attr']) && sizeof($args['tr_attr'])>0){
					foreach($args['tr_attr'] as $key=>$value){
						$tr_attr .= $key ."='". $value ."' ";
					}
				}
			
			if($args['colspan']=='all'){
				echo "<tr class='colspan-row ".(!empty($args['tr_classes'])? implode(' ',$args['tr_classes']):'')."' ".$tr_attr.">";
				echo "<td class='column span_column ".(!empty($args['classes'])? implode(' ',$args['classes']):'')."' colspan='{$args['colspan_count']}'>".$args['content']."</td>";
			}else{
				echo "<tr class='regular-row ".(!empty($args['tr_classes'])? implode(' ',$args['tr_classes']):'')."' ".$tr_attr.">";
				foreach($data as $key=>$value){
				
					echo "<td class='column column-{$key} ".(!empty($args['classes'])? implode(' ',$args['classes']):'')."'>".$value."</td>";
				}
			}
			
			echo "</tr>";
		}
		function table_footer(){
			?>
			</tbody>
			</table>
			<?php
		}

// LEGACY
	// select row
		function _print_row_select($A){
			echo EVO()->elements->get_element(array(
				'row_class'=> $A['class'],
				'name'=>$A['name'],
				'value'=> $A['def_val'],
				'options'=> $A['options'],
			)); 
		}

		// tool tips
		function tooltips($content, $position='', $echo = false){
			$content = EVO()->elements->tooltips($content, $position);
			if($echo){ echo $content;  }else{ return $content; }	
		}
		function echo_tooltips($content, $position=''){
			$this->tooltips($content, $position,true);
		}
	// YES NO Button
		function html_yesnobtn($args=''){
			return EVO()->elements->yesno_btn($args);
		}	

	// lightbox content box
		function lightbox_content($arg){
			EVO()->lightbox->admin_lightbox_content($arg);
		}
		

}