<?php
// get user weigh in data as JSON
add_action('wp_ajax_wlpt_getUserWeighInData', 'wlpt_getUserWeighInData');	
add_action('wp_ajax_nopriv_wlpt_getUserWeighInData', 'wlpt_getUserWeighInData');
function wlpt_getUserWeighInData() {	
	global $post;
			
	$args = array(
		'post_type' => 'current-weight',			
		'meta_key' => 'current_weight',
		'post_status' => 'publish',
		'orderby' => 'post_date',
		'order' => 'ASC',
		'showposts' => 1000			
	);		
 
	// Add a filter to do complex 'where' clauses...
	add_filter( 'posts_where', 'wlpt_userWeightsWhereFilter' );
	
	$query = new WP_Query( $args );
	
	// Take the filter away again so this doesn't apply to all queries.
	remove_filter( 'posts_where', 'wlpt_userWeightsWhereFilter' );
 
	$userweights = $query->posts;	
	
	$table = array();
	$table['cols'] = array(
		/* define your DataTable columns here
		 * each column gets its own array
		 * syntax of the arrays is:
		 * label => column label
		 * type => data type of column (string, number, date, datetime, boolean)
		 */
		// I assumed your first column is a "string" type
		// and your second column is a "number" type
		// but you can change them if they are not
		array('label' => 'post_date', 'type' => 'string'),
		array('label' => 'Your Weight', 'type' => 'number')
	);
	
	$rows = array();
	foreach ($userweights as $userweight) {	
		$temp = array();
		// each column needs to have data inserted via the $temp array
		$temp[] = array('v' => date('M d', strtotime($userweight->post_date)));
		$temp[] = array('v' => (float) $userweight->current_weight); // typecast all numbers to the appropriate type (int or float) as needed - otherwise they are input as strings
		
		// insert the temp array into $rows
		$rows[] = array('c' => $temp);
	}
	
	// populate the table with rows of data
	$table['rows'] = $rows;
	
	// encode the table as JSON
	$jsonTable = json_encode($table);
	
	// set up header; first two prevent IE from caching queries
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	
	// return the JSON data
	ob_end_clean();	      
	echo $jsonTable;
	die();
}
?>