<?php

if (!defined('ABSPATH'))
    exit;

class WCPA_Backend extends WCPA_Order_Meta
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

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function __construct($file = '', $version = '1.0.0')
    {
        $this->_version = $version;
        $this->_token = WCPA_TOKEN;
        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        register_activation_hook($this->file, array($this, 'install'));
        register_deactivation_hook($this->file, array($this, 'deactivation'));

        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);


        add_filter('woocommerce_order_item_display_meta_value', array($this, 'display_meta_value'), 10, 3);


        add_action('woocommerce_before_order_itemmeta', array($this, 'order_item_line_item_html'), 10, 3);

        add_action('woocommerce_before_save_order_items', array($this, 'before_save_order_items'), 10, 2);

        add_action('woocommerce_order_item_get_formatted_meta_data', array($this, 'order_item_get_formatted_meta_data'), 10, 2);

        add_filter('post_row_actions', array($this, 'post_row_actions'), 10, 2);

        add_action('wp_ajax_wcpa_duplicate_form', array($this, 'duplicate_form'));
        add_action('wp_ajax_wcpa_import_form', array($this, 'import_form'));


        add_filter('manage_product_posts_columns', array($this, 'manage_products_columns'), 20, 1);
        add_action('manage_product_posts_custom_column', array($this, 'manage_products_column'), 10, 2);

        add_filter('manage_' . WCPA_POST_TYPE . '_posts_columns', array($this, 'manage_form_columns'), 20, 1);
        add_action('manage_' . WCPA_POST_TYPE . '_posts_custom_column', array($this, 'manage_form_column'), 10, 2);


        add_filter("manage_taxonomies_for_" . WCPA_POST_TYPE . "_columns", array($this, 'manage_taxonomies_for_list'), 10, 2);


        add_filter("woocommerce_product_export_product_default_columns", array($this, 'export_product_default_columns'), 10, 1);
        add_filter("woocommerce_product_export_product_column_wcpa_forms", array($this, 'export_product_column_wcpa_forms'), 10, 3);

        add_filter("woocommerce_csv_product_import_mapping_default_columns", array($this, 'import_mapping_default_columns'), 10, 1);
        add_filter("woocommerce_csv_product_import_mapping_options", array($this, 'product_import_mapping_options'), 10, 2);
        add_filter("woocommerce_product_importer_pre_expand_data", array($this, 'product_importer_pre_expand_data'), 10, 1);


        // return apply_filters( 'woocommerce_csv_product_import_mapping_options', $options, $item );


        add_action('save_post', array($this, 'delete_transient'), 1);
        add_action('edited_term', array($this, 'delete_transient'));
        add_action('delete_term', array($this, 'delete_transient'));
        add_action('created_term', array($this, 'delete_transient'));


        add_filter('woocommerce_email_format_string', array($this, 'email_format_string'), 2, 10);
