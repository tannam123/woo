<?php
add_filter ( 'cron_schedules', 'wp_pinterest_automatic_once_a_minute' );
function wp_pinterest_automatic_once_a_minute($schedules) {
	
	// Adds once weekly to the existing schedules.
	$schedules ['once_a_minute'] = array (
			'interval' => 60,
			'display' => __ ( 'Once minute' ) 
	);
	return  $schedules;
}

if (! wp_next_scheduled ( 'wp_pinterest_automatic_pin_hook' )) {
	wp_schedule_event ( time (), 'once_a_minute', 'wp_pinterest_automatic_pin_hook' );
}

add_action ( 'wp_pinterest_automatic_pin_hook', 'wp_pinterest_automatic_pin_function_wrap' );
function wp_pinterest_automatic_pin_function() {

	//CHECK IF WE ARE ELIGIBLE TO RUN NOW OR SKIP THIS TIME
	$lastrun=get_option('wp_pinterest_last_run',1392146043);
	$wp_pinterest_next_interval = get_option('wp_pinterest_next_interval',3);
	$timenow= current_time('timestamp');
	$timediff=$timenow - $lastrun ;
	 
	
	//diable running two instances of this function by flock
	$dir = wp_upload_dir();
	$fp =  fopen($dir['path'].'/pin_lock.txt', 'w+');
	
	$lock=false;
	if(!flock($fp, LOCK_EX | LOCK_NB,$lock)) {
		echo 'Another instance of the script already running..exiting';
		
		//check if the lock file were defected so we delete it
		if($timediff > $wp_pinterest_next_interval * 60 *  3){
			
			//deleting 
			unlink($dir['path'].'/pin_lock.txt');
			
		}
		
		exit(-1);
	} 

	//CHECK LICENSE
	$licenseactive=get_option('wp_pinterest_automatic_license_active','');
	if(trim($licenseactive) == '' ) {
		echo '<br>Error:The license is not active, please activate the license first.';
		return ;
	}
	 
	
	//if not passed 3 minutes sleep
	if(!isset($_GET['test'])){
		
		if($timediff < 180  || $timediff < $wp_pinterest_next_interval * 60 ) {
			echo 'The Cron last processing was from '.$timediff.' seconds and it should wait for '.$wp_pinterest_next_interval * 60 .' seconds so additional '.($wp_pinterest_next_interval * 60 - $timediff) .' seconds needed.';
			return;
		}
		
	}
	
	
	//clear old log 
	global  $wpdb;
	$wp_pinterest_automatic_interval_clear = get_option('wp_pinterest_automatic_interval_clear',7);
    $delete_date= current_time('timestamp') - $wp_pinterest_automatic_interval_clear * 24 * 60 *60 ;
	$delete_date = date ( 'Y-m-d H:i:s' ,$delete_date);
	$query="delete from wp_pinterest_automatic where date < '$delete_date'";
	$wpdb->query($query);
	
	
	//good we are eligble to process
	$wp_pinterest_options=get_option('wp_pinterest_options',array());
	
	//UPDATE LAST RUN
	update_option('wp_pinterest_last_run', $timenow);
	
	//get random interval for next run 
	$wp_pinterest_automatic_interval_min = get_option('wp_pinterest_automatic_interval_min','3');
	$wp_pinterest_automatic_interval_max = get_option('wp_pinterest_automatic_interval_max','5');
	$next_interval = rand($wp_pinterest_automatic_interval_min, $wp_pinterest_automatic_interval_max);
	update_option('wp_pinterest_last_run', $timenow);
	update_option('wp_pinterest_next_interval', $next_interval); // in minutes
	
	echo 'Cron triggered successfully now and will be eligible to run again after '.$next_interval . ' minutes.';
	
	//check we are deactivated
	$deactive = get_option('wp_pinterest_automatic_deactivate', 5 );
 
	 
	if( time() < $deactive ) {
		if(  in_array('OPT_IDLE', $wp_pinterest_options)  ){
			return ;
		} 
		 
	}
	
	//Allowed time check
	$wp_pinterest_automatic_interval_from_h  = get_option('wp_pinterest_automatic_interval_from_h','00');
	$wp_pinterest_automatic_interval_from_m  = get_option('wp_pinterest_automatic_interval_from_m','00');
	
	$wp_pinterest_automatic_interval_to_h  = get_option('wp_pinterest_automatic_interval_to_h','23');
	$wp_pinterest_automatic_interval_to_m  = get_option('wp_pinterest_automatic_interval_to_m','59');
	
	
	$wp_pinterest_automatic_interval_from_h_2  = get_option('wp_pinterest_automatic_interval_from_h_2','00');
	$wp_pinterest_automatic_interval_from_m_2  = get_option('wp_pinterest_automatic_interval_from_m_2','00');
	
	$wp_pinterest_automatic_interval_to_h_2  = get_option('wp_pinterest_automatic_interval_to_h_2','23');
	$wp_pinterest_automatic_interval_to_m_2  = get_option('wp_pinterest_automatic_interval_to_m_2','59');
	
	
	$hour_now = date ( 'H',$timenow );
	$minute_now = date ( 'i',$timenow );
	
	$valid_run = true;
	
	if( $wp_pinterest_automatic_interval_to_h >= $wp_pinterest_automatic_interval_from_h ){
	
		if($hour_now > $wp_pinterest_automatic_interval_from_h){
			
			//hour is bigger go 
			
			
		}elseif($hour_now == $wp_pinterest_automatic_interval_from_h){
			
			//same hour let's check minutes
			
			if($minute_now >= $wp_pinterest_automatic_interval_from_m){
				
				//good minutes are bigger go 
				
			}else{
				echo '<br>Interval 1 Start time still not reached to activate queue*';
				$valid_run = false;
				
			}
			
		}else{
			
			echo '<br>Interval 1 start time still not reached to activate queue';
			$valid_run = false;
			
		}
		
		
		//end time 
		if($hour_now < $wp_pinterest_automatic_interval_to_h){
			
			//go 
			
		}elseif($hour_now == $wp_pinterest_automatic_interval_to_h){
			
			//same hour check minutes
			if($minute_now <= $wp_pinterest_automatic_interval_to_m){
				//go
			}else{
				echo '<br>Interval 1 queue proecess end time reached';
				$valid_run = false;
			}
			
		}else{
			
			//passed
			echo '<br>Interval 1 queue proecess end time reached';
			$valid_run = false;
			
		}
	 
	}else{
		echo '<br><span style="color:red">Notice:</span> Your queue processing start time is higher than the end time, Please review this configuration in the plugin settings page. ';
	}
	
	
	//second interval check
	if($valid_run === false){
		
		$valid_run = true;
		
		if($wp_pinterest_automatic_interval_to_h_2 >= $wp_pinterest_automatic_interval_from_h_2 ){
			
			if($hour_now > $wp_pinterest_automatic_interval_from_h_2){
				
				//hour is bigger go
				
				
			}elseif($hour_now == $wp_pinterest_automatic_interval_from_h_2){
				
				//same hour let's check minutes
				
				if($minute_now >= $wp_pinterest_automatic_interval_from_m_2){
					
					//good minutes are bigger go
					
				}else{
					echo '<br>Interval 2 start time still not reached to activate queue*';
					$valid_run = false;
					
				}
				
			}else{
				
				echo '<br>Interval 2 Start time still not reached to activate queue';
				$valid_run = false;
				
			}
			
			
			//end time
			if($hour_now < $wp_pinterest_automatic_interval_to_h_2){
				
				//go
				
			}elseif($hour_now == $wp_pinterest_automatic_interval_to_h_2){
				
				//same hour check minutes
				if($minute_now <= $wp_pinterest_automatic_interval_to_m_2){
					//go
				}else{
					echo '<br>Interval 2 Queue proecess end time reached will start next day';
					$valid_run = false;
				}
				
			}else{
				
				//passed
				echo '<br>Interval 2 Queue proecess end time reached will start next day';
				$valid_run = false;
				
			}
			
		}else{
			echo '<br><span style="color:red">Notice:</span> Your queue processing start time 2 is higher than the end time, Please review this configuration in the plugin settings page. ';
		}
		
		
	}//end-second-interval-check
	

	if($valid_run === false){
		return;
	}
	
	//chek if logged in 
	$wp_pinterest_automatic_session =get_option('wp_pinterest_automatic_session','');
	
	if(trim($wp_pinterest_automatic_session) == '' ){
		echo '<br>Not logged in';
		return;
	}
	 
	global $post;
	// display posts having the wp_pinterest_automatic_bot custom field
	
	remove_all_filters('pre_get_posts' );
	 
	$query_arr =  array (
			'post_status'=>'publish',
			
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
			),
			'post_type' => 'any' ,
			'ignore_sticky_posts' => true
			
			 
	) ;
	
	
	//PolyLange plugin support 
	if(function_exists('pll_get_post')){
		$query_arr['lang'] = '' ;
	}
	  
	$the_query = new WP_Query ($query_arr);
	
	//stop list
	$wp_pinterest_stop =get_option('wp_pinterest_stop','');
	$wp_pinterest_stop_arr = array_filter( explode("\n" , $wp_pinterest_stop) );
	
	if(! is_array($wp_pinterest_stop_arr) ) $wp_pinterest_stop_arr = array();

	$wp_pinterest_stop_arr[] = 'data:image' ;
	
	// loop
	
	
	$i = 1;
	if ($the_query->have_posts ()) {
			
		while ( $the_query->have_posts () ) {
	
			$the_query->the_post ();
			$post_id = $post->ID;
			
			//excluded categories check 
			$isPostExcluded = false;
			$wp_pinterest_excluded_post_category = get_option('wp_pinterest_excluded_post_category' ,array());
			if(count($wp_pinterest_excluded_post_category) > 1){
				
				unset($wp_pinterest_excluded_post_category[0]);
				 
				$post_type = $post->post_type ;
				$post_taxonomies = 	 get_object_taxonomies($post_type);
 
 				foreach($post_taxonomies as $tax){
					
					if( is_taxonomy_hierarchical($tax) ){
					
						foreach ($wp_pinterest_excluded_post_category as $excluded_cat ){
							if(has_term( $excluded_cat,$tax,$post_id ) ){
							 
								echo  '<br>Bot post >> Skipped','Post '.$post_id. ' is in an excluded category '.$excluded_cat.' excluded from pinning.' ;
								
								//delete bot to process flag
								delete_post_meta($post_id, 'wp_pinterest_automatic_bot');
								$isPostExcluded = true;
								continue;
							}
								
						}
					}
					
					if($isPostExcluded) continue;
					
				}
				
			}
 
			if($isPostExcluded) continue;
			
			//images to pin var ini
			$imagesToPin = array();
			$imagesToPinAlts = array();
			
			 //check thumbnail url 
			$post_title=$post->post_title;
			
			//remove automatic filter that removes gallery images
			if(function_exists('wp_automatic_the_content_filter')) remove_filter( 'the_content', 'wp_automatic_the_content_filter');
			
			if( stristr($post->post_content, 'nextpage') ) {
				$cont = apply_filters( 'the_content' , $post->post_content);
			}else{
				$cont=  apply_filters( 'the_content', get_the_content() );
			}
			
			 
			//extracing thumbnail if not extracting first image
			$post_thumbnail_id = get_post_thumbnail_id($post_id);
			$post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );
			$img=$post_thumbnail_url;
			$txtalt = get_post_meta($post_thumbnail_id , '_wp_attachment_image_alt', true);
			 
		 	//adding the featured image
		 	$featured_image_exists = false;
		 	if(trim($img) != '') {
		 		$imagesToPin[] = $img;
		 		$imagesToPinAlts[] = $txtalt;
		 		$featured_image_exists = true;
		 	}
		 	
		 	
		 	
			//if no featured image check custom field image
			if(trim($img) == ''){
				
				$customf=get_option('wp_pinterest_automatic_cf','');
				
				 
				
				if(trim($customf) != ''){
					//get custom field value
					$imgsrc_custom=get_post_meta($post_id,$customf,true);
				 
					
					if(trim($imgsrc_custom) != ''){
						//good found value for the custom field let's check if image
						$img = trim($imgsrc_custom);
						$imagesToPin[] = $img;
						$imagesToPinAlts[] = '';
					}

				}
			}
			 
			 //content images	
			preg_match_all('/<img [^>]*src=["|\']([^"|\']+)["|\'].*?>/i', $cont, $matches);
			$imgs           =    array();
			$imgs_html  =    array();
			$imgs_alts   =     array() ;
			@$imgs=$matches[1];
			@$imgs_html = $matches[0];
			
			//attached images
			if(  in_array( 'OPT_UPLOADED', $wp_pinterest_options)  ){
				
				$media = get_attached_media( 'image' , $post_id );
			
				if(count($media) > 0){
					
					foreach($media as $smedia){
						
						$imgs[] = $smedia->guid;
						
						$alt  = get_post_meta($smedia->ID , '_wp_attachment_image_alt', true);
						
						$imgs_html[] = '<img src="' . $smedia->guid . '" alt="'. $alt .'" />' ;
						 
						 
					}
					
				}
			}
			 
			//stoplist 
			if(count($wp_pinterest_stop_arr) > 0){
				
				//featured image
				if( count($imagesToPin) > 0 ){
					
					foreach ($wp_pinterest_stop_arr as $stop_word){
						if(stristr($imagesToPin[0] , trim($stop_word))){
							
							$imagesToPin = array();
							$imagesToPinAlts = array();
							
							break;
						}
					}
					
				}
				
				//content image
				$i=0;
				foreach( $imgs as $singleImg ){
					foreach ($wp_pinterest_stop_arr as $stop_word){
						if(stristr($singleImg , trim($stop_word))){
							unset($imgs[$i]);
							unset($imgs_html[$i]);
							break;
						}
					}
					$i++;
				}
				
				$imgs = array_values($imgs);
				$imgs_html = array_values($imgs_html);
				
			}

			$img='';
			@$img=$imgs[0];
			 
			foreach ($imgs_html as $imgHtml ) {

				preg_match_all('/alt="([^"]*)"/i',$imgHtml, $alt);
				$txtalt = '';
				@$txtalt=$alt[1][0];
				$imgs_alts[] = $txtalt;

			}
			 
			//gallery images if exists
			if($post->post_type == 'product'  &&  class_exists( 'WooCommerce' ) ){
				$product = new WC_product($post_id);
				$attachment_ids = $product->get_gallery_image_ids();
				 
				
				foreach ($attachment_ids as $attachment_id){
					$img_meta = wp_prepare_attachment_for_js($attachment_id) ;
					
					$img_url = $img_meta['url'];
					$img_alt = $img_meta['alt'];
					
					$imgs[] = $img_url;
					$imgs_alts[] = $img_alt;
					
				}
				
			}
			
			
			//Clean duplicates
			$uniqueArr = array();
			$imgsCopy = $imgs;
			$i = 0 ;
			foreach ($imgs as $singleImage){
				
				if(! in_array( $singleImage , $uniqueArr  )){
					$uniqueArr[] = $singleImage;
				}else{
					//duplicate image
					unset( $imgsCopy[$i] ) ; 
					unset( $imgs_alts[$i]  )  ;
				}
				
				$i++;
			}
			
			$imgs = array_values( $imgsCopy );
			$imgs_alts = array_values($imgs_alts);
		  		
			//if the featured image which is in $imagesToPin exists at the contet $imgs skip it
			if(count($imagesToPin) > 0){
				
				$featuredImage = preg_replace('{-\d*x\d*(\.[a-z]*$)}', "$1", $imagesToPin[0]);
				
				$i= 0;
				$imgsCopy = $imgs;
				foreach ($imgs as $singleImage){
					$singleImage = preg_replace('{-\d*x\d*(\.[a-z]*$)}', "$1", $singleImage);
					
					if($featuredImage == $singleImage){
						unset( $imgsCopy[$i] ) ;
						unset( $imgs_alts[$i]  )  ;
					}
					 
					$i++;
				}
				
				$imgs = $imgsCopy;
				
			}
			
			//ignore featured image option?
			if(  in_array('OPT_FEAT_SKIP',$wp_pinterest_options) && $featured_image_exists == true  ){
				if(count($imgs) > 0 ){
					$imagesToPin = array();
					$imagesToPinAlts = array();
				}
			}
			
			$imagesToPin = array_merge($imagesToPin , $imgs);
			$imagesToPin = array_map('html_entity_decode' , $imagesToPin);
			
			$imagesToPinAlts = array_merge ($imagesToPinAlts , $imgs_alts) ;
			
			//Duplicate pins check
			if( in_array( 'OPT_NO_DUPLICATE' ,$wp_pinterest_options ) ){
				
				$pins=get_post_meta($post_id,'pins',1);
				if(! is_array($pins)) $pins = array();
				
				  
				$num_of_excluded = 0 ;
				if(count ($pins) > 0  ){
					
					foreach ($pins as $old_pinned_img){
						
						 
						$duplicate_img_key = array_search( html_entity_decode( $old_pinned_img ) ,$imagesToPin );
					  
						 if($duplicate_img_key !== false){
						 	unset($imagesToPin[$duplicate_img_key]);
						 	unset($imagesToPinAlts[$duplicate_img_key]);
						 	$num_of_excluded++;
						 }
						
					}
					
					if($num_of_excluded != 0){
						$imagesToPin = array_values($imagesToPin);
						$imagesToPinAlts = array_values($imagesToPinAlts);
						
					}
					
				}
				 
			}
			
			//Maximum number of images. now $imagesToPin contains the whole images. check maximum images allowed and remove the rest
			if(count($imagesToPin) > 1){ //multiple images
				
				//ok we now have multiple images. Now check if the post is a bot or bulk queued?
				$wp_pinterest_automatic_bot_c = get_post_meta($post_id , 'wp_pinterest_automatic_bot_c',1);
				
				if(trim($wp_pinterest_automatic_bot_c) == 't'){
					// Real bot
					
					$numOfImagesFieldName = 'wp_pinterest_bot_num';
					 
				}else{
					
					// bulk pin bost
					$numOfImagesFieldName = 'wp_pinterest_bot_num_q';
					
				}
				
				$numOfImagesOpt = get_option($numOfImagesFieldName , 1) ;
				
				if($numOfImagesOpt == 'custom'){
					$numOfImagesOpt = get_option($numOfImagesFieldName . '_n' , 1) ;
				}elseif($numOfImagesOpt == 'all'){
					$numOfImagesOpt = count($imagesToPin) ;
				}

				if(! is_numeric($numOfImagesOpt)) $numOfImagesOpt = 1;
				
				
				if($numOfImagesOpt < count($imagesToPin)){
					
					$newImagesToPin = array();
					$newImagesAlts   = array();
					
					for($i=0;$i<$numOfImagesOpt;$i++){
						
						$newImagesToPin[] = $imagesToPin[$i];
						$newImagesAlts[]    = $imagesToPinAlts[$i];
						
					}
						
					$imagesToPin = $newImagesToPin;
					$imagesToPinAlts = $newImagesAlts;
					
				}

			}
			 	
			
			if ( count($imagesToPin) > 0 ) {
					
				require_once(str_replace('pin_schedule.php','p_core.php',__FILE__));
				$pinterest=new wp_pinterest_automatic;
				$pinterest->log('Bot post','Found bot post '.$post_id. ' with images in content ' );
					
				$pinterest->log('Bot post >> Add image to queue', count($imagesToPin) .' images starting with '.$imagesToPin[0] );
					
				$pin_images= $imagesToPin;
				
				$pin_board=get_option('wp_pinterest_board','');
				 
				$pin_text=get_option('wp_pinterest_default','')  . ':separator:' . get_option('wp_pinterest_default_title','')  ;
			 
				update_post_meta($post_id,'pin_images',$pin_images);
				update_post_meta($post_id,'pin_text',$pin_text);
				update_post_meta($post_id,'pin_board',$pin_board);
				update_post_meta($post_id,'pin_alt', $imagesToPinAlts);
				update_post_meta($post_id,'pin_index',$pin_images);
				update_post_meta($post_id,'pin_try',0);
			
				//building image trials array
				foreach($pin_images as $pin_image){
					$images_try [md5($pin_image)] = 0 ;
				}
			
				update_post_meta($post_id,'images_try',$images_try);
			
				//found image
			}
			
			//delete bot to process flag
			delete_post_meta($post_id, 'wp_pinterest_automatic_bot');

			//add bot processed flag
			update_post_meta($post_id, 'wp_pinterest_automatic_bot_processed', 'yes');
			
			$i ++;
		}
	}
	
	
	
	
	// PROCESS QUEUE
	global $post;
	$posts_displayed = array ();
	
	if(! in_array('OPT_RAND',$wp_pinterest_options)){
	
		$query_arr = array (
				
				'posts_per_page' => 1,
				'post_status' => 'publish',
				'meta_query' => array (
						
						array (
								
								'key' => 'pin_images',
								'compare' => 'EXISTS'
						)
				),
				
				'orderby' => 'meta_value_num',
				'meta_key' => 'pin_try',
				'order' => 'ASC',
				'post_type' => 'any' ,
				'ignore_sticky_posts' => true
		);
		
		//PolyLange plugin support
		if(function_exists('pll_get_post')){
			$query_arr['lang'] = '' ;
		}
		
		$the_query = new WP_Query ( $query_arr  ); 
		
	}else{
		
		// Random post
		
		$query_arr = array (
				
				'posts_per_page' => 1,
				'post_status' => 'publish',
				'meta_query' => array (
						
						array (
								
								'key' => 'pin_images',
								'compare' => 'EXISTS'
						)
				),
				
				'orderby' => 'rand',
				'post_type' => 'any' ,
				'ignore_sticky_posts' => true
		);
		
		//PolyLange plugin support
		if(function_exists('pll_get_post')){
			$query_arr['lang'] = '' ;
		}
		
		$the_query = new WP_Query ( $query_arr  );
		
	}
	
	if ($the_query->have_posts ()) {
	
		while ( $the_query->have_posts () ) {
	
			//get post id
			$the_query->the_post ();
			$post_id = $post->ID;
			 
			//incrment trial for this post 
			$pin_trial=get_post_meta($post_id , 'pin_try',1);
			$pin_trial = $pin_trial +1;
			update_post_meta($post_id,'pin_try',$pin_trial);
			
			//get pin variables
			$pin_images=get_post_meta($post_id,'pin_images',1); // array of images to pin
			
			//check if pin_images field contains valid images
			if( ! is_array($pin_images) ) delete_post_meta($post_id, 'pin_images');
			
			$pin_text=get_post_meta($post_id,'pin_text',1); // pin text
			if(trim($pin_text) == '') $pin_text ="[post_title]";
			
			$pin_board=get_post_meta($post_id,'pin_board',1); 
			$pin_alt=get_post_meta($post_id,'pin_alt',1);
			$images_index=get_post_meta($post_id,'pin_index',1); //index of all images 
		 	
			$post_title= addslashes ( $post->post_title );
			$images_try_pre=get_post_meta($post_id,'images_try',1);
			
		
			foreach($pin_images as $pin_img){
				
				if(! isset($images_try_pre[md5($pin_img)]) || ! is_numeric($images_try_pre[md5($pin_img)]) ){
					$current_try=0;
				}else{
					$current_try = $images_try_pre[md5($pin_img)] ;
				}
				
				$images_try[md5($pin_img)]=$current_try;
			}
			
			
			
			//CTT CHECK
			if(in_array('OPT_CTT', $wp_pinterest_options)){
					
				$default_board=get_option('wp_pinterest_board','');
					
				$wp_pinterest_automatic_wordpress_tags = get_option ( 'wp_pinterest_automatic_wordpress_tags', array ());
				$wp_pinterest_automatic_pinterest_tags = get_option ( 'wp_pinterest_automatic_pinterest_tags', array () );
					
				//check if this is a default board or user selected
				if(trim($default_board) == $pin_board){
			
					//get tags
					$wp_pinterest_automatic_tax_tags= get_option('wp_pinterest_automatic_tax_tags','post_tag,product_tag');
					$tax_txt=$wp_pinterest_automatic_tax_tags;
						
					if(! stristr($tax_txt, 'post_tag') ){
						$tax_txt='post_tag,product_tag';
					}
						
					$tax=explode(',', $tax_txt);
					$tax=array_filter($tax);
					$tax=array_map('trim', $tax);
						
					foreach($tax as $key=>$taxitm){
						if(!taxonomy_exists($taxitm)){
							unset($tax[$key]);
						}
					}
						
					$n=0;
						
					foreach($wp_pinterest_automatic_wordpress_tags as $wp_tag ){
						
						$tagApplies = false;
						
						foreach ($tax as $singleTax){
							
							if( has_term($wp_tag,$singleTax,$post_id)  ){
								//get board matching this category
								$pin_board=$wp_pinterest_automatic_pinterest_tags[$n];
								$tagApplies = true;
								break;
							}
							
						}
						
						if($tagApplies) break;
						
						$n++;
					}
				}
					
			}//ctt checked
			
			//CTB CHECK
			if(in_array('OPT_CTB', $wp_pinterest_options)){
					
				$default_board=get_option('wp_pinterest_board','');
					
				$wp_pinterest_automatic_wordpress_category = get_option ( 'wp_pinterest_automatic_wordpress_category', array ());
				$wp_pinterest_automatic_pinterest_category = get_option ( 'wp_pinterest_automatic_pinterest_category', array () );
					
				//check if this is a default board or user selected
				if(trim($default_board) == $pin_board){
					
					//get categories
					$tax_txt=get_option('wp_pinterest_automatic_tax','category,product_cat');
					
					if(! stristr($tax_txt, 'category') ){
						$tax_txt='category,product_cat';
					}
					
					$tax=explode(',', $tax_txt);
					$tax=array_filter($tax);
					$tax=array_map('trim', $tax);
					

					foreach($tax as $key=>$taxitm){
						if(!taxonomy_exists($taxitm)){
							unset($tax[$key]);
						}
					}
					
					$n=0;
					foreach($wp_pinterest_automatic_wordpress_category as $cat ){
			
						$taxApplies = false; // flag

						foreach ($tax as $singleTax){
							if( has_term($cat,$singleTax,$post_id) ){
								//get board matching this category 
								$pin_board=$wp_pinterest_automatic_pinterest_category[$n];
								$taxApplies = true;
								break;
							}
						}
						
						// Stop if found applied tax
						if($taxApplies) break;
							
						$n++;
					}
				}
			
			}//ctb check
			
			//process pinning for one image of that post with id = $post_id
			require_once(str_replace('pin_schedule.php','p_core.php',__FILE__));
			$pinterest=new wp_pinterest_automatic;
			$pinterest->log('Cron >> Pinning Post','Post with id {'.$post_id.'} has '.count($pin_images). ' scheduled pins'  );
			
			//logging
			 
			
			//order pin_images asc by img_try
			$zeroTryImages = array();
			$oneTryImages = array();
			$twoTryImages = array();
			
			foreach ($pin_images as $pin_image){
				
				$current_try= $images_try[ md5($pin_image) ]  ;
				
				if($current_try == 0) {
					$zeroTryImages[] = $pin_image;
				}elseif( $current_try == 1){
					$oneTryImages[] = $pin_image;
				}else{
					$twoTryImages[] = $pin_image;
				}
				
			}
			 
			$pin_images = array_merge($zeroTryImages , $oneTryImages , $twoTryImages) ;
			
		 
			
			//min width check OPT_MINWIDTH
			if( in_array('OPT_MINWIDTH', $wp_pinterest_options) || in_array('OPT_MINHEIGHT', $wp_pinterest_options)  ){
				
				$wp_pinterest_automatic_minwidth = get_option('wp_pinterest_automatic_minwidth','');
				$wp_pinterest_automatic_minheight = get_option('wp_pinterest_automatic_minheight','');
				
				
				
				if( is_numeric ($wp_pinterest_automatic_minwidth) ||  is_numeric ($wp_pinterest_automatic_minheight)  ){
					
					foreach($pin_images as $key=>$pin_image){
					
						$validWidth = in_array('OPT_MINWIDTH', $wp_pinterest_options) ? false : true  ; //width validity ini
						$validHeight =  in_array('OPT_MINHEIGHT', $wp_pinterest_options) ?  false : true ;
						
						$notSmallPin_image = $pin_image;
						$wasSmallImage = false;
						
						$smallImageWidth = '' ;
						$smallImageHeight = '';
						
						// if width is in the url
						if(  preg_match( '{-(\d*)x(\d*)\.[a-z]*$}', $pin_image,$matches )){
							
							$notSmallPin_image = preg_replace('{-\d*x\d*(\.[a-z]*$)}', "$1", $notSmallPin_image) ;

							if(isset($matches[1]) && is_numeric($matches[1])){
								$smallImageWidth = $matches[1];
							}
							
							if(isset($matches[2]) && is_numeric($matches[2])){
								$smallImageHeight = $matches[2];
							}
							 
							$wasSmallImage = true;
						}
						
						
						$localFilePath = ''; // path of the local file
						
						// if the image is located at the wp-content
						if(stristr($notSmallPin_image,  WP_CONTENT_URL )){                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
							
							$localFilePath= str_replace(  WP_CONTENT_URL , WP_CONTENT_DIR ,$notSmallPin_image) ;
							
						}else{
							
							// file is not in the wp-content dir download it
							$imgGet = wp_remote_get($notSmallPin_image);
							
							if( is_array($imgGet) ) {
								
								//if full image does not exist, get the small image
								if( $imgGet['response']['code'] == 404 ){
									$imgGet = wp_remote_get($pin_image);
								}
								
								if($imgGet['response']['code'] == 200){

									$body = $imgGet['body'];
									 
									//save to temp file 
									$write = file_put_contents ( WP_CONTENT_DIR . '/uploads/wp_pinterest_automatic' , $body);
									
									if($write != false){
										
										$localFilePath = WP_CONTENT_DIR. '/uploads/wp_pinterest_automatic' ;
										
									}else{
										$pinterest->log('Cron >> Image width check', 'Failed to save the fetched image :'.$notSmallPin_image   );
									}
									
								}else{
									
									$pinterest->log('Cron >> Image width check', 'Fetched content is not valid for :'.$notSmallPin_image   );
									
								}
								
							}else{
								$pinterest->log('Cron >> Image width check', 'Failed to fetch the file:'.$notSmallPin_image   );
							}
							
						}// file path got
						 
						//check the width of the local file
						if(  stristr(  $localFilePath , '/' )    ){
							
							$size = getimagesize($localFilePath);
							
							// if the image is local, small and fullsized didn't exist  then extract from the URL
							if($size == false  && $wasSmallImage == true && stristr($notSmallPin_image,  WP_CONTENT_URL )  ){
								
								if(is_numeric($smallImageWidth))	$size = $smallImageWidth;
								$size= array();
								$size[0] = $smallImageWidth;
								$size[1] = $smallImageHeight;
								
							}
 							
							
							if($size){
								
								if( in_array('OPT_MINWIDTH', $wp_pinterest_options) ){
									if($size[0] >$wp_pinterest_automatic_minwidth){
										
										$validWidth = true ; // set valid width flag
										$pinterest->log('Cron >> Image width check', 'width is '.$size[0].' is higher than the minimum width  :'.$notSmallPin_image  );
									
									}else{
									
										$pinterest->log('Cron >> Image width check', 'width is '.$size[0].' is lower than the minimum width  :'.$notSmallPin_image  );
									
									}
								} 
								
								if( in_array('OPT_MINHEIGHT', $wp_pinterest_options) ){
									if($size[1] >$wp_pinterest_automatic_minheight){
										
										$validHeight = true ; // set valid width flag
										$pinterest->log('Cron >> Image height check', 'Height is '.$size[1].' is higher than the minimum height  :'.$notSmallPin_image  );
										
									}else{
										
										$pinterest->log('Cron >> Image Height check', 'Height is '.$size[1].' is lower than the minimum height  :'.$notSmallPin_image  );
										
									}
								} 
								
							}else{
								
								$pinterest->log('Cron >> Image width/height check', 'Can not read width  :'.$notSmallPin_image  );
							
							}
							
							
						}else{
							$pinterest->log('Cron >> Image width check', 'Check failed :'.$notSmallPin_image  );
						}
						
						// final decision valid or not ?  if not delete
						
						if($validWidth && $validHeight){
							//stop the loop, the first image is valid which will be pinned
							break;
						}else{
							//delete this current image
							unset($pin_images[$key]);
							
							if(count($pin_images) > 0 ){
								
								update_post_meta($post_id, 'pin_images', $pin_images);
							
							}else{
								
								delete_post_meta($post_id,'pin_images');
							
							}
							
						 
						}
						 
					 
					}
					
				}
				
			}

			//if no items return
			if(count($pin_images) == 0 ) return;
			
			// pick first image
			foreach($pin_images as $pin_image){
				 break;
			}
			
			$min_try_val = $images_try[md5($pin_image)];
			if(! is_numeric($min_try_val)) $min_try_val = 0 ;
			
			//increment try value 
			$images_try[md5($pin_image)]=$min_try_val +1 ;
			update_post_meta($post_id, 'images_try', $images_try);
			$pinterest->log('Cron >> Pinning image', $pin_image   );
			
			//pinning the image if successfull pin remve it from pin images array
			
			
			if( trim($wp_pinterest_automatic_session) != '' &&  trim($pin_text) != '' ){
					
				$tocken=$pinterest->pinterest_login();
			
				if(trim($tocken) != ''){
					//valid login let's pin
					
						$sp= new WPASpintax;
						$pintext=$sp->spin($pin_text);
							
						if(trim($pintext == '')){
							$pintext= $pin_text ;
						}
							
						$i=0;
						foreach($images_index as $image){
							if($pin_image == $images_index[$i]){
								break;
							}
							$i++;
						}
							
						$thepost=get_post($post_id);
						$user=get_userdata( $thepost->post_author  );
						$username=$user->display_name;
						 	
						if(trim($thepost->post_excerpt) == '' && defined( 'WPSEO_FILE' ) ){
							
							$possible_excerpt = get_post_meta($thepost->ID,'_yoast_wpseo_metadesc',1);
							
							if(trim($possible_excerpt) != '')
								$thepost->post_excerpt = $possible_excerpt;
							
						}
						
						if(trim($thepost->post_excerpt) == '' && function_exists('aiosp_add_cap') ){
								
							$possible_excerpt = get_post_meta($thepost->ID,'_aioseop_description',1);
								
							if(trim($possible_excerpt) != '')
								$thepost->post_excerpt = $possible_excerpt;
									
						}
						
						
						//excerpt generation
						if( stristr($pintext, 'post_excerpt') && trim($thepost->post_excerpt) == '') {
							$wp_pinterest_automatic_excerpt=get_option('wp_pinterest_automatic_excerpt','150');
							
						 
								$new_excerpt = wp_pinterest_substr(  wp_pinterest_texturize($thepost->post_content) , 0,$wp_pinterest_automatic_excerpt);
						 
						
							if(trim($new_excerpt) != '') {
								$new_excerpt.= '...';
							}
						
							$thepost->post_excerpt = $new_excerpt;
						}
						
						$pintext=str_replace('[post_title]',$post_title,$pintext);
						$pintext=str_replace('[post_excerpt]',  strip_tags($thepost->post_excerpt) ,$pintext);
						
						if( stristr( $pintext,'post_content' ) )
						$pintext=str_replace('[post_content]', wp_pinterest_texturize($thepost->post_content) ,$pintext);
						
						$pintext=str_replace('[post_author]', $username ,$pintext);
						$pintext=str_replace('[post_link]', get_permalink( $post_id ) ,$pintext);
						@$pintext=str_replace('[image_alt]',  $pin_alt[$i] ,$pintext);

						//get tags
						if(stristr($pintext, '[post_tags]')){
							//get tags
							//$taxonomies = get_taxonomies(array('public' => true ,'hierarchical' => false , 'show_ui' => true),'names');
							
							$tax_txt=get_option('wp_pinterest_automatic_tax_tags','post_tag,product_tag');
							$taxonomies_raw = explode( ',' , $tax_txt );
							
							$taxonomies = array();
							foreach ( $taxonomies_raw as $new_tax){
								if( taxonomy_exists($new_tax) ) $taxonomies[ $new_tax ] = $new_tax;
							}
							 
							$tags=wp_get_object_terms($post_id,$taxonomies);
						 
							$tagLimit = count($tags);
							if( in_array('OPT_TAG_LIMIT', $wp_pinterest_options) ){
								$wp_pinterest_automatic_tag_limit = get_option('wp_pinterest_automatic_tag_limit','');
								
								if(is_numeric($wp_pinterest_automatic_tag_limit)){
									$tagLimit = $wp_pinterest_automatic_tag_limit ;
								}
							}
							
							$tag_text= '';
							
							$i = 0 ;
							foreach($tags as $tag){
								
								$tag_text = $tag_text .' #'. str_replace(' ', '', $tag->name);
								$i++;
								
								if($i == $tagLimit) break;
								
							}
						
							$pintext=str_replace('[post_tags]',  $tag_text ,$pintext);
							
						}
						
						// Product price if applicable
						if(stristr($pintext, '[product_price]')){
							$productPrice = get_post_meta($post_id,'_price',1);
							$pintext=str_replace('[product_price]', $productPrice ,$pintext);
						}
						
						//get category
						if( stristr ($pintext, '[post_category]' ) ){
							$cats = (get_the_category($thepost->ID));
							$cat= '';//ini
							if ( is_array($cats) && isset($cats[0] ) ){
								$cat = $cats[0];
								$cat = $cat->name;
							}
							
							$pintext= str_replace('[post_category]' , $cat,$pintext);
							
						}
						
						//WOO category
						if( stristr($pintext, '[product_cat]') ){
							
							$cats = (get_the_terms($thepost->ID , 'product_cat'  ));
							
							
							$cat= '';//ini
							if ( is_array($cats) && isset($cats[0] ) ){
								$cat = $cats[0];
								$cat = $cat->name;
							}
							
							$pintext= str_replace('[product_cat]' , $cat,$pintext);
							
						}
						
						//get categories as hashtags
						if( stristr($pintext, '[post_categories]') ){
							$cats = (get_the_category($thepost->ID));
							$cat= '';//ini
							if ( is_array($cats) && isset($cats[0] ) ){
								
								foreach ($cats as $single_cat){
									$cat .= ' #' . str_replace(' ','',$single_cat->name);
								}
								
							}
							
							$pintext= str_replace('[post_categories]' , $cat,$pintext);
							
						}
						
						
						//WOO category
						if( stristr($pintext, '[product_categories]') ){
							
							$cats = (get_the_terms($thepost->ID , 'product_cat'  ));
							
							
							$cat= '';//ini
							if ( is_array($cats) && isset($cats[0] ) ){
								foreach ($cats as $single_cat){
									$cat .= ' #' . str_replace(' ','',$single_cat->name);
								}
							}
							
							$pintext= str_replace('[product_categories]' , $cat,$pintext);
							
						}
						
						//get other fields
						if(stristr($pintext,'[')){
							
							preg_match_all('{\[(.*?)\]}' , $pintext , $shortMatchs);
							$foundShortCodes = $shortMatchs[1];
							
							foreach ($foundShortCodes as $foundShortCode){
								
								if( stristr( $foundShortCode , 'pa_' ) && function_exists('wc_get_product_terms')  ){
									 
									$shortValue = implode( ',' ,  wc_get_product_terms(  $post_id ,  $foundShortCode , array( 'fields' => 'names' ) )) ;
								 
								}else{
									$shortValue = get_post_meta($post_id,$foundShortCode,1);
								}
								
								$pintext = str_replace(  '['.$foundShortCode.']' , $shortValue , $pintext );
							
							}
							
						}
						
						//pagination?
						$pin_link = wp_pinterest_pin_link_paginate( get_permalink( $post_id ) , $post->post_content , $pin_image );
						 
						$pinstatus=$pinterest->pinterest_pin($tocken,$pin_board,$pintext, $pin_link ,$pin_image,$wp_pinterest_options,$post_id);
						
						if($pinstatus == true){
							$pins=get_post_meta($post_id,'pins',1);
							if(! is_array($pins)) $pins = array();
							$pins[]=$pin_image;
							update_post_meta($post_id,'pins', array_unique($pins));
						}
			
						if($min_try_val >= 2 && $pinstatus == false) $pinterest->log('Skipping image','Due to 3 failed pin trials for the image, It will be skipped from pinning and removed from queue.');
						
						if($pinstatus == true || $min_try_val >= 2){ 	
							$pin_images=array_filter($pin_images);
							//clear queue
							if(count($pin_images) == 1){
								//last image delete all
								delete_post_meta($post_id,'pin_images');
							}else{
								//delete this image only 
								foreach($pin_images as $pinimg){
									if($pinimg != $pin_image) $newpinimages[]=$pinimg;
								}
								
								update_post_meta($post_id, 'pin_images', $newpinimages);
								
							}
						}
					
			
				}//trim(tocken)
			}//COMPLETE DATA
			
			break; 
		}
	}else{
		echo '<br>No queued posts available';
	}
	
	 
	@update_option('wp_pinterest_p', $post_id);
	
	wp_reset_postdata();
	
	
}

function wp_pinterest_automatic_pin_function_wrap(){
	
	$wp_pinterest_options=get_option('wp_pinterest_options',array());
	
	if(in_array('OPT_EXTERNAL_CRON', $wp_pinterest_options)){
		return;
	}
	
	wp_pinterest_automatic_pin_function();
	 
	
}