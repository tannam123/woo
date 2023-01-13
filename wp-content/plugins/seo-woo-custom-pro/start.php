<?php
/*
 * Plugin Name: SEO Woo Custom
 * Version: 4.1.4
 * Plugin URI: https://www.facebook.com/nguyenvietminh93
 * Description: SEO Woo Custom plugin. This is the plugin edited by NVM gift for Mr. Hoa.
 * Author URI: https://www.facebook.com/nguyenvietminh93
 * Author: NVM
 * Requires at least: 4.0
 * Tested up to: 5.9
 * Text Domain: wcpa-text-domain
 * WC requires at least: 3.5.0
 * WC tested up to: 5.6.0
 */


if ( defined('WCPA_POST_TYPE') && defined('WCPA_VERSION') ) {//to check free version alredy running
    add_action('admin_notices', function () {
        ?>
        <div class="error">
            <p>It is found that free version of this plugin <strong> SEO Woo Custom</strong> is
                running on this site.
                Please deactivate or remove the same in order to work this plugin properly </p>
        </div>
        <?php
    });
} else {

    define('WCPA_POST_TYPE', 'wcpa_pt_forms');
    define('WCPA_LIST_PAGE_HOOCK', 'wcpa_manage');
    define('WCPA_CART_ITEM_KEY', 'wcpa_data');

    if(!defined('WCPA_UPLOAD_DIR')){
        define('WCPA_UPLOAD_DIR', 'wcpa_uploads');
    }
    if(!defined('WCPA_UPLOAD_CUSTOM_BASE_DIR')){
        define('WCPA_UPLOAD_CUSTOM_BASE_DIR', false);
    }

    define('WCPA_PRODUCT_META_KEY', '_wcpa_product_meta');
    define('WCPA_PRODUCT_META_FIELD', 'wcpa_product_meta');
    define('WCPA_ORDER_META_KEY', '_WCPA_order_meta_data');
    define('WCPA_TEXT_DOMAIN', 'wcpa-text-domain');// dont use this MACRO, directly use wcpa-text-domain
    define('WCPA_FORM_META_KEY', '_wcpa_fb-editor-data');
    define('WCPA_SETTINGS_KEY', 'wcpa_settings_key');
    define('WCPA_META_SETTINGS_KEY', 'wcpa_meta_settings_key');
    define('WCPA_PRODUCTS_TRANSIENT_KEY', 'wcpa_products_transient_ver_2');
    define('WCPA_EMPTY_LABEL', 'wcpa_empty_label');
    define('WCPA_TOKEN', 'wcpa');
    define('WCPA_CRON_HOOK', 'wcpa_daily_event');
    define('WCPA_VERSION', '4.1.4');
    define('WCPA_FILE', __FILE__);
    define('WCPA_ITEM_ID', 167);
    define('WCPA_PLUGIN_NAME', 'Woocommerce Custom Product Addons');
    define('WCPA_STORE_URL', 'https://api.acowebs.com');


    require_once(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes/helpers.php');
    if (!function_exists('wcpa_init')) {

        function wcpa_init()
        {
            $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
            load_plugin_textdomain('wcpa-text-domain', false, $plugin_rel_path);
        }

    }


    if (!function_exists('wcpa_autoloader')) {

        function wcpa_autoloader($class_name)
        {
            if (0 === strpos($class_name, 'WCPA')) {
                $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
                $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
                require_once $classes_dir . $class_file;
            }
        }

    }

    if (!function_exists('WCPA')) {

        function WCPA()
        {
            $instance = WCPA_Backend::instance(__FILE__, WCPA_VERSION);
            return $instance;
        }

    }
    add_action('plugins_loaded', 'wcpa_init');
    spl_autoload_register('wcpa_autoloader');
    if (is_admin()) {
        WCPA();
    }
    $wcpa_field_counter = 1;
    new WCPA_Front_End(__FILE__, WCPA_VERSION);
}

function wcpa_pro_activation()
{
    if (in_array('woo-custom-product-addons/start.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        deactivate_plugins(WP_PLUGIN_DIR . '/woo-custom-product-addons/start.php');
        if (in_array('woo-custom-product-addons/start.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $free_version_name = get_plugin_data(WP_PLUGIN_DIR . '/woo-custom-product-addons/start.php');
            $message = 'Free version of plugin Woocommerce Custom Product Addons has installed on this site.
                    Remove  ' . $free_version_name['Name'] . ' in order to function this plugin properly ';
            echo $message;
            @trigger_error($message, E_USER_ERROR);
        }
    }
}

register_activation_hook(__FILE__, 'wcpa_pro_activation');
