<?php
// Exit if accessed directly
if ( ! defined( 'DGWT_WCAS_FILE' ) ) {
	exit;
}

add_filter( 'hfg_template_locations', function ( $locations ) {
	$locations = array_merge( array( DGWT_WCAS_DIR . 'partials/themes/neve/' ), $locations );

	return $locations;
} );
