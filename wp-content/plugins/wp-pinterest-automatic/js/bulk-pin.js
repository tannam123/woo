jQuery(document).ready(function(){
	//ADD OPTION
	jQuery('select[name="action"],select[name="action2"]').append('<option value="wp_automatic_pin">Pin them</option>');
	
	
	//CLICH PIN
	jQuery('#doaction,#doaction2').click(function(){
	    if(jQuery('select[name="action"]').val() == 'wp_automatic_pin' || jQuery('select[name="action2"]').val() == 'wp_automatic_pin' ){
	        
	    	//add the spinner
	    	if(jQuery('.spinner-bulk-action').length == 0){
	    		jQuery(this).after('<span class="spinner spinner-bulk-action">');
	    	}
	    	
	    	//show the spinner
	    	jQuery('.spinner-bulk-action').addClass('is-active');
	    	
	    	var itms='';
	    	var itms_count=0;

	    	jQuery('input[name="post[]"]:checked').each(
	    	    function(index,itm){
	    	        console.log(jQuery(itm).val());
	    	        itms=itms + ',' + jQuery(itm).val();
	    	        itms_count++;    
	    	    }
	    	    
	    	    
	    	);

	        jQuery.ajax({
	            url: ajaxurl,
	            type: 'POST',

	            data: {
	                action: 'pinterest_automatic_pin',
	                itms: itms
	            },
	            
	            success : function(data){
	            	
	            	jQuery('.spinner-bulk-action').remove();

		       	alert(itms_count + ' items sent to the pin queue' );
	            	

	            	jQuery('input[name="post[]"]:checked').prop('checked',false);
 
		    	    	 jQuery('select[name="action"]').val('-1');
		    	    	 jQuery('select[name="action2"]').val('-1');

	            	
	            }
	        });

	    		
	    		        return false;  
	    }
	});
 
});