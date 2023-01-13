<?php 

//AJAX UPDATE PINNED IMAGES
add_action( 'wp_ajax_pinterest_automatic', 'wp_pinterest_automatic_callback' );

function wp_pinterest_automatic_callback() {
	global $wpdb; // this is how you get access to the database

	$pid=$_POST['pid'];

	$pin_images = get_post_meta($pid,'pin_images',1);
	$pins = get_post_meta($pid,'pins',1);
	
	if(! is_array($pins))$pins=array();
	
	print_r(json_encode($pins));
	
	
	die();
	
	
}

//AJAX SEND TO PIN QUEUE
add_action( 'wp_ajax_pinterest_automatic_pin', 'wp_pinterest_automatic_pin_callback' );

function wp_pinterest_automatic_pin_callback() {
	$itms=$_POST['itms'];
	
	$itms_arr = explode(',', $itms);
	$itms_arr= array_filter($itms_arr);
	
	foreach($itms_arr as $post_id ){
		update_post_meta($post_id, 'wp_pinterest_automatic_bot', 1);
		delete_post_meta($post_id, 'wp_pinterest_automatic_bot_processed');
	}
	
	die();
}

//AJAX TO CLEAR LOG
add_action( 'wp_ajax_pinterest_automatic_clear', 'wp_pinterest_automatic_clear_callback' );

function wp_pinterest_automatic_clear_callback() {
	global $wpdb;
	$wpdb->query('delete from wp_pinterest_automatic');
	
	print_r(json_encode(array('success')));
	
	die();
}

add_action( 'wp_ajax_wp_pinterest_automatic_clear_queue', 'wp_pinterest_automatic_clear_queue_callback' );

function wp_pinterest_automatic_clear_queue_callback() {
 
	 
	
	//clear the queue 
	global $wpdb;
	 
	$query="SELECT * FROM {$wpdb->prefix}postmeta where meta_key= 'pin_images'";
	
	$rows=$wpdb->get_results($query);

	$count=0;
	foreach ($rows as $row){
	
		$id=$row->post_id;
	
		delete_post_meta($id, 'pin_images');
		delete_post_meta($id, 'pin_text');
		delete_post_meta($id, 'pin_board');
		delete_post_meta($id, 'pin_alt');
		delete_post_meta($id, 'pin_index');
		delete_post_meta($id, 'pin_try');
	
		$count++;
	
	}
	
	//removing bots posts
	$query="DELETE FROM {$wpdb->prefix}postmeta where meta_key= 'wp_pinterest_automatic_bot'";
	$wpdb->query($query);
	$wpdb->rows_affected;
	
	
	$return['message'] = $count . ' posts removed from queue + '.$wpdb->rows_affected .' bot post skipped'   ;
	
	print_r(json_encode($return));
	die();
}

add_action( 'wp_ajax_wp_pinterest_automatic_clear_post', 'wp_pinterest_automatic_clear_post_callback' );

function wp_pinterest_automatic_clear_post_callback() {
 
	
	$id = $_POST['id'];
	
	if(isset($_POST['img'])){
		
		//clear single image
		
		$pin_image=$_POST['img'];
		$post_id=$id;
		$pin_images=get_post_meta($post_id,'pin_images',1);
		
		
		$pin_images=array_filter($pin_images);
		//clear queue
		if(count($pin_images) == 1){
			//last image delete all
			if(md5($pin_images[0]) == $pin_image) delete_post_meta($post_id,'pin_images');
		}else{
			//delete this image only
			foreach($pin_images as $pinimg){
				if(md5($pinimg) != $pin_image) $newpinimages[]=$pinimg;
			}
		
			update_post_meta($post_id, 'pin_images', $newpinimages);
		
		}
		
	}else{
		//clear all images
		
		delete_post_meta($id, 'pin_images');
		delete_post_meta($id, 'pin_text');
		delete_post_meta($id, 'pin_board');
		delete_post_meta($id, 'pin_alt');
		delete_post_meta($id, 'pin_index');
		delete_post_meta($id, 'pin_try');
	  
	}
	
}

