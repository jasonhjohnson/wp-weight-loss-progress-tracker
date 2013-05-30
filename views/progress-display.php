<!--Load the AJAX API-->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>   
<script type="text/javascript">
// Load the Visualization API and the piechart package.
google.load('visualization', '1', {'packages':['corechart']});
  
// Set a callback to run when the Google Visualization API is loaded.
google.setOnLoadCallback(drawChart);
  
function drawChart() {
  var jsonData = jQuery.ajax({
	  url: "<?php bloginfo('wpurl') ?>/wp-admin/admin-ajax.php?action=wlpt_getUserWeighInData",
	  dataType:"json",
	  async: false
	  }).responseText;
	  
  // Create our data table out of JSON data loaded from server.  
  var data = new google.visualization.DataTable(jsonData);
	
  // Instantiate and draw our chart, passing in some options.		  
  var options = {
	title: 'Weight',
	width: 600,
	height: 350
  };

  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
  chart.draw(data, options);
  
  //var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
  //chart.draw(data, {width: 400, height: 240});
}

</script>
<style>
/*.netGain{ color: red; background: url(http://cdn1.iconfinder.com/data/icons/splashyIcons/remove_minus_sign_small.png) no-repeat; padding-left:14px; font-weight:bold;  }
.netLoss{ color: green; background: url(http://cdn1.iconfinder.com/data/icons/splashyIcons/add_small.png) 0 2px no-repeat; padding-left:14px; font-weight:bold;}
*/
.netGain{ color: red; font-weight:bold;  }
.netLoss{ color: green; font-weight:bold;}
</style>

<?php
global $wpdb;	 		

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
	$user_data = get_userdata(wp_get_current_user()->ID);
	$registration_date = $user_data->user_registered;	
	
	$startingWeight = get_user_meta( $user_data->ID, "startingWeight", true );
	$currentWeight = $userweights[0]->current_weight;
	$currentWeightDate = date("Y-m-d", strtotime($userweights[0]->post_date));
	$height = get_user_meta( $user_data->ID, "height", true );
	list($feet, $inches) = explode("'", $height);			
	$heightInInches = ($feet * 12) + $inches;
	// BMI: (weight (lbs) / [height (in)2] x 703)
	$currentBmi = round(($currentWeight / pow($heightInInches, 2)) * 703, 2);
	$startingBmi = round(($startingWeight / pow($heightInInches, 2)) * 703, 2);	
	
	if ($currentWeight < $startingWeight) {
		$netGainOrLoss = "<span class='netLoss'>down " . round($startingWeight - $currentWeight, 2) . " lbs.</span>";
	}
	else {
		$netGainOrLoss = "<span class='netGain'>up " . round($currentWeight - $startingWeight, 2) . " lbs.</span>";
	}
	
	?>
	
	<p><small>Your starting weight was <strong><?php echo $startingWeight ?></strong> and your starting BMI was <strong><?php echo $startingBmi ?></strong>, both recorded on <strong><?php echo date( "M d, Y", strtotime( $registration_date ))  ?></strong></small></p>
	<p>Your current weight is <strong><?php echo $currentWeight ?></strong> (<?php echo $netGainOrLoss ?>) and your current BMI is <strong><?php echo $currentBmi ?></strong></p>
	<!--Div that will hold the chart-->
	<div id="chart_div"></div>
    
    <div style="display:none">
        <p><a href="../../progress-tracker/wp-admin/admin-ajax.php?action=wlpt_get_user_weights">Load the JSON</a>	
        <div id="userweights">
            <div class="wrapper">					
                <ul>
                    <?php
                    foreach ($userweights as $userweight) {							
                        echo "<li>" . $userweight->current_weight . " - " . date('d-M-Y', strtotime($userweight->post_date)) . "</li>"; 							
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
	<?php
endif;
?>