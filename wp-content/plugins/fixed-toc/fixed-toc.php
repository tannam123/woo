<?php
/*
 * Plugin Name: Fixed TOC
 * Plugin URI: https://codecanyon.net/item/fixed-toc-wordpress-plugin/7264676?ref=wphigh
 * Description: Generate a table of contents automatically from content of a post. Fixing in the page, user-friendly view.
 * Author: wphigh
 * Author URI: https://codecanyon.net/user/wphigh?ref=wphigh
 * Version: 3.1.24
 * Created: 26 March 14
 * Last Update: 20 July 21
 * Text Domain: fixedtoc
 * License: See http://codecanyon.net/licenses
 */

/**
 * Prevent access directly.
 *
 * @since 3.1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Define the plugin version.
 *
 * @since 3.1.16
 *
 * @todo Change version after upgrade
 */
const FTOC_VERSION = '3.1.24';

/**
 * Define the plugin absolute root directory.
 *
 * @since 3.0.0
 */
define( 'FTOC_ROOTDIR', plugin_dir_path( __FILE__ ) );

/**
 * Define the plugin absolute root file.
 *
 * @since 3.1.0
 */
const FTOC_ROOTFILE = __FILE__;

/**
 * Functions
 *
 * @since 3.0.0
 */
require_once 'inc/functions.php';

/**
 * Initialization
 *
 * @since 3.1.0
 */
require_once 'inc/init.php';
new Fixedtoc_Init();

/**
 * Conditions
 *
 * @since 3.0.0
 */
require_once 'inc/class-conditions.php';

/**
 * Admin control
 *
 * @since 3.0.0
 */
require_once 'admin/class-admin-control.php';
new Fixedtoc_Admin_Control();

/**
 * Frontend control
 *
 * @since 3.1.22 Only running after the 'after_setup_theme' action.
 * @since 3.0.0
 */
if ( ! is_admin() ) {
	add_action( 'after_setup_theme', function () {
		require_once 'frontend/class-frontend-control.php';
		new Fixedtoc_Frontend_Control();
	} );
}

/**
 * The code below is set to compatible with some special themes and plugins.
 *
 * @since 3.1.16
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Compatible with Rate My Post plugin.
if ( ! is_admin() && is_plugin_active( 'rate-my-post/rate-my-post.php' ) ) {
	require_once 'compatibility/rate-my-post-plugin.php';
}

// Compatible with Rank Math plugin.
if ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
	require_once 'compatibility/rank-math-plugin.php';
}

/**
 * Compatible with Kadence Blocks plugin.
 *
 * @since 3.1.21
 */
if ( is_plugin_active( 'kadence-blocks/kadence-blocks.php' ) ) {
	require_once 'compatibility/kadence-blocks-plugin.php';
}

/**
 * Compatible with amp plugin.
 *
 * @since 3.1.22
 */
if ( is_plugin_active( 'amp/amp.php' ) ) {
	require_once 'compatibility/amp-plugin.php';
}