//CLEAR BOT POST
add_action( 'wp_ajax_wp_pinterest_automatic_clear_bot_post', 'wp_pinterest_automatic_clear_bot_post_callback' );

function wp_pinterest_automatic_clear_bot_post_callback() {




	if(isset($_POST['id'])){

		$id = $_POST['id'];
		
		delete_post_meta($id, 'wp_pinterest_automatic_bot');
		
 	}

 
}

//Queue last run time,current pin interval , seconds since last run , latest pins
add_action( 'wp_ajax_wp_pinterest_automatic_queue_vals', 'wp_pinterest_automatic_queue_vals_callback' );
function wp_pinterest_automatic_queue_vals_callback() {
 
	 $ret=array();
	 
	 $ret['status'] = 'success';
	
	 //last run 
	 $lastrun=get_option('wp_pinterest_last_run',1392146043);
	 $ret['last_run'] =  date("H:i:s",$lastrun);
	 
	 //current pin interval
	 $wp_pinterest_next_interval = get_option('wp_pinterest_next_interval',4);
	 $ret['interval_mintes'] = $wp_pinterest_next_interval ;
	 $ret['interval_seconds'] = $wp_pinterest_next_interval * 60 ;
	 
	 //seconds science last run
	 $timenow=current_time('timestamp');
	 $timediff=$timenow - $lastrun ;
	 $ret['wp_pinterest_run_before'] = $timediff;

	 $wp_pinterest_next_pin = $wp_pinterest_next_interval * 60 - $timediff;
	 
	 if($wp_pinterest_next_pin < 0) {
	 	$wp_pinterest_next_pin = 0 ;
	 }
	
	 $ret['next_run'] = $wp_pinterest_next_pin;
	 
	 $last_pin_url = get_option('wp_automatic_last_pin_url','');
	 $last_pin_img = get_option('wp_automatic_last_pin_src','');
	 
	 $ret['last_img'] = $last_pin_img;
	 $ret['last_url'] = $last_pin_url;
	 $ret['last_hash'] = md5($last_pin_img); 
	 
	 print_r(json_encode($ret));
	 
	
 die();
}

//Queue Items AJax
add_action( 'wp_ajax_wp_pinterest_automatic_queue_itms', 'wp_pinterest_automatic_queue_itms_callback' );

