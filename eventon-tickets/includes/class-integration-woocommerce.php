<?php
/**
 * Ticket Integration with Woocommerce
 * @version 1.7.7
 */

class EVOTX_WC{
	public function __construct(){


		$this->fnc = new evotx_functions();

		$this->opt2 = EVOTX()->opt2;
		$this->eotx = get_option('evcal_options_evcal_tx');


		// Register ticket item data in Cart
			add_filter('woocommerce_add_cart_item_data',array($this,'add_item_data'),1,2);

			add_filter('woocommerce_get_cart_item_from_session', array($this,'get_cart_items_from_session'), 1, 3 );

		// ADDING to CART
			add_action('evotx_after_ticket_added_to_cart', array($this, 'add_ticket_data_tocart_session'), 10, 4);
		// cart item view
			add_filter('woocommerce_cart_item_class',array($this, 'cart_item_class'),10, 3);
			add_filter('woocommerce_cart_item_quantity',array($this,'cart_item_quantity'),1,3);
			// display custom date in cart
			add_filter('woocommerce_cart_item_name',array($this,'cart_item_name_box'),1,3);
			add_filter('woocommerce_cart_item_permalink',array($this,'cart_item_permalink'),1,3);

			// display order details			
			add_filter('woocommerce_order_item_class', array($this, 'order_item_class_names'), 10,3);
			add_action('woocommerce_check_cart_items', array($this, 'cart_validation'), 10);

		// cart modification
			//add_action('woocommerce_after_cart_item_quantity_update',array($this,'remove_user_custom_data_options_from_cart'),1,1);
			add_action('woocommerce_before_cart_item_quantity_zero',array($this,'remove_ticket_data'),1,1);
			add_filter('woocommerce_cart_emptied', array($this,'remove_ticket_data'), 10, 1 );
			add_filter('woocommerce_remove_cart_item', array($this,'remove_ticket_data'), 10, 2 );
			add_filter('woocommerce_cart_item_removed', array($this,'remove_ticket_data'), 10, 2 );

			// cart updates with quantity changes
			add_filter('woocommerce_update_cart_action_cart_updated', array($this, 'cart_tickets_updated'), 10, 1);

		// checkout
			add_action('woocommerce_checkout_create_order_line_item',array($this,'order_item_meta_update_new'),1,4);
			
			add_action('woocommerce_checkout_order_processed', array($this, 'create_evo_tickets'), 10, 1);
			add_action('woocommerce_checkout_order_processed', array($this, 'reduce_stock_at_checkout'), 10, 1);
			//add_action('woocommerce_reduce_order_stock', array($this, 'reduce_stock'), 10, 1);
			//add_action('woocommerce_restore_order_stock', array($this, 'restock_stock'), 10, 1);

		// Additional order fields - guest names
			if( empty($this->eotx['evotx_hideadditional_guest_names']) || $this->eotx['evotx_hideadditional_guest_names'] !='yes' ):
				// show additional fields in checkout
					add_filter( 'woocommerce_checkout_fields', array($this,'filter_checkout_fields') );
					add_action( 'woocommerce_after_order_notes' ,array($this,'extra_checkout_fields') );
					add_action( 'woocommerce_after_checkout_validation' ,array($this,'extra_fields_process'), 10,2 );

				// save extra information
				add_action( 'woocommerce_checkout_update_order_meta', array($this,'save_extra_checkout_fields') );

				// display in order details section
				add_action( 'woocommerce_order_details_after_order_table', array($this,'display_orderdetails'),10,1 );
			endif;
			
		// Thank you page
			if( !evo_settings_val('evotx_hide_thankyou_page_ticket',$this->eotx) ){
				add_action('woocommerce_thankyou', array( $this, 'wc_order_tix' ), 10 ,1);
			}

			if( !evo_settings_check_yn($this->eotx,'evotx_hide_orderpage_ticket')){
				add_action('woocommerce_view_order', array( $this, 'wc_order_tix' ), 10 ,1);
			}

		// AFTER ORDER			
			// Restock refunded tickets
			foreach(array(
				array('old'=>'processing','new'=>'refunded'),
				array('old'=>'completed','new'=>'refunded'),
				array('old'=>'on-hold','new'=>'refunded'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'restock_stock_from_orderid'), 10,1);
			}

			// when orders are cancelled
			foreach(array(
				array('old'=>'processing','new'=>'cancelled'),
				array('old'=>'completed','new'=>'cancelled'),
				array('old'=>'on-hold','new'=>'cancelled'),
				array('old'=>'pending','new'=>'cancelled'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], array($this, 'restock_cancelled_orders'), 10,1);
			}

			// when orders failed
			foreach(array(
				array('old'=>'processing','new'=>'failed'),
				array('old'=>'completed','new'=>'failed'),
				array('old'=>'on-hold','new'=>'failed'),
				array('old'=>'pending','new'=>'failed'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'restock_failed_orders'), 10,1);
			}

			// when refunded orders were repurchased or completed
			foreach(array(
				array('old'=>'refunded','new'=>'processing'),
				array('old'=>'refunded','new'=>'completed'),
			) as $status){
				add_action('woocommerce_order_status_'.$status['old'] .'_to_'. $status['new'], 
					array($this, 'reduce_stock_from_orderid'), 10,1);
			}

			add_action('woocommerce_order_refunded', array($this, 'order_refunded'), 10, 2);

			// hide some order item meta from showing in order edit page
			add_filter('woocommerce_hidden_order_itemmeta', array($this,'hide_order_item_metafields'),10,1);			

		// Format ticket meta key slug name
			add_filter('woocommerce_order_items_meta_display', array($this, 'ordermeta_display'), 10,2);
			add_filter('woocommerce_display_item_meta', array($this, 'order_item_meta'), 10, 3);

		// EMAILING
			if(empty($this->eotx['evotx_tix_email']) || (!empty($this->eotx['evotx_tix_email']) && $this->eotx['evotx_tix_email']!='yes') ){
				add_action('woocommerce_order_status_completed', array($this, 'send_ticket_email'), 15, 1);	
			}
			add_filter('woocommerce_order_item_name', array($this, 'order_item_name'), 10, 2);			
			add_filter('woocommerce_email_order_meta_fields', array($this, 'order_item_meta_alt'), 10, 3);
			add_action( 'woocommerce_email_after_order_table', array( $this, 'order_details' ), 10, 4 );
	}

