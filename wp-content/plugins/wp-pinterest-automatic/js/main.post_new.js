//INI
var isAutoChecked = 0;
var pinterestIndex = 0;
var wp_pinterest_automatic_selector;
var wp_pinterest_automatic_cf  = 'dummyonehere';

//ADD IMAGES LOOP
function timedCount() {

    // from editor
    jQuery('#content_ifr,.wp_attachment_holder' + wp_pinterest_automatic_selector).contents().find('img').each(
        function () {
        	
        	
        	//chek if not tinymce image
        	if( jQuery(this).attr('src').indexOf("tinymce") == -1  && jQuery(this).attr('src').indexOf("base64") == -1  ){
        	
        		var original = this;
        		var src = jQuery(this).attr('src');
        		src = src.replace("-150x150.",".");
        		
	            if (jQuery('#pin-images img[src="' + src + '"]').attr('src') != src ) {
	                jQuery('#img_template img').attr('src', src);
	                jQuery('#img_template input:checkbox').val(src);
	                jQuery('#pin-images').append(jQuery('#img_template').html());
	                jQuery('#field-pin_options-container').append('<input name="wp_pinterest_alts[]" type="hidden" value="' + jQuery(this).attr('alt') + '"/>');
	                jQuery('#field-pin_options-container').append('<input name="wp_pinterest_index[]" type="hidden" value="' + src + '"/>');
	
	                if (isAutoChecked == 0) {
	                    if (jQuery.inArray('OPT_CHECK', val_arr) == 1) {
	                        jQuery('#pin-contain input').filter(':first').attr('checked', 'checked');
	                        isAutoChecked = 1;
	                    }
	                }
	
	            }
	            
        	}
            
        });

    // featured image
    jQuery('#set-post-thumbnail img').each(
        function () {

            var src = jQuery(this).attr('src');

            srcs = src.split('-');

            console.log(srcs);

            var lastIndex = (srcs.length - 1);

            var lastItem = srcs[lastIndex];

            console.log(lastItem);

            var lastItemParts = lastItem.split('.');
            //console.log(lastItemParts);
            var extention = lastItemParts[lastItemParts.length - 1];
            //console.log(extention);

            var i = 0;

            imgsrc = '';
            jQuery.each(srcs, function (index, value) {
                if (index == srcs.length - 1) {
                    if (lastItem.search("\\d+x\\d+") != '-1') {
                        imgsrc = imgsrc + '.' + extention;
                    } else {
                        imgsrc = imgsrc + '-' + lastItem;
                    }
                } else if (index != 0) {
                    imgsrc = imgsrc + '-' + value;
                } else {
                    imgsrc = value;
                }
            });


            //console.log( jQuery('#pin-images img[src="'+ jQuery(this).attr('src') +'"]' ).attr('src') );

            if (jQuery('#pin-images img[src="' + imgsrc + '"]').attr('src') != imgsrc) {
                jQuery('#img_template img').attr('src', imgsrc);
                jQuery('#img_template input:checkbox').val(imgsrc);
                jQuery('#pin-images').append(jQuery('#img_template').html());
                
                //alt text 
                jQuery('#field-pin_options-container').append('<input name="wp_pinterest_alts[]" type="hidden" value="' + jQuery(this).attr('alt') + '"/>');
                jQuery('#field-pin_options-container').append('<input name="wp_pinterest_index[]" type="hidden" value="' + imgsrc + '"/>');



                if (isAutoChecked == 0) {
                    if (jQuery.inArray('OPT_CHECK', val_arr) == 1) {
                        jQuery('#pin-contain input').filter(':first').attr('checked', 'checked');
                        isAutoChecked = 1;
                    }
                }


            }
        });
    
    //custom filed image
    if( jQuery('input[value="'+ wp_pinterest_automatic_cf +'"]').length > 0 ){
        var fkey = (jQuery('input[value="'+ wp_pinterest_automatic_cf +'"]').attr('name').replace('key','value'));
        console.log(fkey);
        fkey=fkey.replace('meta[','');
        fkey=fkey.replace('][value]','');
        
       imgsrc = (jQuery('#meta\\['+fkey+'\\]\\[value\\]').val());
       
       if (jQuery('#pin-images img[src="' + imgsrc + '"]').attr('src') != imgsrc) {
           jQuery('#img_template img').attr('src', imgsrc);
           jQuery('#img_template input:checkbox').val(imgsrc);
           jQuery('#pin-images').append(jQuery('#img_template').html());

            
           
           if (isAutoChecked == 0) {
               if (jQuery.inArray('OPT_CHECK', val_arr) == 1) {
                   jQuery('#pin-contain input').filter(':first').attr('checked', 'checked');
                   isAutoChecked = 1;
               }
           }


       }
  
    }


    t = setTimeout("timedCount()", 5000);
}

timedCount();


jQuery(document).ready(function () {

    //select all
    jQuery('#wp_pinterest_automatic_all')
        .click(
            function () {



                if (jQuery(this).attr('checked') == 'checked') {
                    jQuery(
                        '#pin-contain input.pin_check')
                        .attr('checked', 'true');
                } else {
                    jQuery(
                        '#pin-contain input.pin_check')
                        .removeAttr('checked');
                }
            });



    //close link
    function activate_close() {
        jQuery('.close').click(function () {

            jQuery(this).parent().fadeOut('slow');

        });

    }


    //slider function
    function slider(id, slide) {

        if (jQuery(id).attr("checked")) {
            jQuery(slide).slideDown();
        } else {
            jQuery(slide).slideUp();
        }
    }

    //slider function
    function slider_hider(id, slide, hide) {

        if (jQuery(id).attr("checked")) {
            jQuery(hide).slideUp('fast', function () {
                jQuery(slide).slideDown();
            });
        } else {
            jQuery(hide).slideDown();
            jQuery(slide).slideUp();
        }
    }

    slider('#field-pin_options-1', '#pin-contain');

    jQuery("#field-pin_options-1").change(function () {

        slider('#field-pin_options-1', '#pin-contain');

    });

    //slider
    slider('#field-pin_options-2', '#pin_vars');

    jQuery("#field-pin_options-2").change(function () {

        slider('#field-pin_options-2', '#pin_vars');

    });

});