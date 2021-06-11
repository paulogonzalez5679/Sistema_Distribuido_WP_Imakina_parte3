/**
 * Event Ticket script 
 * @version 1.6
 */
jQuery(document).ready(function($){
	
// on change variable product selection
    $('body').on('change','table.variations select',function(){
        CART = $(this).closest('table').siblings('.evotx_orderonline_add_cart');
        STOCK = CART.find('p.stock');

        // check if variable products are out of stock
        if(STOCK.hasClass('out-of-stock')){
            CART.find('.variations_button').hide();
        }else{
            CART.find('.variations_button').show();
        }
    });


// get ticket product total price
    $('body').on('evotx_qty_changed', function(event,QTY, MAX, OBJ ){
        SECTION = OBJ.closest('.evotx_ticket_purchase_section');

        $('body').trigger('evotx_calculate_total', [SECTION]);
        
    });

// calculate total price
    $('body').on('evotx_calculate_total', function(event, SECTION ){

        QTY = SECTION.find('input[name=quantity]').val();
        sin_price = SECTION.find('p.price.tx_price_line span.value').data('sp');
        sin_price = parseFloat(sin_price);

        // include sin price additions
        if( SECTION.find('p.price.tx_price_line input').length>0){
            DATA = SECTION.find('p.price.tx_price_line input').data('prices');

            price_add = 0;
            if( Object.keys(DATA).length>0){
                $.each(DATA, function(index, val){
                    p =  parseFloat(val.price);
                    p = p * parseInt( val.qty);

                    price_add += p;
                })
            }
            sin_price += price_add;
        }

        new_price = sin_price * QTY;       
        new_price = get_format_price( new_price, SECTION);
        SECTION.find('.evotx_addtocart_total span.value').html( new_price);
    });

// GET format the price
    function get_format_price(price, SECTION){

        // price format data
        PF = SECTION.find('.evotx_data').data('pf');
       
        totalPrice = price.toFixed(PF.numDec); // number of decimals
        htmlPrice = totalPrice.toString().replace('.', PF.decSep);

        if(PF.thoSep.length > 0) {
            htmlPrice = _addThousandSep(htmlPrice, PF.thoSep);
        }
        if(PF.curPos == 'right') {
            htmlPrice = htmlPrice + PF.currencySymbol;
        }
        else if(PF.curPos == 'right_space') {
            htmlPrice = htmlPrice + ' ' + PF.currencySymbol;
        }
        else if(PF.curPos == 'left_space') {
            htmlPrice = PF.currencySymbol + ' ' + htmlPrice;
        }
        else {
            htmlPrice = PF.currencySymbol + htmlPrice;
        }
        return htmlPrice;
    }
    function _addThousandSep(n, thoSep){
        var rx=  /(\d+)(\d{3})/;
        return String(n).replace(/^\d+/, function(w){
            while(rx.test(w)){
                w= w.replace(rx, '$1'+thoSep+'$2');
            }
            return w;
        });
    };

// increase and reduce quantity
    $('body').on('click','.evotx_qty_change', function(event){

        OBJ = $(this);

        if(OBJ.closest('.evotx_quantity').hasClass('one')) return;

        QTY = parseInt(OBJ.siblings('em').html());
        MAX = OBJ.siblings('input').data('max');        
        if(!MAX) MAX = OBJ.siblings('input').attr('max');
           

        NEWQTY = (OBJ.hasClass('plu'))?  QTY+1: QTY-1;

        NEWQTY =(NEWQTY <= 0)? 0: NEWQTY;


        // can not go below 1
        if( NEWQTY == 0 && OBJ.hasClass('min') && !OBJ.hasClass('zpos')){
            return;
        }

        NEWQTY = (MAX!='' && NEWQTY > MAX)? MAX: NEWQTY;

        OBJ.siblings('em').html(NEWQTY);
        OBJ.siblings('input').val(NEWQTY);

        if( QTY != NEWQTY) $('body').trigger('evotx_qty_changed',[NEWQTY, MAX, OBJ]);
       
        if(NEWQTY == MAX){

            PLU = OBJ.parent().find('b.plu');
            if(!PLU.hasClass('reached')) PLU.addClass('reached');   

            if(QTY == MAX)   $('body').trigger('evotx_qty_max_reached',[NEWQTY, MAX, OBJ]);                 
        }else{            
            OBJ.parent().find('b.plu').removeClass('reached');
        } 
    });

// on triggers for variations form
    $('body').on('reset_data','form.evotx_orderonline_variable',function(event){
        FORM = $(this);     
        FORM.find('.evotx_variation_purchase_section').hide();
    });

    $('body').on('evolightbox_end',function(){
        $('body').trigger('show_variation');
    });
    $('body').on('show_variation','form.evotx_orderonline_variable',function(event, variation, purchasable){
        FORM = $(this);    
        
        // variation not in stock
        if(!variation.is_in_stock){
            FORM.find('.evotx_variations_soldout').show();
            FORM.find('.evotx_variation_purchase_section').hide();
        }else{
            FORM.find('.evotx_variations_soldout').hide();
            FORM.find('.evotx_variation_purchase_section').show();
        }

        if(variation.sold_individually){
            FORM.find('.evotx_quantity').hide();
        }

        NEWQTY = parseInt(FORM.find('.evotx_quantity_adjuster em').html());
        NEWQTY = (variation.max_qty!= '' && NEWQTY > variation.max_qty)? variation.max_qty: NEWQTY;

        FORM.find('.evotx_quantity_adjuster em').html( NEWQTY);
        FORM.find('.evotx_quantity_adjuster input').val( NEWQTY);
    });


// Standalone button
    $('body').on('click','.trig_evotx_btn',function(){
        LIGHTBOX = $('.evotx_lightbox');
        LIGHTBOX.addClass('show');
        $('body').trigger('evolightbox_show');


        // get form html
        var ajaxdataa = {};
        
        ajaxdataa['action'] = 'evotx_standalone_form';
        ajaxdataa['eid'] = parseInt($(this).data('eid'));
        ajaxdataa['ri'] = parseInt($(this).data('ri'));
        $.ajax({
            beforeSend: function(){ 
                LIGHTBOX.find('.evo_lightbox_body').addClass('evoloading').html('<p class="loading_content"></p>');
            },                  
            url:    evotx_object.ajaxurl,
            data:   ajaxdataa,  dataType:'json', type:  'POST',
            success:function(data){

                LIGHTBOX.find('.evo_lightbox_body').html( data.html );
                
            },complete:function(){ 
                LIGHTBOX.find('.evo_lightbox_body').removeClass('evoloading');
            }
        });


    });

// Add to cart custom method
// this method is used by ticket addons when adding tickets to cart
// @version 1.7
    $('body').on('click', '.evotx_addtocart', function(){
        ajaxdata = {};

        BTN = $(this);
        SECTION = BTN.closest('.evotx_ticket_purchase_section');
        EVOROW = BTN.closest('.evorow');  
        EVOTX_DATA = SECTION.find('.evotx_data');        

        ajaxdata['qty'] = SECTION.find('input[name="quantity"]').val(); 
        ajaxdata['action'] = 'evotx_add_to_cart';
        ajaxdata['data'] = EVOTX_DATA.data();

        // check for quantity
        if( ajaxdata.qty== undefined || ajaxdata.qty=='' || ajaxdata.qty == 0){
            $('body').trigger('evotx_ticket_msg',[EVOROW,'bad', 't5' ]);
            return false;
        }

        $.ajax({
            beforeSend: function(){ 
                EVOROW.addClass( 'evoloading');
            },                  
            url:    evotx_object.ajaxurl,
            data:   ajaxdata,  dataType:'json', type:  'POST',
            success:function(data){

                if( data.status == 'good'){                    

                    $('body').trigger('evotx_added_to_cart',[ data, SECTION]);
                    $('body').trigger('evotx_ticket_msg',[EVOROW,'good']);

                    // if need to be redirected to cart after adding
                        if(evotx_object.redirect_to_cart == 'cart'){
                            window.location.href = evotx_object.cart_url;
                        }else if( evotx_object.redirect_to_cart =='checkout'){
                            window.location.href = evotx_object.checkout_url;
                        }else{
                            $('body').trigger('evo_update_wc_cart');
                        }  
                }else{ 
                    $('body').trigger('evotx_ticket_msg', [EVOROW,'bad', data.msg]);
                }     

            },complete:function(){ 
                EVOROW.removeClass( 'evoloading');
            }
        });
    });

// repopulate evotx_data data values
    $('body').on('evotx_repopulate_evotx_data', function(event, EVOROW, data){
        evotx_DATA = $(EVOROW).find('.evotx_data');
        $.each(data, function(index, value){
            evotx_DATA.data( index, value);
        });
    });

// Show add to cart notification messages
    $('body').on('evotx_ticket_msg', function(event, EVOROW, STATUS, bad_msg){
        MSG = $(EVOROW).find('.evotx_addtocart_msg');

        if( !MSG) return false; // if add to cart message section is not present

        evotx_DATA = $(EVOROW).find('.evotx_data').data('t');

       
        if(evotx_DATA === undefined) return false;
        
        HTML = '';
        if( STATUS == 'good'){
            target = evotx_object.tBlank? 'target="_blank"':'';
            HTML += "<p class='evotx_success_msg'><b>" + evotx_DATA.t1+"!</b>";
            HTML +="<span><a class='evcal_btn' href='"+evotx_object.cart_url+"' "+target+">"+evotx_DATA.t2 +"</a><em>|</em><a class='evcal_btn' href='"+evotx_object.checkout_url+"' "+target+">"+evotx_DATA.t3+"</a></span></p>";

        }else{
            bad_msg = bad_msg? 
                (bad_msg in evotx_DATA? evotx_DATA[bad_msg] : bad_msg)
                : evotx_DATA.t4;
            HTML += "<p class='evotx_success_msg bad'><b>" +bad_msg+"!</b>";
        }

        // hiding message afterwards
        MSG_int = $(EVOROW).find('.evotx_data').data('msg_interaction');
        //console.log(MSG_int);
        if( MSG_int.hide_after == true ){
            setTimeout(function(){
                $('body').trigger('evotx_ticket_msg_hide', [EVOROW]);
            }, 3000);
        }

        // redirecting
        if(MSG_int.redirect != 'nonemore'){
            $(EVOROW).find('.evotx_hidable_section').hide(); // hide only the hidable section
        }

        MSG.html(HTML).show();
    });
    $('body').on('evotx_ticket_msg_hide',function(event, EVOROW){
        $(EVOROW).find('.evotx_addtocart_msg').hide();
    });
    
// click add to cart for variable product
// OLD Method
    $('body').on('click','.evoAddToCart', function(e){

        e.preventDefault();
        thisButton = $(this);

        // loading animation
        thisButton.closest('.evoTX_wc').addClass('evoloading');

        // Initial
            TICKET_ROW = thisButton.closest('.evo_metarow_tix');
            PURCHASESEC = TICKET_ROW.find('.evoTX_wc');

        // set cart item additional data
            var ticket_row = thisButton.closest('.evo_metarow_tix');
            var event_id = ticket_row.attr('data-event_id');
            var ri = ticket_row.attr('data-ri');
            var lang = thisButton.data('l');
            var event_location = thisButton.closest('.evcal_eventcard').find('.evo_location_name').html();
           
            event_location = (event_location !== undefined && event_location != '' )? 
                encodeURIComponent(event_location):'';

            // passing location values
               location_str = event_location!= ''? '&eloc='+event_location: '';

            // pass lang
               lang_str = ( lang !== undefined)? '&lang='+lang:'';

            //console.log(event_location);
            
            // variable item
                if(thisButton.hasClass('variable_add_to_cart_button')){

                    var variation_form = thisButton.closest('form.variations_form'),
                        variations_table = variation_form.find('table.variations'),
                        singleVariation = variation_form.find('.single_variation p.stock');

                        // Stop processing is out of stock
                        if(singleVariation.hasClass('out-of-stock')){
                            return;
                        }

                    var product_id = parseInt(variation_form.attr('data-product_id'));
                    var variation_id = parseInt(variation_form.find('input[name=variation_id]').val());
                    var quantity = parseInt(variation_form.find('input[name=quantity]').val());

                    quantity = (quantity=== undefined || quantity == '' || isNaN(quantity)) ? 1: quantity;

                    values = variation_form.serialize();

                    var attributes ='';
                    variations_table.find('select').each(function(index){
                        attributes += '&'+ $(this).attr('name') +'='+ $(this).val();
                    });

                    // get data from the add to cart form
                    dataform = thisButton.closest('.variations_form').serializeArray();
                    var data_arg = dataform;

                    $.ajax({
                        type: 'POST',data: data_arg,
                        url: '?add-to-cart='+product_id+'&variation_id='+variation_id+attributes+'&quantity='+quantity +'&ri='+ri+'&eid='+event_id + location_str + lang_str,
                        beforeSend: function(){
                            $('body').trigger('adding_to_cart');
                        },
                        success: function(response, textStatus, jqXHR){

                            // Show success message
                            $('body').trigger('evotx_ticket_msg',[TICKET_ROW,'good']);

                        }, complete: function(){
                            thisButton.closest('.evoTX_wc').removeClass('evoloading');

                            // if need to be redirected to cart after adding
                            if(evotx_object.redirect_to_cart == 'cart'){
                                window.location.href = evotx_object.cart_url;
                            }else if( evotx_object.redirect_to_cart =='checkout'){
                                window.location.href = evotx_object.checkout_url;
                            }else{
                                update_wc_cart();
                            }                        
                        }
                    }); 
                }

            // simple item
                if(thisButton.hasClass('single_add_to_cart_button')){
                    // /console.log('66');
                    
                    TICKET_section = thisButton.closest('.evoTX_wc');
                    QTY_field = TICKET_section.find('input[name=quantity]');
                    
                    var sold_individually = TICKET_section.data('si');
                    var qty = (sold_individually=='yes')? 1: QTY_field.val();
                    var product_id = thisButton.attr('data-product_id');
                    MAX_qty = QTY_field.attr('max');

                    //console.log(MAX_qty+' '+qty);

                    // check if max quantity is not exceeded
                    if( MAX_qty != '' && parseInt(MAX_qty) < qty){
                        $('body').trigger('evotx_ticket_msg',[TICKET_ROW,'bad']);
                        thisButton.closest('.evoTX_wc').removeClass('evoloading');
                    }else{

                        // get data from the add to cart form
                        dataform = thisButton.closest('.tx_orderonline_single').serializeArray();
                        var data_arg = dataform;

                        $.ajax({
                            type: 'POST',
                            data: data_arg,
                            url: '?add-to-cart='+product_id+'&quantity='+qty +'&ri='+ri+'&eid='+event_id + location_str + lang_str,
                            beforeSend: function(){
                                //$('body').trigger('adding_to_cart');
                            },
                            success: function(response, textStatus, jqXHR){

                                // Show success message
                                $('body').trigger('evotx_ticket_msg',[TICKET_ROW,'good']);

                            }, complete: function(){
                                thisButton.closest('.evoTX_wc').removeClass('evoloading');

                                // reduce remaining qty
                                /*
                                    var remainingEL = thisButton.closest('.evcal_evdata_cell').find('.evotx_remaining');
                                    var remaining_count = parseInt(remainingEL.attr('data-count'));
                                    
                                    //console.log(remaining_count);
                                    if(remaining_count){
                                    	var new_count = remaining_count-qty;
                                        new_count = new_count<0? 0: new_count;
                                       
                                        // update
                                            remainingEL.attr({'data-count':new_count}).find('span span').html(new_count);
                                           	// change input field max value
                                           		thisButton.siblings('.quantity').find('input.qty').attr('max',new_count);

                                            // hide if no tickets left
                                            if(new_count==0)    $(this).fadeOut();
                                    }
                                */
                               
                                // if need to be redirected to cart after adding
                                    if(evotx_object.redirect_to_cart == 'cart'){
                                        window.location.href = evotx_object.cart_url;
                                    }else if( evotx_object.redirect_to_cart =='checkout'){                                    
                                        window.location.href = evotx_object.checkout_url;
                                    }else{
                                        update_wc_cart();
                                    } 
                            }   
                        });
                         
                    }
                }
        
        return false;
    });

// Update mini cart content
    $('body').on('evo_update_wc_cart',function(){
        update_wc_cart();
    });
    function update_wc_cart(){
        var data = {
            action: 'evoTX_ajax_09'
        };
        $.ajax({
            type:'POST',url:evotx_object.ajaxurl,
            data:data,
            dataType:'json',
            success:function(data){
                
                if (!data) return;

                var this_page = window.location.toString();
                this_page = this_page.replace( 'add-to-cart', 'added-to-cart' );

                var fragments = data.fragments;
                var cart_hash = data.cart_hash;

                // Block fragments class
                fragments && $.each(fragments, function (key, value) {
                    $(key).addClass('updating');
                });
                 
                // Block fragments class
                    if ( fragments ) {
                        $.each( fragments, function( key ) {
                            $( key ).addClass( 'updating' );
                        });
                    }   

                // Block widgets and fragments
                    $( '.shop_table.cart, .updating, .cart_totals' )
                        .fadeTo( '400', '0.6' )
                        .block({
                            message: null,
                            overlayCSS: {
                                opacity: 0.6
                            }
                    });           
                 
                // Replace fragments
                    if ( fragments ) {
                        $.each( fragments, function( key, value ) {
                            $( key ).replaceWith( value );
                        });

                        $( document.body ).trigger( 'wc_fragments_loaded' );            
                    }
                 
                // Unblock
                $( '.widget_shopping_cart, .updating' ).stop( true ).css( 'opacity', '1' ).unblock();
                 
                // Cart page elements
                $( '.shop_table.cart' ).load( this_page + ' .shop_table.cart:eq(0) > *', function() {

                    $( '.shop_table.cart' ).stop( true ).css( 'opacity', '1' ).unblock();

                    $( document.body ).trigger( 'cart_page_refreshed' );
                });

                $( '.cart_totals' ).load( this_page + ' .cart_totals:eq(0) > *', function() {
                    $( '.cart_totals' ).stop( true ).css( 'opacity', '1' ).unblock();
                });
                 
                // Trigger event so themes can refresh other areas
                $( document.body ).trigger( 'added_to_cart', [ fragments, cart_hash ] );
            }
        });
    }

// inquiry submissions
    $('body').on('click','.evotx_INQ_btn', function(){
        $(this).siblings('.evotxINQ_box').slideToggle();
    });
    $('body').on('click','.evotx_INQ_submit', function(event){
        event.preventDefault;
        var form = $(this).closest('.evotxINQ_form');
        var notif = form.find('.notif');

        //reset 
        	form.find('.evotxinq_field').removeClass('error');

        //reset notification
        notif.html( notif.attr('data-notif') );

        var data = {
            action: 'evoTX_ajax_06',
            event_id: form.attr('data-event_id'),
            ri: form.attr('data-ri'),
        };

        error = 'none';
        form.find('.evotxinq_field').each(function(index){
            if( $(this).val()==''){
            	error='yes';
            	$(this).addClass('error');
            } 
            data[$(this).attr('name')] = $(this).val();
        });

        // validate captcha
        var human = validate_human( form.find('input.captcha') );
		if(!human){
			form.find('input.captcha').addClass('error');
			error=3;
		}

        if(error=='none'){
            $.ajax({
                type:'POST',url:evotx_object.ajaxurl,
                data:data,
                beforeSend: function(){
                    form.addClass('loading');
                },success:function(data){
                    form.slideUp();
                    form.siblings('.evotxINQ_msg').fadeIn(function(){
                        form.removeClass('loading');
                    });
                }
            });
        }else{
            notif.html( form.attr('data-err') );
        }
    });
	// validate humans
		function validate_human(field){
			if(field==undefined){
				return true;
			}else{
				var numbers = ['11', '3', '6', '3', '8'];
				if(numbers[field.attr('data-cal')] == field.val() ){
					return true;
				}else{ return false;}
			}				
		}

// add to cart button from eventtop
     $('body').on('click','.evotx_add_to_cart em', function(){   });

    // hover over guests list icons
        $('body').on('mouseover','.evotx_whos_coming span', function(){
            name = $(this).attr('data-name');
            html = $(this).html();
            $(this).html(name).attr('data-intials', html).addClass('hover');
        });
        $('body').on('mouseout','.evotx_whos_coming span', function(){
            $(this).html( $(this).attr('data-intials')).removeClass('hover');
        });
	
// ActionUser event manager
    // show ticket stats for events
        $('#evoau_event_manager').on('click','a.load_tix_stats',function(event){
            event.preventDefault();
            MANAGER = $(this).closest('.evoau_manager');
            var data_arg = {
                action: 'evotx_ajax_get_auem_stats',
                eid: $(this).data('eid')
            };
            $.ajax({
                beforeSend: function(){
                    MANAGER.find('.eventon_actionuser_eventslist').addClass('evoloading');
                },
                type: 'POST',
                url:evotx_object.ajaxurl,
                data: data_arg,
                dataType:'json',
                success:function(data){
                    $('body').trigger('evoau_show_eventdata',[MANAGER, data.html, true]);
                },complete:function(){ 
                    MANAGER.find('.eventon_actionuser_eventslist').removeClass('evoloading');
                }
            });
        });

    // check in attendees
        $('body').on('click','.evotx_status', function(){
            var obj = $(this);
            if(obj.hasClass('refunded')) return false;
            if( obj.data('gc')== false) return false;
           
            var data_arg = {
                action: 'the_ajax_evotx_a5',
                tid: obj.data('tid'),
                tiid: obj.data('tiid'),
                status: obj.data('status'),
            };
            $.ajax({
                beforeSend: function(){    obj.html( obj.html()+'...' );  },
                type: 'POST',
                url:evotx_object.ajaxurl,
                data: data_arg,
                dataType:'json',
                success:function(data){
                    obj.data('status', data.new_status)
                    obj.html(data.new_status_lang).removeAttr('class').addClass('evotx_status '+ data.new_status);

                }
            });
        });
    // open incompleted orders
        $('.evoau_manager_event_content').on('click','span.evotx_incomplete_orders',function(){
            $(this).closest('table').find('td.hidden').toggleClass('bad');
        });
});