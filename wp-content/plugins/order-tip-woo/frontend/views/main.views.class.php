<?php
/**
*
* Frontend Views
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOO_Order_Tip_Main_Views {

    /**
    * Tip form view
    **/
    function tip_form( $data ) {

        $settings   = $data['settings'];
        $tip_type   = $settings['wc_order_tip_type'] == '1' ? '%' : get_woocommerce_currency_symbol();
        $tip_rates  = apply_filters( 'wc_order_tip_rates', $settings['wc_order_tip_rates'] );
        $wc_session = WC()->session;
        $active_tip = $wc_session->get('tip');
        $active_class = '';

?>
    <div id="wooot_order_tip_form">

        <div class="order_tip_title"><?php echo apply_filters( 'wc_order_tip_title', $settings['wc_order_tip_title'] ); ?></div>

        <?php
            foreach( $tip_rates as $tip_rate ) {
                switch( $settings['wc_order_tip_type'] ) {
                    case '1':
                        $tip_label = $tip_rate . $tip_type;
                    break;
                    case '2':
                        $tip_label = $tip_type . ' ' . $tip_rate;
                    break;
                }

                if( $active_tip ) {
                    $active_class = $tip_rate == $active_tip['tip'] && $active_tip['tip_custom'] == '0' ? 'active' : '';
                }
        ?>

        <button id="woo_order_tip_<?php echo $tip_rate; ?>" type="button" class="woo_order_tip <?php echo isset( $active_class ) ? $active_class : ''; ?>" data-tip="<?php echo $tip_rate; ?>" data-tip-type="<?php echo $settings['wc_order_tip_type'] ?>" data-tip-custom="0" data-tip-cash="0"><?php echo $tip_label; ?></button>

        <?php } ?>

        <?php
            if( $settings['wc_order_tip_cash'] ) {

                if( $active_tip ) {
                    $active_class =  $active_tip['tip_custom'] == '0' && $active_tip['tip_cash'] == '1' ? 'active' : '';
                }
        ?>

        <button id="woo_order_tip_custom" type="button" class="woo_order_tip <?php echo $active_class; ?>" data-tip="0" data-tip-type="2" data-tip-custom="0"  data-tip-cash="1">
            <?php echo apply_filters( 'wc_order_tip_cash_label', $settings['wc_order_tip_cash_label'] ); ?>
        </button>

        <?php } ?>

        <?php
            if( $settings[ 'wc_order_tip_custom' ] ) {
                $ds = get_option( 'woocommerce_price_decimal_sep' );
                $ds ? $ds : '.';
                $active_tip_amount = isset( $active_tip['tip'] ) && $active_tip['tip'] ? str_replace( ',', $ds, str_replace( '.', $ds, $active_tip['tip'] ) ) : '';
                $custom_tip_suffix = isset( $active_tip['tip_custom'] ) && $active_tip['tip_custom'] == 1 ? ' (' . get_woocommerce_currency_symbol() . $active_tip_amount . ')' : '';
                $active_class      = isset( $active_tip['tip_custom'] ) && $active_tip['tip_custom'] == 1 ? 'active' : '';
        ?>

        <button id="woo_order_tip_custom" type="button" class="woo_order_tip <?php echo $active_class; ?>" data-tip="custom" data-tip-type="2" data-tip-custom="1" data-tip-cash="0">
            <?php echo apply_filters( 'wc_order_tip_custom_label', $settings['wc_order_tip_custom_label'] ); ?><?php echo $custom_tip_suffix; ?>
        </button>

        <p class="form-row woo_order_tip_custom_text_field">
            <input style="display:none;" type="text" class="input-text woo_order_tip_custom_text" data-tip-type="<?php echo esc_html( $settings['wc_order_tip_type'] ); ?>" data-currency="<?php echo get_woocommerce_currency_symbol(); ?>" placeholder="<?php echo apply_filters( 'wc_order_tip_custom_enter_tip_placeholder', $settings['wc_order_tip_enter_placeholder'] ); ?>" />
        </p>

        <?php } ?>

        <button class="woo_order_tip_apply" type="button" name="woo_order_tip_apply" style="display:none;"><?php echo esc_html( $settings['wc_order_tip_custom_apply_label'] ); ?><span></span></button>

        <button class="woo_order_tip_remove" type="button" style="<?php echo ! $active_tip ? 'display:none;' : ''; ?>"><?php echo esc_html( $settings['wc_order_tip_custom_remove_label'] ); ?></button>

    </div>
<?php

    }

}
?>
