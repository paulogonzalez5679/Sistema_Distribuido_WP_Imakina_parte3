<?php
/**
 * Ticket Addon Helpers for ticket addon extensions
 * @updated 1.6.7
 */

class evotx_helper{
	 
	function convert_to_currency($price, $symbol = true){		

		extract( apply_filters( 'wc_price_args', wp_parse_args( array(), array(
	        'ex_tax_label'       => false,
	        'currency'           => '',
	        'decimal_separator'  => wc_get_price_decimal_separator(),
	        'thousand_separator' => wc_get_price_thousand_separator(),
	        'decimals'           => wc_get_price_decimals(),
	        'price_format'       => get_woocommerce_price_format(),
	    ) ) ) );

		$sym = $symbol? html_entity_decode(get_woocommerce_currency_symbol($currency)):'';

		$negative = $price < 0;
		$price = floatval($negative? $price *-1: $price);
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

		

		if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $decimals > 0 ) {
	        $price = wc_trim_zeros( $price );
	    }

	    $return = ( $negative ? '-' : '' ) . sprintf( $price_format, $sym, $price );

	    if ( $ex_tax_label && wc_tax_enabled() ) {
	        $return .= ' <small class="woocommerce-Price-taxLabel tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
	    }


		return $return;
	}

	// HTML: remaining stock
	// @added 1.7
	function remaining_stock_html($stock, $text='', $visible=true){
		$remaining_count = apply_filters('evotx_remaining_stock', (int)$stock);

		// text string
		if(empty($text)){
			$text = $remaining_count>1? 
				EVO()->frontend->lang('','evoTX_013','Tickets Remaining!') : 
				evo_lang('Ticket Remaining!');
		} 

		echo "<p class='evotx_remaining' data-count='{$remaining_count}' style='display:". ($visible?'block':'none')."'>
			<span class='evotx_remaining_stock'>";
		echo "<span>" . $remaining_count . "</span> ";
		echo $text;
		echo "</span></p>";
	}

	// HTML Price 
	// @updated: 1.7.3
	function base_price_html($price, $unqiue_class='', $striked_price = '', $label_additions=''){

		$strike_  = (!empty($striked_price) && $striked_price != $price)? "<span class='strikethrough' style='text-decoration: line-through'>". $this->convert_to_currency($striked_price).'</span> ':'';

		$label_addition  = !empty($label_additions)? " <span class='label_add' style='font-style:italic; text-transform:none;opacity:0.6'>". $label_additions.'</span> ':'';
		?>
		<div itemprop='offers' itemscope itemtype='http://schema.org/Offer'>
			<p itemprop="price" class='price tx_price_line <?php echo $unqiue_class;?>' content='<?php echo $price;?>'>
				<meta itemprop='priceCurrency' content='<?php echo get_woocommerce_currency_symbol();?>'/>
				<meta itemprop='availability' content='http://schema.org/InStock'/>
				<span class='evo_label'><?php echo evo_lang('Price');?><?php echo $label_addition;?></span> 
				<span class='value' data-sp='<?php echo $price;?>'><?php echo $strike_;?><?php echo $this->convert_to_currency( $price);?></span>
				<input type="hidden" data-prices=''>
			</p>
		</div> 
		<?php
	}
	function custom_item_meta($name, $value, $unqiue_class=''){
		?>
		<p class='evotx_ticket_other_data_line <?php echo $unqiue_class;?>'>
			<span class='evo_label'><?php echo $name;?></span> 
			<span class='value' ><?php echo $value;?></span>
		</p>
		<?php
	}
	function ticket_qty_html($max='', $unqiue_class=''){
		$max = empty($max)? '':$max;
		?>
		<p class="evotx_quantity">
			<span class='evo_label'><?php evo_lang_e('How many tickets?');?></span>
			<span class="qty evotx_qty_adjuster">
				<b class="min evotx_qty_change <?php echo $unqiue_class;?>">-</b><em>1</em>
				<b class="plu evotx_qty_change <?php echo $unqiue_class;?> <?php echo (!empty($max) && $max==1 )? 'reached':'';?>">+</b>
				<input type="hidden" name='quantity' value='1' data-max='<?php echo $max;?>'/>
			</span>
		</p>
		<?php
	}
	// @+1.7.2
	function ticket_qty_one_hidden(){
		?>
		<p class="evotx_quantity" style='display:none'>
			<span class="qty evotx_qty_adjuster">
				<input type="hidden" name='quantity' value='1' data-max='1'/>
			</span>
		</p>
		<?php
	}
		
	function total_price_html($price, $unqiue_class='', $wcid=''){
		?>
		<p class='evotx_addtocart_total <?php echo $unqiue_class;?>'>
			<span class="evo_label"><?php evo_lang_e('Total Price');?></span>
			<span class="value"  data-wcid='<?php echo $wcid;?>'><?php echo $this->convert_to_currency($price);?></span>
		</p>
		<?php
	}
	function add_to_cart_btn_html($btn_class='', $data_arg = array()){
		$str = '';
		foreach( $data_arg as $field=>$val){
			$str .= ' data-'.$field."='". $val."'";
		}
		?>
		<p class='evotx_addtocart_button'>
			<button class="evcal_btn <?php echo $btn_class;?>" style='margin-top:10px' <?php echo $str;?>><?php evo_lang_e('Add to Cart')?></button>
		</p>
		<?php
	}

	// Echo the add to cart item meta data
	function print_add_to_cart_data($data = array()){

		$data = $this->get_add_to_cart_evotx_data_ar($data);

		$str = '';
		foreach( $data as $field=>$val){
			$str .= ' data-'.$field."='". json_encode($val)."'";
		}
		?>
	 	<div class='evotx_data' <?php echo $str;?>></div>
		<?php
	}

	// returns the evotx_data array content
	function get_add_to_cart_evotx_data_ar($new_data = array()){

		$daya = array();
		$data['pf'] = $this->get_price_format_data();
		$data['t'] = $this->get_text_strings();

		$ticket_redirect = evo_settings_value(EVOTX()->evotx_opt,'evotx_wc_addcart_redirect');
		$wc_redirect_cart = get_option( 'woocommerce_cart_redirect_after_add' );
		if( empty($ticket_redirect) && $wc_redirect_cart == 'yes') 
			$ticket_redirect = 'cart';

		// after adding to cart message behavior values
		$data['msg_interaction']['hide_after'] = ($ticket_redirect =='none')? false: true;
		if($ticket_redirect === false ) $data['msg_interaction']['hide_after'] = false;
		$data['msg_interaction']['redirect'] = $ticket_redirect;

		// merging with defaults
		if(count($new_data)>0){
			foreach($new_data as $field=>$val){
				if(count($val)>0){
					foreach($val as $f=>$v){
						$data[$field][$f] = $new_data[$field][$f];
					}
				}				
			}
		}
		
		//$data = array_replace_recursive($data, $new_data);
		
		return apply_filters('evotx_add_to_cart_evotxdata', $data);
	}

	// Return price formatting values
		function get_price_format_data(){
			return array(
				'currencySymbol'=>get_woocommerce_currency_symbol(),
				'thoSep'=> get_option('woocommerce_price_thousand_sep'),
				'curPos'=> get_option('woocommerce_currency_pos'),
				'decSep'=> get_option('woocommerce_price_decimal_sep'),
				'numDec'=> get_option('woocommerce_price_num_decimals')
			);
		}
		private function get_text_strings(){
			return apply_filters('evotx_addtocart_text_strings',array(
				't1'=>evo_lang('Added to cart'),
				't2'=>evo_lang('View Cart'),
				't3'=>evo_lang('Checkout'),
				't4'=>evo_lang('Ticket could not be added to cart, try again later'),
				't5'=>evo_lang('Quantity of Zero can not be added to cart!'),
			));
		}

	// success or fail message HTML after adding to cart
	function add_to_cart_html($type='good', $msg=''){
		$newWind = (evo_settings_check_yn(EVOTX()->evotx_opt,'evotx_cart_newwin'))? 'target="_blank"':'';
		ob_start();
		if( $type =='good'):
			?>
			<p class='evotx_success_msg'><b><?php evo_lang_e('Added to cart');?>!</b>
			<span>
				<a class='evcal_btn' href="<?php echo wc_get_cart_url();?>" <?php echo $newWind;?>><?php evo_lang_e('View Cart');?></a> 
				<a class='evcal_btn' href="<?php echo wc_get_checkout_url();?>" <?php echo $newWind;?>><?php evo_lang_e('Checkout');?></a></span>
			</p>
			<?php
		else:
			if(empty($msg)) $msg = evo_lang('Ticket could not be added to cart, try again later');
			?>
			<p class='evotx_success_msg bad'><b><?php echo $msg;?>!</b>
			<?php
		endif;
		return ob_get_clean();
	}	

	function __get_addtocart_msg_footer($type='', $msg=''){
		?>
		<div class='tx_wc_notic evotx_addtocart_msg'>
		<?php
			if( !empty($type)){
				echo $this->add_to_cart_html($type, $msg);
			}
		?>
		</div>
		<?php
	}

}