	// CART INIT
		// add ticket item data from AJAX to session
			function add_item_data($cart_item_data,$product_id){	        
		        
		        if( !empty($_REQUEST['add-to-cart']) &&	$_REQUEST['add-to-cart'] == $product_id && 
		        	isset($_REQUEST['ri']) &&
		        	!empty($_REQUEST['eid'])
		        ){
		        	$new_value = array();
		        	
		        	if(!isset($cart_item_data['evotx_repeat_interval_wc']))
		        		$new_value['evotx_repeat_interval_wc'] = (!empty($_REQUEST['ri'])? $_REQUEST['ri']:0);
		        	
		        	$new_value['evotx_event_id_wc'] = $_REQUEST['eid'];

		        	if(!empty($_REQUEST['eloc'])) $new_value['evotx_elocation'] = urldecode($_REQUEST['eloc']);

		        	// language
		        	if(!empty($_REQUEST['lang'])) $new_value['evotx_lang'] = urldecode($_REQUEST['lang']);

		        	return (empty($cart_item_data))? $new_value: array_merge($cart_item_data,$new_value);

		        }
		        return $cart_item_data;
		    }

	    // get ticket item from session and add to cart object
		    function get_cart_items_from_session($session_data, $values, $key){
			    
		        $cart_session_data = apply_filters('evotx_cart_session_item_values', array(
		        	'evotx_event_id_wc',
		        	'evotx_repeat_interval_wc',
		        	'evotx_elocation',
		        	'evotx_lang'
		        ));

		        //print_r($session_data);
		        foreach($cart_session_data as  $meta_key){
		        	if (array_key_exists( $meta_key, $values ) ){
		        		$session_data[$meta_key] = $values[$meta_key];
		        	}
	        	}

	        	// set custom price
	        	// altered prices by ticket addons will be set using filtes in priority order
	        	if(!isset($values['line_total'])) return $session_data;
	        	$alter_ticket_price = apply_filters('evotx_ticket_item_price_for_cart',false, $values['line_total'], $session_data, $values);

	        	if( $alter_ticket_price === false) return $session_data;

	        	$session_data['data']->set_price( $alter_ticket_price );

		        return apply_filters('evotx_get_cart_item_from_session',$session_data,$values, $key);
		    }
	
	// Adding to CART
		function add_ticket_data_tocart_session($cart_item_key, $EVENT, $DATA, $cart_item_data){

			// add ticket data to cart session
			$data = (array)WC()->session->get( '_evotx_cart_data' );
			if ( empty( $data[$cart_item_key] ) ) {
				$data[$cart_item_key] = array();
			}

			// add quantity to cart item data
			if(isset($DATA['qty'])) $cart_item_data['quantity'] = $DATA['qty'];
			if(isset($DATA['event_data']) && isset($DATA['event_data']['wcid'])) $cart_item_data['wcid'] = $DATA['event_data']['wcid'];

			$data[$cart_item_key] = $cart_item_data;

			WC()->session->set( '_evotx_cart_data', $data );
		}

	// CART item View
		// cart class name
			function cart_item_class($name, $cart_item, $cart_item_key){
				if(empty($cart_item['evotx_event_id_wc'])) return $name;			
				return $name .' '.'evo_event_ticket_item';
			}
		// cart ticekt permalink alteration
			function cart_item_permalink($link, $cart_item, $cart_item_key){
				if(empty($cart_item['evotx_event_id_wc'])) return $link;
				$t = $cart_item;

				unset($t['data']);
				//print_r($t);
				return get_permalink($cart_item['evotx_event_id_wc']);
			}