function wp_pinterest_automatic_queue_itms_callback() {

 	//get items 
	global $post;
	
	//displayed posts
	$posts_displayed = array ();
	
	//all posts array
	$allNewPosts = array();
	
	//query posts with pin images set	
	$the_query = new WP_Query ( array (
				
			'posts_per_page' => 100,
			'post_status' => 'publish',
			'meta_query' => array (
						
					array (
								
							'key' => 'pin_images',
							'compare' => 'EXISTS' ,
	
	
					)
			),
				
			'orderby' => 'meta_value_num',
			'meta_key' => 'pin_try',
			'order' => 'ASC',
			'post_type' => 'any' ,
			'ignore_sticky_posts' => true
	) );

	
	
	// The Loop
	require ('pin_queue_loop_ajax.php');
	
	// other than published
	$view_separator = 1;
	
	$new_query =  array (
			
			'posts_per_page' => 100,
			
			'meta_query' => array (
					
					array (
							
							'key' => 'pin_images',
							'compare' => 'EXISTS' ,
							
					)
			) ,
			'orderby' => 'meta_value_num',
			'meta_key' => 'pin_try',
			'order' => 'ASC',
			'post_type' => 'any' ,
			'ignore_sticky_posts' => true
	);
	
	$the_query = new WP_Query ( $new_query );
		
	// The Loop
	require ('pin_queue_loop_ajax.php');
	
	$query_arr =  array (
			
			'posts_per_page' => 100,
			'meta_query' => array (
					
					array (
							
							'key' => 'wp_pinterest_automatic_bot',
							'compare' => 'EXISTS'
					),
					array(
							'key' => 'wp_pinterest_automatic_bot_processed',
							'compare' => 'NOT EXISTS'
							
					)
			) ,
			'post_type' => 'any' ,
			'ignore_sticky_posts' => true
	) ;
	
	//PolyLange plugin support
	if(function_exists('pll_get_post')){
		$query_arr['lang'] = '' ;
	}
	
	// display posts having the wp_pinterest_automatic_bot custom field
	$the_query = new WP_Query ($query_arr);

	//ini bot posts array	
	$allBotPosts = array();
	
	// loop
	if ($the_query->have_posts ()) {
			
		while ( $the_query->have_posts () ) {
	
			$botPost = array();
			
			$the_query->the_post ();
			
			//post id
			$post_id = $post->ID;
			$botPost['post_id'] = $post_id;
			
			//title
			$ttl = $post->post_title;
			if (trim ( $ttl ) == '') $ttl = '(#'.$post_id.')';
			$botPost['post_title'] = $ttl;
			
			//admin uri
			$admin_uri = admin_url ( 'post.php?post=' . $post_id . '&action=edit' );
			$botPost['post_uri'] = $admin_uri;
			
			//post status
			$botPost['post_status'] = $post->post_status;
			
			//add post to all bots posts
			$allBotPosts[] = $botPost;
			 
		}
	}

	$finalArr = array();
	
	$finalArr['published'] = $allNewPosts;
	$finalArr['bot'] = $allBotPosts;
	
	//get last pin vars
	$last_pin_url = get_option('wp_automatic_last_pin_url','');
	$last_pin_img = get_option('wp_automatic_last_pin_src','');
	
	$lastPin['url'] = $last_pin_url;
	$lastPin['img'] = $last_pin_img;
	$lastPin['hash'] = md5( $last_pin_img );
	
	$finalArr['last_pin'] = $lastPin;
	
	//boards index 
	$wp_pinterest_boards = get_option ( 'wp_pinterest_boards', array (
			'ids' => array (),
			'titles' => array ()
	) );
	
	$wp_pinterest_boards_ids = $wp_pinterest_boards ['ids'];
	$wp_pinterest_boards_titles = $wp_pinterest_boards ['titles'];
	
	$boards = array();
	
	$n = 0 ;
	
	foreach ($wp_pinterest_boards_ids as $id){
		$boards[$id] = $wp_pinterest_boards_titles[$n];
		$n++;
	}
	
	$finalArr['boards'] = $boards;
	
	print_r( json_encode($finalArr) );
	exit;
	
	
 die();
}

add_action( 'wp_ajax_wp_pinterest_automatic_log_itms', 'wp_pinterest_automatic_log_itms_callback' );

function wp_pinterest_automatic_log_itms_callback() {
  
	global $wpdb;
	
	if(! isset($_POST['last'])){
		$_POST['last'] = 0;
	}
	
	$last = $_POST['last'];
	
	
	$query="SELECT * FROM wp_pinterest_automatic where id > $last";
	$rows=$wpdb->get_results($query,ARRAY_A);
	
	print_r(json_encode($rows));
	
	die();
}


add_action( 'wp_ajax_wp_pinterest_automatic_boards', 'wp_pinterest_automatic_boards_callback' );

function wp_pinterest_automatic_boards_callback() {

		$sess = trim( stripslashes( $_POST['sess'] ) );
		$sess = str_replace('"','',$sess);
		
		update_option('wp_pinterest_automatic_session',$sess);
		
	    require_once   dirname(__FILE__) . '/p_core.php';
		$gm=new wp_pinterest_automatic();
		$gm->log('Fetching boards','Trying to fetch boards if login success ');
 		
		if(trim($sess) != ''){
			$gm->pinterest_getboards();
		}else{
			$res['status']='fail';
			print_r(json_encode($res));
		}
		
		die();
	
	 
}

 

?>