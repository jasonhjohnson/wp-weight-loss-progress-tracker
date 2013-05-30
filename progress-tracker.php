<?php
/*
Plugin Name: Weight Loss Progress Tracker  
Description: A plugin that records and displays a user's weight loss progress
Version: 1.0
Author: Jason Johnson
Author URI: http://infinity-software.com
*/   

define('wlpt_PROGRESS_TRACKER_DIR', plugin_dir_path(__FILE__));
define('wlpt_PROGRESS_TRACKER_URL', plugin_dir_url(__FILE__));

$wlpt_version = '20130104';

// includes
require_once ('library/json-weight-data-for-google-consumption.php');	
if (is_admin()) require_once( 'admin/admin.php' );		

// enqueue script and style
add_action ('init', 'wlpt_enqueueResources');
function wlpt_enqueueResources() {	
	$current_url = trailingslashit('http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);	
	if (!is_admin()) {
		// style		
		wp_enqueue_style ('wlpt_style', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/gift-tracker.css', false, $wlpt_version, 'all');
				
		// script		
		wp_enqueue_script ('wlpt_script', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/resources/gift-tracker.js', array('jquery'), $wlpt_version);		
	}
}
	
// weight capture view
add_shortcode('acquire_current_weight', 'wlpt_acquireCurrentWeight');	
function wlpt_acquireCurrentWeight() {	
	ob_start();
	include (WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/views/submission-form.php');
	return ob_get_clean();
}	
	
// add new weights
add_action('init','wlpt_addCurrentWeight');	 
function wlpt_addCurrentWeight(){
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'post' ){
		if ( !is_user_logged_in() )
			return;
		global $current_user;
 
		$user_id			 = $current_user->ID;			
		$post_currentweight  = $_POST['post-currentweight'];
		$post_title     	 = $current_user->display_name . " weighed in at " . $post_currentweight;	
		$post_date			 = date($_POST['post-date']);
		$post_content	     = "";
		$tags			     = "";		
 
		global $error_array;
		$error_array = array();
 
		if (empty($post_currentweight)) $error_array[]='Your current weight is required.';	
 
		if (count($error_array) == 0) { 
			$post_id = wp_insert_post( array(
				'post_author'	=> $user_id,
				'post_title'	=> $post_title,
				'post_type'     => 'current-weight',
				'post_content'	=> $post_content,
				'post_date' 	=> $post_date,
				'tags_input'	=> $tags,
				'post_status'	=> 'publish'
				) );			
				
			update_post_meta($post_id, 'current_weight', $post_currentweight);
 
			global $notice_array;
			$notice_array = array();
			$notice_array[] = "Thank you. Today's weight has been saved. ";
			add_action('gift-notice', 'wlpt_notices');
		} 
		else {
			add_action('gift-notice', 'wlpt_errors');
		}
	}
}	

// progress display view
add_shortcode('display_user_progress', 'wlpt_displayUserProgress');	
function wlpt_displayUserProgress() {	
	ob_start();
	include (WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/views/progress-display.php');
	return ob_get_clean();
}	

function wlpt_userWeightsWhereFilter( $where = '' ) {
	// ...where dates are blank
	$where .= " AND wp_posts.post_author = ". wp_get_current_user()->ID;
	return $where;
}


?>