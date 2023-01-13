<?php
/**
* @package cloudWEB VidaXL Dropshipping
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

if ( ! class_exists( 'WOO_Order_Tip_Admin_Settings' ) ) :


function woo_order_tip_admin_add_settings( $settings ) {

	/**
	 * Settings class
	 * @since 1.0.0
	 */
	class WOO_Order_Tip_Admin_Settings extends \WC_Settings_Page {

        protected $id, $label;

		/**
		 * Setup settings class
		 * @since  1.0.0
		 */
		public function __construct() {

			$this->id    = 'order_tip';
			$this->label = __( 'Order Tip', 'order-tip-woo' );

			add_filter( 'woocommerce_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id,      array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'woocommerce_sections_' . $this->id,      array( $this, 'output_sections' ) );
			add_action( 'woocommerce_admin_field_order_tip_reports', array( $this, 'display_synchronize_products_field' ) );

		}

		/**
		 * Include reports in the Order Tip settings section under WooCommerce settings
		 * @since  1.2.0
		 */
        public function display_synchronize_products_field( $values ) {

           wc_get_template('reports.php', array(), '', WOOOTIPPATH . '/templates/reports/');

        }

		/**
		 * Get settings sections
		 * @since 1.0.0
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				'settings' => __( 'Settings', 'order-tip-woo' ),
				'reports'  => __( 'Tip Reports', 'order-tip-woo' )
			);

			return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );

		}


		/**
		 * Get settings array
		 * @since 1.0.0
		 * @param string $current_section Optional. Defaults to empty string.
		 * @return array Array of settings
		 */
		public function get_settings( $current_section = '' ) {

            switch( $current_section ) {

                case 'settings': default:

                    $settings = apply_filters( 'wc_order_tip_settings', array(

                        array(
                            'name'     => __( 'Order Tip Settings', 'order-tip-woo' ),
                            'type'     => 'title',
                            'desc'     => '',
                            'id'       => 'wc_order_tip_section_title'
                        ),

                        array(
                            'name'     => __( 'Shortcode', 'order-tip-woo' ),
                            'type'     => 'title',
                            'desc'     => sprintf( __( 'You can use the %1$s[order_tip_form]%2$s shortcode in any page, post or widget. It will automatically take the settings below. However, we %1$sdo not%2$s recommend to use the shortcode on the cart or checkout pages if you enable the tip form using the checkboxes below.', 'order-tip-woo' ), '<strong>', '</strong>' ),
                            'id'       => 'wc_order_tip_shortcode'
                        ),

                        array(
                            'name'     => __( 'Enabled on Cart page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'If checked, the tip form will appear under the Apply Coupon form on the Cart page', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                'yes'  => __( 'Yes', 'order-tip-woo' ),
                                'no'   => __( 'No', 'order-tip-woo' )
                            ),
                            'label'    => __( 'Enable', 'order-tip-woo' ),
                            'id'       => 'wc_order_tip_enabled_cart'
                        ),

                        array(
                            'name'    => __( 'Select position on the cart page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'Select the position on the cart page. Please make sure to enable the tip form using the checkbox above.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                'before_cart'  => __( 'Before the cart', 'order-tip-woo' ),
                                'after_coupon' => __( 'After the coupon and before the Update cart button', 'order-tip-woo' ),
                                'after_cart_table' => __( 'After the cart table', 'order-tip-woo' ),
                                'before_totals'=> __( 'Before cart totals', 'order-tip-woo' ),
                                'after_cart'   => __( 'After the cart', 'order-tip-woo' ),
                            ),
                            'id'       => 'wc_order_tip_cart_position'
                        ),

                        array(
                            'name'     => __( 'Refresh the order totals on the Cart page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'If checked, the order totals on the cart page will be automatically updated when a tip is added or removed. If unchecked, the tip will not appear in the totals until a page refresh is performed.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_refresh_cart'
                        ),

                        array(
                            'name'     => __( 'Enabled on Checkout page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'If checked, the tip form will appear under the Checkout form on the Checkout page', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                'yes'  => __( 'Yes', 'order-tip-woo' ),
                                'no'   => __( 'No', 'order-tip-woo' )
                            ),
                            'label'    => __( 'Enable', 'order-tip-woo' ),
                            'id'       => 'wc_order_tip_enabled_checkout'
                        ),

                        array(
                            'name'    => __( 'Select position on the checkout page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'Select the position on the checkout page. Please make sure to enable the tip form using the checkbox above.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                'before_checkout_form'  => __( 'Before the checkout form', 'order-tip-woo' ),
                                'before_order_notes'    => __( 'Before the order notes field', 'order-tip-woo' ),
                                'after_customer_details'=> __( 'After customer details', 'order-tip-woo' ),
                                'before_order_review'   => __( 'Before the order review', 'order-tip-woo' ),
                                'after_checkout_form'   => __( 'After the checkout form', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_checkout_position'
                        ),

                        array(
                            'name'     => __( 'Refresh the order totals on the Checkout page', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'If checked, the order totals on the cart page will be automatically updated when a tip is added or removed. If unchecked, the tip will not appear in the totals until a page refresh is performed.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_refresh_checkout'
                        ),

                        array(
                            'name'     => __( 'Is taxable', 'order-tip-woo' ),
                            'type'     => 'select',
                            'desc'     => __( 'If checked, the tip amount will be taxed as per your WooCommerce Tax settings.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'options'  => array(
                                'no'   => __( 'Yes', 'order-tip-woo' ),
                                'yes'  => __( 'No', 'order-tip-woo' )
                            ),
                            'label'    => __( 'Enable', 'order-tip-woo' ),
                            'id'       => 'wc_order_tip_is_taxable'
                        ),

                        array(
                            'name'     => __( 'Tip fee name', 'order-tip-woo' ),
                            'type'     => 'text',
                            'desc'     => __( 'The tip fee name will appear before the order total. It will always be followed by the tip amount. Default format is "Tip (AMOUNT)"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => 'Tip',
                            'label'    => __( 'Enable', 'order-tip-woo' ),
                            'id'       => 'wc_order_tip_fee_name'
                        ),

                        array(
                            'name'     => __( 'Tip form title', 'order-tip-woo' ),
                            'type'     => 'text',
                            'desc'     => __( 'The tip form title will appear before the tip form', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'label'    => __( 'Enable', 'order-tip-woo' ),
                            'id'       => 'wc_order_tip_title'
                        ),

                        array(
                            'name'     => __( 'Tip Type', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Percent of the order total', 'order-tip-woo' ),
                                '2'    => __( 'Fixed amount', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_type',
                            'desc'     => __( 'Select the type of tip you would like to use.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Tip Rates', 'order-tip-woo' ),
                            'type'     => 'multiselect',
                            'css'      => 'min-height:120px',
                            'options'  => array(
                                '5'    => '5',
                                '10'   => '10',
                                '15'   => '15',
                                '20'   => '20',
                                '25'   => '25',
                                '30'   => '30'
                            ),
                            'id'       => 'wc_order_tip_rates',
                            'desc'     => __( 'Enable various tip rates. Keep CTRL or CMD key pressed while selecting.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Enable custom tip field', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_custom',
                            'desc'     => __( 'If enabled, the customer will be able to add their own fixed amount tip.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Custom tip field label', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_custom_label',
                            'desc'     => __( 'Set a label of your choice for the custom tip button. The default label is "Custom Tip"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Custom Tip', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Display custom tip field label in tip name', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_display_custom_tip_label_in_tip_name',
                            'desc'     => __( 'Display the custom tip field label in the tip fee name in paranthesis. Eg.: Tip (Add your own amount). If set to "Yes", this will appear on the cart page, on the totals page and in the order emails.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Custom tip apply tip button label', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_custom_apply_label',
                            'desc'     => __( 'Set a label of your choice for the custom tip apply button. The default label is "Add tip to order"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Add tip to order', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Custom tip enter tip input placeholder label', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_enter_placeholder',
                            'desc'     => __( 'Set a placeholder of your choice for the custom enter tip input field. The default label is "Enter tip amount"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Enter tip amount', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Custom tip remove tip button label', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_custom_remove_label',
                            'desc'     => __( 'Set a label of your choice for the custom tip remove button. The default label is "Remove tip"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Remove tip', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Enable cash tip', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_cash',
                            'desc'     => __( 'If enabled, customers will be able to choose to tip by cash (on delivery or local pickup).', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Cash tip field label', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_cash_label',
                            'desc'     => __( 'Set a label of your choice for the cash tip button. The default label is "Cash"', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Cash', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Clear tip after the order has been placed', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_remove_new_order',
                            'desc'     => __( 'If enabled, the tip that may be added to the cart, will be removed. Otherwise, it will be preserved on future orders in the current session.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Enable alert when removing the tip', 'order-tip-woo' ),
                            'type'     => 'select',
                            'options'  => array(
                                '1'    => __( 'Yes', 'order-tip-woo' ),
                                '0'    => __( 'No', 'order-tip-woo' )
                            ),
                            'id'       => 'wc_order_tip_enable_alert_remove_tip',
                            'desc'     => __( 'If enabled, an alert will pop up when the Remove Tip button is clicked.', 'order-tip-woo' ),
                            'desc_tip' => true
                        ),

                        array(
                            'name'     => __( 'Remove tip confirmation message', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_remove_confirm_msg',
                            'desc'     => __( 'Set a message of your choice for the tip removal confirmation message.', 'order-tip-woo' ),
                            'desc_tip' => true,
                            'default'  => __( 'Are you sure you wish to remove the tip?', 'order-tip-woo' )
                        ),

                        array(
                            'name'     => __( 'Updated to 1.1', 'order-tip-woo' ),
                            'type'     => 'text',
                            'id'       => 'wc_order_tip_updated_1_1',
                            'default'  => '1'
                        ),

                        array(
                            'type' => 'sectionend',
                            'id'   => 'general_settings'
                        ),

                    ) );

                break;

                case 'reports':

                    $settings = apply_filters( 'cw_vidaxl_ds_sync_products_settings', array(

						array(
                            'name' => __( 'Reports', 'order-tip-woo' ),
                            'type' => 'title',
                            'desc' => '',
                            'id'   => 'order_tip_reports_group',
                        ),

                        array(
                            'type'     => 'order_tip_reports',
                            'id'       => 'reports'
                        ),

						array(
                            'type' => 'sectionend',
                            'id'   => 'order_tip_reports_group'
                        ),

                    ) );

                break;

            }

			/**
			 * Filter MyPlugin Settings
			 *
			 * @since 1.0.0
			 * @param array $settings Array of the plugin settings
			 */
			return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

		}


		/**
		 * Output the settings
		 *
		 * @since 1.0
		 */
		public function output() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

			\WC_Admin_Settings::output_fields( $settings );

		}


		/**
	 	 * Save settings
	 	 *
	 	 * @since 1.0
		 */
		public function save() {

			global $current_section;

			$settings = $this->get_settings( $current_section );

			\WC_Admin_Settings::save_fields( $settings );

		}

	}

	$settings[] = new WOO_Order_Tip_Admin_Settings();

	return $settings;

}
add_filter( 'woocommerce_get_settings_pages', 'woo_order_tip_admin_add_settings', 15 );

endif;

?>