		// CART ticket item name
		    function cart_item_name_box($product_name, $values, $cart_item_key ) {
		    	if(!isset($values['evotx_repeat_interval_wc'])) return $product_name;
		    	if( empty($values['evotx_event_id_wc']) ) return $product_name;

		    	$event_id = $values['evotx_event_id_wc'];
		    	$ri = $values['evotx_repeat_interval_wc'];
		    	
		    	// Set global eventon lang
		    	$lang = isset($values['evotx_lang'])? $values['evotx_lang']:'L1';
		    		evo_set_global_lang($lang);
        		
        		$EVENT = new EVO_Event( $event_id);
        		$EVENT->set_lang( $lang);

	        	// get the correct event time
	        	$ticket_time = EVOTX()->functions->get_event_time('', $ri, $values['evotx_event_id_wc']);

	        	$event_name = sprintf( '<a href="%s">%s</a>', esc_url( $EVENT->get_permalink() ), get_the_title($EVENT->ID) );
	        			        	
	            $return_string = $event_name;
	            $return_string .= "<p><span class='item_meta_data'>";


	            // show other ticket item meta data in cart
	            	foreach( apply_filters('evotx_ticket_item_meta_data', array(
	            		'event_time' => array($this->langX('Event Time','evoTX_005a'), ucwords($ticket_time) ),
	            		'event_location' => (isset($values['evotx_elocation'])? array($this->langX('Event Location','evoTX_005c'), stripslashes($values['evotx_elocation']) ):''),
	            	), $values, $EVENT) as $field=>$val){
	            		if(empty($val)) continue;
	            		$return_string .= '<span class="item_meta_data_'.$field.'"><b>'. $val[0]."</b> " . $val[1]. "</span>";
	            	}
	            		            
	            $return_string .= "</span></p>";  
	            
	            return apply_filters('evotx_cart_item_name', $return_string, $EVENT, $values, $cart_item_key);
		    }
		// Quantity
			function cart_item_quantity($product_quantity, $cart_item_key, $cart_item='' ){
				if(empty($cart_item)) return $product_quantity;
		   		if(empty($cart_item['evotx_event_id_wc']) ) return $product_quantity;
		   		if(!isset($cart_item['evotx_repeat_interval_wc']) ) return $product_quantity;
	   		

		   		$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		   		// if set to sold individually
	   			if( $_product->is_sold_individually()  ) return $product_quantity;

	   			// pluggability
	   			$product_quantity_alt = apply_filters('evotx_cart_item_quantity', false, $_product, $cart_item_key, $cart_item);
	   			if( $product_quantity_alt !== false ) return $product_quantity_alt;

		   		$max_qty = $_product->backorders_allowed() ? '' : $_product->get_stock_quantity();

		   		if( $_product && $_product->is_type('simple')){

		   			$event_pmv = get_post_meta($cart_item['evotx_event_id_wc']);
		   			$product_pmv = get_post_meta($_product->get_id());

		   			global $evotx;
		   			$tix_inStock = $evotx->functions->event_has_tickets($event_pmv, $product_pmv, $cart_item['evotx_repeat_interval_wc']);

		   			// Set maximum quantity based on the ticket's stock values
		   			$max_qty = $tix_inStock;
		   			if($tix_inStock === false) $max_qty = 0;
		   			if($tix_inStock === true) $max_qty = '';
		   		}

		   		$product_quantity =woocommerce_quantity_input( array(
					'input_name'  => "cart[{$cart_item_key}][qty]",
					'input_value' => $cart_item['quantity'],
					'max_value'   => $max_qty,
					'min_value'   => '0',
				), $_product, false );
		   		return $product_quantity;
		   		
		   	}

		// ticekt item meta display			
			function order_item_class_names($name, $item, $order){
				$item_id = $item->get_ID();

				$event_id = wc_get_order_item_meta($item_id ,'_event_id'); 

				if(!$event_id) return $name;

				return $name.' evo_event_ticket_item';
			}

