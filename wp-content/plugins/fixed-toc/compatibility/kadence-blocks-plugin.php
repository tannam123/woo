<?php
/**
 * Compatible with Kadence Blocks plugin.
 *
 * @since 3.1.21
 */

// Fix a bug: Tabs style disappears when the Tabs block is applied.
add_filter( 'fixedtoc_data_raw_content', function ( $content ) {
	return str_replace( 'wp:kadence/tabs', '', $content );
} );