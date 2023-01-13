jQuery(document).ready(function() {

   if( jQuery('.woo_order_tip').length ) {
       woo_order_select_tip();
   }

   jQuery('body').on('click', '.woo_order_tip_apply', function(e){
       e.preventDefault();
       woo_order_apply_tip( jQuery(this) );
   });

   jQuery('body').on('click', '.woo_order_tip_remove', function(e){
       e.preventDefault();
       woo_order_remove_tip();
   });

});

function woo_order_select_tip() {

   jQuery('body').on('click', '.woo_order_tip', function(e){

       e.preventDefault();

       var applyTip = jQuery(this).parent().find('button.woo_order_tip_apply');

       jQuery('.woo_order_tip').removeClass('active');

       jQuery(this).addClass('active');

       var tip = jQuery(this).data('tip');

       if( tip == 'custom' ) {
           applyTip.toggle();
           jQuery(this).parent().find('.woo_order_tip_custom_text').toggle().focus();
       } else {
           woo_order_apply_tip( jQuery(this) );
       }

       jQuery('.woo_order_tip_custom_text').on('keypress', function(e){
           if(e.keyCode == 13) {
               e.preventDefault();
               return false;
           }
       });

       jQuery('.woo_order_tip_custom_text').on('change', function(e){

           jQuery(this).val( jQuery(this).val().replace(/[^0-9.,]/g, '') );

       });

   });

}

function woo_order_apply_tip( trigger ) {

   var container = trigger.parent(),
       tip       = container.find('.woo_order_tip.active').data('tip'),
       tip_type  = container.find('.woo_order_tip.active').data('tip-type'),
       tip_custom= container.find('.woo_order_tip.active').data('tip-custom'),
       tip_cash  = container.find('.woo_order_tip.active').data('tip-cash'),
       tip_label = container.find('.woo_order_tip.active').text(),
       errors    = 0;

   if( tip == 'custom' ) {

       tip = container.find('.woo_order_tip_custom_text').val();

       if( ! tip ) {
           container.find('.woo_order_tip_custom_text').css('border', '1px solid red').focus();
           errors = 1;
           return false;
       } else {
           container.find('.woo_order_tip_custom_text').css('border', 'initial');
           errors = 0;
       }

   }

   if( ! errors ) {

       jQuery('.woocommerce').block({message: ''});

       jQuery.ajax({
           type: "POST",
           url: wootip.au,
           dataType: 'html',
           data: ({action: 'apply_tip', tip: tip, tip_type: tip_type, tip_label: tip_label, tip_custom: tip_custom, tip_cash: tip_cash, security: wootip.n}),
           success: function (tipApplied) {

               if( tipApplied == 'success' ) {
                   if( tip_custom ) {
                       jQuery('.woo_order_tip[data-tip="custom"]').text( wootip.s.cut + ' (' + wootip.cs + tip.replace( ',', wootip.ds ).replace( '.', wootip.ds ) + ')' );
                   }
                   jQuery('body').trigger( 'update_checkout' );
                   if( jQuery( 'button[name="update_cart"]' ).length ) {
                       jQuery( 'button[name="update_cart"]' ).attr('aria-disabled', false).removeAttr('disabled').trigger('click');
                   }
                   jQuery('.woocommerce').unblock();
                   jQuery('.woo_order_tip_remove').show();
                   jQuery('.woo_order_tip_apply').hide();
                   jQuery('.woo_order_tip_custom_text').hide();
               }

           }
       });

   }

}

function woo_order_remove_tip() {

    if( wootip.eart == '1' ) {

        if( confirm( wootip.s.rtc ) === true ) {
            do_woo_order_remove_tip();
        }

    } else {

        do_woo_order_remove_tip();

    }

}

function do_woo_order_remove_tip() {

    jQuery('.woocommerce').block({message: ''});

    jQuery.ajax({
        type: "POST",
        url: wootip.au,
        dataType: 'html',
        data: ({action: 'remove_tip', security: wootip.n2}),
        success: function (tipRemoved) {

            if( tipRemoved == 'success' ) {
                //location.reload(true);
                jQuery('.woo_order_tip[data-tip="custom"]').text( wootip.s.cut );
                jQuery('body').trigger( 'update_checkout' );
                jQuery('[name="update_cart"]').attr('aria-disabled', false).removeAttr('disabled').trigger('click');
                jQuery('.woocommerce').unblock();
                jQuery('.woo_order_tip_remove').hide();
                jQuery('.woo_order_tip').removeClass('active');
            }

        }
    });

}