		// cart item validation
			function cart_validation(){
				global $evotx;
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

					//print_r($cart_item['evotx_event_id_wc']);
					// if event id and repeat interval missing skip those cart items
					if(empty($cart_item['evotx_event_id_wc'])) continue;
					if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;

					if ( $cart_item['product_id'] > 0 ) {

						$E = new evotx_event( (int)$cart_item['evotx_event_id_wc'],'', (int)$cart_item['evotx_repeat_interval_wc'] );
						$event_meta = get_post_custom($cart_item['evotx_event_id_wc']);
						$product_meta = get_post_custom($cart_item['product_id']);


						// if tickets disabled for events
						if(!$E->check_yn('evotx_tix')){
							WC()->cart->remove_cart_item($cart_item_key);
							wc_add_notice( 'TIcket is no longer for sale!' );
						}else{

							// check for stop selling tickets validation
							$stop_selling = $E->is_stop_selling_now();

							$stock = $E->has_tickets();

							// if there is no stocks or quantity is more than stock
							if(!$stock || $stop_selling){
								
								WC()->cart->remove_cart_item($cart_item_key);
								wc_add_notice( 'Ticket removed from cart, no longer available in stock!', 'error' );

							}elseif( $stock < $cart_item['quantity']){
								// if quantity is more than stock update quantity and refresh total
								WC()->cart->set_quantity($cart_item_key, $stock, true);
								wc_add_notice( 'Ticket quantity adjusted to stock levels!' );
							}
						}
						

						// action hook 
						do_action('evotix_cart_item_validation', $cart_item_key, $cart_item, $cart_item['evotx_event_id_wc'],$event_meta);
					}
					
				}
			}

	// CHECKOUT
		// add custom data to new order item 
		// this data can be used to access order item data later
		    function order_item_meta_update_new($item, $cart_item_key, $values, $order){
		       			       
		        // event id
		        	if(isset($values['evotx_event_id_wc'])){

		        		$ri = (!empty($values['evotx_repeat_interval_wc']))? $values['evotx_repeat_interval_wc']: 0;
		        		$time = EVOTX()->functions->get_event_time('', $ri, $values['evotx_event_id_wc']);
		        		$ticket_time = ucwords($time); // capitalize the words			

		        		$item->add_meta_data( '_event_id' , $values['evotx_event_id_wc'] , true); 
		        		$item->add_meta_data( 'Event-Time' , $ticket_time , true); 
		        	}

		        // saving other order item data
			   		if( isset($values['evotx_event_id_wc'])){
			   			
			   			// other data
				        foreach(array(
				        	'evotx_repeat_interval_wc'=> '_event_ri',
				        	'evotx_elocation'=> 'Event-Location',
				        	'evotx_lang'=> '_evo_lang',
				        ) as $kk=>$vv){
				        	if(!isset($values[$kk]) ) continue;
				        	$item->add_meta_data( $vv , $values[$kk] , true); 
				        }

				        // pluggable
			   			do_action('evotx_checkout_create_order_line_item', $item, $cart_item_key, $values, $order);
			   		}   
			}

		// When cart item quantity was set to zero // AKA removed item from cart
			function remove_ticket_data($cart_item_key = null){

				$data = (array)WC()->session->get( '_evotx_cart_data' );

				// if no item is specified delete all item data
				if ( $cart_item_key == null ) {
					WC()->session->set( '_evotx_cart_data', array() );
					return;
				}

				// If item is specified, but no data exists, just return
				if(!isset( $data[$cart_item_key] )) return;

				// restock ticket
				do_action('evotx_cart_ticket_removed', $cart_item_key, $data[$cart_item_key] );

				// remove deleted cart item data from ticket cart session
				unset( $data[$cart_item_key] );
				WC()->session->set( '_evotx_cart_data', $data );
			}
		// cart ticket updates
			function cart_tickets_updated($cart_updated){

				// run through each item in cart that are event tickets
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

					//print_r($cart_item['evotx_event_id_wc']);
					// if event id and repeat interval missing skip those cart items
					if(empty($cart_item['evotx_event_id_wc'])) continue;
					if(!isset($cart_item['evotx_repeat_interval_wc'])) continue;	

					// do action
					do_action('evotx_cart_tickets_updated', $cart_item_key, $cart_item);

				}

				return $cart_updated;
			}

		// create associate evo-tix post when order is completed
			function create_evo_tickets($order_id){
				$ET = new evotx_tix();
				$ET->create_tickets_for_order($order_id);
			}
		// alter event orders when checkout order is processed
			function alter_event_orders($order_id){
				global $evotx;			
				EVOTX()->functions->alt_initial_event_order($order_id);
			}
		
		// RESTOCK & REDUCE STOCK 
			function reduce_stock($order){// @dep
				$order_id = $order->get_id();
				$this->adjust_ticket_var_stock($order_id,'reduce');
			}
			function restock_stock_from_orderid($order_id){
				$this->adjust_ticket_var_stock($order_id,'restock');
			}
			function reduce_stock_from_orderid($order_id){
				$this->adjust_ticket_var_stock($order_id,'reduce');
			}
			function restock_stock($order){// @dep
				$order_id = $order->get_id();
				$this->adjust_ticket_var_stock($order_id,'restock');
			}
			function reduce_stock_at_checkout($order_id){
				$this->adjust_ticket_var_stock($order_id,'reduce', 'cart');
			}
			function restock_cancelled_orders($order_id){
				$this->adjust_ticket_var_stock($order_id,'restock','cancelled');
			}
			function restock_failed_orders($order_id){
				$this->adjust_ticket_var_stock($order_id,'restock','failed');
			}

		// Adjust ticket stock
		// this will not run for cancelled or failed orders
			function adjust_ticket_var_stock($order_id, $type='reduce', $stage='def'){
				$order = new WC_Order( $order_id );	

				if(sizeof( $order->get_items() ) <= 0) return false;

				// if restocking tickets and auto restock ticket stock is disabled, bail
				if( $type=='restock' && !evo_settings_val( 'evotx_restock',EVOTX()->evotx_opt)) return false;
				
				// check if the stock was reduced when order placed
				$evo_stock_reduced = get_post_meta($order_id, 'evo_stock_reduced',true);
				$evo_stock_reduced = empty($evo_stock_reduced)? false: $evo_stock_reduced;

				$proceed = false;
				
				if(!$evo_stock_reduced) $proceed = true;
				if( $evo_stock_reduced && 
					( ($evo_stock_reduced == 'yes'&& $type =='restock') ||	
					($evo_stock_reduced == 'no'&& $type=='reduce') ) 
				){					
					$proceed = true;
				}

				if(!$proceed) return false;
			
				$stock_reduced = false;
				// each order item in the order
			    	foreach ( $order->get_items() as $item_id=>$item) {

			    		if ( $item['product_id'] > 0 ) {    			
				    		
				    		$event_id = ( isset($item['_event_id']) )? $item['_event_id']:'';
				    		$event_id = !empty($event_id)? $event_id: get_post_meta( $item['product_id'], '_eventid', true);				    		
				    		if(empty($event_id)) continue; // skip non ticket items

				    		$_product = $order->get_product_from_item( $item );

				    		$TIX_EVENT = new evotx_event($event_id);
				    		
				    		$qty   = (int)$item['qty']; // order ticket quantity
				    		$old_stock = $_product->get_stock_quantity(); // old total ticket quantity
				    		
				    		$item_name = $_product->get_sku() ? $_product->get_sku(): $item['product_id'];

				    		
				    		// REPEATING EVENT
				    		if( $TIX_EVENT->is_ri_count_active()){
				    			
				    			$ri = EVOTX()->functions->get_ri_from_itemmeta($item);

				    			// update repeat stock
					    			$qty_adjust = ($type == 'reduce')? $qty * -1: $qty * +1;
				    				EVOTX()->functions->update_repeat_capacity($qty_adjust, $ri, $event_id );
				    			
				    			// restock ONLY on def or failed stage
				    				if(($stage == 'def' || $stage == 'failed') && $type == 'restock' && !empty($new_quantity)){
				    					// adjust product stock
				    					$new_quantity = wc_update_product_stock($_product, $qty, 'increase' );	
				    					
				    					$order->add_order_note( sprintf(
				    						__( 'Event: %s ticket capacity increased from %s to %s.', 'woocommerce' ), 
				    						$TIX_EVENT->get_title(), $old_stock, $new_quantity) );
				    				}

				    			// NOTICE
									$order->add_order_note( sprintf( 
										__( 'Event: (%s) repeat instance capacity changed by %s.', 'evotx' ), 
										$TIX_EVENT->get_title(), $qty_adjust 
									));

								if($type=='reduce') $stock_reduced = true;
				    		// none repeating capacity activated events
				    		}else{

				    			// only for def stage
				    			if($stage == 'def' || $stage == 'failed'){
				    				
					    			// adjust product stock
					    			$new_quantity = wc_update_product_stock($_product, $qty, ($type == 'reduce')?'decrease':'increase' );	
									
									if(!empty($new_quantity)){
										if($type == 'reduce'){
											$order->add_order_note( sprintf( 
												__( 'Event: (%s) ticket capacity reduced from %s to %s.', 'woocommerce' ), 
												$TIX_EVENT->get_title(), $old_stock, $new_quantity) );
										}else{
											$order->add_order_note( sprintf( 
												__( 'Event: (%s) ticket capacity increased from %s to %s.', 'woocommerce' ), 
												$TIX_EVENT->get_title(), $old_stock, $new_quantity) );
				 						}
									}
								}							
				    		}
			    		
				    		// pluggable
				    		$stock_reduced = apply_filters('evotx_adjust_orderitem_ticket_stockother', $stock_reduced, $TIX_EVENT, $order, $item_id, $item, $type, $stage);			    		
				    	}
			    	}

			    $stock_reduced = ($type=='reduce')? true:false;
			    update_post_meta($order_id, 'evo_stock_reduced',($stock_reduced?'yes':'no'));				
			}

	// ADDITIONAL ORDER FILEDS
		function filter_checkout_fields($fields){
		    $fields['evotx_field'] = array(
		            'evotx_field' => array(
		                'type' => 'text',
		                'required'      => false,
		                'label' => __( 'Event Ticket Data' )
		                ),
		            );
		    //print_r($fields);
		    return $fields;
		}
		function extra_checkout_fields(){ 

		    $checkout = WC()->checkout(); 

		    //print_r($checkout->checkout_fields['evotx_field']);

		    // fields required
		    	$required = evo_settings_check_yn($this->eotx, 'evotx_reqadditional_guest_names');	    
		   
		    // there will only be one item in this array - just to pass these values only for tx
		    foreach ( $checkout->checkout_fields['evotx_field'] as $key => $field ) : 
		    	
		    	global $woocommerce;
		    	$items = $woocommerce->cart->get_cart();

		    	$output = '';

		    	$datetime = new evo_datetime();

		    	// @+ 1.7.6
		    	$_event_instance = 1;
		    	$_cart_events = array();


		    	// foreach item in the cart
		        foreach($items as $item => $values) { 

		        	$event_id = !empty($values['evotx_event_id_wc'])? $values['evotx_event_id_wc']:
		        		(!empty($values['evost_eventid'])? $values['evost_eventid']: false);

		        	if(!$event_id) continue;

		        	// add event to cart events array
		        	// same event with different item meta values 
		        	// @+ 1.7.6
		        		if(in_array($event_id, $_cart_events)){	
		        			// if once instance of event exists in cart items	        			
		        			$_event_instance++;		      
		        		}else{
		        			$_event_instance=1;
		        			$_cart_events[] = $event_id;
		        		}


		        	// set language
		        	if(isset($values['evotx_lang'])){
		        		evo_set_global_lang($values['evotx_lang']);
			        }
		        	
		        	// get event time
			        	$RI = !empty($values['evotx_repeat_interval_wc'])? (int)$values['evotx_repeat_interval_wc']:0;
			        	$event_times = $datetime->get_correct_event_time($event_id, $RI);
			        	$event_time = $datetime->get_formatted_smart_time($event_times['start'], $event_times['end'],'',$event_id);

			        $EVENT = new EVO_Event( $event_id,'', $RI);

		        	$_product = wc_get_product($values['variation_id'] ? $values['variation_id'] : $values['product_id']);
 
		        	$product_id = $_product->get_id();

		        	// if there are variation
		        		$variation_text = '';
		        		if(!empty($values['variation'])){
		        			//print_r($values);
		        			foreach($values['variation'] as $key=>$value){
		        				$field = str_replace('attribute_','',$key);
		        				$field = str_replace('pa_', '', $field);

		        				$value = str_replace('-', ' ', $value);
		        				$field = urldecode($field);
		        				$variation_text .= "<span>".$field. ': '.$value."</span> ";
		        			}
		        			$variation_text = "<br/>".$variation_text;
		        		}

		        	$output.= "<div class='evotx_ticket_additional_info'><p class='evo_event_information'>";
		        	$output .= "<span style='display:block'><b>". evo_lang('Event Name').':</b> '. get_the_title($event_id) . $variation_text."</span>";
		        	
		        	$output .= "<span style='display:block'><b>". evo_lang_get('evoTX_005a','Event Time').':</b> '.apply_filters('evotx_cart_add_field_eventtime', $event_time, $values) ."</span>";

		        	$output = apply_filters('evotx_checkout_addnames_other_vars', $output, $values, $EVENT);
		        	
		        	$output .= "</p>";


		        	// for each ticket
		        	if($values['quantity']>0){
		        		$x = 0;
		        		for($x=0; $x<$values['quantity']; $x++){

		        			$Q = $x; // ticket number index

		        			$output .= "<div class='evotx_tai_oneholder'>";
		        			$output .= "<span class='evotx_tai_oneholder_title'>". evo_lang('Ticket Holder') ." #".($Q +1)."</span>";

		        			foreach( $this->_supportive_checkout_additional_fiels($event_id, $item, $values)  as $key=>$data){
		        				
		        				
		        				$placeholder = isset($data['placeholder'])? $data['placeholder']: $data['label'];
			        			
			        			$result = woocommerce_form_field(
			        				'tixholders['.$event_id.']['.$RI.']['.$Q.']['.$_event_instance.']['.$key.']', 
			        			array(
									'type' => 		$data['type'],
									'class' => 		array('my-field-class form-row') ,
									'label' => 		$data['label'],
									'placeholder' => $placeholder,
									'required' => 	$data['required'],
									'return'=>		true
								) , $checkout->get_value('tixholders['.$event_id.']['.$RI.']['.$Q.']['.$_event_instance.']['.$key.']'));

			        			$output .= apply_filters('evotx_checkout_fields', $result, $event_id, $x );

		        			}
		        			$output .= "</div>";   
		        			
		        		}
		        	}  

		        	$output .= "</div>";  
		        } 


		        // tixholders structure is event_id > repeat interval > quantity > event instance > field


		        echo !empty($output)? "<div class='extra-fields'>
		        	<div class='evotx_checkout_additional_names'>
		        	<h3>".evo_lang( 'Additional Ticket Information' )."</h3>".$output . 
		        	'</div></div>':'';

		    endforeach; ?>	  
		<?php }

			// supportive
				private function _supportive_checkout_additional_fiels($event_id, $item, $values){
					$required = evo_settings_check_yn($this->eotx, 'evotx_reqadditional_guest_names');

					$fields = array();
					$fields['name'] =array(
    					'type'=>'text',
    					'label'=> apply_filters('evotx_checkout_addnames_label',evo_lang('Full Name'),$item, $values, $event_id),
    					'required'=> $required,
    				);

    				// additional fields
    				$ad_fields = evo_settings_value($this->eotx, 'evotx_add_fields');
    				if($ad_fields){
    					foreach($ad_fields as $field){
    						switch($field){
    							case 'phone':
    								$fields['phone'] =array(
				    					'type'=>'tel',
				    					'label'=> evo_lang('Phone Number'),
				    					'required'=> $required,
				    				);
    							break;
    							case 'email':
    								$fields['email'] =array(
				    					'type'=>'email',
				    					'label'=> evo_lang('Email Address'),
				    					'required'=> $required,
				    				);
    							break;
    						}
    					}
    				}

					return apply_filters('evotx_additional_ticket_info_fields', $fields);
				}

		function extra_fields_process( $data, $errors ){
			//print_r($data);
			if(!empty($_POST['tixholders'])){
				$required = evo_settings_check_yn($this->eotx, 'evotx_reqadditional_guest_names');	

				//print_r($_POST['tixholders']);

				// if additional fields are required check for data
				if($required){
					$empty = false;
					foreach($_POST['tixholders'] as $event=>$RIS){

						if($empty) continue;	

						foreach($RIS as $ri=>$qtys){	
							foreach($qtys as $qty=>$instances){
								foreach($instances as $V){

									// empty name
									if( empty($V['name']) ) $empty = true;
									// check for minimal name length
									if( strlen($V['name']) <2) $empty = true;
								}
								
							}
						}
					}

					if($empty){ 
						wc_add_notice(  
							sprintf( 
								_x( '%s %s.', 'FIELDNAME %s.', 'evotx' ), 
								'<strong>'. evo_lang('Additional Ticket Information').'</strong>' ,
								evo_lang('is a required field')
							), 
						'error' );
					}
				}
			}			
		}

		function save_extra_checkout_fields( $order_id ){
			if( !empty( $_POST['tixholders'] ) ) {
		    	update_post_meta( $order_id, '_tixholders',  $_POST['tixholders']  );
		    	do_action('evotx_checkout_fields_saving', $order_id);
		    }
		}

		
		function display_orderdetails($order){
			$TA = new EVOTX_Attendees();
			$ticket_holders = $TA->_get_tickets_for_order($order->get_id(), 'event');
			
			if(!$ticket_holders) return $order;	

			//print_r(get_post_meta(668));
			?>
				<header><h2><?php evo_lang_e( 'Ticket Holder Details' ); ?></h2></header>
				<table class="shop_table ticketholder_details" cellspacing="0">
					<?php 
					foreach($ticket_holders as $e=>$dd){
						?><tr><th><?php echo evo_lang( 'Attendee' ); ?></th><td><?php
						foreach($dd as $tn=>$nm){ 
							echo $TA->__display_one_ticket_data($tn, $nm, array(
								'inlineStyles'=>true,
								'showExtra'=>false								
							));
						}
						?></td></tr><?php
	        		}?>					
				</table>
			<?php 
			
			do_action('evotx_checkout_fields_display_orderdetails', $order);
		}

	// THANK YOU PAGE
		// show ticket in frontend customer account pages
		public function wc_order_tix($order_id){
			
			$order = new WC_Order( $order_id );

			if(EVOTX()->functions->does_order_have_tickets($order_id)){

				?><section class='eventon-ticket-details'><?php
				
				// completed orders
				if ( in_array( $order->get_status(), array( 'completed' ) ) ) {

					$evotx_tix = new evotx_tix();
					
					$customer = get_post_meta($order_id, '_customer_user');
					$userdata = get_userdata($customer[0]);

					$order_tickets = $evotx_tix->get_ticket_numbers_for_order($order_id);
					
					$email_body_arguments = array(
						'orderid'=>$order_id,
						'tickets'=>$order_tickets, 
						'customer'=>(isset($userdata->first_name)? $userdata->first_name:'').
							(isset($userdata->last_name)? ' '.$userdata->last_name:'').
							(isset($userdata->user_email)? ' '.$userdata->user_email:''),
						'email'=>''
					);

					$wrapper = "-webkit-text-size-adjust:none !important;margin:0;";
					$innner = "-webkit-text-size-adjust:none !important; margin:0;";
					
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',$this->opt2);?></h2>
					<div class='evotx_event_tickets_section' style="<?php echo $wrapper; ?>">
					<div class='evotx_event_tickets_section_in' style='<?php echo $innner;?>'>
					<?php
						$email = new evotx_email();
						echo $email->get_ticket_email_body_only($email_body_arguments);

					echo "</div></div>";

					
				
				}elseif($order->get_status() == 'refunded'){
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',EVOTX()->opt2);?></h2>
					<p><?php evo_lang_e('This order has been refunded!');?></p>
					<?php
						
				}else{
					?>
					<h2><?php echo evo_lang_get('evoTX_014','Your event Tickets','',EVOTX()->opt2);?></h2>
					<p><?php evo_lang_e('Once the order is processed your event tickets will show here!');?></p>
					<?php
				}	

				// PLUG
				do_action('evotx_wc_thankyou_page_end', $order);

				?></section><?php		
			}
		}

	// AFTER ORDER
		function hide_order_item_metafields($array){
			$array[]= '_event_id';
			$array[]= '_event_ri';
			$array[]= '_evo_lang';
			return apply_filters('evotx_hidden_order_itemmeta', $array);
		}

		// when order is refunded partially change ticket number status
		function order_refunded($order_id, $refund_id){

			if(empty($order_id)) return false;

			$order = new WC_Order( $order_id );	
			$items = $order->get_items();

			if ( count( $items ) <= 0 ) return false;

			if($items){
				$ET = new evotx_tix();
				$EA = new EVOTX_Attendees();

				// each event for which tickets were purchased in the order
				foreach ($items as $item_id => $item) {	
					$event_id = get_post_meta( $item['product_id'], '_eventid', true); 
					if(empty($event_id)) continue;	

					$total_qty = $item->get_quantity();
					$refunded_qty = ($order->get_qty_refunded_for_item($item_id)*-1);
					$non_refunded = $total_qty - $refunded_qty;

					// restock tickets
					

					// get tickets for this event in the order item
					$TH = $EA->get_tickets_for_order($order_id);
					
					$count = 1;
					foreach($TH as $tn=>$td){

						if($count > $non_refunded){
							$ET->change_ticket_number_status('refunded',$tn);
						}else{
							$ET->change_ticket_number_status('check-in',$tn);
						}
						$count++;
					}

				}
			}

		}

	// FORMAT ticekt item meta date
		function ordermeta_display($output, $obj){
			$output = str_replace('Event Time', $this->langX('Event Time','evoTX_005a'), $output);
			$output = str_replace('Event Location', $this->langX('Event Location','evoTX_005c'), $output);
			return $output;
		}
		function order_item_meta($html, $item, $args){
			$html = $this->_format_ticket_item_meta($html);							
			return $html;
		}

		function _format_ticket_item_meta($html){
			foreach(apply_filters('evotx_order_item_meta_slug_replace',array(
				'Event-Time'=>$this->langX('Event Time','evoTX_005a'),
				'Event-Location'=>$this->langX('Event Location','evoTX_005c'),
			)) as $slug=>$name){

				if( strpos($html, $slug) == false) continue;

				$html = str_replace($slug, $name , $html);
			}			
			return $html;
		}

	// EMAILING
		function send_ticket_email($order_id){
			$email = new evotx_email();
			// initial ticket email
			$email->send_ticket_email($order_id, false, true);
		}
		function order_item_name($item_name, $item){

			if(!isset($item['product_id'])) return $item_name;			

			$event_id = get_post_meta($item['product_id'] , '_eventid', true);

			if(!$event_id) return $item_name;

			return sprintf( '<a href="%s">%s</a>', get_permalink($event_id), get_the_title($event_id));
		}
		function order_item_meta_alt($array){
			$updated_array = $array;
			foreach($array as $index=>$field){
				if( isset($field['label'])){
					if( strpos($field['label'], 'Event-Time') !== false){
						$updated_array[$index]['label'] = str_replace('Event-Time', $this->langX('Event Time','evoTX_005a') , $field['label']);						
					}
					if( strpos($field['label'], 'Event-Location') !== false){
						$updated_array[$index]['label'] = str_replace('Event-Location', $this->langX('Event Location','evoTX_005a') , $field['label']);						
					}
				}
			}
			return $updated_array;
		}

		// show additional ticket holders in WC email_body_arguments
		function order_details($order, $sent_to_admin = false, $plain_text = false, $email = ''){

			$TA = new EVOTX_Attendees();
			$ticket_holders = $TA->_get_tickets_for_order($order->get_id(), 'event');

			if(!$ticket_holders) return false;
			if(sizeof($ticket_holders) < 1 ) return false;

			?>
			<div style='margin-bottom:40px'>
			<h2><?php evo_lang_e('Ticket Holder Details');?></h2>
			<table class="shop_table ticketholder_details" style='width:100%; border:1px solid #e5e5e5' cellpadding="0" cellspacing="0">
				<?php 

				foreach($ticket_holders as $e=>$dd){
        			?><tr><td style='border:1px solid #e5e5e5'><?php
        			foreach($dd as $tn=>$nm){ 
						echo $TA->__display_one_ticket_data($tn, $nm, array(
							'inlineStyles'=>true,
							'orderStatus'=>$order->get_status(),								
						));
					}
        			?></td></tr><?php
        		}?>					
			</table>
			</div>

			<?php
		}

	// get language fast for evo_lang
		function lang($text){	return evo_lang($text, '', EVOTX()->opt2);}
		function langE($text){ echo $this->lang($text); }
		function langX($text, $var){	return eventon_get_custom_language(EVOTX()->opt2, $var, $text);	}
		function langEX($text, $var){	echo eventon_get_custom_language(EVOTX()->opt2, $var, $text);		}

}
new EVOTX_WC();