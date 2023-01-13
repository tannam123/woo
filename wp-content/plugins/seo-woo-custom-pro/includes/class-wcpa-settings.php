<?php
if (!defined('ABSPATH'))
    exit;

class WCPA_Settings
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;
    private $settings = array();

    public function __construct()
    {
        $this->_version = WCPA_VERSION;
        $this->_token = WCPA_TOKEN;
        $this->file = WCPA_FILE;
        $this->dir = dirname($this->file);
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
		update_option('wcpa_activation_license_status','valid');
        $this->settings = [
            'disp_show_field_price' => 'boolean',
            'disp_summ_show_total_price' => 'boolean',
            'disp_summ_show_product_price' => 'boolean',
            'disp_summ_show_option_price' => 'boolean',
            'show_meta_in_cart' => 'boolean',
            'show_meta_in_checkout' => 'boolean',
            'show_meta_in_order' => 'boolean',
            'show_price_in_cart' => 'boolean',
            'show_price_in_checkout' => 'boolean',
            'show_price_in_order' => 'boolean',
            'show_price_in_order_meta' => 'boolean',
            'pric_exc_product_base_price' => 'boolean',
            'enable_recaptcha' => 'boolean',
            'options_total_label' => 'text',
            'options_product_label' => 'text',
            'total_label' => 'text',
            'fee_label' => 'text',
            'file_droppable_action_text' => 'text',
            'add_to_cart_text' => 'text',
            'form_loading_order_by_date' => 'boolean',
            'use_sumo_selector' => 'boolean',
            'load_all_scripts' => 'boolean',
            'wcpa_show_form_json' => 'boolean',
            'hide_empty_data' => 'boolean',
            'google_map_api_key' => 'text',
            'google_map_countries' => 'text',
            'recaptcha_site_key' => 'text',
            'recaptcha_secret_key' => 'text',
            'price_prefix_label' => 'text',
            'field_option_price_format' => 'text',
            'change_price_as_quantity' => 'boolean',
            'wcpa_hide_option_price_zero' => 'boolean',
            'ajax_add_to_cart' => 'boolean',
            'add_to_cart_button_class' => 'text',
            'cart_hide_price_zero' => 'boolean',
            'file_button_text' => 'text',
            'consider_product_tax_conf' => 'boolean',
            'count_fee_once_in_a_order' => 'boolean',
            'show_fee_in_line_subtotal' => 'boolean',
            'wcpa_apply_coupon_to_fee' => 'boolean',
            'show_assigned_products_in_list' => 'boolean',
            'show_field_price_x_quantity' => 'boolean',
            'wcpa_show_form_order' => 'boolean',
            'remove_discount_from_fields' => 'boolean'

        ];

        add_action('admin_menu', array($this, 'register_options_page'));
        add_action('admin_menu', array($this, 'register_setting'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('init', array($this, 'check_migration'));
        $plugin = plugin_basename($this->file);
        add_filter("plugin_action_links_$plugin", array($this, 'add_settings_link'));
    }

    /**
     *
     *
     * Ensures only one instance of CPO is loaded or can be loaded.
     *
     * @return Main CPO instance
     * @see WordPress_Plugin_Template()
     * @since 1.0.0
     * @static
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    public function add_settings_link($links)
    {
        $settings = '<a href="' . admin_url('options-general.php?page=wcpa_settings') . '">' . __('Settings') . '</a>';
        $products = '<a href="' . admin_url('edit.php?post_type=' . WCPA_POST_TYPE) . '">' . __('Create Forms') . '</a>';
        array_push($links, $settings);
        array_push($links, $products);
        return $links;


    }

    public function check_migration()
    {
        $migration = new WCPA_Migration();
        $migration->check();
    }

    public function admin_notices()
    {
        if ($this->check_lcesnse() === FALSE) {
            ?>
            <div class="error">
                <p>You have invalid or expired license keys for<strong> <?php echo WCPA_PLUGIN_NAME; ?></strong>.
                    Please go to the <a
                            href="<?php echo admin_url('options-general.php?page=wcpa_settings#wcpa-license'); ?>">
                        License page</a> to correct this issue.

                </p>
            </div>
            <?php
        }
        if ($this->check_free_version_installed() == true) {
            $free_version_name = get_plugin_data(WP_PLUGIN_DIR . '/woo-custom-product-addons/start.php');
            ?>
            <div class="error">
                <p>Free version of plugin <strong> <?php echo WCPA_PLUGIN_NAME; ?></strong> has installed on this site.
                    Remove <strong> <?php echo $free_version_name['Name']; ?></strong> in order to function this plugin
                    properly

                </p>
            </div>
            <?php
        }


        if (isset($_GET['sl_activation']) && !empty($_GET['message'])) {
            switch ($_GET['sl_activation']) {
                case 'false':
                    $message = urldecode($_GET['message']);
                    ?>
                    <div class="error">
                        <p><?php echo $message; ?></p>
                    </div>
                    <?php
                    break;
                case 'true':
                default:
                    // Developers can put a custom success message here for when activation is successful if they way.
                    break;
            }
        }
    }

    public function check_lcesnse()
    {
        $license_status = get_option('wcpa_activation_license_status');
        if ($license_status == 'valid') {
            return true;
        } else {
            return FALSE;
        }
    }

    public function check_free_version_installed()
    {


        if (in_array('woo-custom-product-addons/start.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        } else {
            return false;
        }
    }

    function register_options_page()
    {

        add_options_page('Custom Product Addons', 'Custom Product Addons', 'manage_options', 'wcpa_settings', array($this, 'options_page'));
    }

    function register_setting()
    {
        register_setting('wcpa_license', 'wcpa_activation_license_key', array('type' => 'text',
            'sanitize_callback' => array($this, 'sanitize_license')));
        if (isset($_POST['wcpa_license_activate'])) {
            $this->activate_license();
        } else if (isset($_POST['wcpa_license_deactivate'])) {
            $this->deactivate_license();
        }
    }

    public function activate_license()
    {

        // run a quick security check
        if (!check_admin_referer('wcpa_activate', 'wcpa_nounce')) {
            return; // get out if we didn't click the Activate button
        }
        $license = trim(sanitize_text_field($_POST['wcpa_activation_license_key']));
        update_option('wcpa_activation_license_key', $license);

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license,
            'item_id' => WCPA_ITEM_ID, // The ID of the item in EDD
            'url' => home_url()
        );
        // Call the custom API.
        $response = wp_remote_post(WCPA_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));


        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $temp = $response->get_error_message();
                if (empty($temp)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }
            }
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));

            if (false === $license_data->success) {
                switch ($license_data->error) {
                    case 'expired' :
                        $message = sprintf(
                            __('Your license key expired on %s.'), date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                        );
                        break;
                    case 'revoked' :
                        $message = __('Your license key has been disabled.');
                        break;
                    case 'missing' :
                        $message = __('Invalid license.');
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $message = __('Your license is not active for this URL.');
                        break;
                    case 'item_name_mismatch' :
                        $message = sprintf(__('This appears to be an invalid license key for %s.'), EDD_SAMPLE_ITEM_NAME);
                        break;
                    case 'no_activations_left':
                        $message = __('Your license key has reached its activation limit.');
                        break;
                    default :
                        $message = __('An error occurred, please try again.');
                        break;
                }
            }
        }
        do_action('wcpa_license_updated');
        // Check if anything passed on a message constituting a failure
        if (!empty($message)) {
            $base_url = admin_url('options-general.php?page=wcpa_settings');
            $redirect = add_query_arg(array('sl_activation' => 'false', 'message' => urlencode($message)), $base_url);
            wp_redirect($redirect);
            exit();
        }
        // $license_data->license will be either "valid" or "invalid"
        update_option('wcpa_activation_license_status', $license_data->license);
        wp_redirect(admin_url('options-general.php?page=wcpa_settings'));
        exit();
    }

    public function deactivate_license()
    {
        // run a quick security check
        if (!check_admin_referer('wcpa_deactivate', 'wcpa_nounce')) {
            return; // get out if we didn't click the Activate button
        }

        $license = trim(sanitize_text_field($_POST['wcpa_activation_license_key']));
        $old = get_option('wcpa_activation_license_key');
        if ($old && $old != $license) {
            delete_option('wcpa_activation_license_status'); // new license has been entered, so must reactivate
        }

        update_option('wcpa_activation_license_key', $license);

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'deactivate_license',
            'license' => $license,
            'item_id' => WCPA_ITEM_ID, // The ID of the item in EDD
            'url' => home_url()
        );
        // Call the custom API.
        $response = wp_remote_post(WCPA_STORE_URL, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $temp = $response->get_error_message();
                if (empty($temp)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('An error occurred, please try again.');
                }
            }
        } else {
            $license_data = json_decode(wp_remote_retrieve_body($response));
            if (false === $license_data->success) {

                switch ($license_data->error) {
                    case 'expired' :
                        $message = sprintf(
                            __('Your license key expired on %s.'), date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
                        );
                        break;
                    case 'revoked' :
                        $message = __('Your license key has been disabled.');
                        break;
                    case 'missing' :
                        $message = __('Invalid license.');
                        break;
                    case 'invalid' :
                    case 'site_inactive' :
                        $message = __('Your license is not active for this URL.');
                        break;
                    case 'item_name_mismatch' :
                        $message = sprintf(__('This appears to be an invalid license key for %s.'), EDD_SAMPLE_ITEM_NAME);
                        break;
                    case 'no_activations_left':
                        $message = __('Your license key has reached its activation limit.');
                        break;
                    default :
                        $message = __('An error occurred, please try again.');
                        break;
                }
            }
        }
        do_action('wcpa_license_updated');

        // Check if anything passed on a message constituting a failure
        if (!empty($message)) {
            $base_url = admin_url('options-general.php?page=wcpa_settings');
            $redirect = add_query_arg(array('sl_activation' => 'false', 'message' => urlencode($message)), $base_url);
            wp_redirect($redirect);
            exit();
        }
        // $license_data->license will be either "valid" or "invalid"
        update_option('wcpa_activation_license_status', $license_data->license);
        wp_redirect(admin_url('options-general.php?page=wcpa_settings'));
        exit();
    }

    public function sanitize_license($new)
    {
        $old = get_option('wcpa_activation_license_key');
        if ($old && $old != $new) {
            delete_option('wcpa_activation_license_status'); // new license has been entered, so must reactivate
        }
        return $new;
    }

    public function options_page()
    {


        if (array_key_exists('wcpa_save_settings', $_POST)) {
            $this->save_settings();
        }
        if (array_key_exists('action', $_GET)) {
            if ($_GET['action'] == 'migrate') {
                if (isset($_GET['wcpa_nonce']) && wp_verify_nonce($_GET['wcpa_nonce'], 'wcpa_migration')) {
                    $migration = new WCPA_Migration();
                    $response = $migration->version_migration();
                    WCPA_Backend::view('settings-migration', ['response' => $response]);
                }
            }
        } else if (array_key_exists('view', $_GET)) {
            if ($_GET['view'] == 'migration') {
                WCPA_Backend::view('settings-migration', []);
            }
        } else {
            WCPA_Backend::view('settings-main', ['asset_url' => $this->assets_url]);
        }
    }

    public function save_settings()
    {
        if (isset($_POST['wcpa_save_settings']) && wp_verify_nonce($_POST['wcpa_nonce'], 'wcpa_save_settings')) {
            $settings = get_option(WCPA_SETTINGS_KEY);
            
            foreach ($this->settings as $key => $type) {
                if ($type == 'text') {
                    if (isset($_POST[$key])) {
                        if ('field_option_price_format' == $key) {
                            if ((strpos($_POST[$key], 'price') !== false)) {
                                $settings[$key] = stripslashes(sanitize_text_field($_POST[$key]));
                            }

                        } else {
                            $settings[$key] = stripslashes(sanitize_text_field($_POST[$key]));
                        }

                    }
                } else if ($type == 'boolean') {
                    if (isset($_POST[$key])) {
                        $settings[$key] = true;
                    } else {
                        $settings[$key] = false;
                    }
                }
            }

            // Added to replace user input to programmatic form
            if(isset($_POST['file_droppable_desc_text'])){
                $settings['file_droppable_desc_text'] = stripslashes(sanitize_text_field(str_replace('{action}', '%s', $_POST['file_droppable_desc_text'])));
            } 

            $val_settings = array();
            if(isset($_POST['validation_requiredError'])){
                $val_settings['validation_requiredError'] = stripslashes(sanitize_text_field($_POST['validation_requiredError']));
            }
            if(isset($_POST['validation_allowedCharsError'])){
                $val_settings['validation_allowedCharsError'] = stripslashes(sanitize_text_field(str_replace('{characters}', '%s', $_POST['validation_allowedCharsError'])));
            }
            if(isset($_POST['validation_patternError'])){
                $val_settings['validation_patternError'] = stripslashes(sanitize_text_field(str_replace('{pattern}', '%s', $_POST['validation_patternError'])));
            }
            if(isset($_POST['validation_minlengthError'])){
                $val_settings['validation_minlengthError'] = stripslashes(sanitize_text_field(str_replace('{minlength}', '%s', $_POST['validation_minlengthError'])));
            }
            if(isset($_POST['validation_maxlengthError'])){
                $val_settings['validation_maxlengthError'] = stripslashes(sanitize_text_field(str_replace('{maxlength}', '%s', $_POST['validation_maxlengthError'])));
            }
            if(isset($_POST['validation_minValueError'])){
                $val_settings['validation_minValueError'] = stripslashes(sanitize_text_field(str_replace('{minvalue}', '%s', $_POST['validation_minValueError'])));
            }
            if(isset($_POST['validation_maxValueError'])){
                $val_settings['validation_maxValueError'] = stripslashes(sanitize_text_field(str_replace('{maxvalue}', '%s', $_POST['validation_maxValueError'])));
            }
            if(isset($_POST['validation_minFieldsError'])){
                $val_settings['validation_minFieldsError'] = stripslashes(sanitize_text_field(str_replace('{minfield}', '%s', $_POST['validation_minFieldsError'])));
            }
            if(isset($_POST['validation_maxFieldsError'])){
                $val_settings['validation_maxFieldsError'] = stripslashes(sanitize_text_field(str_replace('{maxfield}', '%s', $_POST['validation_maxFieldsError'])));
            }
            if(isset($_POST['validation_maxFileCountError'])){
                $val_settings['validation_maxFileCountError'] = stripslashes(sanitize_text_field(str_replace('{maxfilecount}', '%s', $_POST['validation_maxFileCountError'])));
            }
            if(isset($_POST['validation_maxFileSizeError'])){
                $val_settings['validation_maxFileSizeError'] = stripslashes(sanitize_text_field(str_replace('{maxfilesize}', '%s', $_POST['validation_maxFileSizeError'])));
            }
            if(isset($_POST['validation_minFileSizeError'])){
                $val_settings['validation_minFileSizeError'] = stripslashes(sanitize_text_field(str_replace('{minfilesize}', '%s', $_POST['validation_minFileSizeError'])));
            }
            if(isset($_POST['validation_fileExtensionError'])){
                $val_settings['validation_fileExtensionError'] = stripslashes(sanitize_text_field(str_replace('{fileextensions}', '%s', $_POST['validation_fileExtensionError'])));
            } 
            if(isset($_POST['validation_quantityRequiredError'])){
                $val_settings['validation_quantityRequiredError'] = stripslashes(sanitize_text_field($_POST['validation_quantityRequiredError']));
            }
            if(isset($_POST['validation_otherFieldError'])){
                $val_settings['validation_otherFieldError'] = stripslashes(sanitize_text_field($_POST['validation_otherFieldError']));
            }
            if(isset($_POST['validation_charleftMessage'])){
                $val_settings['validation_charleftMessage'] = stripslashes(sanitize_text_field(str_replace('{charleft}', '%s', $_POST['validation_charleftMessage'])));
            }
            
            if(count($val_settings)>0){
                $settings['wcpa_validation_strings'] =$val_settings;
            }

            if (isset($_POST['product_custom_field_name'])) {
                $custom_fields_name = $_POST['product_custom_field_name'];
                $custom_fields_value = $_POST['product_custom_field_value'];
                $_current_fields = isset($settings['product_custom_fields']) ? $settings['product_custom_fields'] : false;
                $current_fields = array();
                $count = 0;
                if (is_array($_current_fields) && !empty($_current_fields)) {
                    foreach ($_current_fields as $key => $val) {
                        $field_value = isset($custom_fields_value[$key]) ? trim($custom_fields_value[$key]) : 0;
                        if (isset($custom_fields_name[$key]) && !empty($custom_fields_name[$key])) {
                            $count++;
                            $current_fields['cf_' . $count] = array(
                                'name' => sanitize_key(trim($custom_fields_name[$key])),
                                'value' => $field_value
                            );
                            unset($custom_fields_name[$key]);
                        }
                    }
                }


                if (is_array($custom_fields_name)) {
                    foreach ($custom_fields_name as $key => $val) {
                        $count++;
                        $field_value = isset($custom_fields_value[$key]) ? trim($custom_fields_value[$key]) : 0;
                        if (!empty($val)) {
                            $current_fields['cf_' . $count] = array(
                                'name' => sanitize_key(trim($val)),
                                'value' => $field_value
                            );
                        }


                    }
                }

                $settings['product_custom_fields'] = $current_fields;

            }

            if (isset($_POST['wcpa_extension_name'])) {
                $extensions = [];
                $extension_name = $_POST['wcpa_extension_name'];
                $extension_mime = $_POST['wcpa_extension_mime'];
                if($extension_name){
                    foreach($extension_name as $key=>$ext) {
                        if(isset($ext) && !empty($ext) && isset($extension_mime[$key])){
                            $extensions[$ext] = $extension_mime[$key];
                        }
                    }
                }
                $settings['wcpa_custom_extensions'] = $extensions;
            }  else {
                $settings['wcpa_custom_extensions'] = [];
            }
            
            if (isset($_POST['wcpa_custom_extension_choose'])) {
                $extensions = [];
                $extension_choosed = $_POST['wcpa_custom_extension_choose'];

                $wcpa_mimetypes =[];
                require('mimetypes.php'); 

                $wcpa_mimetypes = apply_filters( 'wcpa_custom_mime_types', $wcpa_mimetypes );
                
                if($extension_choosed) {
                    foreach($extension_choosed as $ext) {
                        if($ext && isset($wcpa_mimetypes[$ext])){
                            $extensions[$ext] = $wcpa_mimetypes[$ext];
                        }
                    } 
                }
                $settings['wcpa_custom_extensions_choose'] = $extensions;
            } else {
                $settings['wcpa_custom_extensions_choose'] = [];
            }

            update_option(WCPA_SETTINGS_KEY, $settings);
            $ml = new WCPA_Ml();
            if ($ml->is_active()) {
                $ml->settings_to_wpml();
            }

            if (isset($_POST['wcpa_activation_license_key'])) {
                $license = trim(sanitize_text_field($_POST['wcpa_activation_license_key']));
                $old = get_option('wcpa_activation_license_key');
                if ($old && $old != $license) {
                    delete_option('wcpa_activation_license_status'); // new license has been entered, so must reactivate
                }

                update_option('wcpa_activation_license_key', $license);
            }


        }
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

}
