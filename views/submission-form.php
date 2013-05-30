<?php 
global $post;
$weightEntryAllowed = true;

$args = array(
	'post_type' => 'current-weight',			
	'meta_key' => 'current_weight',
	'post_status' => 'publish',
	'orderby' => 'post_date',
	'order' => 'DESC',
	'showposts' => 1000			
);		

// Add a filter to do complex 'where' clauses...
add_filter( 'posts_where', 'wlpt_userWeightsWhereFilter' );

$query = new WP_Query( $args );

// Take the filter away again so this doesn't apply to all queries.
remove_filter( 'posts_where', 'wlpt_userWeightsWhereFilter' );

$userweights = $query->posts;

if($userweights) :
	foreach ($userweights as $userweight) {					
		if (date("Y-m-d", strtotime($userweight->post_date)) == date("Y-m-d")) {
			$weightEntryAllowed = false;
		}							
	}	
else :
	$weightEntryAllowed = true;
endif;
?>

<div id="simple-gift-postbox" class="<?php if(is_user_logged_in()) echo 'closed'; else echo 'loggedout'?>">
	
<?php do_action( 'gift-notice' ); ?>
<div class="simple-gift-inputarea">
	<?php if(is_user_logged_in()) { ?>
        <form id="gift-new-post" name="new_post" method="post" action="<?php the_permalink(); ?>">	
            <?php if($weightEntryAllowed) { ?>
                <p><label>Weight: </label><input type="text" id="post-currentweight" name="post-currentweight" /> <input type="text" id="post-date" name="post-date" value="<?php echo date("Y-m-d") ?>" /></p>	
                <input id="submit" type="submit" tabindex="3" value="<?php esc_attr_e( 'Post', 'simple-gift' ); ?>" />					
            <?php } else { ?>		
                <h5>You've already recorded your weight for this day.</h5>
            <?php } ?>
            <input type="hidden" name="action" value="post" />
            <input type="hidden" name="empty-description" id="empty-description" value="1"/>
            <?php wp_nonce_field( 'new-post' ); ?>
        </form>
	<?php } else { ?>		
		<h4>You must be logged in to record your weight.</h4>
<?php } ?>
</div>
	  
</div> <!-- #simple-gift-postbox -->
<?php
	// Output the content.
	$output = ob_get_contents();
	//ob_end_clean();
 
	// return only if we're inside a page. This won't list anything on a post or archive page. 
	if (is_page()) return  $output;
 
// Add the shortcode to WordPress. 
//add_shortcode('simple-gift', 'simple_gift');				

function wlpt_errors(){
	?>
	<style>
	
	</style>
	<?php
		global $error_array;
		foreach($error_array as $error){
			echo '<p class="simple-gift-error">' . $error . '</p>';
		}
	}
	 
	function wlpt_notices(){
	?>
	<style>
	
	</style>
	<?php
	 
	global $notice_array;
	foreach($notice_array as $notice){
		echo '<p class="gift-notice">' . $notice . '</p>';
	}
}
?>