//UPDATE CURRENT TIME
var serverHour = 0;
var serverDiff = 200;
var currentHour = 0;
var nextRun = 0;
var isActive = true; //is document active or not

//UPDATE CLOCK FUNCTION
function timedCountin() {
	
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
	
	//loop here 
	if( isActive == true){
		
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
					
					//last image
					if(jQuery('.last_pin').attr('data-img-hash') != res['last_hash'] ){
	
						jQuery('#last_pin_link').attr('href',res['last_url']);
						jQuery('.last_pin').css('background-image','url('+res['last_img']+')');
						jQuery('.last_pin').attr('data-img-hash',res['last_hash']);
						
					}
	
				} else if (res['status'] == 'fail') {
	
				}
	
				//posting message 
	
			} 
		});

	}// Active 
	
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

//loop to add recent items 
function timedCount() {
	
	if( isActive == true){
			
		//loop here ; 
	  	jQuery('#update_log').trigger('click');
	}
	
  	//schedule
  	t = setTimeout("timedCount()", 5000);
}



jQuery(document).ready(function(){
	
	//UPDATE LOG
	jQuery('#update_log').click(function(){
		
		jQuery('.spinner').addClass('is-active'); 
		jQuery.ajax({
			url : ajaxurl,
			type : 'POST',
			dataType: 'json',
			data : {
				action : 'wp_pinterest_automatic_log_itms',
				last   : jQuery('.row-id').attr('data-row-id') 

			},
			
			success: function(data){
				var divide = 1;
				var divclass= 'alternate';
				
				if(jQuery('.table_log tr:first-child').hasClass('alternate') ){
					divide = 0;
				}
				
				jQuery('.spinner').removeClass('is-active');
					
					jQuery(data).each(function(ind,val){
						
						console.log(val['id']);
						
						jQuery('.empty_log').remove();
						
						if( divide % 2 == 1){
							divclass= 'alternate';
						}else{
							divclass= '';
						}
						
						jQuery('.table_log tbody').prepend('<tr class=" '+ divclass + ' ' + val['action'] +'"><td class="column-date row-id" data-row-id="' +val['id']+'">NEW</td><td style="padding:5px" class="column-response">'+  val['date']   +'</td><td style="padding:5px" class="column-response">' +val['action'] +'</td><td style="padding:5px"> '+ decodeURIComponent(val['data']) +' </td></tr>');
						
						divide = divide +1;
						
					}); 
			}
			
		});
		
		if(jQuery('.table_log tbody tr').length ==0 && jQuery('.empty_log').length == 0 ){
			jQuery('tbody').append('<p class="empty_log" style="margin-left:20px">Empty Log .. </p>');
		}
		
		return false;
		
	});
	
	//TRIGER CRON
	jQuery('#wp_pinterest_automatic_trigger_cron').click(function() {
		jQuery.ajax({
			url : jQuery(this).attr('href'),
			type : 'POST',

 
            beforeSend:function(){
            	 
            		jQuery('.spinner').addClass('is-active');
            	
            },
            
            success:function(data){
  
	            	jQuery('.spinner').removeClass('is-active');
	            	alert(data);
	             	
            }
            
           
		});

		return false;

	});
	
	//trigger loop
	timedCount();
	
	
	//CLEAR LOG 
	jQuery('#clear_log').click(function(){
		
		if(!confirm('Clear all log records?')){
			return false;
		}  
 		
		jQuery('.spinner').show();
		jQuery.ajax({
            url: ajaxurl,
            type: 'POST',

            data: {
                action: 'pinterest_automatic_clear',
                
            },
            
            success:function(){
            	jQuery('.spinner').hide();
            	jQuery('.row-id').parent().fadeOut('slow',function(){jQuery(this).remove();});
        		jQuery('tbody').append('<p class="empty_log" style="margin-left:20px">Log Cleared .. </p>');
        		
            }
            
            
        });
		
		return false;
		
		
	});
	
	//clock trigger
	timedCountin();
	
	jQuery(window).blur(function(){
		  //your code here
		 
		isActive = false;
		
		});
	jQuery(window).focus(function(){
		  //your code
		isActive = true;
		});

});
  