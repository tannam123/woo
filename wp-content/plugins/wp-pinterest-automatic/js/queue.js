//UPDATE CURRENT TIME
var serverHour = 0;
var serverDiff = 200;
var currentHour = 0;
var nextRun = 0;
var isActive = true; //is document active or not

//UPDATE CLOCK FUNCTION
function timedCountin() {
	
	//console.log(isActive);

	if( isActive == true){
		 
		//loop here 
		var x = new Date();
	    
		//get hours difference between server and browser 
	    if (serverDiff == 200) {
	    		serverDiff = x.getHours() - serverHour;
	    }
	    
	    //current server hour
	    currentHour = (x.getHours() - serverDiff);
	     
	    //update current time
	    jQuery('.current_time').html(currentHour + ":" + x.getMinutes() + ":" + x.getSeconds());
	    
	    //increment run from by one second
	    jQuery('.wp_pinterest_run_before').html( parseInt( jQuery('.wp_pinterest_run_before').html() )  + 1);
	    
	    //update estimated pin arrival number
	    	
	    
	    //check if positive 
	    if(parseInt( jQuery('.next_run').html() ) > 0 ){
	    	//ok require update
	        nextRun = parseInt( jQuery('.interval_mintes').html() ) * 60 - parseInt( jQuery('.wp_pinterest_run_before').html() )  ;
	        
	        if(nextRun > 0){
	        	//positive number just update
	        	jQuery('.next_run').html(nextRun);
	        }else{
	        	//negative number just update with zero and trigger cron 
	        	
	        	//update with zero 
	        	jQuery('.next_run').html('0');
	        	
	        	
	        }
	
	    	
	    	
	    }else{
	    	// already zero skip updating it
	    }
	}
	
	t = setTimeout("timedCountin()", 1000);

}



//update values from server
function serverValsUpdatetimedCount() {
	
	if( isActive == true){
	
		//loop here 
		jQuery.ajax({
			url : ajaxurl ,
			type : 'GET',
			data : {'action':'wp_pinterest_automatic_queue_vals'},	
			success : function(data) {
				 
	
				var res = jQuery.parseJSON(data);
				if (res['status'] == 'success') {
					
					//execute call back
					jQuery('.last_run').html(res['last_run']);
					jQuery('.interval_mintes').html(res['interval_mintes']);
					jQuery('.interval_seconds').html(res['interval_seconds']);
					jQuery('.wp_pinterest_run_before').html(res['wp_pinterest_run_before']);
					jQuery('.next_run').html(res['next_run']);
	
				} else if (res['status'] == 'fail') {
	
				}
	
				//posting message 
	
			} 
		});

	}//isActive
	t = setTimeout("serverValsUpdatetimedCount()", 5000);
}

serverValsUpdatetimedCount();

//TRIGGER CRON EVERY MINUTE
function triggerCrontimedCount() {
	
	if( isActive == true){
	
		//loop here 
		jQuery.ajax({
			url : jQuery('#wp_pinterest_automatic_trigger_cron').attr('href') ,
			type : 'GET' 
	 
		});
		
	}

	t = setTimeout("triggerCrontimedCount()", 60000);
}
triggerCrontimedCount();

