<?php
/**
 * Plugin Name: SalesGen - Store Info
 * Plugin URI: https://salesgen.io/woocommerce-store-info/
 * Description: Dynamic store info for all page, widgets...
 * Version: 0.3
 * Author: salesgen.io
 * Author URI: https://salesgen.io/
 * Text Domain: salesgen-store-info
 * Domain Path: /i18n/languages/
 *
 * @package Store Info
 */

if( !defined( 'SGSI' ) )
	define( 'SGSI', '0.3' );

if( !defined( 'DS' ) )
	define( 'DS', DIRECTORY_SEPARATOR );

/**
 * Set constants.
 */
define( 'SGSI_FILE', __FILE__ );

/**
 * Loader
 */
require_once 'core/loader.php';