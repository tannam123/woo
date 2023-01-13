<?php
/**
*
* Admin Config
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOO_Order_Tip_Admin_Config {

    /**
    * Constructor
    **/
    function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ), 100 );
        add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
    }

    /**
    * Register and load assets
    * @since 1.0.0
    **/
    function scripts() {

        add_action( 'admin_body_class', array( $this, 'admin_body_class' ) );

        wp_register_style( 'woo-order-tip-jqueryui', WOOOTIPURL . 'admin/assets/css/jqueryui-1.12.1/jquery-ui.min.css' );
        wp_register_style( 'woo-order-tip-admin-reports', WOOOTIPURL . 'admin/assets/css/woo-order-tip-admin-reports.css' );
        wp_register_script( 'woo-order-tip-admin-blockui', WOOOTIPURL . 'admin/assets/js/jquery.blockUI.js', array('jquery'), null, true );
        wp_register_script( 'woo-order-tip-admin-reports', WOOOTIPURL . 'admin/assets/js/woo-order-tip-admin-reports.js', array('jquery'), null, true );
        wp_localize_script( 'woo-order-tip-admin-reports', 'wootipar', array(
            'au'  => admin_url(),
            'aju' => admin_url( 'admin-ajax.php' ),
            'ajn' => wp_create_nonce('reps')
        ) );

        if(
            isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' &&
            isset( $_GET['tab'] ) && $_GET['tab'] == 'order_tip'
        ) {
            wp_enqueue_script( 'woo-order-tip-admin', WOOOTIPURL . 'admin/assets/js/woo-order-tip-admin.js', array('jquery'), null, true );
        }

    }

    /**
    * Add specific class on the body tag on the reports page under WooCommerce -> Order Tip -> Tip Reports in order to hide the submit button which is irrelevant on this page
    * @since 1.2.0
    **/
    function admin_body_class( $classes ) {

        if(
            isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' &&
            isset( $_GET['tab'] ) && $_GET['tab'] == 'order_tip' &&
            isset( $_GET['section'] ) && $_GET['section'] == 'reports'
        ) {
            return $classes . ' order-tip-reports-settings';
        }

    }

    /**
    * Add row links to the plugins screen, along with the Deactivate link
    * @since 1.2.0
    **/
    function plugin_action_links( $plugin_actions, $plugin_file ) {

        $new_actions = array();

        if ( $plugin_file == 'order-tip-woo/order-tip-for-woocommerce.php' ) {
            $new_actions['order_tip_settings'] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=order_tip' ) ), __( 'Settings', 'order-tip-woo' ) );
            $new_actions['order_tip_reports'] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=order_tip&section=reports' ) ), __( 'Tip Reports', 'order-tip-woo' ) );
        }

        return array_merge( $new_actions, $plugin_actions );

    }

}
?>
