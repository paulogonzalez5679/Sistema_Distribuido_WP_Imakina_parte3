<?php
/**
 * Ticket extension of the event
 * @version 0.1
 */

class evotx_event extends EVO_Event{

	public function __construct($event_id, $event_pmv='', $RI=0, $wcid=''){
		parent::__construct($event_id, $event_pmv);
		$this->wcid = empty($wcid)? $this->get_wcid(): $wcid;
		$this->wcmeta = $this->wcid? get_post_custom($this->wcid): false;	
		$this->ri = $RI;	

		//global $product;
		$this->product = wc_get_product($this->wcid);
		$GLOBALS['product'] = $this->product;
	}

	function get_wcid(){
		return $this->get_prop('tx_woocommerce_product_id')? (int)$this->get_prop('tx_woocommerce_product_id'):false;
	}
	
	// get repeat with stocks available
	function next_available_ri($current_ri_index, $cut_off = 'start'){
		$current_ri_index = empty($current_ri_index)? 0:$current_ri_index;

		if(!$this->is_ri_count_active()) return false;
		
		// if all stocks are out of stock
		$stock_status = $this->get_ticket_stock_status();
		if($stock_status=='outofstock') return false;


		// check repeats
		$repeats = $this->get_repeats();
		if(!$repeats) return false;

		date_default_timezone_set('UTC');	
		$current_time = current_time('timestamp');

		$return = false;
		foreach($repeats as $index=>$repeat){
			if($index<= $current_ri_index) continue;

			// check if start time of repeat is current
			if($cut_off == 'start' && $repeat[0]>=  $current_time) $return = true;
			if($cut_off != 'start' && $repeat[1]>=  $current_time) $return = true;

			if($return){

				$ri_stock = $this->get_repeat_stock($index);

				if($ri_stock>0) return array('ri'=>$index, 'times'=>$repeat);
			}				
		}
		
		return false;
	}


// Cart - output - array
// @1.7.2
	function add_ticket_to_cart($DATA){
		if(!isset($DATA)) return false;

		$default_ticket_price = $this->product->get_price();

		$cart_item_keys = false;
		$status = 'good'; $output = $msg_var = '';

		$qty = $DATA['qty'];
		$event_data = $DATA['event_data'];

		// hook for ticket addons
		$plug = apply_filters('evotx_add_ticket_to_cart_before',false, $this,$DATA);
		if($plug !== false){	return $plug;	}
		
		// gather cart item data before adding to cart
			$cart_item_data = apply_filters('evotx_add_cart_item_meta',
				array(
					'evotx_event_id_wc'			=> $this->ID,
					'evotx_repeat_interval_wc'	=> $this->ri,
					'evotx_lang'				=> (isset($event_data['l'])? $event_data['l']: 'L1')
				), 
			$this, $default_ticket_price, $DATA);


		// Add ticket to cart
			$cart_item_keys = WC()->cart->add_to_cart(
				$this->wcid,
				apply_filters('evotx_add_cart_item_qty',$qty, $this, $default_ticket_price, $DATA),
				0,array(),
				$cart_item_data
			);

			if($cart_item_keys){

				// get total cart quantity for this item
				$DATA['cart_qty'] = WC()->cart->cart_contents[ $cart_item_keys ]['quantity'];
				do_action('evotx_after_ticket_added_to_cart', $cart_item_keys, $this, $DATA, $cart_item_data);
			}
	

		// Successfully added to cart
		if($cart_item_keys!== false){
			$tx_help = new evotx_helper();
			$output = $tx_help->add_to_cart_html();
			$msg = evo_lang('Ticket added to cart successfully!');
		}else{
			$status = 'bad';
			$msg = evo_lang('Could not add ticket to cart, please try later!');
			$msg_var = 't4';
		}

		return json_encode( apply_filters('evotx_ticket_added_cart_ajax_data', array(
			'msg'=>$msg, 
			'msg_var' => $msg_var,
			'status'=> $status,
			'html'=>$output,
			't'=>$DATA
		), $this, $DATA));

	}

// WC Ticket Product
	function wc_is_type($type){
		return $this->product->is_type($type);
	}

// Event Repeat & Stock
	function get_repeat_stock($repeat_index = 0){
		if(!$this->is_ri_count_active()) return false;

		$ri_capacity = $this->get_prop('ri_capacity');
		if(!isset( $ri_capacity[$repeat_index] )) return 0;
		return $ri_capacity[$repeat_index];
	}

// tickets
	function has_tickets(){
		// check if tickets are enabled for the event
			if( !$this->check_yn('evotx_tix')) return false;

		// if tickets set to out of stock 
			if(!empty($this->wcmeta['_stock_status']) && $this->wcmeta['_stock_status'][0]=='outofstock') return false;
		
		// if manage capacity separate for Repeats
		$ri_count_active = $this->is_ri_count_active();

		if($ri_count_active){
			$ri_capacity = $this->get_prop('ri_capacity');
				$capacity_of_this_repeat = 
					(isset($ri_capacity[ $this->ri ]) )? 
						$ri_capacity[ $this->ri ]
						:0;
				return ($capacity_of_this_repeat==0)? false : $capacity_of_this_repeat;
		}else{
			// check if overall capacity for ticket is more than 0
			$manage_stock = (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes')? true:false;
			$stock_count = (!empty($this->wcmeta['_stock']) && $this->wcmeta['_stock'][0]>0)? $this->wcmeta['_stock'][0]: false;
			
			// return correct
			if($manage_stock && !$stock_count){
				return false;
			}elseif($manage_stock && $stock_count){	return $stock_count;
			}elseif(!$manage_stock){ return true;}
		}
	}
	
	function is_stop_selling_now(){
		$stop_sell = $this->get_prop('_xmin_stopsell');
		if($stop_sell ){

			EVO()->cal->set_cur('evcal_tx');
			$stopsellingwhen = EVO()->cal->get_prop('evotx_stop_selling_tickets');
			$stopsellingwhen = $stopsellingwhen && $stopsellingwhen == 'end'? 'end':'start';

			//date_default_timezone_set('UTC');	
			$current_time = current_time('timestamp');

			$eventUNIX = $this->get_event_time( $stopsellingwhen );			
			$timeBefore = (int)($this->get_prop('_xmin_stopsell'))*60;	

			$cutoffTime = $eventUNIX -$timeBefore;

			//echo $current_time.' '. $timeBefore.' '. $eventUNIX.' '. $cutoffTime;
			//echo ($cutoffTime < $current_time)?'y':'n';
			return ($cutoffTime < $current_time)? true: false;
		}else{
			return false;
		}
	}

	// check if the stock of a ticket is sold out
	// @added 1.7
	function is_sold_out(){
		if(!empty($this->wcmeta['_stock_status']) && $this->wcmeta['_stock_status'][0]=='outofstock')
			return true;
		return false;
	}

	// show remaining stop or not
	// @added 1.7 @~ 1.7.2
		function is_show_remaining_stock($stock = ''){

			$tickets_in_stock = $this->has_tickets();

			if(!$this->wc_is_type('simple')) return false;
			if(is_bool($tickets_in_stock) && !$tickets_in_stock) return false;

			if(
				$this->check_yn('_show_remain_tix') &&
				evo_check_yn($this->wcmeta,'_manage_stock') 
				&& !empty($this->wcmeta['_stock']) 
				&& $this->wcmeta['_stock_status'][0]=='instock'
			){

				// show remaining count disabled
				if(!$this->get_prop('remaining_count')) return true;

				// show remaing at set but not managing cap sep for repeats
				if( $this->get_prop('remaining_count') && !$this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $this->wcmeta['_stock'][0]) return true;

				if( $this->get_prop('remaining_count') && $this->check_yn('_manage_repeat_cap') && (int)$this->get_prop('remaining_count') >= $stock ) return true;

				return false;
			}
			return false;
		}

// Attendees
	
	
// stock
	function get_ticket_stock_status(){
		return (!empty($this->wcmeta['_stock_status']))? $this->wcmeta['_stock_status'][0]: false;
	}
	function is_ri_count_active(){
		return (!empty($this->wcmeta['_manage_stock']) && $this->wcmeta['_manage_stock'][0]=='yes'
		&& ($this->get_prop('_manage_repeat_cap')) && $this->get_prop('_manage_repeat_cap')=='yes'
		&& ($this->get_prop('ri_capacity'))
		&& $this->is_repeating_event()
		)? true: false;
	}

}