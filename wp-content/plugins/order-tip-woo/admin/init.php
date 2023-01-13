<?php
/**
*
* Autoloader Admin
*
* @package Order Tip for WooCommerce
* @author  Adrian Emil Tudorache
* @license GPL-2.0+
* @link    https://www.tudorache.me/
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$files = array(
    'controllers/config.class.php'   => 'WOO_Order_Tip_Admin_Config',
    //'controllers/settings.class.php' => 'WOO_Order_Tip_Admin_Settings',
    'controllers/settings.class.php' => '',
    'views/reports.views.class.php'  => '',
    'controllers/reports.class.php'  => 'WOO_Order_Tip_Admin_Reports',
    'controllers/notices.class.php'  => 'WOO_Order_Tip_Admin_Notices'
);
foreach( $files as $file => $init ) {
    require_once( __DIR__ . '/' . $file );
    if( $init )
        new $init;
}
?>
