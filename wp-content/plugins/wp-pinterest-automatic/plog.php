<?php
function wppinterestautomatic() {
	
	// db ini
	global $wpdb;
	
	// filter ini
	$filter = '';
	
	// sql action
	if (isset ( $_POST ['action_type'] )) {
		$act = $_POST ['action_type'];
		if ($act == 'Error') {
			$action = " action like '%Pinning >> Fail%'  ";
		} elseif ($act == 'approved') {
			$action = " action like 'Comment approved%'";
		} elseif ($act == 'Success') {
			$action = " action = 'Pinning >> Success'";
		}
	} else {
		$action = '';
	}
	
	if ($action != '') {
		if ($filter == '') {
			$filter = " where $action";
		} else {
			$filter .= " and $action";
		}
	}
	
	// records number
	if (isset ( $_POST ['number'] )) {
		$num = $_POST ['number'];
	} else {
		$num = '100';
	}
	
	// define limit
	$limit = '';
	if (is_numeric ( $num ))
		$limit = " limit $num ";
		
		// finally date filters `date` >= str_to_date( '07/03/11', '%m/%d/%y' )
	$qdate = '';
	if (isset ( $_POST ['from'] ) && $_POST ['from'] != '') {
		$from = $_POST ['from'];
		$qdate = " `date` >= str_to_date( '$from', '%m/%d/%y' )";
	}
	
	if (isset ( $_POST ['to'] ) && $_POST ['to'] != '') {
		$to = $_POST ['to'];
		if ($qdate == '') {
			$qdate .= " `date` <= str_to_date( '$to', '%m/%d/%y' )";
		} else {
			$qdate .= " and `date` <= str_to_date( '$to', '%m/%d/%y' )";
		}
	}
	
	if ($qdate != '') {
		if ($filter == '') {
			$filter = " where $qdate";
		} else {
			$filter .= "and $qdate";
		}
	}
	
	// echo $filter;
	$query = "SELECT * FROM wp_pinterest_automatic $filter ORDER BY id DESC $limit";
	// echo $query;
	$res = $wpdb->get_results ( $query );
	
	?>


<style>
.ttw-date {
	width: 81px;
}
</style>
<div class="wrap">
	 
	<h2>Action log</h2>
	
		<?php $lastrun=get_option('wp_pinterest_last_run',1392146043); 
		  $wp_pinterest_next_interval = get_option('wp_pinterest_next_interval',3);
		   
	?>
	
	<ul class="pin_log_breads">
		
		<li>Current time<br><span class="big_tag current_time"><?php echo date( 'h:i:s' , current_time('timestamp') ) ?></span><br> on server</li>
		<li>Cron last run at <br><span class="big_tag last_run"><?php echo date("h:i:s",$lastrun ) ?></span><br> this is <strong><span class="wp_pinterest_run_before"><?php echo $timdiff = current_time('timestamp') - $lastrun ?></span></strong> seconds ago </li>
		<li>Random pin interval <br><span class="big_tag interval_mintes"><?php echo $wp_pinterest_next_interval .'</span><br> minutes <abbr title="after this interval the cron should process one item from the queue. This interval changes every time cron process one item.">(<span class="interval_seconds">'.$wp_pinterest_next_interval * 60 .'</span> seconds) </abbr>'  ?> </li>
		<li>Estimated Next Pin arrival<br><span class="big_tag next_run">  <?php 
				
			$wp_pinterest_next_pin = $wp_pinterest_next_interval * 60 - $timdiff;

			
			
			if($wp_pinterest_next_pin < 0) {
				echo '0</span><br> seconds.';
			}else{
				echo $wp_pinterest_next_pin .'</span><br> seconds.';
			}
		
			?> 
		
		</li>
		<li>Last Pin <div class="last_pin"></div><a id="last_pin_link" target="_blank" href="#">visit pin</a></li>
	</ul>
	<div style="clear:both"></div>
	
	
	<form method="post" action="">
		 
		 		<h3>Actions</h3>
				
				<a target="_blank" id="wp_pinterest_automatic_trigger_cron" class="button" href="<?php echo site_url('?wp_pinterest_automatic=cron')  ?>">Trigger Cron</a>
				<button id="clear_log" style=" " class="button">Clear Log</button>
				<button id="update_log" style="margin-right: 5px;  " class="button">Update Log</button>

				<span class="spinner"></span>
		 
				 <input style="float:right" type="submit" value="Filter" class="button-secondary" id="post-query-submit" name="submit">
		 
     			 <select style="float: right;" name="number">
					<option <?php wp_pinterest_automatic_opt_selected($num,'50') ?> value="999">Records</option>
					<option <?php wp_pinterest_automatic_opt_selected($num,'100') ?> value="100">100</option>
					<option <?php wp_pinterest_automatic_opt_selected($num,'500') ?> value="500">500</option>
					<option <?php wp_pinterest_automatic_opt_selected($num,'1000') ?> value="1000">1000</option>
					<option <?php wp_pinterest_automatic_opt_selected($num,'all') ?> value="all">All</option>
				</select>
		 		 
		<div style="clear: both" /></div>

		<h3>Log records</h3>
		
		<table class="widefat fixed table_log">
			<thead>
				<tr>
					<th class="column-date">Index</th>
					<th class="column-response">Date</th>
					<th class="column-response">Type of action</th>
					<th>Data Processed</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>index</th>
					<th>Date</th>
					<th>Type of action</th>
					<th>Data Processed</th>
				</tr>
			</tfoot>
			<tbody>

			<?php
	$i = 1;
	foreach ( $res as $rec ) {
		$action = $rec->action;
		// filter the data strip keyword
		$datas = explode ( ';', $rec->data );
		$data = $datas [0];
		
		if (stristr ( $action, 'Posted:' )) {
			$url = plugins_url () . '/wp-pinterest-automatic';
			$action = 'New Post';
			// restoring link
		} elseif (stristr ( $action, 'Processing' )) {
			$action = 'Processing Campaign';
		}
		
		if (stristr ( $data, 'html' )) {
			
			if( stristr($data , 'if available.  <br><br>') ){

				$data_parts = explode('if available.  <br><br>' , $data );
 				$data = $data_parts[0] . 'if available.  <br><br>' . '<textarea>' . htmlspecialchars ( ($data_parts[1]) ) . '</textarea>';
 				
			}
 
		} else {
			// $data=htmlspecialchars( ($data) );
		}
		
		if ($i % 2 == 0) {
			$alternate = '';
		} else {
			$alternate = 'alternate';
		}
		
		echo '<tr class="' . $alternate . ' ' . strip_tags($rec->action) . '"><td data-row-id="' . $rec->id . '" class="column-date row-id">' . $i . '</td><td  class="column-response" style="padding:5px">' . urldecode ( $rec->date ) . '</td><td  class="column-response" style="padding:5px">' . $action . '</td><td  style="padding:5px">' . urldecode ( $data ) . ' </td></tr>';
		$i ++;
	}
	
	 
	
	?>
			</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">

	serverHour = <?php echo date('H',current_time('timestamp')); ?>;
		
</script>


<?php
}