<?php
/*
* Plugin Name: Order Tip for WooCommerce
* Plugin URI: https://order-tip-for-woocommerce.tudorache.me/
* Description: Adds a form to the cart and checkout pages where customer can add tips to the WooCommerce orders.
* Version: 1.2.2
* Author: Adrian Emil Tudorache
* Author URI: https://www.tudorache.me
* Text Domain: order-tip-woo
* Domain Path: /languages
* WC requires at least: 3.0.0
* WC tested up to: 6.3.0
* License: GPLv2 or later
*/

/**
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WOOTIPVER', '1.1' );
define( 'WOOOTIPPATH', plugin_dir_path( __FILE__ ) );
define( 'WOOOTIPBASE', plugin_basename( __FILE__ ) );
define( 'WOOOTIPURL', plugin_dir_url( __FILE__ ) );

load_plugin_textdomain( 'order-tip-woo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

require_once( __DIR__ . '/frontend/init.php' );
if( is_admin() ) {
    require_once( __DIR__ . '/admin/init.php' );
}

require_once( __DIR__ . '/global/uninstall.php' );

function woootip_deactivate_uninstall() {
    woootip_uninstall();
    flush_rewrite_rules();
}
register_uninstall_hook( __FILE__, 'woootip_deactivate_uninstall' );
?>