//UPDATE QUEUED ITEMS
function queueItemstimedCount() {
	
	if( isActive == true){
		
		//loop here 
		jQuery.ajax({
			url : ajaxurl,
			type : 'POST',
			dataType: 'json',
			data : {
				action : 'wp_pinterest_automatic_queue_itms',
	
			},
			
			success: function(data){
				
				//display published posts pins
				
				var res= data['published'];
				var boards = data['boards'];
				
				var newPost ;
				var newPostId ;
				var randId =  Math.random(); 
				 
				//loop all posts
				jQuery(res).each(function(){
				  
				  //current post
				  newPost = this;
				  
				  //current post id
				  newPostId = newPost['post_id'];
				  
				  //check if not already displayed display it's row
				  if ( jQuery('.tr-post-' + newPost['post_id']   ).length == 0  ){
				    
				    //append post row to the table 
				    jQuery('.waiting_posts tbody').append('<tr class="tr-post tr-post-' + newPostId +' alternate"> <td class="max-1">-</td> <td><a href="' + newPost['post_uri'] + '">' + newPost['post_title'] + '</a></td><td class="max-1 max-2 ">' + newPost['pin_text'] + '</td><td  class="max-1 max-2 ">' +boards[ newPost['pin_board'] ]+ '</td><td  class="max-1 max-2 ">' + newPost['post_status'] + '</td><td  class="max-1 max-2 ">('+ newPost['pin_try'] +')  </td><td><a class="wp_pinterest_automatic_delete_post" data-post="'+newPostId+'" href="#">delete post pins</a><span class="spinner-delete-'+newPostId+' spinner"></span></td></tr>');
				    
				    //append images row
				    jQuery('.waiting_posts tbody').append('<tr class="tr-post tr-post-'+newPostId+'"><td></td><td colspan="6" class="wp_pins_holder wp_pins_holder-'+newPostId+'" style="padding-bottom:30px">  </td></tr>');
				    
				  }
				  
				  //set row for this post as valid for this update 
				  jQuery('.tr-post-' + newPostId).attr('data-rand',randId);
				  
				  //appending new images to the queue 
				  jQuery(newPost['images']).each(function(index,image){
				     
				    
				    //if image not exists append it
				    if(jQuery( '.wp_pins_holder-'+newPostId +' .wp_pinterest-delete-pin[data-hash="'+ image['image_hash'] +'"]' ).length == 0){
				      
				      //append image
				      jQuery( '.wp_pins_holder-'+newPostId  ).append('<div style="background-image:url(\''+ image['pin_image'] +'\');" class="pin_img_log"><span class="wp_pinterest-delete-pin" data-hash ="' + image['image_hash'] + '" data-img="'+ image['pin_image'] +'" data-post="'+newPostId+'" style="display: none;"></span></div>');
				    }
				    
				    //set this image as valid with rand var
				    jQuery('.wp_pins_holder-'+ newPostId + ' .pin_img_log .wp_pinterest-delete-pin[data-hash="'+ image['image_hash'] +'"]'  ).attr('data-rand',randId);
				    
				  });
				  
	
				});
				
				//remove displayed but not exists posts
				jQuery('.tr-post[data-rand!="'+randId+'"]').fadeOut('slow',function(){ jQuery(this).remove(); }) ;
				
				//remove displayed but not exists pins
				jQuery('.pin_img_log span[data-rand!="'+randId+'"]' ).parent().fadeOut('slow',function(){ jQuery(this).remove(); }) ;
				 
				//if empty add empty notice if full remove it
				if(jQuery('.tr-post:visible').length == '0'  ){
					
					if( jQuery('.empty_table_post').length == '0'){
						jQuery('.waiting_posts tbody').append('<p class="empty_table_post" style="padding:20px;width:100px">Empty</p>');
					}
					
				}else{
					jQuery('.empty_table_post').remove();
				}
				
				 
				
				//displaying bots posts
				res = data['bot'];
				
				 
				
				jQuery(res).each(function(ind,val){
					
					 
					 if(jQuery('.tr-bot-' + val['post_id']  ).length == 0  ){
						 jQuery('.bot_table tbody').append(' <tr class="tr-bot tr-bot-'+ val['post_id'] +'">  <td><a href="' + val['post_uri'] +  '">' + val['post_title'] + '</a></td><td>' + val['post_status'] + '</td> <td><a class="bot-delete" data-id="' + val['post_id']+'" href="">Delete</a><span class="spinner bot-spinner-'+ val['post_id'] +'"></span></td> </tr> ');
					 }
					 
					 jQuery('.tr-bot-' + val['post_id'] ).attr('data-rand',randId);
					 
				});
				
				//remove displayed but not exists posts
				jQuery('.tr-bot[data-rand!="'+randId+'"]').fadeOut('slow',function(){ jQuery(this).remove(); }) ;
				
				//if empty add empty notice if full remove it
				if(jQuery('.tr-bot:visible').length == '0'  ){
					
					if( jQuery('.empty_table').length == '0'){
						jQuery('.bot_table tbody').append('<p class="empty_table" style="padding:20px">Empty</p>');
					}
					
				}else{
					jQuery('.empty_table').remove();
				}
				
				//update last pin image
				res = data['last_pin'];
				
				if(jQuery('.last_pin').attr('data-img-hash') != res['hash'] ){
	
					jQuery('#last_pin_link').attr('href',res['url']);
					jQuery('.last_pin').css('background-image','url('+res['img']+')');
					jQuery('.last_pin').attr('data-img-hash',res['hash']);
					
				}
				
			}
		});
		
	}//isActive
	
	t = setTimeout("queueItemstimedCount()", 10000);
}

