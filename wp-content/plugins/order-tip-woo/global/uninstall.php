<?php
/**
*
* Remove all the plugin's options on uninstall
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function woootip_uninstall() {

    $options = array(
        'wc_order_tip_enabled_cart',
        'wc_order_tip_cart_position',
        'wc_order_tip_enabled_checkout',
        'wc_order_tip_checkout_position',
        'wc_order_tip_is_taxable',
        'wc_order_tip_fee_name',
        'wc_order_tip_title',
        'wc_order_tip_type',
        'wc_order_tip_rates',
        'wc_order_tip_custom',
        'wc_order_tip_custom_label',
        'wc_order_tip_custom_apply_label',
        'wc_order_tip_enter_placeholder',
        'wc_order_tip_custom_remove_label',
        'wc_order_tip_cash',
        'wc_order_tip_cash_label',
        'wc_order_tip_remove_new_order',
        'wc_order_tip_updated_1_1'
    );

    foreach( $options as $option ) {
        delete_option( $option );
    }

}
?>
