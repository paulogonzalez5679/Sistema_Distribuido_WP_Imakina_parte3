/**
 * EventON elements
 * version: 2.9
 */
jQuery(document).ready(function($){

// yes no button afterstatement hook
	$('body').on('evo_yesno_changed', function(event, newval, obj, afterstatement){

		if(afterstatement === undefined) return;
		
		if(newval == 'yes'){
			obj.closest('.evo_elm_row').next().show();
		}else{
			obj.closest('.evo_elm_row').next().hide();
		}
	});
// self hosted tooltips
	$('body').find('.ajdethistooltip').each(function(){
		tipContent = $(this).find('.ajdeToolTip em').html();
		toolTip = $(this).find('.ajdeToolTip');
		classes = toolTip.attr('class').split('ajdeToolTip');
		toolTip.remove();
		$(this).append('<em>' +tipContent +'</em>').addClass(classes[1]);
	});
// Select in a row	 
	 $('.evo_row_select').on('click','span.opt',function(){

	 	var O = $(this);
	 	var P = O.closest('p');
	 	const multi = P.hasClass('multi')? true: false;
				
		if(multi){
			if(O.hasClass('select')){
				O.removeClass('select');
			}else{
				O.addClass('select');
			}

		}else{
			P.find('span.opt').removeClass('select');
			O.addClass('select');
		}

		var val = '';
		P.find('.opt').each(function(){
			if( $(this).hasClass('select')) val += $(this).attr('value')+',';
		});

		val = val.substring(0, val.length-1);

		P.find('input').val( val );		

		$('body').trigger('evo_row_select_selected',[P, $(this).attr('value'), val]);			
	});

// Color picker
	setup_colorpicker();
	$('body').on('evo_page_run_colorpicker_setup',function(){
		setup_colorpicker();
	});
	function setup_colorpicker(){
		$('body').find('.evo_elm_color').each(function(){
			var elm = $(this);

			elm.ColorPicker({
				onBeforeShow: function(){
					$(this).ColorPickerSetColor( '#888888');
				},
				onChange:function(hsb, hex, rgb,el){
					elm.css({'background-color':'#'+hex});		
					elm.siblings('.evo_elm_hex').val( hex );
				},onSubmit: function(hsb, hex, rgb, el) {
					elm.css({'background-color':'#'+hex});		
					elm.siblings('.evo_elm_hex').val( hex );
					$(el).ColorPickerHide();

					var _rgb = get_rgb_min_value(rgb, 'rgb');
					elm.siblings('.evo_elm_rgb').val( _rgb );
				}
			});
		});
	}

	function get_rgb_min_value(color,type){
			
		if( type === 'hex' ) {			
			var rgba = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);	
			var rgb = new Array();
			 rgb['r']= parseInt(rgba[1], 16);			
			 rgb['g']= parseInt(rgba[2], 16);			
			 rgb['b']= parseInt(rgba[3], 16);	
		}else{
			var rgb = color;
		}
		
		return parseInt((rgb['r'] + rgb['g'] + rgb['b'])/3);			
	}

	
	

// plus minus changer
	$('body').on('click','.evo_plusminus_change', function(event){

        OBJ = $(this);

        QTY = parseInt(OBJ.siblings('input').val());
        MAX = OBJ.siblings('input').data('max');        
        if(!MAX) MAX = OBJ.siblings('input').attr('max');           

        NEWQTY = (OBJ.hasClass('plu'))?  QTY+1: QTY-1;

        NEWQTY =(NEWQTY <= 0)? 0: NEWQTY;

        // can not go below 1
        if( NEWQTY == 0 && OBJ.hasClass('min') ){    return;    }

        NEWQTY = (MAX!='' && NEWQTY > MAX)? MAX: NEWQTY;

        OBJ.siblings('input').val(NEWQTY);

        if( QTY != NEWQTY) $('body').trigger('evo_plusminus_changed',[NEWQTY, MAX, OBJ]);
       
        if(NEWQTY == MAX){
            PLU = OBJ.parent().find('b.plu');
            if(!PLU.hasClass('reached')) PLU.addClass('reached');   

            if(QTY == MAX)   $('body').trigger('evo_plusminus_max_reached',[NEWQTY, MAX, OBJ]);                 
        }else{            
            OBJ.parent().find('b.plu').removeClass('reached');
        } 
    });

// date time picker
	var RTL = $('body').hasClass('rtl');

	// load date picker libs
	_evo_elm_load_datepickers();
	$('body').on('evo_elm_load_datepickers',function(){
		_evo_elm_load_datepickers();
	});
	$('body').on('click','.evo_dpicker',function(){	
		_evo_elm_load_datepickers( true);
	});

	function _evo_elm_load_datepickers( call = false){
		$('body').find('.evo_dpicker').each(function(){

			var OBJ = $(this);
			if( OBJ.hasClass('dp_loaded')) return;

			var rand_id = OBJ.closest('.evo_date_time_select').data('id');			
			var D = $('body').find('.evo_dp_data').data('d');

			OBJ.addClass('dp_loaded');

			OBJ.datepicker({
				dateFormat: D.js_date_format,
				firstDay: D.sow,
				numberOfMonths: 1,
				altField: OBJ.siblings('input.alt_date'),
				altFormat: 'yy/mm/dd',
				isRTL: RTL,
				onSelect: function( selectedDate , ooo) {

					//var date = new Date(ooo.selectedYear, ooo.selectedMonth, ooo.selectedDay);
					var date = OBJ.datepicker('getDate');

					$('body').trigger('evo_elm_datepicker_onselect', [OBJ, selectedDate, date, rand_id]);

					if( OBJ.hasClass('start') ){
						// update end time
						var eO = $('body').find('.evo_date_time_select.end[data-id="'+rand_id+'"]').find('input.datepickerenddate');
						if(eO.length>0){

							
							eO.datepicker( 'setDate', date);
							eO.datepicker( "option", "minDate", date );
						}
					}
				}
			});
			if( call) OBJ.datepicker('show');
		});
	}

	
// time picker
	$('body').on('change','.evo_timeselect_only',function(){
		var P = $(this).closest('.evo_time_edit');
		var min = 0;

		min += parseInt(P.find('._hour').val() ) *60;
		min += parseInt(P.find('._minute').val() );

		P.find('input').val( min );
	});

// lightbox select
	$('body').on('click','.evo_elm_lb_field input',function(event){
		const lb = $(this).closest('.evo_elm_lb_select');
		$('body').find('.evo_elm_lb_window.show').removeClass('show').fadeOut(300);
		lb.find('.evo_elm_lb_window').show().delay(100).queue(function(){
		    $(this).addClass("show").dequeue();
		});
	});

	// close lightbox
		$(window).on('click', function(event) {
			if( !($(event.target).hasClass('evo_elm_lb_field_input')) )
				$('body').find('.evo_elm_lb_window').removeClass('show').fadeOut(300);
		});
		$(window).blur(function(){
			//$('body').find('.evo_elm_lb_window').removeClass('show').fadeOut(250);
		});

	// selecting options in lightbox select field
	$('body')
		.on('click','.eelb_in span',function(){
			const field = $(this).closest('.evo_elm_lb_select').find('input');
			if($(this).hasClass('select')){
				$(this).removeClass('select');
			}else{
				$(this).addClass('select');
			}

			var V = '';

			$(this).parent().find('span.select').each(function(){
				V += $(this).attr('value')+',';
			});

			field.val( V ).trigger('change');
			$('body').trigger('evo_elm_lb_option_selected',[ $(this), V]);
		})
		.on('click','.evo_elm_lb_window',function(event){
			event.stopPropagation();
		})
	;
});