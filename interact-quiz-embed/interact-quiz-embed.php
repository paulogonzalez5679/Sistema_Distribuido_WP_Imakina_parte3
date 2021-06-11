<?php
/*
Plugin Name: Interact Quiz Embed
Plugin URI: https://www.tryinteract.com
Description: Use this plugin to embed your Interact quiz into your Wordpress site.
Author: The Quiz Collective Inc.
Version: 3.0.7
Author URI: https://www.tryinteract.com

Copyright 2018 The Quiz Collective  (email: help@tryinteract.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
*
* Short Code Hook Legacy
*
*/

function interact_quiz_embed($atts) {
	shortcode_atts(array('user'=>'', 'id'=>'', 'w'=>'600', 'h'=>'500'), $atts);

	if(isset($atts['w']))
		$width = $atts['w'];
	else
		$width = '600';
	if(isset($atts['h']))
		$height = $atts['h'];
	else
		$height = '500';

	if (isset($atts['user'])) {
		$username = $atts['user'];
		$id = $atts['id'];
		return '
			<link rel="stylesheet" type="text/css" href="https://www.tryinteract.com/css/interact.css">
			<iframe src="https://quiz.tryinteract.com/#/'.$username.'/'.$id.'" class="interact-embed" width="'.$width.'" height="'.$height.'" frameborder="0"></iframe>
		';
	} else {
		$app_id = $atts['id'];
		return '<iframe src="https://quiz.tryinteract.com/#/'.$app_id.'" class="interact-embed" width="'.$width.'" height="'.$height.'" frameborder="0" style="margin:0;max-width:100%;"></iframe>';
	}
}

add_shortcode('interact-quiz','interact_quiz_embed');


/*
*
* Short Code Hook 
*
*/

function interact_embed($atts) {
	shortcode_atts(array('id'=>'', 'type'=>'quiz', 'w'=>'800', 'h'=>'800', 'no_cover'=>'false'), $atts);

	wp_enqueue_script('interact-embed-script');

	if(isset($atts['w'])) { $width = $atts['w']; } else { $width = '800'; }
 	if(isset($atts['h'])) { $height = $atts['h']; } else { $height = '800'; }
 	if(isset($atts['type'])) { $type = $atts['type']; } else { $type = 'quiz'; }
 	if(isset($atts['no_cover'])) { $no_cover = 'true'; } else { $no_cover = 'false'; }
 	if(isset($atts['mobile'])) { $mobile = $atts['mobile']; } else { $mobile = 'true'; }
 	if(isset($atts['align'])) { $align = $atts['align']; } else { $align = null; }
 	if(isset($atts['redirect'])) { $redirect = $atts['redirect']; } else { $redirect = 'false'; }

	$app_id = $atts['id'];
	$ref = $app_id . md5($app_id . rand());

	if($align) {
		$align = 'style="text-align:' . $align . ';"';
	}

	$container = '<div id="interact-' . $ref . '"' . $align . '></div>';

	return '
		' . $container . '
		<script type="text/javascript">
			(function(){				

				window.addEventListener("load", function(){
					var app_id = "' . $app_id . '";
					var ref = "' . $ref . '";
					var w = "' . $width . '";
					var h = "' . $height . '";
					var host = "' . $type . '.tryinteract.com";
					var no_cover = ' . $no_cover . ';
					var mobile = ' . $mobile . ';
					var redirect = "' . $redirect . '";

					var params = { "ref":ref, "appId": app_id, "width":w, "height":h, "async":true, "host":host, "auto_resize":true, "mobile":mobile, "no_cover":no_cover };

					if(redirect === "host") { 
						params.redirect_host = true;
					}

					window[ref] = new InteractApp(); 
					window[ref].initialize(params); 
					window[ref].display(); 
				});

			})(window);
		</script>
	';
}

add_shortcode('interact','interact_embed');

/*
*
* Promotion Script Hook inject into <head>
*
*/

