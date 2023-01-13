<?php

/**
 * Supports AMP reader(legacy) mode
 *
 * @since 3.1.22
 */
add_filter( 'fixedtoc_frontend_init_hook', function ( $hook ) {
	if ( fixedtoc_amp_is_request() && fixedtoc_amp_is_legacy() ) {
		return 'wp';
	}

	return $hook;
} );