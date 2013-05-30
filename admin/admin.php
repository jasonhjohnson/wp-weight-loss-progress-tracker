<?php 
// register the post type in the admin area	
add_action('init', 'wlpt_register_user_weights');	 
function wlpt_register_user_weights() {
	$labels = array(
		'name' => _x( 'User Weights', 'post type general name' ),
		'singular_name' => _x( 'User Weight', 'post type singular name' ),
		'add_new' => _x( 'Add New', 'User Weight' ),
		'add_new_item' => __( 'Record User Weight' ),
		'edit_item' => __( 'Edit User Weight' ),
		'new_item' => __( 'New User Weight' ),
		'view_item' => __( 'View User Weight' ),
		'search_items' => __( 'Search User Weights' ),
		'not_found' =>  __( 'No User Weights found' ),
		'not_found_in_trash' => __( 'No User Weights found in Trash' ),
		'parent_item_colon' => ''
	);
 
	$args = array(
		'labels' => $labels,
		'singular_label' => __('User Weight', 'user-weights'),
		'public' => true,
		'capability_type' => 'post',
		'rewrite' => false,
		'supports' => array('title')
		//'supports' => array('title', 'editor'),
	);
	
	register_post_type('current-weight', $args);
}

// add the gift metabox in the admin area (add the ability to manage the current weight value)
add_action('add_meta_boxes', 'wlpt_add_metabox');
function wlpt_add_metabox() {
	add_meta_box( 'wlpt_metabox_id', 'Current Weight', 'wlpt_metabox', 'current-weight', 'normal', 'high' );
}

function wlpt_metabox( $post ) {
	$values = get_post_custom( $post->ID );
	$current_weight = isset( $values['current_weight'] ) ? esc_attr( $values['current_weight'][0] ) : '';	
	wp_nonce_field( 'wlpt_metabox_nonce', 'metabox_nonce' );
	?>
	<p>
		<label for="current_weight">Current Weight</label>
		<input type="text" name="current_weight" id="current_weight" value="<?php echo $current_weight; ?>" />
	</p>		
	<?php
}

// the save metabox procedure
add_action('save_post', 'wlpt_metabox_save');  
function wlpt_metabox_save( $post_id ) {
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
 
	if( !isset( $_POST['metabox_nonce'] ) || !wp_verify_nonce( $_POST['metabox_nonce'], 'wlpt_metabox_nonce' ) )
		return $post_id;
 
	if( !current_user_can( 'edit_post' ) )
		return $post_id;
 
	// Make sure data is set
	if( isset( $_POST['current_weight'] ) ) {
 
		$valid = 0;
		$old_value = get_post_meta($post_id, 'current_weight', true);
 
		if ( $_POST['current_weight'] != '' ) {				
			$valid = 1;
		}
 
		if ($valid)
			update_post_meta( $post_id, 'current_weight', $_POST['current_weight'] );
		elseif (!$valid && $old_value)
			update_post_meta( $post_id, 'current_weight', $old_value );
		else
			update_post_meta( $post_id, 'current_weight', '');
	}		
}

add_filter( 'manage_edit-current-weight_columns', 'my_currentWeightColumns' ) ;
function my_currentWeightColumns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Status' ),
		'current_weight' => __( 'Weighed In At' ),	
		'date' => __( 'Date' )
	);

	return $columns;
}

add_action( 'manage_current-weight_posts_custom_column', 'my_manageCurrentWeightColumns', 10, 2 );
function my_manageCurrentWeightColumns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		/* If displaying the 'current_weight' column. */
		case 'current_weight' :

			/* Get the post meta. */
			$weight = get_post_meta( $post_id, 'current_weight', true );

			/* If no data is found, output a default message. */
			if ( empty( $weight ) )
				echo __( 'Missing' );			
			else
				echo $weight;

			break;

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-current-weight_sortable_columns', 'my_currentWeightSortableColumns' );
function my_currentWeightSortableColumns( $columns ) {
	$columns['current_weight'] = 'current_weight';
	return $columns;
}

// style action for the post new page and post edit pages
add_action('admin_print_styles-post-new.php', 'posttype_admin_css'); 
add_action('admin_print_styles-post.php', 'posttype_admin_css'); 
function posttype_admin_css() {
	global $post_type;
	if($post_type == 'current-weight') {
		// hide the permalink display and the view button as we don't need it for user weights
		echo '<style type="text/css">#edit-slug-box,#view-post-btn,#post-preview,.updated p a{display: none;}</style>';
	}
}
