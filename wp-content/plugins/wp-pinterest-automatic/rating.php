<?php 


function wp_pinterest_automatic_rating_notice() {
	 
	$uri=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

	
	if(stristr($uri, '?')){
		$uri.='&wp_deandev_rating=cancel';
	}else{
		$uri.='?wp_deandev_rating=cancel';
	}
	
	if(! stristr($uri,'http')){
		$uri='//'.$uri;
	}
	
	if  ( isset($_GET['wp_deandev_rating']) ) {
		 update_option('wp_deandev_pinterest_automatic_rating','cancel');
	 
	}
	
	$wp_deandev_rating=get_option('wp_deandev_pinterest_automatic_rating','');
	
	if(trim($wp_deandev_rating) == ''){
		//get count of successful pins 
		global $wpdb;
		$query="SELECT count(*) as count FROM wp_pinterest_automatic where action='Pinning >> Success'";
		$rows=$wpdb->get_results($query);
		$row=$rows[0];
		$count=$row->count;
		
		 
		if($count > 5 ){
			
			?>
			
			
		    <div class="updated">
		        <p><?php echo 'Do you mind helping (<a href="https://deandev.com/">ValvePress</a>) by rating  "Wordpress Pinterest Automatic" ? your good rating will <strong>help us improve</strong> the plugin <a style="text-decoration: underline;" href="http://codecanyon.net/downloads">Rate Now »</a> <a  style="float:right"  href="'.$uri.'">(x) </a> '; ?></p>
		    </div>
		    <?php
		
		}//count ok
	}//rating yes
	 
}
add_action( 'admin_notices', 'wp_pinterest_automatic_rating_notice' );


