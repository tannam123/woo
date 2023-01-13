<?php
	/*
	 Plugin Name:Wordpress Pinterest Automatic
	 Plugin URI: http://codecanyon.net/item/pinterest-automatic-pin-wordpress-plugin/2203314?ref=ValvePress
	 Description: Pin Images from your posts to your Pinterest account.
	 Version: 4.14.4
	 Author: ValvePress
	 Author URI: http://codecanyon.net/user/ValvePress/portfolio?ref=ValvePress
	 */
	
	/*  Copyright 2012-2021  Wordpress Pinterest Automatic  (email : sweetheatmn@gmail.com) */
	 
	
	// UPDATES
	$licenseactive=get_option('wp_pinterest_automatic_license_active','');
	if(trim($licenseactive) != ''){
	
		//fire checks
		require 'plugin-updates/plugin-update-checker.php';
		$wp_pinterest_automatic_UpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://deandev.com/upgrades/meta/wp-pinterest-automatic.json',
				__FILE__,
				'pinterest-auto'
		);
	
		//append keys to the download url
		$wp_pinterest_automatic_UpdateChecker->addResultFilter('wp_pinterest_automatic_addResultFilter');
		function wp_pinterest_automatic_addResultFilter($info){
			
			$wp_pinterest_automatic_license = get_option('wp_pinterest_automatic_license','');
	
			if(isset($info->download_url)){
				$info->download_url = $info->download_url . '&key='.$wp_pinterest_automatic_license;
			}
			return $info;
		}
	}
	
	// PIN SCHEDULE PROCESS
	require_once 'pin_schedule.php';
	
	//AJAX UPDATE PINNED
	require_once 'pajax.php';
	
	//PIN THEM BULK ACTION
	require_once 'pactions.php';
	
	
	
	/* Add a new meta box to the admin menu. */
	add_action( 'admin_menu', 'wppinterest_create_meta_box' );
	add_action('admin_print_scripts-' . 'post-new.php', 'wp_pinterest_automatic_admin_scripts_new');
	add_action('admin_print_scripts-' . 'post.php', 'wp_pinterest_automatic_admin_scripts2');
	add_action('admin_print_scripts-' . 'edit.php', 'wp_pinterest_automatic_admin_edit');
	add_action('admin_print_scripts'  , 'wp_pinterest_automatic_admin_icon');
	
	//icon font
	function wp_pinterest_automatic_admin_icon(){
		
		wp_enqueue_style(
		'wp_pinterest_automatic_icon_font',plugins_url( '/css/style-generic.css' , __FILE__ )
		);
		
	}
	
	//bulk pin scripts
	function wp_pinterest_automatic_admin_edit(){
		
		 
		
		if(isset($_GET['post_type'])){
			
		 
			$wp_pinterest_types=get_option('wp_pinterest_types',array('post','page'));
			
			if(in_array($_GET['post_type'], $wp_pinterest_types)){
				//valid type 
				
			}else{
				
				return ;
				
			}
		}
		
		wp_enqueue_script(
		'wp_pinterest_automatic_bulk_pin',plugins_url( '/js/bulk-pin.js' , __FILE__ )
		);
	}
	
	//create metabox
	function wppinterest_create_meta_box() {
	
		$wp_pinterest_options=get_option('wp_pinterest_options',array());
		
		//hide from contributors and show to admin only
		if(in_array('OPT_ADMIN_ONLY', $wp_pinterest_options)){
			if(!current_user_can('administrator')){
				return;
			}	
		}
		
		
		
			$wp_pinterest_types=get_option('wp_pinterest_types',array('post','page'));
			foreach($wp_pinterest_types as $post_type){
				add_meta_box( 'wppinterest-meta-boxes', 'Pinterest Automatic', 'wppinterest_meta_boxes', $post_type, 'side', 'high' );
			
			}
			
		
	}
	
	//metabox function
	function wppinterest_meta_boxes(){
		require_once('pmetabox.php');
	}
	
	
	//scripts for queue page
	function wp_pinterest_automatic_queue_scripts(){
		wp_enqueue_style( 'wp_pinterest_automatic-admin-style', plugins_url('css/style.css', __FILE__) );
		wp_enqueue_script( 'wp_pinterest_automatic-queue-script', plugins_url('js/queue.js' ,  __FILE__  ) , array() , '1.0.3' );
		
	}
	 
	
	// scripts for options page
	function wp_pinterest_automatic_admin_scripts(){
		//jquery
		wp_enqueue_script('jquery');
	
		 
	 
		
		//jquery otions
		wp_enqueue_script(
					'wp_pinterest_automatic_jquery_options',plugins_url( '/js/options.js' , __FILE__ ),array(),'2.2.0'
		);
	
	
		//styles
		wp_enqueue_style( 'wp_pinterest_automatic-options-style', plugins_url('css/options.css', __FILE__) );
	
	
	
	}
	
	//scripts for log page
	function wp_pinterest_automatic_admin_scripts_log(){
		
		 
		//jquery

		wp_enqueue_script('jquery');

	

		//jquery tools

		wp_enqueue_script(

				'wp_pinterest_automatic_jquery_tools',plugins_url( '/js/jquery.tools.js' , __FILE__ )

		);

	

		 

	

		//jquery main

		wp_enqueue_script('wp_pinterest_automatic_jquery_main',plugins_url( '/js/main_log.js' , __FILE__ ));

		 
		
		wp_enqueue_style( 'wp_pinterest_automatic-admin-style', plugins_url('css/style.css', __FILE__) );

		

		

		wp_enqueue_style( 'wp_pinterest_automatic-admin-style2', plugins_url('css/uniform.css', __FILE__) );

		

	}
	
	
	//post.php scripts
	function wp_pinterest_automatic_admin_scripts2(){
	
		//jquery
		wp_enqueue_script('jquery');
	 
		//jquery main
		wp_enqueue_script(
					'wp_pinterest_automatic_jquery_main',plugins_url( '/js/main.js' , __FILE__ ) , array() ,'1.2.0'
		);
	
		wp_enqueue_style( 'wp_pinterest_automatic-admin-style', plugins_url('css/style.css', __FILE__) );
			
	}
	
	//post-new.php scripts
	function wp_pinterest_automatic_admin_scripts_new(){ 
		//jquery
		wp_enqueue_script('jquery');
	
		//jquery main
		wp_enqueue_script(
				'wp_pinterest_automatic_jquery_main-new',plugins_url( '/js/main.js' , __FILE__ ) , array() ,'1.1.0'
		);
	
		wp_enqueue_style( 'wp_pinterest_automatic-admin-style', plugins_url('css/style.css', __FILE__) );
	}
	
	//function select 
	if(! function_exists('wp_pinterest_automatic_opt_selected')){
	
		function wp_pinterest_automatic_opt_selected($src,$val){
			echo 'src='.trim($src) . ' dest='.trim($val);
			if (trim($src) === trim($val)) {
					
				echo ' selected="selected" ';
			}
				
		}
	}
	
	// adding menu to dashboard
	if(is_admin())
	{
		add_action('admin_menu', 'wppinterest_automatic_init');
	}
	
	//function to list catgory with childs
	function wp_pinterest_automatic_list_cat($cat,$selected,$tax,$spce=''){
	
		?>
			<option  value="<?php echo $cat->term_id ?>" <?php wp_pinterest_automatic_opt_selected($cat->term_id,$selected) ?>><?php echo $spce . $cat->cat_name ?></option>
		<?php
	
		if($cat->term_id != 0){
			//get child categories
			$childs =  get_categories ( array (
													'hide_empty'               => 0 ,
													'parent'                   =>$cat->term_id,
				 									'taxonomy'                 => $cat->taxonomy,				
		    ) );
		
			foreach($childs as $child){
			  
					 wp_pinterest_automatic_list_cat($child,$selected,$child->taxonomy,$spce . '—');
			 		
			}
		}
		
	}
	
	function wppinterest_automatic_init()
	{
		add_menu_page('Pinterest Automatic', 'Pinterest<br> <small>Automatic</small>', 'administrator', 'wppinterestautomaticq', 'wppinterestautomaticq', 'dashicons-test-wp-pi-social-pinterest', 1000);
		
		
		$page_hook_suffix=add_submenu_page( 'wppinterestautomaticq', 'Pinning queue', ' Pinning queue', 'administrator', 'wppinterestautomaticq', 'wppinterestautomaticq' );
		add_action('admin_print_scripts-' . $page_hook_suffix, 'wp_pinterest_automatic_queue_scripts');
		
		$hook=add_submenu_page('wppinterestautomaticq', 'Pinterest automatic action log', 'Action Log', 'manage_options', 'wppinterestautomatic_log','wppinterestautomatic' );
		
		$page_hook_suffix=add_submenu_page( 'wppinterestautomaticq', 'Settings', ' Settings', 'administrator', 'wppinterestautomatics', 'wppinterestautomatics' );
		
		
		add_action('admin_print_scripts-' . $page_hook_suffix, 'wp_pinterest_automatic_admin_scripts');
		add_action('admin_print_scripts-' . $hook, 'wp_pinterest_automatic_admin_scripts_log');
		 
		
	}
	
	//check curl , conflict with wp pinner
	function wp_pinterest_automatic_admin_notice() {

		
		if( ! function_exists('curl_init')){
		
		?>
	<div class="error">
		<p><?php echo 'cURL is not installed . it should be installed for <strong>"Wordpress Pinterest Automatic"</strong> to work !'; ?></p>
	</div>
	<?php
		
		}
		
		if(  function_exists('wppinner_publishpost_data')){
			?>
	<div class="error">
		<p><?php echo 'WP Pinner plugin should be deactivated for <strong>"Wordpress Pinterest Automatic"</strong> to work !'; ?></p>
	</div>
	<?php

			}
	    

	}

	add_action( 'admin_notices', 'wp_pinterest_automatic_admin_notice' );
	
	function wppinterestautomaticq(){
		require_once 'pin_queue.php';
	}
	
	
	function wppinterestautomatics(){
		require_once(dirname(__FILE__).'/options.php');
	}
	
	/* Saves the meta box data */
	add_action( 'save_post', 'wp_pinterest1_save_meta_data' );
	
	$wp_pinterest_types=get_option('wp_pinterest_types',array('post','page'));

	foreach($wp_pinterest_types as $post_type){

		add_action('publish_'.$post_type,'wp_pinterest_publish');

	}
	
	function wp_pinterest_publish($post_id){
		
		
		//check if scheduled ?
		$pin_images=get_post_meta($post_id,'pin_images',1);
		if(0){
			
		
		}else{//is_array
		
		
		$publish='';
		if( isset($_POST['post_status'] ) ) $publish=$_POST['post_status'];
		
		$action = 'none';
		
		if(isset($_POST['action']))
		$action = $_POST['action'];
		
		
		 
		//check if instant publish 
		if ( trim($publish) == 'publish' && ! isset($_POST['post_date']) && $action != 'press-this-save-post' ){
	
				//echo 'instant publish ';
			
				$pin_options=$_POST['pin_options'];
					
				if(is_array($pin_options)){
	 
					$pimages=array();
					@$pimages=$_POST['pin_images'];
					if( in_array('OPT_PIN',$pin_options) & is_array($pimages) ){
						
						 
						
						global $post;
						
					 
					  
						require_once(str_replace('pinterest-auto.php','p_core.php',__FILE__));
						
						$wp_pinterest_automatic_session = get_option('wp_pinterest_automatic_session','');

						$PIN_BOARD=$_POST['PIN_BOARD'];
						$pin_board=$_POST['PIN_BOARD'];
						$pin_text=$_POST['pin_text'];
						$pin_title=$_POST['pin_title'];
						
						if( trim($wp_pinterest_automatic_session) != '' && trim($pin_text)  != ''){
							
							$pin_text=$_POST['pin_text'] . ':separator:'.$_POST['pin_title'] ;
							$pin_board=$_POST['PIN_BOARD'];
							$pin_images=$_POST['pin_images'];
							$pin_images=array_filter($pin_images);
							$post_title=  $post->post_title ;
							$wp_pinterest_options=get_option('wp_pinterest_options',array());
							
							//CTT CHECK
							if(in_array('OPT_CTT', $wp_pinterest_options)){
							
								$default_board=get_option('wp_pinterest_board','');
							
								$wp_pinterest_automatic_wordpress_tags = get_option ( 'wp_pinterest_automatic_wordpress_tags', array ());
								$wp_pinterest_automatic_pinterest_tags = get_option ( 'wp_pinterest_automatic_pinterest_tags', array () );
							
								//check if this is a default board or user selected
								if(trim($default_board) == $pin_board){
							
									//get categories
									$tax_txt=get_option('wp_pinterest_automatic_tax_tags','post_tag,product_tag');
										
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
									foreach($wp_pinterest_automatic_wordpress_tags as $tag ){
							
										$tagApplies = false;
										
										foreach ($tax as $singleTax){
										
											if( has_term($tag,$singleTax,$post_id) ){
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
							
							}//ctt check
							
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
							 
							
							
							//add other pins to the queue
							if( count($pin_images) > 1 || in_array('OPT_QUEUE_ONLY', $wp_pinterest_options) ){
								$flag=0;
							
								//deleting first one
								
								foreach($pin_images as $pin_image){
									if($flag != 0  ){
										$schedulespinimages[]=$pin_image;
									}else{
										$flag =1;
										
										//add first image also if add all to queue
										if(in_array('OPT_QUEUE_ONLY', $wp_pinterest_options)){
											$schedulespinimages[]=$pin_image;
										}
										
									}
								}
							 	
								//save pin variables
								$pin_text=$_POST['pin_text']  . ':separator:' .$_POST['pin_title']  ;
								
								$post_title=$_POST['post_title'];
								$pin_alt=$_POST['wp_pinterest_alts'];
								@$images_index=$_POST['wp_pinterest_index'];
									
								update_post_meta($post_id,'pin_images',$schedulespinimages);
								update_post_meta($post_id,'pin_text',$pin_text);
								update_post_meta($post_id,'pin_board',$pin_board);
								update_post_meta($post_id,'pin_alt',$pin_alt);
								update_post_meta($post_id,'pin_index',$images_index);
								update_post_meta($post_id,'pin_try',0);
									
								//building image trials array
								$firstitm = 0;
								foreach ( $pin_images as $pin_image ) {
									if ($firstitm != 0) {
										$images_try [md5 ( $pin_image )] = 0;
									} else {
										$firstitm = 1;
									}
								}
								
								update_post_meta ( $post_id, 'images_try', $images_try );
							}
							
							
							//check if all items added to the queue then return 
							if(in_array('OPT_QUEUE_ONLY', $wp_pinterest_options)){

								return;
									
							}elseif(count($pin_images) == 1){
								//delete any scheduled item may be from draft posts
								delete_post_meta($post_id, 'pin_images');
							}
							 
							
							$pinterest=new wp_pinterest_automatic;
							$tocken=$pinterest->pinterest_login();
							if(trim($tocken) != ''){
								
								
								//valid login let's pin
								$instantpinimages[]=$pin_images[0];
								foreach($instantpinimages as $img){
									$sp= new WPASpintax;
									$pintext=$sp->spin($pin_text);
	
									if(trim($pintext == '')){
										$pintext= $pin_text ;
									}
	
									$thepost=get_post($post_id);
									 
									$user=get_userdata( $thepost->post_author  );
									$username=$user->display_name; 
									@$images_alt=$_POST['wp_pinterest_alts'];
									@$images_index=$_POST['wp_pinterest_index'];
							 
									
									$i=0;
									foreach($images_index as $image){
										if($img == $images_index[$i]){
											break;
										}
										$i++;
									}
									
									
									
							  		//excerpt generation
									if( stristr($pintext, 'post_excerpt') ) {
										
										if( isset($_POST) && array_key_exists('yoast_wpseo_metadesc',$_POST) &&  trim($_POST['yoast_wpseo_metadesc']) != ''){
											$thepost->post_excerpt = $_POST['yoast_wpseo_metadesc'];
										}
										
										if(trim($thepost->post_excerpt) == '' && defined( 'WPSEO_FILE' ) ){
											$possible_excerpt = get_post_meta($thepost->ID,'_yoast_wpseo_metadesc',1);
											
											if(trim($possible_excerpt) != '')
											$thepost->post_excerpt = $_POST['yoast_wpseo_metadesc'];
											
										}
										

										if( isset($_POST) && array_key_exists('aiosp_description',$_POST) &&  trim($_POST['aiosp_description']) != ''){
											$thepost->post_excerpt = trim($_POST['aiosp_description']);
										}
										
										
										if( trim($thepost->post_excerpt) == ''   ){
											  
											$wp_pinterest_automatic_excerpt=   get_option('wp_pinterest_automatic_excerpt','150');
											   
												$new_excerpt = wp_pinterest_substr(  wp_pinterest_texturize($thepost->post_content) , 0,$wp_pinterest_automatic_excerpt);
											  
											
											if(trim($new_excerpt) != '') {
												$new_excerpt.= '...';
											}
												
											$thepost->post_excerpt = $new_excerpt;
										}
										
										
									}
									
									
									$pintext=str_replace('[post_title]',$post_title,$pintext);
									$pintext=str_replace('[post_excerpt]' ,  strip_tags($thepost->post_excerpt) ,$pintext);
									
									if( stristr( $pintext,'post_content' ) )
										$pintext=str_replace('[post_content]' , wp_pinterest_texturize($thepost->post_content) ,$pintext);
									
									$pintext=str_replace('[post_author]', $username ,$pintext);
									$pintext=str_replace('[post_link]', get_permalink( $post_id ) ,$pintext);
								 	@$pintext=str_replace('[image_alt]',  $images_alt[$i] ,$pintext);
									
								 	
								 	
								 	//get tags
								 	if(stristr($pintext, '[post_tags]')){
								 		 
								 		//get tags
								 		///$taxonomies = get_taxonomies(array('public' => true ,'hierarchical' => false , 'show_ui' => true),'names');
								 		$tax_txt=get_option('wp_pinterest_automatic_tax_tags','post_tag,product_tag');
								 		$taxonomies_raw = explode( ',' , $tax_txt );
								 		
								 		$taxonomies = array();
								 		foreach ( $taxonomies_raw as $new_tax){
								 			
								 			 if( taxonomy_exists($new_tax) )  $taxonomies[ $new_tax ] = $new_tax;
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
								 			
								 			$tag_text = $tag_text .' #'. str_replace(' ','',$tag->name);
								 			$i++;
								 			
								 			if($i == $tagLimit) break;
								 			
								 		}
								 		 
								 		$pintext=str_replace('[post_tags]', $tag_text ,$pintext);
								 		   
								 	}
								 	
								  
							 
								 	//get category
								 	if( stristr($pintext, '[post_category]') ){
								 		$cats = (get_the_category($thepost->ID));
								 		$cat= '';//ini
								 		if ( is_array($cats) && isset($cats[0] ) ){
								 			$cat = $cats[0];
								 			$cat = $cat->name;
								 		} 
								 	
								 		$pintext= str_replace('[post_category]' , $cat,$pintext);
								 	
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
								 	
								 	
								 	
								 	//WOO categories
								 	if( stristr($pintext, '[product_cat]') ){
								 		
								 		$cats = (get_the_terms($thepost->ID , 'product_cat'  ));
								 		
								 		
								 		$cat= '';//ini
								 		if ( is_array($cats) && isset($cats[0] ) ){
								 			$cat = $cats[0];
								 			$cat = $cat->name;
								 		}
								 		
								 		$pintext= str_replace('[product_cat]' , $cat,$pintext);
								 		
								 	}
							 
								 	
								 	// Product price if applicable
								 	if(stristr($pintext, '[product_price]')){
								 		$productPrice = get_post_meta($post_id,'_price',1);
								 		$pintext=str_replace('[product_price]', $productPrice ,$pintext);
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
								 	$pin_link = wp_pinterest_pin_link_paginate( get_permalink( $post_id ) , $thepost->post_content , $img );
								 	 
								 	// Start Pinning
								
								 	
								 	$pin=$pinterest->pinterest_pin($tocken,$pin_board, $pintext ,$pin_link ,$img,$wp_pinterest_options,$post_id);
								 
									if($pin === true){
										//successfull pin 
										
										$pins=get_post_meta($post_id,'pins',1);
										if(! is_array($pins)) $pins = array();
										$pins[]=$img;
										update_post_meta($post_id,'pins',$pins);
										
										
									}
									 
								}//foreach
							}//trim(tocken)
						}//complete data
					}// if opt_pin
				}// pin_options
	
			}elseif(trim($publish) == '' || $action == 'press-this-save-post' ){//no instant publish + no scheduled images
				
				//check if bot mode enabled 
				$pin_options=get_option('wp_pinterest_options',array());
				
				if(in_array('OPT_BOT', $pin_options)){
	
				// now it will be either manually scheduled or posted with a bot 
				$manual = get_post_meta($post_id,'pin_manual',1);
				
				if(trim($manual) != ''){
					//now it is not manually scheduled i.e a bot post
					
					 
				}else{
					
					 	
						//get post variables like title and content 
						$post=get_post($post_id);
				 
						$wp_pinterest_types=get_option('wp_pinterest_types',array('post','page'));
						$post_type=$post->post_type;
						
						 
						//process post from bots with type post only (no custom post types)
						if( in_array($post_type ,$wp_pinterest_types)){
	
							//now a bot post let's record it 
							update_post_meta($post_id, 'wp_pinterest_automatic_bot', 1);
							update_post_meta($post_id, 'wp_pinterest_automatic_bot_c', 't');
							return;
	
							
						}//post only
						
					 
						 				
				}//bot post
				
			   }//bot mode ?
			}//no instant publish 
			
		}//not scheduled	
		
	}//end function
	
	function wp_pinterest1_save_meta_data( $post_id ) {
	
	 
	
	
		if ( !wp_is_post_revision( $post_id ) ) {
	
			/*
			echo '<pre>';
			print_r($_POST);
			exit;
			*/
	
	 		$manual='';
	 
	 		if(isset($_POST['pin_manual']))
			@$manual=$_POST['pin_manual'];
			 
			if(trim($manual) != '')
			update_post_meta($post_id,'pin_manual',$manual);
	
			//---------
			//return ;
			$publish='';
			if(isset($_POST['post_status']))
			@$publish=$_POST['post_status'];
				
			if( (trim($publish) == 'publish' &&  isset($_POST['post_date']) ) || $publish == 'draft'  || $publish == 'future'  ){
	
				
				@$pin_options=$_POST['pin_options'];
	
				if(is_array($pin_options)){
						
		 		if( in_array('OPT_PIN',$pin_options) & is_array($_POST['pin_images']) ){
		 				
		 			//save pin variables
			 		$pin_text=$_POST['pin_text'];
			 		$pin_board=$_POST['PIN_BOARD'];
			 		$pin_images=$_POST['pin_images'];
			 		$post_title=$_POST['post_title'];
		 			$pin_alt=$_POST['wp_pinterest_alts'];
		 			@$images_index=$_POST['wp_pinterest_index'];
		 			
		 			require_once(str_replace('pinterest-auto.php','p_core.php',__FILE__));
		 			$pinterest=new wp_pinterest_automatic();
		 			$pinterest->log('Scheduling','Scheduling '.count($pin_images).' pins for post with id {'.$post_id.'}');
			 		 
			 		update_post_meta($post_id,'pin_images',$pin_images);
			 		update_post_meta($post_id,'pin_text',$pin_text);
			 		update_post_meta($post_id,'pin_board',$pin_board);
			 		update_post_meta($post_id,'pin_alt',$pin_alt);
			 		update_post_meta($post_id,'pin_index',$images_index);
			 		update_post_meta($post_id,'pin_try',0);
			 		
			 		//building image trials array 
				 	foreach($pin_images as $pin_image){
				 		$images_try [md5($pin_image)] = 0 ; 
				 	}	
				 	
				 	update_post_meta($post_id,'images_try',$images_try);
			 		
			 		return;
			 		 
	
			 		}//foreach
			 		 
		 		}//if pin_opt
				}//is_array
	
				//echo 'schedule';
			}else{
	
				//echo 'ignonre';
			}
				
				
	
	
				
				
				
			//omak();
	
		 
	}// end function
	
	
	/**
	 * custom request for fetch boards
	 */
	function wp_pinterest_automatic_parse_request($wp) {
	
		// only process requests with "my-plugin=ajax-handler"
		if (array_key_exists('wp_pinterest_automatic', $wp->query_vars)) {
			 
			if($wp->query_vars['wp_pinterest_automatic'] == 'boards'){
	
				require_once('p_core.php');
				exit;
	
			}elseif($wp->query_vars['wp_pinterest_automatic'] == 'settings'){
	
				require_once('process_form.php');
				exit;																	
			}elseif($wp->query_vars['wp_pinterest_automatic'] == 'cron'){
				
				wp_pinterest_automatic_pin_function();
				exit;
			}elseif($wp->query_vars['wp_pinterest_automatic'] == 'show_ip'){
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER,0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($ch, CURLOPT_TIMEOUT,20);
				curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
				curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
				//curl_setopt($ch, CURLOPT_COOKIEJAR , "cookie.txt");
				
				//curl get
				$x='error';
				$url='http://www.whatismyip.com/';
				
				curl_setopt($ch, CURLOPT_HTTPGET, 0);
				curl_setopt($ch, CURLOPT_URL, trim($url));
				
				$exec=curl_exec($ch);
				$x=curl_error($ch);
					
				echo $exec.$x;
				exit;
				
				
			}elseif($wp->query_vars['wp_pinterest_automatic'] == 'show_post'){
	
				if( isset( $_GET['pid'] ) &&  ($_GET['front'] == 1 )   ){
					$pid=$_GET['pid'];
					$post_type = $_GET['post_type'];
					
					if(trim($post_type) == '') $post_type = 'post';
					
					$the_query = new WP_Query ( 'p='.$pid.'&post_type='.$post_type );
					
					while ( $the_query->have_posts () ) {
					
						$the_query->the_post ();

						// Thrive content builder integration
						if(function_exists('tve_editor_css')){
							add_filter( 'the_content', 'tve_editor_content' );
						}
						
						global $pages;
						
						if(isset($pages[0])) $pages[0] = str_replace('<!--more','<!--amore' , $pages[0]);
						
						
						$postContent = apply_filters( 'the_content', get_the_content() );
						 
						
						//VC
						if( strpos( $postContent , '[vc_') ){
							
							WPBMap::addAllMappedShortcodes();
							$postContent = apply_filters( 'the_content', get_the_content() );
							
						}

						if(function_exists('et_divi_load_fonts')){
							//$postContent =  html_entity_decode( $postContent );
							$postContent = preg_replace('{\[et_pb_image.*?src="(.*?)".*?\]}' , "<img src='$1'/>" , get_the_content() );
							 
						}
						

						//remove noscripts tags for justified image grid 
						$postContent = preg_replace('{<noscrip.*?>}s', '', $postContent);
						$postContent = preg_replace('{</noscript>}s', '', $postContent);
						$postContent = str_replace('data-src-fg' , 'src' , $postContent);
						 
						echo  (($postContent ));
						
						
					}  
					
				}
				
				if( isset( $_GET['pid'] ) &&  ($_GET['uploaded'] == 1 )   ){
					
					 
					$media = get_attached_media( 'image' , $_GET['pid'] );
					
					 
					if(count($media) > 0){
						
						foreach($media as $smedia){
							
							$alt  = get_post_meta($smedia->ID , '_wp_attachment_image_alt', true);
							 
							echo  '<img    alt="'. $alt .'"    src="' . $smedia->guid . '" />' ;
						}
						
					}
					
				}
				
	
	 			exit;
			}
	
		}
	}
	add_action('parse_request', 'wp_pinterest_automatic_parse_request');
	
	
	
	function wp_pinterest_automatic_query_vars($vars) {
		$vars[] = 'wp_pinterest_automatic';
		return $vars;
	}
	add_filter('query_vars', 'wp_pinterest_automatic_query_vars');
	
	//support widget 
	require_once('widget.php');
	
	//rating 
	require_once('rating.php');
	
	//update 
	require_once('updated.php');
	
	
	/* ------------------------------------------------------------------------*
	 * Add Table when First activation
	 * ------------------------------------------------------------------------*/
	register_activation_hook( __FILE__, 'create_table_wp_automatic_pinterest' );
	/* ------------------------------------------------------------------------*
	 *Create a new table Comments
	 * ------------------------------------------------------------------------*/
	function create_table_wp_automatic_pinterest()
	{
		
		//clear the hook for setting it to 3 minutes other than one minute
		wp_clear_scheduled_hook( 'wp_pinterest_automatic_pin_hook' );
		
		global $wpdb;
		//comments table
		if(!exists_table_wp_automatic_pinterest('wp_pinterest_automatic')){
			$querys="SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";
				CREATE TABLE IF NOT EXISTS `wp_pinterest_automatic` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `action` varchar(50) NOT NULL,
	  `data` text NOT NULL,
	  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `camp` varchar(20) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=483 ;
				
				";
			//executing quiries
			$que=explode(';',$querys);
			foreach($que  as $query){
				if(trim($query)!=''){
					$wpdb->query($query);
				}
			}
		}
		
		//remove last run 
		delete_option('wp_pinterest_last_run');
		
	}
	
	function exists_table_wp_automatic_pinterest($table){
		global $wpdb;
		$rows = $wpdb->get_row('show tables like "'.$table.'"', ARRAY_N);
		
		if( ! isset($rows)) return false;
		
		return (count($rows)>0);
	}
	
	//clear schedule on deactivation
	register_deactivation_hook( __FILE__, 'wp_automatic_pinterest_deactivation' );
	/**
	 * On deactivation, remove all functions from the scheduled action hook.
	*/
	function wp_automatic_pinterest_deactivation() {
		wp_clear_scheduled_hook( 'wp_pinterest_automatic_pin_hook' );
	}
	
	/**
	 * Convert html to plain text for pin description
	 */
	function wp_pinterest_texturize($text){
		
		$text = trim( strip_tags(strip_shortcodes( $text ))) ;
		$text = str_replace("\n",' ',$text);
		$text = str_replace("\t" , " " , $text );
		$text =   preg_replace("{\s+}s" , ' ' , $text);

		return $text;
		
	}
	
	/**
	 * Add pagination support for the image
	 * @param unknown $link
	 * @param unknown $content
	 *@param $img the image URL
	 */
	function wp_pinterest_pin_link_paginate($link,$content,$img){
		 
		if( stristr($content,'<!--nextpage-->') ){
			
			$pages = explode( '<!--nextpage-->' , $content   );
			 
			$i=1;
			$page_index = 1; //ini
			foreach($pages as $page){
				
				if(stristr($page,$img)){
					 
					$page_index = $i;
					break;
				}
				
				$i++;
			}
			
			if($page_index > 1){
				if(stristr($link,'?p')){
					$link = $link .= "&page=".$page_index;
				}else{
					$link = preg_replace( '{/$}' ,'' , trim($link));
					$link = $link . '/' . $page_index;
				}
			}
			 
			
			
		}
		
		return $link;
		
	}
	
	/**
	 * substr non-latin support
	 */
	function wp_pinterest_substr( $text , $start , $end ){
		
		if(function_exists('mb_substr')){
			$truncated = mb_substr(  $text , $start ,$end);
		}else{
			$truncated = substr(  $text , $start ,$end);
		}
		
		return $truncated;

	}
	
	//Log 
	require_once 'plog.php';
	
	//license
	require_once 'plicense.php';
	
	
	