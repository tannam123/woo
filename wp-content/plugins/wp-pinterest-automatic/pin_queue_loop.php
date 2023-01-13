<?php
$i = 1;
if ($the_query->have_posts ()) {
	
	while ( $the_query->have_posts () ) {
		
		$the_query->the_post ();
		$post_id = $post->ID;
		
		if (! in_array ( $post->ID, $posts_displayed )) {
			$posts_displayed[]=$post->ID;
			
			//display separator between published posts and other formats
			if(isset($view_separator) && $i == 1 ){
				echo '<tr style="background:#a3a3a3;color:#fff"><td  style="padding-left:150px;color:#fff" colspan="7" ><strong>Below posts are in the queue but will not be processed until published .  </td></tr>';
			}
			
			$pin_text = get_post_meta ( $post_id, 'pin_text', 1 );
			$pin_board = get_post_meta ( $post_id, 'pin_board', 1 );
			$pin_alt = get_post_meta ( $post_id, 'pin_alt', 1 );
			$images_index = get_post_meta ( $post_id, 'pin_index', 1 );
			$pin_images = get_post_meta ( $post_id, 'pin_images', 1 );
			$images_try =get_post_meta ( $post_id, 'images_try', 1 );
			$pin_try=get_post_meta ( $post_id, 'pin_try', 1 );
			if(trim($pin_try) == '') $pin_try =0;
			
			$ttl = $post->post_title;
			if (trim ( $ttl ) == '')
				$ttl = '(no title)';
		 
			 
			
			echo '<tr class="tr-post tr-post-'.$post_id.' alternate">';
			echo ' <td>' . $i . '</td>';
			
			
			echo ' <td><a href="' . admin_url('post.php?post='.$post_id.'&action=edit')  . '">' . $ttl . '</a></td>';
			echo "<td>$pin_text</td>";
			echo "<td>$pin_board</td>";
			echo "<td>$post->post_status</td>";
			echo "<td>($pin_try)  </td>";
			echo '<td><a href="#" data-post="'.$post->ID.'" class="wp_pinterest_automatic_delete_post">delete post pins</a><span class="spinner-delete-'.$post_id.' spinner"></span></td>';
			echo '</tr>';
			
			
			echo '<tr class="tr-post-'.$post_id.'" ><td></td><td class="wp_pins_holder-'.$post_id.'" colspan="6"  style="padding-bottom:30px">';
			foreach ( $pin_images as $pin_image ) {
				
				//get the image with lowest try 
				$image_try=$images_try[md5($pin_image)];
				
				echo '  <div class="pin_img_log" style="background-image:url(\'' . $pin_image . '\');"  ><span data-post="'.$post_id.'" data-img="'. $pin_image .'" class="wp_pinterest-delete-pin"></span></div>';
				
			}
			
			echo '</td></tr>';
			
			$i ++;
		}//not displayed
	}
	
	
	
	
	
} else {
	// no posts found
	if(!  isset($view_separator) ){
		echo '<tr><td colspan="7"  ><strong>no posts waiting for pinning . </td></tr>';
	}
}

/* Restore original Post Data */
wp_reset_postdata ();

?>