function interact_scripts(){
	global $post;

	// promotion script
	if(get_option('interact_promotion_id') !== false) {
	  ?>
	  	<script type="text/javascript">
			  (function(i,n,t,e,r,a,c){i['InteractPromotionObject']=r;i[r]=i[r]||function(){
			  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=n.createElement(t),
			  c=n.getElementsByTagName(t)[0];a.async=1;a.src=e;c.parentNode.insertBefore(a,c)
			  })(window,document,'script','https://i.tryinteract.com/promotions/init.js','i_promo');
			  i_promo('init', '<?php echo get_option('interact_promotion_id') ?>'); 
			</script>
		<?php
	}
}

add_action( 'wp_head', 'interact_scripts' );



/*
*
* Register the Script
*
*/

function interact_enqueue_scripts(){
	wp_register_script('interact-embed-script', plugins_url('interact-embed.js', __FILE__), null, '1.0', true);
}

add_action( 'wp_enqueue_scripts', 'interact_enqueue_scripts' );  



/*
*
* Options Page for Plugin
*
*/

function interact_option_page(){

	if(isset($_POST['interact_promotion_id'])) {
		$updatedPromoId = false;
		$newId = $_POST['interact_promotion_id'];

		if($newId === '') {
			$updatedPromoId = true;
			delete_option('interact_promotion_id');
		} 
		
		if(strlen($newId) > 7) {
			$updatedPromoId = true;
			update_option('interact_promotion_id', $_POST['interact_promotion_id']);
		}
	}

	$id = get_option('interact_promotion_id');

	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h1>Interact Quiz Embed Plugin</h1>
		<h2>Embed your Quiz, Poll, or Giveaway with a Shortcode</h2>
		<p>This plugin generates a shortcode which embeds your Interact App into your WordPress content. <a href='https://en.support.wordpress.com/shortcodes/' target='_blank'>How do I use a shortcode?</a></p>

		<?php
		if(isset($_POST['app_url'])) {

			$app_url = $_POST['app_url'];
			$parts = explode('/', $app_url);

			$app_id = null;
			$app_type = null;

			if(count($parts) === 6) {
				if($parts[4] === 'quiz' || $parts[4] === 'poll' || $parts[4] === 'giveaway') {
					if($parts[5] && strlen($parts[5]) > 5) {
						$app_id = $parts[5];
						$app_type = $parts[4];
					}
				}
			}


			if($app_id && $app_type) {
				echo '<h4>Copy &amp; Paste your shortcode into your Post:';
				echo '<pre style="display:block;max-width:720px;background: #333;padding: 20px;border-radius: 4px;color: white;font-weight: 400;">';
				echo '[interact id="'. $app_id .'" type="'. $app_type .'"';
				
				if(isset($_POST['interact_size_w']) && !empty($_POST['interact_size_w'])){
					echo ' w="'.$_POST['interact_size_w'].'"';
				}
				if(isset($_POST['interact_size_h']) && !empty($_POST['interact_size_h'])){
					echo ' h="'.$_POST['interact_size_h'].'"';
				}
				
				if(isset($_POST['interact_disable_cover'])){
					echo ' no_cover="true"';
				}

				echo ']</pre></h4>';
			} else {
				echo '<h4 style="color: red;">Invalid App URL...</h4>';
			}
		}
		?>
		<form action="" method="post" id="interact-embed-form">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="app_id">Interact App URL</label></th>
					<td><input name="app_url" type="text" id="app_id" placeholder="https://www.tryinteract.com/share/app/ID" value="" class="regular-text" />
					<p>The URL above can be found in your dashboard under <br/><b>'Embed &amp; Share' &gt; 'Embed in your Website' &gt; 'WordPress'.</b></p>
					</td>
				</tr>
				<tr>
					<th scope="row">Embed Size</th>
					<td>
						<label for="interact_size_w">Width</label>
						<input name="interact_size_w" type="number" step="1" min="0" id="interact_size_w" value="" class="small-text" />
						<label for="interact_size_h">Height</label>
						<input name="interact_size_h" type="number" step="1" min="0" id="interact_size_h" value="" class="small-text" />
						<p class="description">Default size is 600x500px (optional)</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Cover Page</th>
					<td>
						<label for="interact_disable_cover">
							<input name="interact_disable_cover" id="interact_disable_cover" type="checkbox"/> Disable Cover Page
							<p class="description">Quiz will begin on the first question and skip the cover page (optional)</p>
						</label>
					</td>
				</tr>
			</table>

			<p><input type="submit" name="submit" value="Generate Shortcode" class="button button-primary"></p>
		</form>
		<br/>
		<br/>

		<hr/>
		<h2>Promote your Quiz with a Popup or Announcement Bar</h2>
		<form action="" method="post">
			<p>Enter your <b>Promotion ID</b> which can be found in your dashboard under 'Embed &amp; Share'</p>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="app_id">Promotion ID</label></th>
					<td>
						<input name="interact_promotion_id" type="text" id="interact_promotion_id" class="code" value="<?php if($id){ echo $id; } ?>" />
					</td>
				</tr>
			</table>

			<?php if($id !== false): ?>
				<p>Promotions are now <b>configured</b> and can be configured in your dashboard under 'Embed &amp; Share'.</p>
			<?php endif; ?>
			<?php if($updatedPromoId): ?><p><b>Success:</b> Promotion ID was updated...</p><?php endif;?>	
			<?php if($newId && !$updatedPromoId): ?><p><b>Warning:</b> Promotion ID was not updated...</p><?php endif;?>	
			<p><input type="submit" name="submit" value="<?php if($id === false): ?>Set<?php else: ?>Update<?php endif;?> Promotion ID" class="button button-primary"></p>
		</form>
	</div>

	<?php
}

function interact_plugin_menu(){
	add_options_page('Interact Embed Shortcode Generator','Interact','manage_options','interact_plugin','interact_option_page');
}

add_action('admin_menu','interact_plugin_menu');