//        str_replace( $find, $replace, $string ), $this

        WCPA_Form_Editor::instance();

        WCPA_Product_Meta::instance();
        WCPA_Settings::instance();
        $updater = new WCPA_Updater(WCPA_STORE_URL, WCPA_FILE, array(
                'version' => WCPA_VERSION, // current version number
                'license' => get_option('wcpa_activation_license_key'), // license key (used get_option above to retrieve from DB)
                'item_id' => WCPA_ITEM_ID, // id of this product in EDD
                'author' => 'Acowebs', // author of this plugin
                'url' => home_url()
            )
        );

        add_action('pll_init', array($this, 'pll_init')); // poly lang init

        add_filter('woocommerce_email_format_string', array($this, 'email_format_string'), 2, 10);

        add_action('upgrader_process_complete', array($this, 'upgrader_process'), 10, 2);
        
    }

    static function view($view, $data = array())
    {
        extract($data);
        include(plugin_dir_path(__FILE__) . 'views/' . $view . '.php');
    }

    /**
     *
     *
     * Ensures only one instance of WCPA is loaded or can be loaded.
     *
     * @return Main WCPA instance
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


    /**
     * Append plugin update failed guideline for update error
     *
     */
    public function upgrader_process($that, $action)
    {
        if ($action &&
            isset($action['plugins'][0]) && $action['plugins'][0] == 'woo-custom-product-addons-pro/start.php' &&
            $that->result == null &&
            $that instanceof Plugin_Upgrader) {
            $errors = $that->skin->get_errors();
            if (count($errors->errors)) {
                $that->skin->error(new WP_Error('error_guideline',
                    sprintf(__('Please ensure you have activated license for current domain, <a href="%s" target="_blank" >Check our Guideline</a> for more details', 'wcpa-text-domain'), 'https://acowebs.com/guideline/general/plugin-update-failed/')
                ));
            }
        }
    }

    public function pll_init()
    {
        $ml = new WCPA_Ml();
        if ($ml->is_active()) {
            $ml->settings_to_ml_poly();
        }

    }

    public function delete_transient($arg = false)
    {
        if ($arg) {
            in_array(get_post_type($arg), ['product', WCPA_POST_TYPE]) && delete_transient(WCPA_PRODUCTS_TRANSIENT_KEY);
        } else {
            delete_transient(WCPA_PRODUCTS_TRANSIENT_KEY);
        }

    }


    public function export_product_default_columns($cols = array())
    {
        $cols['wcpa_forms'] = __('WCPA Forms', 'wcpa-text-domain');
        return $cols;
    }

    public function product_import_mapping_options($options, $item)
    {
        $options['wcpa_forms'] = __('WCPA Forms', 'wcpa-text-domain');
        return $options;
    }

    public function import_mapping_default_columns($cols = array())
    {
        $cols[__('WCPA Forms', 'wcpa-text-domain')] = 'wcpa_forms';
        return $cols;
    }


    public function product_importer_pre_expand_data($data)
    {
        // Images field maps to image and gallery id fields.
        if (isset($data['wcpa_forms'])) {
            $forms = explode(',', $data['wcpa_forms']);
            $meta_field = array();
            foreach ($forms as $form_id) {
                $form_id = (int)sanitize_text_field($form_id);
                $meta_field[] = $form_id;
            }
            unset($data['wcpa_forms']);
            $data['meta:' . WCPA_PRODUCT_META_KEY] = $meta_field;

        }

        return $data;
    }


    public function export_product_column_wcpa_forms($value, $product, $col_id)
    {
        $col_id;
        $pro_id = $product->get_parent_id();
        if ($pro_id == 0) {
            $pro_id = $product->get_id();
        }
        $meta_fields = get_post_meta($pro_id, WCPA_PRODUCT_META_KEY, true);
        if ($meta_fields && is_array($meta_fields)) {
            $value = implode(",", $meta_fields);
        }
        return $value;
    }

    /**
     * Handling importing form ajax
     * @access  public
     * @return  string
     * @since   3.5.0
     */
    public function import_form()
    {
        // Check the nonce
        check_ajax_referer('wcpa_form_import_nonce', 'wcpa_nonce');


        $val = sanitize_text_field($_POST['val']);
        $val_original = base64_decode($val);
        $response = ['status' => false];
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        if ($val_original) {
            $array = unserialize($val_original);
            if ($array !== FALSE && isset($array['form_json']) && !empty($array['form_json'])) {
                $import['post_title'] = isset($array['title']) ? $array['title'] : __('Imported Form', 'wcpa-text-domain');
                $import['post_type'] = WCPA_POST_TYPE;
                $import['post_status'] = 'publish';
                $new_id = wp_insert_post($import);
                if (!is_wp_error($new_id)) {
                    if (isset($array['wcpa_settings']) && !empty($array['wcpa_settings'])) {
                        update_post_meta($new_id, WCPA_META_SETTINGS_KEY, $array['wcpa_settings']);
                    }
                    update_post_meta($new_id, WCPA_FORM_META_KEY, wp_slash($array['form_json']));

                    if (isset($array['other_settings']['wcpa_drct_prchsble']) && $array['other_settings']['wcpa_drct_prchsble']) {
                        update_post_meta($new_id, 'wcpa_drct_prchsble', $array['other_settings']['wcpa_drct_prchsble']);
                    }
                    $response = ['status' => true, 'redirect' => get_edit_post_link($new_id, 'link')];
                }
            }
        } else {
            $response = ['status' => false, 'message' => __('Invalid data')];
        }
        wp_send_json($response);
        wp_die();
    }

    /**
     * Handling duplicate ajax action
     * @access  public
     * @return  string
     * @since   4.3.3
     */
    public function duplicate_form()
    {

        // Check the nonce
        check_ajax_referer('wcpa_duplicate_form', 'wcpa_nonce');

        // Get variables
        $original_id = sanitize_text_field($_POST['original_id']);


        global $wpdb;


        $_duplicate = get_post($original_id);

        if (!isset($_duplicate->post_type) || $_duplicate->post_type !== WCPA_POST_TYPE) {
            return false;
        }


        $duplicate['post_title'] = $_duplicate->post_title . ' ' . __('Copy', 'wcpa-text-domain');
        $duplicate['post_type'] = WCPA_POST_TYPE;

        $duplicate_id = wp_insert_post($duplicate);


        $settings = get_post_meta($original_id, WCPA_META_SETTINGS_KEY, true);
        update_post_meta($duplicate_id, WCPA_META_SETTINGS_KEY, $settings);

        $fb_meta = get_post_meta($original_id, WCPA_FORM_META_KEY, true);
        $fb_meta_obj = json_decode($fb_meta);
        $old_id = array();
        if ($fb_meta_obj && is_array($fb_meta_obj)) {
            foreach ($fb_meta_obj as $v) {
                if (isset($v->elementId)) {
                    $_tmp = $v->elementId;
                    $v->elementId = 'wcpa-' . sanitize_title($v->type) . '-' . uniqid();
                    $old_id[$_tmp] = $v->elementId;
                    //replace id in relation
                }
                if (isset($v->name)) {
                    $v->name = sanitize_title($v->type) . '-' . uniqid();
                }
            }
            foreach ($fb_meta_obj as $k => $v) {
                if (isset($v->relations) && is_array($v->relations)) {
                    foreach ($v->relations as $rel) {
                        if (isset($rel->rules) && is_array($rel->rules)) {
                            foreach ($rel->rules as $rul) {
                                if (isset($rul->rules->cl_field) && isset($old_id[$rul->rules->cl_field])) {
                                    $rul->rules->cl_field = $old_id[$rul->rules->cl_field];
                                }
                            }
                        }
                    }
                }
            }
        }

        update_post_meta($duplicate_id, WCPA_FORM_META_KEY, wp_slash(json_encode($fb_meta_obj)));


        echo $duplicate_id;

        wp_die();
    }

    /**
     * Add duplicate form link
     * @access  public
     * @return  string
     * @since   4.3.3
     */
    public function post_row_actions($actions, $post)
    {
        // Check for your post type.
        if ($post->post_type == WCPA_POST_TYPE) {
            $ml = new WCPA_Ml();
            if ($ml->is_active() && !$ml->is_default_lan()) {
                return $actions;
            }
            $label = __('Duplicate Form', 'wcpa-text-domain');

            // Create a nonce & add an action
            $nonce = wp_create_nonce('wcpa_duplicate_form');
            $actions['duplicate_wcpa'] = '<a class="wcpa_duplicate_form" data-nonce="' . $nonce . '" href="#" data-postid="' . $post->ID . '">' . $label . '</a>';
        }


        return $actions;
    }

    // End admin_enqueue_styles ()


    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_styles($hook = '')
    {
        wp_enqueue_style('wp-color-picker');
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_scripts($hook = '')
    {
        wp_enqueue_media();

        wp_register_script($this->_token . '-backend', esc_url($this->assets_url) . 'js/backend.js', array('jquery','jquery-ui-sortable'), $this->_version);

        wp_register_script($this->_token . '-form-builder', esc_url($this->assets_url) . 'js/form-builder.min.js', array('jquery', 'wp-color-picker'), $this->_version);
        // wp_register_script($this->_token . '-form-builder', 'http://localhost:8080/assets/js/form-builder.min.js', array('jquery', 'wp-color-picker'), $this->_version);

        wp_enqueue_script('jquery');
        wp_enqueue_style('wp-color-picker');

        $screen = get_current_screen();
        if (isset($screen->id) && $screen->id === 'wcpa_pt_forms') {
            wp_enqueue_script($this->_token . '-form-builder');
        }
        wp_enqueue_script($this->_token . '-backend');
        $this->print_global_data();
    }

    public function print_global_data()
    {
        $ml = new WCPA_Ml();
        // Put your plugin code here
        $wcpa_global_vars['plugin_path'] = plugin_dir_url($this->file);
        if ($this->check_woocommerce_active()) {
            $attr_tax = [];
            if (function_exists('wc_get_attribute_taxonomies')) {
                $attr_tax = wc_get_attribute_taxonomies();
            }

            $attributes = array();
            foreach ($attr_tax as $atr) {
                $temp['attribute_id'] = $atr->attribute_id;
                $temp['attribute_label'] = $atr->attribute_label;
                $temp['attribute_name'] = $atr->attribute_name;
                $temp['terms'] = get_terms(array(
                    'taxonomy' => wc_attribute_taxonomy_name($atr->attribute_name),
                    'hide_empty' => false,
                    'fields' => 'id=>name'
                ));
                $attributes[] = $temp;
            }
            $wcpa_global_vars['attributes'] = $attributes;

            $custom_fields = wcpa_get_option('product_custom_fields');

            $wcpa_global_vars['custom_fields'] = $custom_fields;


            global $post;
            $forms = get_posts(array('post_type' => WCPA_POST_TYPE, 'posts_per_page' => -1, 'post__not_in' => array($post ? $post->ID : 0)
            ));
            if ($ml->is_active()) {
                $forms = $ml->get_original_forms();
            }

            $wcpa_global_vars['forms_list'] = array_map(function ($e) {
                return ['ID' => $e->ID, 'title' => $e->post_title];
            }, $forms);
        } else {
            add_action('admin_notices', array($this, 'notice_need_woocommerce'));
        }

        wp_localize_script($this->_token . '-backend', 'wcpa_backend_vars', $wcpa_global_vars);
        $translations =[];
        require_once('translations.php');

        wp_localize_script($this->_token . '-form-builder', 'form_builder_i18n', $translations);

        // Get all Checkout Fields
        wc()->frontend_includes();
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
        WC()->customer = new WC_Customer(get_current_user_id(), true);
        
        $checkout_fields = WC()->checkout->get_checkout_fields();
        $checkout_key_val_pair = array();
        if(!empty($checkout_fields)) {
            foreach ( $checkout_fields as $fieldset_key => $fieldset ) {
                if(!empty($fieldset)) {
                    $checkout_key_val_pair['parents'][$fieldset_key] = $fieldset_key;
                    foreach ( $fieldset as $key => $field ){
                        $checkout_key_val_pair['fields'][$fieldset_key][$key] = isset($field['label']) ? $field['label'] : '';
                    }
                }
            }
        } 
        if(empty($checkout_fields)) {
            $checkout_key_val_pair['parents'] = array();
            $checkout_key_val_pair['fields'] = array();
        }
        wp_localize_script($this->_token . '-form-builder', 'checkout_fields', $checkout_key_val_pair);
    }

    public function check_woocommerce_active()
    {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        }
        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins['woocommerce/woocommerce.php']))
                return true;
        }
        return false;
    }

    /**
     * WooCommerce not active notice.
     * @access  public
     * @return string Fallack notice.
     */
    public function notice_need_woocommerce()
    {
        $error = sprintf(__(WCPA_PLUGIN_NAME . ' requires %sWooCommerce%s to be installed & activated!', 'wcpa-text-domain'), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>');
        $message = '<div class="error"><p>' . $error . '</p></div>';
        echo $message;
    }

    public function manage_taxonomies_for_list($tax, $post_type)
    {
        $taxonomies = get_object_taxonomies($post_type, 'object');
        $taxonomies = wp_filter_object_list($taxonomies, array(), 'and', 'name');
        return $taxonomies;
    }

    public function manage_products_columns($columns)
    {

        $new = array_merge(array_slice($columns, 0, -2, true), ['wcpa_forms' => __('Product Forms', 'wcpa-text-domain')], array_slice($columns, -2, null, true));

        return $new;
    }

    public function manage_products_column($column_name, $post_id)
    {
        if ($column_name == 'wcpa_forms') {
            $forms = get_post_meta($post_id, WCPA_PRODUCT_META_KEY, true);
            $link = '';
            if (is_array($forms)) {
                foreach ($forms as $v) {
                    $link .= '<a href="' . get_edit_post_link($v) . '">' . get_the_title($v) . '</a>, ';
                }
            }
            echo trim($link, ', ');
        }
    }

    public function manage_form_columns($columns)
    {
        if (wcpa_get_option('show_assigned_products_in_list', false)) {
            $new = array_merge(array_slice($columns, 0, -1, true), ['wcpa_products' => __('Products', 'wcpa-text-domain')], array_slice($columns, -2, null, true));
        } else {
            return $columns;
        }

        return $new;
    }

    public function manage_form_column($column_name, $post_id)
    {
        if ($column_name == 'wcpa_products') {

            $args = array(
                'post_type' => 'product',
                'meta_query' => array(
                    array(
                        'key' => WCPA_PRODUCT_META_KEY,
                        'value' => 'i:' . $post_id . ';',
                        'compare' => 'LIKE',
                    )
                )
            );
            $prolist = get_posts($args);


//            $forms = get_post($post_id, WCPA_PRODUCT_META_KEY, true);
            $link = '';
            if (is_array($prolist)) {
                foreach ($prolist as $v) {
                    $link .= '<a href="' . get_edit_post_link($v) . '">' . get_the_title($v) . '</a>, ';
                }
            }
            echo trim($link, ', ');
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

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();
        $this->_update_settings();
        $migration = new WCPA_Migration();
        if ($migration->check_has_to_migrate()) {
            $response = $migration->version_migration();
        }
        $this->schedule_cron();
        $this->_protect_upload_dir();
    }

    /**
     * Log the plugin version number.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    }

    private function _update_settings()
    {
        $settings = get_option(WCPA_SETTINGS_KEY, array());
        if (!isset($settings['show_meta_in_cart'])) {
            $settings['show_meta_in_cart'] = true;
        }
        if (!isset($settings['show_meta_in_checkout'])) {
            $settings['show_meta_in_checkout'] = true;
        }
        if (!isset($settings['show_meta_in_order'])) {
            $settings['show_meta_in_order'] = true;
        }

        if (!isset($settings['form_loading_order_by_date'])) {
            $count_posts = wp_count_posts(array('post_type' => WCPA_POST_TYPE));
            if ($count_posts) {
                $published_posts = $count_posts->publish;
            }
            if ($published_posts > 1) {
                $settings['form_loading_order_by_date'] = false;
                $settings['hide_empty_data'] = false;
            } else {
                $settings['form_loading_order_by_date'] = true;
                $settings['hide_empty_data'] = true;
                $settings['use_sumo_selector'] = true;
            }
        }


        update_option(WCPA_SETTINGS_KEY, $settings);
    }

    private function schedule_cron()
    {
        if (!wp_next_scheduled(WCPA_CRON_HOOK)) {
            wp_schedule_event(time(), 'daily', WCPA_CRON_HOOK);
        }
    }

    private function _protect_upload_dir()
    {
        $upload_dir = wp_upload_dir();

        $files = array(
            array(
                'base' => $upload_dir['basedir'] . '/' . WCPA_UPLOAD_DIR,
                'file' => '.htaccess',
                'content' => 'Options -Indexes' . "\n"
                    . '<Files *.php>' . "\n"
                    . 'deny from all' . "\n"
                    . '</Files>'
            )
        ,
            array(
                'base' => $upload_dir['basedir'] . '/' . WCPA_UPLOAD_DIR,
                'file' => 'index.php',
                'content' => '<?php ' . "\n"
                    . '// Silence is golden.'
            )
        );

        foreach ($files as $file) {


            if ((wp_mkdir_p($file['base'])) && (!file_exists(trailingslashit($file['base']) . $file['file']))  // If file not exist
            ) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    public function deactivation()
    {
        wp_clear_scheduled_hook(WCPA_CRON_HOOK);
    }

}
