	<style>
	 
	h3.hndle{
			padding: 20px 8px !important;
	}
	 
	 </style>
	
	<div class="wrap log_wrap">
	
	<form  method="post" novalidate="" autocomplete="off">
	
	<?php
	
	//license ini
	$licenseactive=get_option('wp_pinterest_automatic_license_active','');
	$licenseactive=true;

	
	//purchase check 
	if(isset($_POST['wp_pinterest_automatic_license']) && trim($licenseactive) == '' ){
	
		//save it
		update_option('wp_pinterest_automatic_license' , $_POST['wp_pinterest_automatic_license'] );
		
		//activating
		//curl ini
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT,20);
		curl_setopt($ch, CURLOPT_REFERER, 'http://www.bing.com/');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Many login forms redirect at least once.
		//curl_setopt($ch, CURLOPT_COOKIEJAR , "cookie.txt");
		
		//curl get
		$x='error';
	
		//change domain ?
		$append='';
		
		if( isset($_POST['wp_pinterest_options']) && in_array('OPT_CHANGE_DOMAIN', $_POST['wp_pinterest_options']) ){
			$append='&changedomain=yes';
		}
		
		$proxy = false;
		
		if($proxy == false){
			//$url='https://deandev.com/license/index.php?itm=2203314&domain='.$_SERVER['HTTP_HOST'].'&purchase='.trim($_POST['wp_pinterest_automatic_license']).$append;
		}else{
			//$url='http://deandev-proxy.appspot.com/license/index.php?itm=2203314&domain='.$_SERVER['HTTP_HOST'].'&purchase='.trim($_POST['wp_pinterest_automatic_license']).$append;
		}
		
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_URL, trim($url));
		$exec=curl_exec($ch);
		$x=curl_error($ch);
		$resback=$exec;
		
		if(trim($exec) == ''){
			
			//$url='http://deandev-proxy.appspot.com/license/index.php?itm=2203314&domain='.$_SERVER['HTTP_HOST'].'&purchase='.trim($_POST['wp_pinterest_automatic_license']).$append;
			
			curl_setopt($ch, CURLOPT_HTTPGET, 1);
			curl_setopt($ch, CURLOPT_URL, trim($url));
			
			$exec=curl_exec($ch);
			$resback=$exec;
			$x=curl_error($ch);
		}
		
		$resarr=json_decode($resback);
		
		if(isset($resarr->message)){
			$wp_pinterest_active_message=$resarr->message;
			
			//activate the plugin
			update_option('wp_pinterest_automatic_license_active', 'active');
			update_option('wp_pinterest_automatic_license_active_date', time());
			$licenseactive=get_option('wp_pinterest_automatic_license_active','');
			
		}else{
			update_option('wp_pinterest_automatic_license_active', 'active');
			update_option('wp_pinterest_automatic_license_active_date', time());
			$licenseactive=get_option('wp_pinterest_automatic_license_active','');
		}

	}
	
	// SAVE DATA
	if (isset ( $_POST ['wp_pinterest_automatic_session'] )) {
		
		if( (integer)$_POST['wp_pinterest_automatic_interval_min'] > 2 && (integer)$_POST['wp_pinterest_automatic_interval_max'] > 2 && (integer)$_POST['wp_pinterest_automatic_interval_max'] >= (integer)$_POST['wp_pinterest_automatic_interval_min']   ){
			//valid
			$_POST['wp_pinterest_automatic_interval_min'] = (integer)$_POST['wp_pinterest_automatic_interval_min'];
			$_POST['wp_pinterest_automatic_interval_max'] = (integer)$_POST['wp_pinterest_automatic_interval_max'];
			
		}else{
			$_POST['wp_pinterest_automatic_interval_min'] = 3 ;
			$_POST['wp_pinterest_automatic_interval_max'] = 7 ;
			
		}
		
		 
		
		foreach ( $_POST as $key => $val ) {
			
			if($key == "wp_pinterest_automatic_session"){
				$val = str_replace('"','',stripslashes($val));
			}
			
			update_option ( $key, $val );
		}
		echo '<div class="updated"><p>Changes saved</p></div>';
	}
	
	$dir = WP_PLUGIN_URL . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) );
	
 
	$wp_pinterest_default = get_option ( 'wp_pinterest_default', '[post_title]' );
	$wp_pinterest_default_title = get_option ( 'wp_pinterest_default_title', '[post_title]' );
	
	$wp_pinterest_default_more = get_option('wp_pinterest_default_more','Check more at [post_link]');
	$wp_pinterest_board = get_option ( 'wp_pinterest_board', '' );
	$wp_pinterest_options = get_option ( 'wp_pinterest_options', array (
			'OPT_CHECK',
			'OPT_PIN',
			'OPT_NO_DUPLICATE'
	) );
	$wp_pinterest_types = get_option ( 'wp_pinterest_types', array (
			'post',
			'page',
			'product' 
	) );
	$wp_pinterest_options = array_merge ( $wp_pinterest_options, $wp_pinterest_types );
	$wp_pinterest_options = implode ( '|', $wp_pinterest_options );
	$wp_pinterest_boards = get_option ( 'wp_pinterest_boards', array (
			'ids' => array (),
			'titles' => array () 
	) );
	
 
	
	$wp_pinterest_boards_ids = $wp_pinterest_boards ['ids'];
	$wp_pinterest_boards_titles = $wp_pinterest_boards ['titles'];
	$wp_pinterest_automatic_selector = get_option ( 'wp_pinterest_automatic_selector', '' );
	$wp_pinterest_automatic_tax = get_option('wp_pinterest_automatic_tax','category,product_cat');
	$wp_pinterest_automatic_tax_tags= get_option('wp_pinterest_automatic_tax_tags','post_tag,product_tag');
	$wp_pinterest_automatic_interval_min = get_option('wp_pinterest_automatic_interval_min','3');
	$wp_pinterest_automatic_interval_max = get_option('wp_pinterest_automatic_interval_max','5');
	$wp_pinterest_proxies = get_option('wp_pinterest_proxies','');
	$wp_pinterest_automatic_excerpt=get_option('wp_pinterest_automatic_excerpt','150');
	$wp_pinterest_automatic_interval_clear = get_option('wp_pinterest_automatic_interval_clear',7); 
	$wp_pinterest_search_replace = get_option('wp_pinterest_search_replace','');
	$wp_pinterest_search_replace_txt = get_option('wp_pinterest_search_replace_txt','');
	$wp_pinterest_search_replace_lnk = get_option('wp_pinterest_search_replace_lnk','');
	
	$wp_pinterest_automatic_interval_from_h  = get_option('wp_pinterest_automatic_interval_from_h','00');
	$wp_pinterest_automatic_interval_from_m  = get_option('wp_pinterest_automatic_interval_from_m','00');
	
	$wp_pinterest_automatic_interval_from_h_2  = get_option('wp_pinterest_automatic_interval_from_h_2','00');
	$wp_pinterest_automatic_interval_from_m_2  = get_option('wp_pinterest_automatic_interval_from_m_2','00');
	
	$wp_pinterest_automatic_interval_to_h  = get_option('wp_pinterest_automatic_interval_to_h','23');
	$wp_pinterest_automatic_interval_to_m  = get_option('wp_pinterest_automatic_interval_to_m','59');
	
	$wp_pinterest_automatic_interval_to_h_2  = get_option('wp_pinterest_automatic_interval_to_h_2','00');
	$wp_pinterest_automatic_interval_to_m_2  = get_option('wp_pinterest_automatic_interval_to_m_2','00');
	
	
	$wp_pinterest_automatic_param = get_option('wp_pinterest_automatic_param','');
	$wp_pinterest_excluded_post_category = get_option('wp_pinterest_excluded_post_category' ,array());
	
	$wp_pinterest_bot_num  = get_option('wp_pinterest_bot_num' ,1);
	$wp_pinterest_bot_num_q= get_option('wp_pinterest_bot_num_q' ,1);
	
	$wp_pinterest_pin_link_to = get_option('wp_pinterest_pin_link_to','post');
	$wp_pinterest_pin_link_to_link = get_option('wp_pinterest_pin_link_to_link','');
	
	$wp_pinterest_automatic_minwidth = get_option( 'wp_pinterest_automatic_minwidth' , '' );

	$wp_pinterest_automatic_minheight = get_option( 'wp_pinterest_automatic_minheight' , '' );
	
	$wp_pinterest_automatic_tag_limit  = get_option('wp_pinterest_automatic_tag_limit' , '') ;
	$wp_pinterest_automatic_session = get_option('wp_pinterest_automatic_session','');
	
	$wp_pinterest_stop = get_option('wp_pinterest_stop','');
	
	//Post types
	$post_types = get_post_types(array('public' => true));
	
	
	?>
	<h2>
		Pinterest Automatic Settings <input type="submit" class="button-primary" value="Save Changes" name="save">
	</h2>
	
	<p>Where the Pinterest account gets added, boards get fetched, default board and pin text get set, optionally set a category to board rules or a tag to board rule & more. </p> 
	
	<div class="metabox-holder columns-1" >
		<div style="" class="postbox-container" id="postbox-container-1">
			<div class="meta-box-sortables ui-sortable" id="normal-sortables">
	 
			
				<?php if(trim($licenseactive) != '') { ?>
			
	 
		<div class="postbox">
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			<h3 class="hndle">
				<span>Pinterest account & boards</span>
			</h3>
			<div class="inside">
				<table class="form-table">
							<tbody>
								
								<tr>
									<th scope="row"><label for="field-wp_pinterest_automatic_session"> Pinterest Session Cookie   </label></th>
									<td><input  class="widefat" value="<?php echo $wp_pinterest_automatic_session  ?>" name="wp_pinterest_automatic_session" id="field-wp_pinterest_automatic_session" required="required" type="text"><div class="description">Check <a target="_blank" href="http://valvepress.com/get-pinterest-session-cookie/">this tutorial</a> on know how to get this value.<br><br>Please don't log out of your Pinterest account for this session to stay alive. You can close the browser or turn off the computer but do not click the logout button.</div></td>
									
									
								</tr>
								
								
								
								<tr>
									<th scope="row"><label for="field-wp_pinterest_board"> Default Pin Board ? </label></th>
									<td><select name="wp_pinterest_board" id="field1zz" required="required">
							      		
							      		<?php
							      			
							      			$i = 0;
											foreach ( $wp_pinterest_boards_ids as $id ) {
												
										?>
							      		
										<option value="<?php echo $id ?>" <?php wp_pinterest_automatic_opt_selected( $id ,$wp_pinterest_board) ?>><?php echo $wp_pinterest_boards_titles[$i]?></option>
							      			
							      		<?php
							      			$i ++;
										 }	
										
										?> 
							      	</select>  <a class="button" id="get_boards"  href="<?php echo site_url('/?wp_pinterest_automatic=boards')  ?>"> fetch boards </a><img alt="" id="ajax-loadingimg" class="ajax-loading" src="images/wpspin_light.gif" style=" margin: 3px">
	
										<div class="description">Select which pin board will be the default one so it will be the destination board in case there was no specific chosen board.</div></td>
								</tr>
	
							</tbody>
				</table>
			</div>
		 </div>
	
		<div class="postbox">
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			
			<h3 class="hndle">
				<span>Pin options</span>
			</h3>
			
			<div class="inside">
		 			
		 			<table class="form-table">
							<tbody>
								
								<tr>
									<th scope="row"><label> Default Pin Title   </label></th>
									<td><input class="widefat" value="<?php echo stripslashes(htmlspecialchars($wp_pinterest_default_title, ENT_QUOTES, "UTF-8"))  ?>" name="wp_pinterest_default_title" id="field-wp_pinterest_default" required="required" type="text">
	
										 </td>
								</tr>
								
								<tr>
									<th scope="row"><label> Default Pin Text   </label></th>
									<td><input class="widefat" value="<?php echo stripslashes(htmlspecialchars($wp_pinterest_default, ENT_QUOTES, "UTF-8"))  ?>" name="wp_pinterest_default" id="field-wp_pinterest_default" required="required" type="text">
	
										<div class="description">
											Supported tags: <abbr title="Image alternative text">[image_alt]</abbr> , <abbr title="Post title">[post_title]</abbr> , <abbr title="the post excerpt">[post_excerpt]</abbr> , <abbr title="The post title">[post_author]</abbr> , <abbr title="the post url">[post_link]</abbr> , <abbr title="Post tags as pinterest hashtags">[post_tags]</abbr>, <abbr title="Post categories as hastags">[post_categories]</abbr>, <abbr title="Post first category">[post_category]</abbr>, <abbr title="Woo product first category">[product_cat]</abbr>, <abbr title="Woo product categories as hastags">[product_categories]</abbr> , <abbr title="Woo-Commerce product price if applicable">[product_price]</abbr><br><br>Spintax enabled: text can be added in spintax form like {awesome|nice|fine} where the final pin text will contain either "awesome" , "nice" or "fine".<br><br>Hint:[post_excerpt] tag gets replaced by "AIO SEO Pack plugin SEO Description" or "Yoast SEO Description" if exists.<br><br>add [customFieldName] if you want to get the value from this custom field
											
										</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Check more text   </label></th>
									<td><input class="widefat" value="<?php echo stripslashes($wp_pinterest_default_more)  ?>" name="wp_pinterest_default_more" id="field-wp_pinterest_default" required="required" type="text">
	
										<div class="description">
											This link will be appended to the pin text when the plugin pins images without a link back to the original post. this may happen because pins with a link back to the post are not unlimited but Pinterest limits the number of this type of pins from the same source, account, IP and other factors. <br>Supported tags:  <abbr title="the post url">[post_link]</abbr>  
										</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Upload images without a link back to the site? </label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_REGULAR" type="checkbox"> <span class="option-title">By default, the plugin pins images with a link back to the site. This option will make the pin gets posted without a link back exactly like if it was manually uploaded from your computer.</div></td>
								</tr>
								
								
								
								<tr>
									<th scope="row"><label>Tags</label></th>
									<td><input data-controls="tagfield" name="wp_pinterest_options[]"   value="OPT_TAG_LIMIT" type="checkbox"> <span class="option-title">Limit number of tags.</div>
									
										<div id	="tagfield" >	
	
											<input class="widefat" value="<?php echo $wp_pinterest_automatic_tag_limit  ?>" name="wp_pinterest_automatic_tag_limit" type="text">
											<div class="description">Number of tags replacing  [post_tags] in the pin text. </div>
	
										</div>	
									
									</td>
								</tr>
								
								
								<tr>
									<th scope="row"><label>Don't check if a full-size image really exists <br>(Not Recommended) </label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_FULL_SIZE" type="checkbox"> <span class="option-title">By default, if a thumbnail is being pinned, the plugin gets the full sized image URL and checks if it really exists in the server. Some servers don't allow checking for file existence so activate this option if you think full-size images exist but the plugin thinks they don't.</span></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Post types   </label></th>
									<td>
									<?php
									 
									
									foreach ( $post_types as $post_type ) {
										
										if($post_type != 'attachment'){
										
										?>
							  
												<input name="wp_pinterest_types[]" value="<?php echo $post_type ?>" type="checkbox"> <span class="option-title">
									       			 <?php echo $post_type ?> 
									                </span>
												  
											    <?php
										}
									}
									
									?>
															
									<div class="description">Choose which post types the plugin will support, so it shows it's pin box when editing posts from those post types.</div>
									</td>
	
									
								</tr>
								
							</tbody>
					</table>
					
		 	</div>
		</div>
	
		<div class="postbox">
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			
			<h3 class="hndle">
				<span>Pining box</span>
			</h3>
			
			<div class="inside">
	
		 			<table class="form-table">
							<tbody>
							
								<tr>
									<th scope="row"><label> Pin Box </label></th>
									<td><input name="wp_pinterest_options[]" id="field-wp_pinterest_options-1" value="OPT_PIN" type="checkbox"> <span class="option-title"> Expand pinning box in editing page </span></td>
								</tr>
	
								<tr>
									<th scope="row"><label> Auto check </label></th>
									<td><input name="wp_pinterest_options[]" id="field-wp_pinterest_options-1" value="OPT_CHECK" type="checkbox"> <span class="option-title"> Auto check the first image to be pinned </span></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Parse front-end </label></th>
									<td><input name="wp_pinterest_options[]"  value="OPT_FRONT" type="checkbox"> <span class="option-title"> Parse front end for images. <div class="description">If the images do not appear on the post editing page, Activate this option for the plugin to load them from the front-end. May be, the images get displayed using a shortcode or  not being displayed on the editing page.</div></span></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Capture images uploaded to the post </label></th>
									<td><input name="wp_pinterest_options[]"  value="OPT_UPLOADED" type="checkbox"> <span class="option-title"> Any uploaded image to the post. <div class="description">even if the uploaded image does not display in the post content, this option should capture all images uploaded to the post using the media upload button.</div></span></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Visible to admins only </label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_ADMIN_ONLY" type="checkbox"> <span class="option-title"> Activate this to show the pinning box to administrators only. </span></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Don't instantly pin</label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_QUEUE_ONLY" type="checkbox"> <span class="option-title">Tick this to add all selected post images to the queue. by default, the first one gets pinned directly if you select multiple images from the same post and the rest get queued. </span></td>
								</tr>
							
							</tbody>
					</table>	 			
		
		 	</div>
		</div>
	
		<div class="postbox">
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			
			<h3 class="hndle">
				<span>Bot posts automatic pinning</span>
			</h3>
			
			<div class="inside">
		 			
		 			<table class="form-table">
							<tbody>
								
								<tr>
									<th scope="row"><label> Auto Pin </label></th>
									<td><input name="wp_pinterest_options[]" id="field-wp_pinterest_options-1" value="OPT_BOT" type="checkbox"> <span class="option-title"> Auto pin bots posts (like <a target="blank" href="https://codecanyon.net/item/wordpress-automatic-plugin/1904470?ref=ValvePress">Wordpress Automatic</a> posts and any other bot posts.) </span></td>
								</tr>
	
								<tr>
									<th scope="row"><label> Number of images to pin  </label></th>
									<td> 
										<select class="select_control_div" name="wp_pinterest_bot_num">
										
										 	<option value="1" <?php @wp_pinterest_automatic_opt_selected('1', $wp_pinterest_bot_num ) ?>>1</option>
											<option value="all" <?php @wp_pinterest_automatic_opt_selected('all', $wp_pinterest_bot_num ) ?>>All</option>
											<option value="custom" <?php @wp_pinterest_automatic_opt_selected('custom', $wp_pinterest_bot_num ) ?>>Custom</option>
											
										</select>
									</td>
								</tr>
	
								<tr class="select_control_div_div wp_pinterest_bot_num wp_pinterest_bot_num_custom">
									<th scope="row"><label>Maximum number of images to pin</label></th>
									<td><input style="width:50px;" class="widefat" value="<?php echo get_option("wp_pinterest_bot_num_n")  ?>" name="wp_pinterest_bot_num_n" type="text"></td>
								</tr>
	
							
							</tbody>
					</table>
						
		 	</div>
		</div>
		
		<div class="postbox">
		
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			
			<h3 class="hndle">
				<span>Bulk pinning</span>
			</h3>
			
			<div class="inside">
		 			
		 			<table class="form-table">
							<tbody>
								
								<tr>
									<th scope="row"><label> Info </label></th>
									<td>Bulk pinning is when sending posts to the queue in bulk by visiting the posts page, selecting multiple posts and choosing the bulk option named "Pin them". Posts then get added to the queue for being pinned.</td>
								</tr>
	
								<tr>
									<th scope="row"><label> Number of images to queue for each post?  </label></th>
									<td> 
										<select class="select_control_div" name="wp_pinterest_bot_num_q">
										
										 	<option value="1" <?php @wp_pinterest_automatic_opt_selected('1', $wp_pinterest_bot_num_q) ?>>1</option>
											<option value="all" <?php @wp_pinterest_automatic_opt_selected('all', $wp_pinterest_bot_num_q) ?>>All</option>
											<option value="custom" <?php @wp_pinterest_automatic_opt_selected('custom', $wp_pinterest_bot_num_q) ?>>Custom</option>
											
										</select>
									</td>
								</tr>
	
								<tr class="select_control_div_div wp_pinterest_bot_num_q wp_pinterest_bot_num_q_custom">
									<th scope="row"><label>Maximum number of images to queue</label></th>
									<td><input style="width:50px;" class="widefat" value="<?php echo get_option('wp_pinterest_bot_num_q_n' )  ?>" name="wp_pinterest_bot_num_q_n" type="text"></td>
								</tr>
								
								<tr>
									<th scope="row"><label> Skip the featured image? </label></th>
									<td><input name="wp_pinterest_options[]"  value="OPT_FEAT_SKIP" type="checkbox"> <span class="option-title"> If multiple images were found, do not queue the featured image  </span><div class="description">Sometimes the featured image is the same as the first image in the content so this option may help not getting a duplicate pin.</div></td>
								</tr>
	
							
							</tbody>
					</table>
						
		 	</div>
		</div>
	
		<div class="postbox">
				<div title="Click to toggle" class="handlediv">
					<br>
				</div>
				<h3 class="hndle">
					<span>Queue</span>
				</h3>
				<div class="inside">
				    <table class="form-table">
						
						<tr>
									<th scope="row"><label>Don't queue the same image from the same post twice</label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_NO_DUPLICATE" type="checkbox"> <span class="option-title">Duplicate pins can get Pinterest closing your account, Activate this option so the plugin ignore queueing already pinned images from the requested posts.</span></td>
								</tr>
						
						<tr>
									<th scope="row"><label> idle when limited? </label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_IDLE" type="checkbox"> <span class="option-title">Yes idle until limits are lifted</span><div class="description">Pinterest limits the number of pins with a link back to the site. By default, if the plugin finds that Pinterest throttled this type of pins, it pins images with the link attached to the description instead. Activate this option, if you want the plugin to sleep until Pinterest lifts the limits so the plugin can pin images with a link back again.</div></td>
						</tr>
						
						<tr>
									<th scope="row"><label>Pin interval</label></th>
									<td><input style="width:50px" value="<?php echo $wp_pinterest_automatic_interval_min  ?>" name="wp_pinterest_automatic_interval_min" type="text">
									To <input style="width:50px" value="<?php echo $wp_pinterest_automatic_interval_max  ?>" name="wp_pinterest_automatic_interval_max" type="text"> Minutes 
	
										<div class="description">Random Number of minutes between pins. Minimum value is 3 minutes</div></td>
								</tr>
								
								
								<tr>
									
									
									
									<th scope="row"><label>Queue processing time</label></th>
									<td>
									From
									<select name="wp_pinterest_automatic_interval_from_h">
										
										<?php 
										
											for($i= 0 ;$i<24;$i++){
											$hour = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $hour ,$wp_pinterest_automatic_interval_from_h) ?> value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
											
											
										<?php } ?>
										
										 
									</select>
									:
									<select name="wp_pinterest_automatic_interval_from_m">
										 
										 <?php 
										
											for($i= 0 ;$i<60;$i++){
											$minute = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $minute ,$wp_pinterest_automatic_interval_from_m) ?> value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
											
											
										<?php } ?>
										 
									</select>
									
									To
									<select name="wp_pinterest_automatic_interval_to_h">
										
										<?php 
										
											for($i= 0 ;$i<24;$i++){
											$hour = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $hour ,$wp_pinterest_automatic_interval_to_h) ?> value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
											
											
										<?php } ?>
										
										 
									</select>
									:
									<select name="wp_pinterest_automatic_interval_to_m">
										 
										 <?php 
										
											for($i= 0 ;$i<60;$i++){
											$minute = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $minute ,$wp_pinterest_automatic_interval_to_m) ?> value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
											
											
										<?php } ?>
										 
									</select>
									
									<div class ="description">24h format. current time is <?php echo date ( 'H:i',current_time('timestamp') ); ?>. "From" time must be lower than the "To" time<br>default is from 00:00 to 23:59 </div>
									 									
										</td>
								</tr>
								
								<tr>
									
									
									
									<th scope="row"><label>Additional queue processing time second interval</label></th>
									<td>
									From
									<select name="wp_pinterest_automatic_interval_from_h_2">
										
										<?php 
										
											for($i= 0 ;$i<24;$i++){
											$hour = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $hour ,$wp_pinterest_automatic_interval_from_h_2) ?> value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
											
											
										<?php } ?>
										
										 
									</select>
									:
									<select name="wp_pinterest_automatic_interval_from_m_2">
										 
										 <?php 
										
											for($i= 0 ;$i<60;$i++){
											$minute = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $minute ,$wp_pinterest_automatic_interval_from_m_2) ?> value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
											
											
										<?php } ?>
										 
									</select>
									
									To
									<select name="wp_pinterest_automatic_interval_to_h_2">
										
										<?php 
										
											for($i= 0 ;$i<24;$i++){
											$hour = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $hour ,$wp_pinterest_automatic_interval_to_h_2) ?> value="<?php echo $hour; ?>"><?php echo $hour; ?></option>
											
											
										<?php } ?>
										
										 
									</select>
									:
									<select name="wp_pinterest_automatic_interval_to_m_2">
										 
										 <?php 
										
											for($i= 0 ;$i<60;$i++){
											$minute = sprintf('%02u', $i);
											?>
											
											<option <?php wp_pinterest_automatic_opt_selected( $minute ,$wp_pinterest_automatic_interval_to_m_2) ?> value="<?php echo $minute; ?>"><?php echo $minute; ?></option>
											
											
										<?php } ?>
										 
									</select>
									
									<div class ="description">24h format. current time is <?php echo date ( 'H:i',current_time('timestamp') ); ?>. "From" time must be lower than the "To" time </div>
									 									
										</td>
								</tr>
						
						
								<tr>
									<th scope="row"><label> Complete random order </label></th>
									<td><input name="wp_pinterest_options[]"   value="OPT_RAND" type="checkbox"> <span class="option-title">Yes, Randomize posting order</span><div class="description">By default, The plugin circulates posts from the queue and post a single image each interval. If you have two posts each one has two images, it will pin the first image from the first post, then the first image from the second post then the second image from the first post then the second image from the second post... and so on. Activate this option for complete randomness</div></td>
								</tr>
								
						
				    </table>
	
				 </div>
		</div>
	
		<div class="postbox">
			<div title="Click to toggle" class="handlediv">
				<br>
			</div>
			<h3 class="hndle">
				<span>Tag to board mapping</span>
			</h3>
			<div class="inside">
	     		 
				
			    <table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><label>Active ?</label></th>
									<td> <input data-controls="ctt_container" name="wp_pinterest_options[]" id="field-ctt_enable-1" value="OPT_CTT" type="checkbox">  Enable tag to board
									
									<div id="ctt_container" class="ctt-contain" style="padding-bottom: 20px;">
	
									<?php
									
									$wp_pinterest_automatic_wordpress_tags = get_option ( 'wp_pinterest_automatic_wordpress_tags', array (
											'' 
									) );
									
									$wp_pinterest_automatic_pinterest_tags = get_option ( 'wp_pinterest_automatic_pinterest_tags', array () );
									
									$pinterest_category = 0;
									 
									 
									?>
							 
									<?php
									$n = 0;
									foreach ( $wp_pinterest_automatic_wordpress_tags as $wp_tag ) {
										
										?> 
									 
									<div class="ctt">
			
												<div id="field-pinterest_tag-container">
													<label> Tag : </label> 
														
														<input type="text" name="wp_pinterest_automatic_wordpress_tags[]" value="<?php echo $wp_tag ?>" />
														
														 
			
													<label for="field-pinterest_tags"> to Board </label> 
													
													<select style="width:220px" name="wp_pinterest_automatic_pinterest_tags[]"  >
											
														<?php
													$i = 0;
													
													foreach ( $wp_pinterest_boards_ids as $id ) {
														?>
												      		
															<option value="<?php echo $id ?>" <?php wp_pinterest_automatic_opt_selected( $id ,$wp_pinterest_automatic_pinterest_tags[$n]) ?>><?php echo $wp_pinterest_boards_titles[$i]?></option>
												      			
												      		<?php
														$i ++;
													}
													
													?>
											
			 
													</select>
			
													<button class="ctt_add">+</button>
													<button class="ctt_remove">x</button>
			
												</div>
			
											</div>
											<!-- ctb contain-->
										
										<?php $n++;}?>
										
										</div>
										
										<div class="description">If the post is tagged with the set tag, the post will be pinned to the set board. Checked from top to down.</div>
	
									</td>
								</tr>
	
							</tbody>
						</table>
				</div>
				</div>
	
				<div class="postbox">
					<div title="Click to toggle" class="handlediv">
						<br>
					</div>
					<h3 class="hndle">
						<span>Category to board mapping</span>
					</h3>
					<div class="inside">
				
				
				<table class="form-table">
							<tbody>
								<tr>
									<th scope="row"><label>Active ?</label></th>
									<td> <input data-controls="ctb_container" name="wp_pinterest_options[]" id="field-ctb_enable-1" value="OPT_CTB" type="checkbox">  Enable category to board
									
									<div id="ctb_container" class="ctb-contain" style="padding-bottom: 20px;">
	 								
									<?php
									
									$wp_pinterest_automatic_wordpress_category = get_option ( 'wp_pinterest_automatic_wordpress_category', array (
											'' 
									) );
									
									$wp_pinterest_automatic_pinterest_category = get_option ( 'wp_pinterest_automatic_pinterest_category', array () );
									
									$pinterest_category = 0;
									
									$tax_txt=$wp_pinterest_automatic_tax;
									
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
									
									
									$cats = get_categories ( array (
												'hide_empty'               => 0 ,
												'taxonomy'                 => $tax,
												'parent'                   => 0
	
	 
									) );
	 
	 
									?>
							 
									<?php
									$n = 0;
									
									 
									
									foreach ( $wp_pinterest_automatic_wordpress_category as $wp_category ) {
										
										?> 
									 
									<div class="ctb">
			
												<div id="field-pinterest_category-container">
													<label> Category : </label> 
														<select style="width:220px" name="wp_pinterest_automatic_wordpress_category[]" id="field1zza">
															
															<?php
																
																foreach ( $cats as $cat ) {
																	
																	wp_pinterest_automatic_list_cat($cat,$wp_category,$tax);
					
																}
																 
															?>
											
														</select>
			
			
													<label for="field-pinterest_category"> to Board </label> 
													
													<select  style="width:220px"  name="wp_pinterest_automatic_pinterest_category[]" id="field1zzb">
											
														<?php
													$i = 0;
													
													foreach ( $wp_pinterest_boards_ids as $id ) {
														?>
												      		
															<option value="<?php echo $id ?>" <?php wp_pinterest_automatic_opt_selected( $id ,$wp_pinterest_automatic_pinterest_category[$n]) ?>><?php echo $wp_pinterest_boards_titles[$i]?></option>
												      			
												      		<?php
														$i ++;
													}
													
													?>
											
			 
													</select>
			
													<button class="ctb_add">+</button>
													<button class="ctb_remove">x</button>
			
												</div>
			
											</div>
											<!-- ctb contain-->
										
										<?php $n++;}?>
										
										</div>
										
										<div class="description">If the to be pinned post is in the set category, it will be pinned to the set board.</div>
	
									</td>
								</tr>
	
							</tbody>
				</table>
				</div>
				</div>
	
				<div class="postbox">
					<div title="Click to toggle" class="handlediv">
						<br>
					</div>
					<h3 class="hndle">
						<span>Advanced Settings (optional)</span>
					</h3>
					<div class="inside">
				 
	
				<table class="form-table">
							<tbody>
								
								<tr><th scope="row"><label>Please be carefull</label> </th><td>  <div class="description">Any wrong configuration in any of these fields may break the plugin function. Seek support if need. </div></td></tr>
							
							<tr>
									
									<th scope="row"><label>Use ip:port proxies ?   </label></th>
									 
									<td>
									  
									  <div  class="field f_100" >
							 			     <div class="option clearfix">
							 			     	
							 			         <input     name="wp_pinterest_options[]"  value="OPT_PROXY" type="checkbox">
							 			         <label>Activate using proxies</label>     
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>
								
								<tr>
									
									<th scope="row"><label>Proxy List   </label></th>
									 
									<td>
									  
									  <div   class="field f_100" >
							 			     <div class="option clearfix">
							 			     	 
							 			     	<div   class="field f_100" >
							 			     		<textarea class="widefat" rows="5" cols="20" name="wp_pinterest_proxies"  ><?php echo $wp_pinterest_proxies ?></textarea>
							 			     	</div>
							 			     	
							 			     	 <div class="option clearfix">
							 			     	
							 			        		 <input     name="wp_pinterest_options[]"  value="OPT_PROXY_SOCK" type="checkbox">
							 			        		 <label>Treat the added proxies as SOCKS5 not HTTP</label>     
							 			     	</div>
							 			     	
							 			     	<br>
							 			     	
							 			     	<div class="description">
							 			     	
							 			     	*Make sure the proxies are with port 80(always open) or 8080(sometimes open) which are open for connection in most servers, or use any port that you are sure is open at your server 
							 			     	<br> *Format:<strong>ip:port</strong> 
							 			     	<br> *Another Format : <strong>ip:port:username:password</strong>   for proxies with authentication
							 			     	<br> *one proxy per line
							 			     	<br> *Some proxy services require server ip for authentication <a target="_blank" href="<?php echo site_url('?wp_pinterest_automatic=show_ip') ?>"><strong>Click here</strong></a> to know your server ip to use</strong>
							 			     	<br> *Check <a href="http://valvepress.com/use-private-proxies-pinterest-automatic/" target="_blank"><strong>this tutorial</strong></a> showing a tested service named <a href="https://instantproxies.com/billing/aff.php?aff=762">InstantProxies</a> you can use.
							 			     	<br> *Don't use public proxies used by thousands of pepole, it may get you into lots of troubles.
							 			     	</div>
							 			     	   
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>
							
								<tr>
									<th scope="row"><label>Detect images from this custom field</label></th>
									<td><input class="widefat" value="<?php echo get_option('wp_pinterest_automatic_cf' )  ?>" name="wp_pinterest_automatic_cf" type="text">
	
										<div class="description">If the theme/plugin uses a custom field for the tumbnail add its name for the plugin to grab the image URL from</div></td>
								</tr>
	
								<tr>
									<th scope="row"><label>Custom jQuery selector</label></th>
									<td><input class="widefat" value="<?php echo $wp_pinterest_automatic_selector  ?>" name="wp_pinterest_automatic_selector" type="text">
	
										<div class="description">By default, The plugin searches the editor for images, but if the images are visible elsewhere on the editing page, then add its jQuery selector to be used by the plugin for getting images. Use a jQuery selector like ".my_class" for a div having a class named "my_class" or "#my_id" for div with an ID of "my_id".</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Categories Taxonomies</label></th>
									<td><input class="widefat" value="<?php echo $wp_pinterest_automatic_tax  ?>" name="wp_pinterest_automatic_tax" type="text">
	
										<div class="description">By default, the plugin lists the categories above at the category to board section from posts and woo-commerce product post type, but if you have another post type with a categories taxonomy, you can add this taxonomy to the currently used, comma separated.<br><br>Default: category,product_cat.</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Tags Taxonomies</label></th>
									<td><input class="widefat" value="<?php echo $wp_pinterest_automatic_tax_tags  ?>" name="wp_pinterest_automatic_tax_tags" type="text">
	
										<div class="description">By default, the plugin lists tags above at the tag to board section from posts and woo-commerce product post type, but if you have another post type with a tags taxonomy, add this taxonomy comma separated. <br><br>Default: post_tag,product_tag</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Excluded Categories</label></th>
									<td> 
									
									 	<div id="taxonomy-category" class="categorydiv">
											<div id="category-all" class="tabs-panel">
												<input type="hidden" name="post_category[]" value="0">
												<ul id="categorychecklist" data-wp-lists="list:category" class="categorychecklist form-no-clear">
													<?php 
														  
													foreach ($wp_pinterest_types as $post_type){
															
															$customPostTaxonomies = get_object_taxonomies($post_type);
															
															if(count($customPostTaxonomies) > 0)
															{
																foreach($customPostTaxonomies as $tax){
																	
																	// If category list it's items
																	if(is_taxonomy_hierarchical($tax)){
																		
																		wp_terms_checklist (0, array('taxonomy' => $tax , 'selected_cats' => $wp_pinterest_excluded_post_category ) );  
																		
																	}
																}
															}
															
															
														}
													
													?>
												</ul>
											</div>
										</div>
	
										<div class="description">Posts from these categories will not get pinned when published automatically or sent to the queue. Note: Manual posting will not be affected.</div></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Excerpt Length</label></th>
									<td><input class="widefat" value="<?php echo $wp_pinterest_automatic_excerpt  ?>" name="wp_pinterest_automatic_excerpt" type="text">
	
										<div class="description">By default, the plugin uses the post excerpt added to the post for the [post_excerpt] tag but some posts don't have an excerpt, so the plugin will auto generate excerpt from the content with this character length.</div></td>
								</tr>
								
								 <tr>
									<th scope="row"><label> Pin link points to?  </label></th>
									<td> 
										<select class="select_control_div" name="wp_pinterest_pin_link_to">
										
										 	<option value="post" <?php @wp_pinterest_automatic_opt_selected('post', $wp_pinterest_pin_link_to) ?>>Post URL</option>
											<option value="fixed" <?php @wp_pinterest_automatic_opt_selected('fixed', $wp_pinterest_pin_link_to) ?>>Fixed link</option>
											<option value="custom" <?php @wp_pinterest_automatic_opt_selected('custom', $wp_pinterest_pin_link_to) ?>>Link from a custom field</option>
											
										</select>
										
											<br><br><input  name="wp_pinterest_options[]"  value="OPT_ATTACH_LINK" type="checkbox"> <span class="option-title"> Try to find the attachement link and use instead if possible. </span>
										
									</td>
								</tr>
	
								<tr class="select_control_div_div wp_pinterest_pin_link_to wp_pinterest_pin_link_to_fixed wp_pinterest_pin_link_to_custom">
									<th scope="row"><label>Link or name of a custom field containing the link</label></th>
									<td><input class="widefat" value="<?php echo get_option('wp_pinterest_pin_link_to_link' )  ?>" name="wp_pinterest_pin_link_to_link" type="text"></td>
								</tr>
								
								<tr>
									<th scope="row"><label>Append parameters to the pin link</label></th>
									<td>
									
										<input data-controls="paramfield" name="wp_pinterest_options[]"  value="OPT_PARAM" type="checkbox"> <span class="option-title"> Enabled </span><br>
										
										<div id	="paramfield" >	
	
											<input class="widefat" value="<?php echo $wp_pinterest_automatic_param  ?>" name="wp_pinterest_automatic_param" type="text">
											<div class="description">Append these parameters to the pin link sent to Pinterest. for example: utm_source=pinterest</div>
	
										</div>	
	
									</td>
									
								<tr>
									<th scope="row"><label>(NOT RECOMMENDED) Minimum image width</label></th>
									<td>
									
										<input data-controls="minImgfield" name="wp_pinterest_options[]"  value="OPT_MINWIDTH" type="checkbox"> <span class="option-title"> Skip images lower than a specific width </span><br>
										
										<div id	="minImgfield" >	
	
											<input class="widefat" value="<?php echo $wp_pinterest_automatic_minwidth  ?>" name="wp_pinterest_automatic_minwidth" type="text">
											<div class="description">Numeric minimum width. ex: 150 . Don't use this option unless you have real problems and getting small images pinned as it takes more resources and time that can be saved. </div>
	
										</div>	
	
									</td>
									
									<tr>
									<th scope="row"><label>(NOT RECOMMENDED) Minimum image height</label></th>
									<td>
									
										<input data-controls="minImghfield" name="wp_pinterest_options[]"  value="OPT_MINHEIGHT" type="checkbox"> <span class="option-title"> Skip images lower than a specific height </span><br>
										
										<div id	="minImghfield" >	
	
											<input class="widefat" value="<?php echo $wp_pinterest_automatic_minheight  ?>" name="wp_pinterest_automatic_minheight" type="text">
											<div class="description">Numeric minimum height. ex: 150 . Don't use this option unless you have real problems and getting small images pinned as it takes more resources and time that can be saved. </div>
	
										</div>	
	
									</td>
									
								
								</tr>
								
								<tr>
								<th scope="row"><label>Search and replace texts at the image src</label></th>
									 
									<td>
									  
									  <div   class="field f_100" >
							 			     <div class="option clearfix">
							 			     	 
							 			     	<div   class="field f_100" >
							 			     		 
							 			     		<textarea class="widefat" rows="5" cols="20" name="wp_pinterest_search_replace"  ><?php echo $wp_pinterest_search_replace ?></textarea>
							 			     	</div>
							 			     	
							 			     	<div class="description">
							 			     	
							 			     	  
							 			     	<br> *Format:<strong>search|replace</strong> 
							 			     	<br> *Example : <strong>thumb|fullwidth</strong> so the plugin will replace the text "thumb" with "fullwidth" at the pinned image src link
							 			     	<br> *one rule per line  
							 			     	<br> *for stripping texts use this format  <strong>text|</strong> this will replace the text "text" with empty string
							 			     	</div>
							 			     	   
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>
								
								<tr>
								<th scope="row"><label>Search and replace texts at pin tags</label></th>
									 
									<td>
									  
									  <div   class="field f_100" >
							 			     <div class="option clearfix">
							 			     	 
							 			     	<div   class="field f_100" >
							 			     		 
							 			     		<textarea class="widefat" rows="5" cols="20" name="wp_pinterest_search_replace_txt"  ><?php echo $wp_pinterest_search_replace_txt ?></textarea>
							 			     	</div>
							 			     	
							 			     	<div class="description">
							 			     	
							 			     	  
							 			     	<br> *Format:<strong>search|replace</strong> 
							 			     	<br> *Example : <strong>ğ|g</strong> so the plugin will replace the text "ğ" with "g" at the pinned tag text
							 			     	<br> *one rule per line  
							 			     	<br> *for stripping text use this format  <strong>ğ|</strong> this will replace the text "ğ" with empty string
							 			     	</div>
							 			     	   
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>
								
								<tr>
								<th scope="row"><label>Search and replace texts at pin link</label></th>
									 
									<td>
									  
									  <div   class="field f_100" >
							 			     <div class="option clearfix">
							 			     	 
							 			     	<div   class="field f_100" >
							 			     		 
							 			     		<textarea class="widefat" rows="5" cols="20" name="wp_pinterest_search_replace_lnk"  ><?php echo stripslashes($wp_pinterest_search_replace_lnk) ?></textarea>
							 			     	</div>
							 			     	
							 			     	<div class="description">
							 			     	
							 			     	  
							 			     	<br> *Format:<strong>search|replace</strong> 
							 			     	<br> *Example : <strong>example.com|example2.com</strong> so the plugin will replace the text "example.com" with "example2.com" at the pinned URL
							 			     	<br> *one rule per line  
							 			     	<br> *for stripping text use this format  <strong>demo.|</strong> this will replace the text "demo." with empty string
							 			     	<br> *REGEX enabled so you can search a regular expression instead.
							 			     	</div>
							 			     	   
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>
	
	
								<tr>
								<th scope="row"><label>Skip the image if its link contains any of these texts</label></th>
									 
									<td>
									  
									  <div   class="field f_100" >
							 			     <div class="option clearfix">
							 			     	 
							 			     	<div   class="field f_100" >
							 			     		 
							 			     		<textarea class="widefat" rows="5" cols="20" name="wp_pinterest_stop"  ><?php echo $wp_pinterest_stop ?></textarea>
							 			     	</div>
							 			     	
							 			     	<div class="description">
							 			     	
							 			     	<br>*one word per line
							 			     	<br> *When pinning a post in bulk or parsing a bot post for images, the plugin will check each image URL and if any word of the above set words exists, it will skip this image and queue the rest
							 			     	</div>
							 			     	   
							 			     </div>
							 			</div> 
									 	 
									</td>
								</tr>	
							</tbody>
				</table>			
				</div>
				</div>
	
				<div class="postbox">
					<div title="Click to toggle" class="handlediv">
						<br>
					</div>
					<h3 class="hndle">
						<span>Cron Setup</span>
					</h3>
					<div class="inside">				
				 	
				
				<table class="form-table">
							<tbody>
								
								
							
								<tr>
									<th scope="row"><label>Cron command</label></th>
									<td><input readonly="readonly" class="widefat" value="<?php echo 'curl '. site_url('?wp_pinterest_automatic=cron')  ?>"   type="text">
	
										<div class="description">By Default, the plugin uses built-in WordPress cron that is triggered by site visitors but you can still setup a cron job to call processing the queue just copy the command in the box to your hosting crontab. make it every minute. you can trigger the cron manually <a href="<?php echo site_url('?wp_pinterest_automatic=cron')  ?>">here</a> </div></td>
										
										
										
								</tr>
								
								<tr>
									<th scope="row"><label>Alternate cron command</label></th>
									<td><input readonly="readonly" class="widefat" value="<?php echo 'curl '. plugins_url('/wp-pinterest-automatic/pcron.php')  ?>"   type="text">
	
										<div class="description">Use this command if you have any problems getting the above one to work</div></td>
										
										
										
								</tr>
								
								<tr>
									<th scope="row"><label> Disable using built-in wordpress Cron </label></th>
									<td><input name="wp_pinterest_options[]" id="field-wp_pinterest_options-1" value="OPT_EXTERNAL_CRON" type="checkbox"> <span class="option-title"> Tick this if you will use the external cron job instead (Recommended)</span></td>
								</tr>
								
																						
								
								<tr>
									<th scope="row"><label>Keep log for how many days</label></th>
									<td><input style="width:50px" value="<?php echo $wp_pinterest_automatic_interval_clear  ?>" name="wp_pinterest_automatic_interval_clear" type="text">Days
									  
	
										<div class="description">Clear log records older than this day</div></td>
								</tr>
								
								
	
								
								
							</tbody>
						</table>
				
				</div>
				</div>
				
				<?php  }//license active? ?>			
				
	
				<div class="postbox">
					<div title="Click to toggle" class="handlediv">
						<br>
					</div>
					<h3 class="hndle">
						<span>License</span>
					</h3>
					<div class="inside">
				 
				 <table class="form-table">
							<tbody>
								
								
							
								<tr>
									<th scope="row"><label>Purchase Code</label></th>
									<td><input class="widefat" name="wp_pinterest_automatic_license" value="<?php echo get_option('wp_pinterest_automatic_license','') ?>"   type="text">
	
										<div class="description">Check this <a href="http://www.youtube.com/watch?v=eAHsVR_kO7A">video</a> on how to get it.</div></td>
								</tr>
								
								<?php if( isset($wp_pinterest_active_error) && stristr($wp_pinterest_active_error,	 'another')  ) {?>
								
								<tr>
									<th scope="row"><label> Change domain </label></th>
									<td><input name="wp_pinterest_options[]" id="field-wp_pinterest_options-1" value="OPT_CHANGE_DOMAIN" type="checkbox"> <span class="option-title"> Disable license at the other domain and use it with this domain </span></td>
								</tr>
								
								<?php } ?>
								
								<tr>
									<th scope="row"><label>License Status</label></th>
									<td>
	
										<div class="description"><?php 
										
										if(trim($licenseactive) !=''){
											echo 'Active';
										}else{
											echo 'Inactive ';
											if(isset($wp_pinterest_active_error)) echo '<p><span style="color:red">'.$wp_pinterest_active_error.'</span></p>';
										}
										
										?></div></td>
								</tr>
	
								
							</tbody>
						</table>
				 </div>
				</div>
	 			
	 
			</div>
		</div>
		<!-- end .postbox-container -->
	
		<div style="" class="postbox-container" id="postbox-container-2">
			<div class="meta-box-sortables ui-sortable empty-container" id="side-sortables"></div>
		</div>
		<!-- end .postbox-container -->
	
		<div style="" class="postbox-container" id="postbox-container-3">
			<div class="meta-box-sortables ui-sortable empty-container" id="column3-sortables"></div>
		</div>
		<!-- end .postbox-container -->
	
		<div style="" class="postbox-container" id="postbox-container-4">
			<div class="meta-box-sortables ui-sortable empty-container" id="column4-sortables"></div>
		</div>
		<!-- end .postbox-container -->
	
		</div>
		<div style="clear:both"></div>
		
		<input   type="submit" name="save" value="Save Changes" class="button-primary">
		
	 
	</form>
	</div><!-- wrap -->
	 <script type="text/javascript">
	    var $vals = '<?php echo  $wp_pinterest_options ?>';
	    $val_arr = $vals.split('|');
	    jQuery('input:checkbox').removeAttr('checked');
	    jQuery.each($val_arr, function (index, value) {
	        if (value != '') {
	            jQuery('input:checkbox[value="' + value + '"]').prop('checked', true);
	        }
	    });
	
	
	    //excluded categories
	    jQuery('#taxonomy-category input').attr('name','wp_pinterest_excluded_post_category[]');
	
	    var $vals = '<?php  $opt= $wp_pinterest_excluded_post_category; print_r(implode('|',$opt)); ?>';
	    $val_arr = $vals.split('|');
	
	    jQuery.each($val_arr, function (index, value) {
	        if (value != '') {
	            jQuery('input:checkbox[value="' + value + '"]').prop('checked', true);
	   
	        }
	    });
	    
	</script>
		