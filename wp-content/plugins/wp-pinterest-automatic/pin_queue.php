<div class="wrap">
	<div id="icon-edit-comments" class="icon32">
		<br>
	</div>
	<h2>Pinning Queue</h2>
	<br>
	<?php $lastrun=get_option('wp_pinterest_last_run',1392146043); 
		  $wp_pinterest_next_interval = get_option('wp_pinterest_next_interval',3);
		   
	?>
	
	<ul class="pin_log_breads">
		
		<li>Current time<br><span class="big_tag current_time"><?php echo date( 'H:i:s',current_time('timestamp')) ?></span><br> on server</li>
		<li>Cron last run at <br><span class="big_tag last_run"><?php echo date("H:i:s",$lastrun ) ?></span><br> this is <strong><span class="wp_pinterest_run_before"><?php echo $timdiff = current_time('timestamp') - $lastrun ?></span></strong> seconds ago </li>
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
	
	
	
	<?php 

	
	
	//check we are deactivated
	$timenow=time();
	$deactive = get_option('wp_pinterest_automatic_deactivate', 5 );
	
	if($timenow < $deactive ) {
		echo '<p>Plugin is uploading images with link back attached to description not the image for one hour and still on this mode now as pinterest asked us to lower pins with link back . cron will try to pin with link back again after '. ($deactive - $timenow  ) / 60 .' minutes</p>';
	}
	
	?>
	
	<h3>Actions</h3>
		
 	<button id="wp_pinterest_automatic_clear_queue" class="button"> <span>Clear Queue </span> </button>
	<a target="_blank" id="wp_pinterest_automatic_trigger_cron" class="button wp_pinterest_automatic_trigger_cron" href="<?php echo site_url('?wp_pinterest_automatic=cron')  ?>">Trigger Cron</a>
	<a target="_blank" id="wp_pinterest_automatic_trigger_cron" class="button wp_pinterest_automatic_trigger_cron" href="<?php echo site_url('?wp_pinterest_automatic=cron&test=y')  ?>">Reset Interval and trigger cron</a>
	<br><span style="float:left"  class="spinner spinner-clear"></span>	
	
 	<h3 style="margin-top: 35px;">Queued items</h3>
	
	<form action="" method="post">
		<table class="widefat fixed waiting_posts">
			<thead>
				<tr class="pin_holder_table_head">
					<th class="max-1 check-column"></th>
					<th class="column-response">Post</th>
					<th class="max-1 column-response">Pin Text</th>
					<th class="max-1 max-2 column-response">Pin Board</th>
					<th class="max-1 max-2 column-response">Post Status</th>
					<th class="max-1 max-2 column-response">Pin trials</th>
					<th class="column-response">Clear Post</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="max-1 check-column"></th>
					<th class="column-response">Post</th>
					<th class="max-1 column-response">Pin Text</th>
					<th class="max-1 max-2 column-response">Pin Board</th>
					<th class="max-1 max-2 column-response">Post Status</th>
					<th class="max-1 max-2 column-response">Pin trials</th>
					<th class="column-response">Clear Post</th>
				</tr>
			</tfoot>
			<tbody>
			
			 
				 
			</tbody>
		</table>
		
		<p>*displayed board may differ than the destination board after applying category to board rules and tags to boards rules.</p>

		<h3 style="margin-top: 35px;">To Be Checked</h3>
		
		<p>Below posts are posts that found to be <strong>posted automatically</strong> or <strong>bulk pinned</strong>. once the cron run ( after <span class="next_run">0</span> seconds), they will be checked for images to be pinned.</p>

		<table class="bot_table widefat fixed">
			<thead>
				<tr>
					
					<th class="column-response">Post</th>
					<th class="column-response">Post Status</th>
					<th class="column-response">Delete</th>

				</tr>
			</thead>
			<tbody>
		
		
		 
	</tbody>
		</table>
	</form>
</div>
<script type="text/javascript">

	serverHour = <?php echo date('H',current_time('timestamp')); ?>;
		
</script>