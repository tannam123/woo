<?php

/**
 * Removes the shortcodes of Rate My Post when creating a TOC data.
 *
 * Because the shortcodes only run once, they can't run when creating data.
 *
 * @since 3.1.16
 *
 * @param string $content
 *
 * @return string
 */
function fixedtoc_remove_shortcodes_for_data( $content ) {
	return str_replace( array(
		'[ratemypost]',
		'[ratemypost-result]'
	), '', $content );
}

add_filter( 'fixedtoc_data_raw_content', 'fixedtoc_remove_shortcodes_for_data' );