<?php
/**
*
* Admin Reports - /wp-admin/admin.php?page=wc-reports&tab=order_tip
* Soon these reports will be removed. For the time being they can still be accessed at the above URL
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/***
* @since 1.2.0
*/
class WOO_Order_Tip_Admin_Reports_Views {

    function display_orders_list_reports( $data ) {
        ob_start();
    ?>
    <div id="woo-order-tip-reports">
        <div id="woo-order-tip-reports-date-range">
            <div class="wot-reports-col">
                <label for="wot-reports-date-from">
                    <?php _e( 'From', 'order-tip-woo' ); ?>
                </label>
                <input type="text" id="wot-reports-date-from" placeholder="Click to choose date" value="<?php echo date( 'Y-m-d', strtotime('-30 days') ); ?>" />
            </div>
            <div class="wot-reports-col">
                <label for="wot-reports-date-to">
                    <?php _e( 'To', 'order-tip-woo' ); ?>
                </label>
                <input type="text" id="wot-reports-date-to" placeholder="Click to choose date" value="<?php echo date('Y-m-d'); ?>" />
            </div>
            <div class="wot-reports-col">
                <label for="wot-reports-order-status">
                    <?php _e( 'Order Status', 'order-tip-woo' ); ?>
                </label>
                <select id="wot-reports-order-status">
                    <option value="all"><?php _e( 'All', 'order-tip-woo' ); ?></option>
                    <?php
                        if( $data['av_statuses'] ) {
                            foreach( $data['av_statuses'] as $status => $label ) {
                    ?>
                    <option value="<?php echo $status; ?>"><?php echo $label; ?></option>
                    <?php
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="wot-reports-col">
                <button id="wot-set-filters" class="button"><?php _e( 'Filter', 'order-tip-woo' ); ?></button>
            </div>
            <div class="wot-reports-col">
                <a id="wot-export-csv" href="<?php echo esc_url( admin_url() ) . 'admin.php?page=wc-reports&tab=order_tip&a=export&from=' . date( 'Y-m-d', strtotime('-30 days') ) . '&to=' . date('Y-m-d'); ?>" class="button"><?php _e( 'Export to CSV', 'order-tip-woo' ); ?></a>
            </div>
        </div>
        <div id="woo-order-tip-reports-errors"></div>
        <p id="displaying-from-to">
            <?php
                printf(
                    __( 'Displaying orders between %s and %s', 'order-tip-woo' ),
                    '<span id="displaying-from">' . date( $data['date_format'], strtotime('-30 days') ) . '</span>',
                    '<span id="displaying-to">' . date( $data['date_format'] ) . '</span>'
                );
            ?>
        </p>
        <table id="woo-order-tip-reports-table" class="wp-list-table widefat fixed striped table-view-list pages">
            <thead>
            <tr>
                <th><?php _e( 'Order ID', 'order-tip-woo' ); ?></th>
                <th><?php _e( 'Order Status', 'order-tip-woo' ); ?></th>
                <th><?php _e( 'Customer', 'order-tip-woo' ); ?></th>
                <th><?php _e( 'Type', 'order-tip-woo' ); ?></th>
                <th><?php _e( 'Value', 'order-tip-woo' ); ?></th>
                <th><?php _e( 'Date/Time', 'order-tip-woo' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
                $total = 0;
                foreach( $data['order_ids'] as $order_id => $order_data ) {
                    $order = wc_get_order( $order_id );
                    $order_status = $order->get_status();
                    $total += $order_data['value'];
                    $date = $order_data['date'];
                    $date_format = str_split( $data['date_format'] );
                    if( ! in_array( array( 'a', 'A', 'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'v' ), $date_format ) ) {
                        $date_format = apply_filters( 'wc_order_tip_reports_date_time_format', implode( '', $date_format ) . ' H:i:s' );
                    }
            ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url( admin_url() ); ?>post.php?post=<?php echo esc_html( $order_id ); ?>&action=edit" target="_blank"><?php echo esc_html( $order_id ); ?></a>
                </td>
                <td>
                    <?php echo esc_html( $data['av_statuses'] ? $data['av_statuses'][ 'wc-' . $order_status ] : ucfirst( $order_status ) ); ?>
                </td>
                <td>
                    <?php echo esc_html( $order_data['customer'] ); ?>
                </td>
                <td>
                    <?php echo esc_html( $order_data['type'] ); ?>
                </td>
                <td>
                    <?php echo get_woocommerce_currency_symbol() . esc_html( number_format( $order_data['value'], 2 ) ); ?>
                </td>
                <td>
                    <?php echo esc_html( date( $date_format, strtotime( $order_data['date'] ) ) ); ?>
                </td>
            </tr>
            <?php } ?>
            </tbody>
            <?php if( $data['order_ids'] && $total ) { ?>
            <tfoot>
                <td colspan="6"><strong><?php _e( 'Total', 'order-tip-woo' ); ?>: <?php echo get_woocommerce_currency_symbol(); ?><span id="woo-order-tip-reports-total"><?php echo number_format( $total, 2 ); ?></span></strong></td>
            </tfoot>
            <?php } ?>
        </table>
    </div>
    <?php
        return ob_get_clean();
    }

    function display_orders_list_reports_row( $data ) {

        ob_start();
    ?>
    <tr>
        <td>
            <a href="<?php echo esc_url( admin_url() ); ?>post.php?post=<?php echo esc_html( $data['order_id'] ); ?>&action=edit" target="_blank"><?php echo esc_html( $data['order_id'] ); ?></a>
        </td>
        <td>
            <?php echo esc_html( $data['av_statuses'] ? $data['av_statuses'][ 'wc-' . $data['order_status'] ] : ucfirst( $data['order_status'] ) ); ?>
        </td>
        <td>
            <?php echo esc_html( $data['customer'] ); ?>
        </td>
        <td>
            <?php echo esc_html( $data['type'] ); ?>
        </td>
        <td>
            <?php echo get_woocommerce_currency_symbol() . esc_html( number_format( $data['value'], 2 ) ); ?>
        </td>
        <td>
            <?php echo esc_html( date( $data['date_format'], strtotime( $data['date'] ) ) ); ?>
        </td>
    </tr>
    <?php
        return ob_get_clean();

    }

}
?>
