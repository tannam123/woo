<?php
/**
*
* Admin Notices
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WOO_Order_Tip_Admin_Notices {

    /**
    * Constructor
    **/
    function __construct() {
        add_action('admin_notices', array( $this, 'save_options_notif' ) );
    }

    /**
    * Adds a notification if settings are not saved after update to version 1.1
    * @since 1.0.0
    **/
    function save_options_notif() {

        $updated_1_1 = get_option( 'wc_order_tip_updated_1_1' );
        if( ! $updated_1_1 ) {
?>
    <div class="notice notice-error">
        <p>
            <?php
                printf(
                    __( "To ensure full compatibility of the %sOrder Tip for WooCommerce%s plugin, please navigate to the plugin's %s, review the settings and save before proceeding.", 'order-tip-woo' ),
                    '<strong>',
                    '</strong>',
                    '<a href="' . esc_url( admin_url() ) . 'admin.php?page=wc-settings&tab=order_tip"><strong>' . __( 'settings page', 'order-tip-woo' ) . '</strong></a>'
                );
            ?>
        </p>
    </div>
<?php
        }

    }

}
