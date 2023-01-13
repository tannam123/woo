

			jQuery.ajax(
			
			{
				
				
				
			    url: jQuery(this).attr('href'),
			    type: 'POST',
			    data: {
			        action: "subscribe",
			        mail: jQuery('#admin_email').val() ,
			        original_mail:jQuery('#original_admin_email').val(),
			        original_uri: jQuery('#original_uri').val(),
			        original_name:jQuery('#original_name').val()
			        
			    },

				    success: function (data) {
				    	jQuery('#ajax-loadingimg').addClass('ajax-loading');
	
				    	if(data.substr(0,1) == '{'){
				    	
				    	
				        	var res = jQuery.parseJSON(data);
	
				             
					        if (res['status'] == 'success') {
					            // console.log(data);
		
		 
		
					        } else if (res['status'] == 'fail') {
		
					            alert('Can not login, make sure you of login email and password and try again');
					            jQuery('#ajax-loadingimg').addClass('ajax-loading');
						        return false
					        		
		
					        }
	
				        }else{
	
					        console.log('invalid json');
			
					    }
	
				    },
	
				    beforeSend: function () {
				    	
				    	jQuery('#ajax-loadingimg').removeClass('ajax-loading');
	
				    }// before send
	
	
				});// success
			return false;
			});

			