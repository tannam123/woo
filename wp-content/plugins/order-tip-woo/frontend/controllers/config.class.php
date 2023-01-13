<?php
/**
*
* Main confir Frontend
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOO_Order_Tip_Config {

    /**
    * Constructor
    **/
    function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 100 );
    }

    /**
    * Load assets
    **/
    function scripts() {

        $eart = get_option( 'wc_order_tip_enable_alert_remove_tip' );
        $ds   = get_option( 'woocommerce_price_decimal_sep' );

        wp_register_style( 'woo-order-tip-css', WOOOTIPURL . 'frontend/assets/css/woo-order-tip.css' );
        wp_register_script( 'woo-order-tip-js', WOOOTIPURL . 'frontend/assets/js/woo-order-tip.js', array('jquery'), null, true );
        wp_localize_script( 'woo-order-tip-js', 'wootip', array(
            'n'    => wp_create_nonce('apply_order_tip'),
            'n2'   => wp_create_nonce('remove_order_tip'),
            'au'   => admin_url( 'admin-ajax.php' ),
            'cs'   => get_woocommerce_currency_symbol(),
            'ic'   => is_cart(),
            'eart' => $eart ? $eart : 0,
            'ds'   => $ds ? $ds : '.',
            's'    => array(
                'rtc' => esc_html( get_option( 'wc_order_tip_remove_confirm_msg' ) ),
                'cut' => esc_html( get_option( 'wc_order_tip_custom_label' ) ),
                'cat' => esc_html( get_option( 'wc_order_tip_cash_label' ) )
            ),
        ) );
    }

}
?>
