<?php
/**
 * Global functions.
 *
 * @since 3.0.0
 */

require_once FTOC_ROOTDIR . 'admin/field-data/class-field-data.php';

/**
 * Get option value.
 *
 * @since 3.0.0
 *
 * @param string $name
 *
 * @return mixed
 */
function fixedtoc_get_option( $name ) {
	static $option, $once;
	if ( ! $once ) {
		$option = get_option( 'fixed_toc' );
		$once ++;
	}

	return isset( $option[ $name ] ) ? $option[ $name ] : fixedtoc_get_field_data( $name, 'default' );
}

/**
 * Get meta value.
 *
 * @since 3.0.0
 *
 * @param string $name
 * @param int|bool $post_id
 * @param bool $origin
 *
 * @return mixed
 */
function fixedtoc_get_meta( $name, $post_id = false, $origin = false ) {
	static $meta, $once;
	if ( ! $once ) {
		$post_id = $post_id ?: get_the_ID();
		$meta    = get_post_meta( $post_id, '_fixed_toc', true );
		$once ++;
	}

	if ( $origin ) {
		return isset( $meta[ $name ] ) ? $meta[ $name ] : null;
	} else {
		return isset( $meta[ $name ] ) ? $meta[ $name ] : fixedtoc_get_option( $name );
	}
}

/**
 * Get final value.
 *
 * @since 3.0.0
 *
 * @param string $name
 * @param int|false $post_id
 *
 * @return mixed
 */
function fixedtoc_get_val( $name, $post_id = false ) {
	global $FIXEDTOC_WIDGET_VALS;
	$default = isset( $FIXEDTOC_WIDGET_VALS[ $name ] ) ? $FIXEDTOC_WIDGET_VALS[ $name ] : fixedtoc_get_option( $name );
	$meta    = fixedtoc_get_meta( $name, $post_id, true );
	$val     = ! is_null( $meta ) ? $meta : $default;

	return apply_filters( 'fixedtoc_get_val', $val );
}

/**
 * Determine whether the current request is for an AMP page.
 *
 * @since 3.1.21
 *
 * @return bool
 */
function fixedtoc_amp_is_request() {
	if ( is_plugin_active( 'amp/amp.php' ) && function_exists( 'amp_is_request' ) ) {
		return amp_is_request();
	} else {
		return false;
	}
}

/**
 * Determines whether the legacy AMP post templates are being used.
 *
 * @since 3.1.22
 *
 * @return bool
 */
function fixedtoc_amp_is_legacy() {
	if ( is_plugin_active( 'amp/amp.php' ) && function_exists( 'amp_is_legacy' ) ) {
		return amp_is_legacy();
	} else {
		return false;
	}
}