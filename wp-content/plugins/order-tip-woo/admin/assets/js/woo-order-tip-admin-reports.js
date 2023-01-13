jQuery(document).ready(function(){

    jQuery('#wot-reports-date-from, #wot-reports-date-to').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    jQuery('body').on('click', '#wot-set-filters', function(e){

        e.preventDefault();

        var dateFrom = jQuery('#wot-reports-date-from'),
            dateTo   = jQuery('#wot-reports-date-to'),
            status   = jQuery('#wot-reports-order-status'),
            errormsg = jQuery('#woo-order-tip-reports-errors'),
            containerRes = jQuery('#woo-order-tip-reports-table tbody'),
            totalRes = jQuery('#woo-order-tip-reports-table tfoot #woo-order-tip-reports-total'),
            fromRes  = jQuery('#displaying-from-to #displaying-from'),
            toRes    = jQuery('#displaying-from-to #displaying-to'),
            errors   = order_tip_validate_dates();

        if( ! errors ) {

            errormsg.empty();

            jQuery('#woo-order-tip-reports').block({
                message: '',
                overlayCSS: {
                    backgroundColor: 'rgb(255,255,255)'
                }
            });

            jQuery.ajax({
                type: "POST",
                url: wootipar.aju,
                dataType: 'json',
                data: ({action: 'display_orders_list_reports_ajax', from: dateFrom.val(), to: dateTo.val(), status: status.val(), security: wootipar.ajn}),
                success: function(data) {

                    if( data.status == 'error' ) {
                        jQuery.each( data.errors, function(i, err) {
                            errormsg.append( '<p>' + err + '</p>' );
                        });
                    } else {
                        fromRes.text( data.after_date );
                        toRes.text( data.before_date );
                        containerRes.empty().html( data.result );
                        totalRes.empty().text( data.total );
                    }

                    jQuery('#woo-order-tip-reports').unblock();

                }
            });

        }

    });

    jQuery('body').on('change', '#wot-reports-date-from, #wot-reports-date-to', function(){

        var dateFrom = jQuery('#wot-reports-date-from'),
            dateTo   = jQuery('#wot-reports-date-to'),
            expButton= jQuery('#wot-export-csv'),
            errors   = order_tip_validate_dates();

        console.log(errors);

        if( ! errors ) {
            expButton.removeAttr('disabled').attr('href', wootipar.au + 'admin.php?page=wc-reports&tab=order_tip&a=export&from=' + dateFrom.val() + '&to=' + dateTo.val());
        } else {
            expButton.attr('disabled', 'disabled').attr('href', '#!');
        }

    });

});

function order_tip_validate_dates() {

    var dateFrom = jQuery('#wot-reports-date-from'),
        dateTo   = jQuery('#wot-reports-date-to'),
        errors   = 0;

    if( ! dateFrom.val() ) {
        dateFrom.css('border', '1px solid red').focus();
        errors = 1;
        return errors;
    } else {
        dateFrom.css('border', '1px solid #7e8993');
        errors = 0;
    }

    if( ! dateTo.val() ) {
        dateTo.css('border', '1px solid red').focus();
        errors = 1;
        return errors;
    } else {
        dateTo.css('border', '1px solid #7e8993');
        errors = 0;
    }

    return errors;

}