queueItemstimedCount();


//DOC READY
jQuery(document).ready(function() {

	//CLEAR QUEUE
	jQuery('#wp_pinterest_automatic_clear_queue').click(function() {
		
		jQuery.ajax({
		
			url : ajaxurl,
			type : 'POST',

			data : {
				action : 'wp_pinterest_automatic_clear_queue',
			},
            
            beforeSend:function(){
	        
            		console.log('sending');
	            	jQuery('.spinner-clear').addClass('is-active');
            
            },
            
            success:function(data){
            
            	jQuery('.spinner-clear').removeClass('is-active');
            	
            	//parse
            	var res = jQuery.parseJSON(data);
            	
            	    alert(res['message']);
                location.reload();
            	
            }
             
		});

		return false;

	});
	
	//TRIGER CRON
	jQuery('.wp_pinterest_automatic_trigger_cron').click(function() {

		jQuery.ajax({
			url : jQuery(this).attr('href'),
			type : 'POST',
 
            beforeSend:function(){
            		jQuery('.spinner-clear').addClass('is-active')  ;
            },
            
            success:function(data){
	            	jQuery('.spinner-clear').removeClass('is-active');
	            	alert(data);
            }
           
		});

		return false;

	});
	
	
	//delete post pins link
	jQuery('.waiting_posts').on('click','.wp_pinterest_automatic_delete_post',function(){   
		var pID= jQuery(this).attr('data-post');
		
		jQuery.ajax({
			url : ajaxurl,
			type : 'POST',

			data : {
				action : 'wp_pinterest_automatic_clear_post',
				id     : pID

			},
            
            beforeSend:function(){
            	jQuery('.spinner-delete-'+ pID).show();
            },
            
            success:function(data){
            	jQuery('.spinner-delete-'+ pID).hide();
            	
            	jQuery('.tr-post-'+pID).css('background','red');
            	 jQuery('.tr-post-'+pID).fadeOut('slow',function(){jQuery(this).remove();});
            	
            }
            
           
		});

		return false;

	});
	
	//DELETE BOT POS
	jQuery('.bot_table').on('click','.bot-delete',function(){   
		var pID= jQuery(this).attr('data-id');
		
		jQuery.ajax({
			url : ajaxurl,
			type : 'POST',

			data : {
				action : 'wp_pinterest_automatic_clear_bot_post',
				id     : pID

			},
            
            beforeSend:function(){
            	jQuery('.bot-spinner-'+ pID).show();
            },
            
            success:function(data){
            	jQuery('.bot-spinner-'+ pID).hide();
             	jQuery('.tr-bot-'+pID).css('background','red');
            	jQuery('.tr-bot-'+pID).fadeOut('slow',function(){ jQuery(this).remove(); });
            }
            
           
		});

		return false;

	});
	
	//DELETE PIN BUTTON
	jQuery('.waiting_posts').on('mouseenter','.pin_img_log',function(){
	    jQuery(this).find('.wp_pinterest-delete-pin').show();
	}).on('mouseleave','.pin_img_log',function(){
		jQuery(this).find('.wp_pinterest-delete-pin').hide();
	});
	
	
	//jQuery('.wp_pinterest-delete-pin').click(function() {
	jQuery('.waiting_posts').on('click','.wp_pinterest-delete-pin',function(){
		
		var imgSrc= jQuery(this).attr('data-hash');
		var pID =jQuery(this).attr('data-post');
		jQuery.ajax({
			url : ajaxurl,
			type : 'POST',

			data : {
				action : 'wp_pinterest_automatic_clear_post',
				id     : pID,
				img    : imgSrc
				

			} 
		
		});
		
		jQuery(this).parent().fadeOut(function(){
			jQuery(this).remove();
		});

		return false;

	});
	
	//RESPONSIVE DESIGN COLSPAN
	jQuery( window ).resize(function() {
		  jQuery('.wp_pins_holder').attr('colspan',jQuery('.pin_holder_table_head   th:visible').length -1 );

	});


	
	//update clock
	timedCountin();

	// Active var
	jQuery(window).blur(function(){
		isActive = false;
	});

	jQuery(window).focus(function(){
		isActive = true;
	});

});//Doc ready


