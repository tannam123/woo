<?php
/**
*
* Main Frontend
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOO_Order_Tip_Main {

    /**
    * Frontend Views
    * @var object;
    **/
    private $views;

    /**
    * Plugin options
    * @var array;
    **/
    private $settings;

    /**
    * Constructor
    **/
    function __construct() {

        $this->views = new WOO_Order_Tip_Main_Views;

        $this->settings = array();
        $settings = array(
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
            'wc_order_tip_display_custom_tip_label_in_tip_name',
            'wc_order_tip_custom_apply_label',
            'wc_order_tip_enter_placeholder',
            'wc_order_tip_custom_remove_label',
            'wc_order_tip_cash',
            'wc_order_tip_cash_label',
            'wc_order_tip_enable_alert_remove_tip',
            'wc_order_tip_remove_new_order'
        );
        foreach( $settings as $setting ) {
            $this->settings[ $setting ] = get_option( $setting );
        }
        if( $this->settings['wc_order_tip_enabled_cart'] == 'yes' && $this->settings['wc_order_tip_cart_position'] ) {
            switch( $this->settings['wc_order_tip_cart_position'] ) {
                case 'before_cart':
                    add_action( 'woocommerce_before_cart', array( $this, 'tip_form' ) );
                break;
                case 'after_coupon':
                    add_action( 'woocommerce_cart_coupon', array( $this, 'tip_form' ) );
                break;
                case 'after_cart_table':
                    add_action( 'woocommerce_after_cart_table', array( $this, 'tip_form' ) );
                break;
                case 'before_totals':
                    add_action( 'woocommerce_before_cart_totals', array( $this, 'tip_form' ) );
                break;
                case 'after_cart':
                    add_action( 'woocommerce_after_cart', array( $this, 'tip_form' ) );
                break;
            }
        }
        if( $this->settings['wc_order_tip_enabled_checkout'] == 'yes' && $this->settings['wc_order_tip_checkout_position'] ) {
            switch( $this->settings['wc_order_tip_checkout_position'] ) {
                case 'before_checkout_form':
                    add_action( 'woocommerce_before_checkout_form', array( $this, 'tip_form' ) );
                break;
                case 'before_order_notes':
                    add_action( 'woocommerce_before_order_notes', array( $this, 'tip_form' ) );
                break;
                case 'after_customer_details':
                    add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'tip_form' ) );
                break;
                case 'before_order_review':
                    add_action( 'woocommerce_checkout_order_review', array( $this, 'tip_form' ) );
                break;
                case 'after_checkout_form':
                    add_action( 'woocommerce_after_checkout_form', array( $this, 'tip_form' ) );
                break;
            }

        }

        add_action( 'wp_ajax_apply_tip', array( $this, 'add_tip_to_session' ) );
        add_action( 'wp_ajax_nopriv_apply_tip', array( $this, 'add_tip_to_session' ) );
        add_action( 'wp_ajax_remove_tip', array( $this, 'remove_tip_from_session' ) );
        add_action( 'wp_ajax_nopriv_remove_tip', array( $this, 'remove_tip_from_session' ) );

        add_action( 'init', array( $this, 'init_session' ) );
        add_action( 'wp', array( $this, 'do_add_tip' ) );
        add_action( 'woocommerce_cart_calculate_fees', array( $this, 'do_add_tip' ) );
        add_action( 'woocommerce_new_order', array( $this, 'remove_tip_on_order_placed' ) );

        add_shortcode( 'order_tip_form', array( $this, 'tip_form_shortcode' ) );

    }

    /**
    * Initialize the classic PHP session. The tip is stored in both PHP session and Woo session.
    **/
    function init_session() {
        if( ! session_id() && WC()->session ) {
            session_start();
        }
    }

    /**
    * Store the tip in the session
    **/
    function add_tip_to_session() {

        check_ajax_referer( 'apply_order_tip', 'security' );

        $tip = array(
            'tip'       => floatval( sanitize_text_field( str_replace( ',', '.', $_POST['tip'] ) ) ),
            'tip_type'  => intval( $_POST['tip_type'] ),
            'tip_label' => sanitize_text_field( $_POST['tip_label'] ),
            'tip_cash'  => intval( $_POST['tip_cash'] ),
            'tip_custom'=> intval( $_POST['tip_custom'] )
        );

        if( $tip['tip_type'] == 2 && ! $tip['tip_cash'] ) {
            $tip['tip_label'] = get_option( 'wc_order_tip_custom_label' );
        }

        $_SESSION['tip'] = serialize( $tip );

        $wc_session = WC()->session;
        $sess_customer = $wc_session->get('customer');
        if( $sess_customer ) {
            $sess_customer['tip'] = $tip;
            $wc_session->set( 'tip', $tip );
        }

        echo 'success';

        wp_die();

    }

    /**
    * Remove the tip from the session
    **/
    function remove_tip_from_session() {

        check_ajax_referer( 'remove_order_tip', 'security' );

        $wc_session = WC()->session;
        $wc_session->__unset( 'tip' );

        unset( $_SESSION['tip'] );

        echo 'success';

        wp_die();

    }

    /**
    * Tip form shortcode callback
    **/
    function tip_form_shortcode() {

        return $this->tip_form();

    }

    /**
    * Tip form
    **/
    function tip_form() {

        $display_form = apply_filters( 'wc_order_tip_display_form', 1 );

        if( $display_form ) {
            wp_enqueue_style( 'woo-order-tip-css' );
            wp_enqueue_script( 'woo-order-tip-js' );
            $data = array(
                'settings' => $this->settings
            );
            echo $this->views->tip_form( $data );
        }

    }

    /**
    * Add tip action
    **/
    function do_add_tip() {

        $wc_session = WC()->session;
        $tip = $wc_session ? $wc_session->get('tip') : array();
        if( ! $tip ) {
            if( isset( $_SESSION ) && isset( $_SESSION['tip'] ) && $_SESSION['tip'] ) {
                $tip = unserialize( $_SESSION['tip'] );
            }
        }

        if( $tip ) {

            if( $tip == 'custom' ) {

                $tip_amount = $tip['tip'];

            } else {

                switch( $tip['tip_type'] ) {
                    case '1':
                        //Get subtotal
                        $subtotal = WC()->cart->get_subtotal();
                        $tip_amount = ( $tip['tip'] / 100 ) * $subtotal;
                    break;
                    case '2':
                        $tip_amount = $tip['tip'];
                    break;
                }

            }

            $is_taxable = isset( $this->settings['wc_order_tip_is_taxable'] ) && $this->settings['wc_order_tip_is_taxable'] == 'yes' ? true : false;

            if( $this->settings['wc_order_tip_display_custom_tip_label_in_tip_name'] ) {
                $tip_label = sprintf( '%s (%s)', esc_html( $this->settings['wc_order_tip_fee_name'] ), esc_html( $tip['tip_label'] ) );
            } else {
                $tip_label = sprintf( '%s', esc_html( $this->settings['wc_order_tip_fee_name'] ) );
            }

            WC()->cart->add_fee( $tip_label, $tip_amount, $is_taxable, '' );

        }

    }

    /**
    * Remove tip when an order is placed, if this feature is enabled in the backend
    **/
    function remove_tip_on_order_placed( $orderid ) {

        if( $this->settings['wc_order_tip_remove_new_order'] && ! is_admin() ) {

            $wc_session = WC()->session;
            if( $wc_session && $wc_session->get( 'tip' ) ) {
                $wc_session->__unset( 'tip' );
            }

            if( isset( $_SESSION ) && isset( $_SESSION['tip'] ) && $_SESSION['tip'] ) {
                unset( $_SESSION['tip'] );
            }

        }

    }

}
?>
