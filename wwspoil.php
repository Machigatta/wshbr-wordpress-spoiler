<?php
/*
Plugin Name: wshbr-wordpress-spoiler
Plugin URI: https://github.com/Machigatta/wshbr-wordpress-spoiler
Description: wshbr.de - Provides a spoiler-marker for the thumbnails
Author: Machigatta
Author URI: https://machigatta.com/
Version: 1.2
Stable Tag: 1.2
*/
function wwspoil_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'wwspoil', null, $plugin_dir.'/languages/' );
}
add_action('plugins_loaded', 'wwspoil_init');

function wwspoil_meta_custom() {
	add_meta_box('wwspoildiv', __('wshbr-spoiler','post-expirator'), 'wwspoil_meta_box', 'post', 'side', 'core'); //spoiler meta
}
add_action ('add_meta_boxes','wwspoil_meta_custom');


function wwspoil_meta_box($post) { 
	// Get default month
	wp_nonce_field( plugin_basename( __FILE__ ), 'wwspoil_nonce' );
	$isSpoiler = get_post_meta($post->ID,"isSpoiler",true);	
	?>
	<div>
		<div class="custom-input-radio">
			<input id="isYesSpoiler" name="isSpoiler" <?php echo ($isSpoiler == "1") ? "checked" : ""; ?> value="1" type="radio"/>
			<label for="isYesSpoiler">Yes</label>
		</div>
		<div class="custom-input-radio">
			<input id="isNoSpoiler" name="isSpoiler" <?php echo ($isSpoiler == "0" || $isSpoiler == "") ? "checked" : ""; ?> value="0" type="radio"/>
			<label for="isNoSpoiler">No</label>
		</div>
	</div>

	<?php
}
add_action( 'save_post', 'wwspoil_field_data' );

//activate in settings - TO:DO
function wwspoil_add_to_title($title) {
	$post_id = get_the_ID();
	$isSpoiler = get_post_meta($post_id,"isSpoiler",true);
	if(is_front_page() && $isSpoiler == "1"){
		$title = $title;
	}

	return $title;
}
add_action('the_title', 'wwspoil_add_to_title');

function wwspoil_add_to_the_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr) {
	$post_id = get_the_ID();
	$isSpoiler = get_post_meta($post_id,"isSpoiler",true);
	if(!is_single() && $isSpoiler == "1"){
		$spoiler_html = "<img class='spoiler-image' src='/wp-content/plugins/". basename(dirname(__FILE__))."/assets/img/spoiler.png'>";
		if(isset($attr["class"])){
			if(strpos($attr["class"],"no-spoiler-image") !== false ){
				$spoiler_html = "";
			}
		}
		$html = $html . $spoiler_html;
	}else{

	}
	return $html;
}
add_action('post_thumbnail_html', 'wwspoil_add_to_the_thumbnail',20,5);

function wwspoil_field_data($post_id) {
	    // check if this isn't an auto save
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
    // security check
    if ( !wp_verify_nonce( $_POST['wwspoil_nonce'], plugin_basename( __FILE__ ) ) )
        return;
	
    // now store data in custom fields based on checkboxes selected
    if ( isset( $_POST['isSpoiler'] )){
		if($_POST['isSpoiler'] == "1"){
			update_post_meta( $post_id, 'isSpoiler', "1" );
		}else{
			update_post_meta( $post_id, 'isSpoiler', "0" );
		}
	}
}

add_action('wp_enqueue_scripts', 'wwspoil_add_styles_scripts');
add_action( 'admin_enqueue_scripts', 'wwspoil_add_styles_scripts' );
function wwspoil_add_styles_scripts()
{
	$options = get_option('wwspoil_settings');
	wp_enqueue_style('wwspoil-font', 'https://fonts.googleapis.com/css?family=Open+Sans');
	wp_enqueue_style('wwspoil-style', trailingslashit(plugin_dir_url(__FILE__)) . 'assets/css/style.css', array(), "0.0.6");
}