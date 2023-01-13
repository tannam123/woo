<?php
if (!defined('ABSPATH'))
    exit;

class WCPA_Product_Meta {

    private static $_instance = null;

    public function __construct() {
        add_filter('woocommerce_product_data_tabs', array($this, 'add_my_custom_product_data_tab'), 101, 1);
        add_action('woocommerce_product_data_panels', array($this, 'add_my_custom_product_data_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'woocommerce_process_product_meta_fields_save'));
    }

    public function add_my_custom_product_data_tab($product_data_tabs) {
        $product_data_tabs['wcpa_product-meta-tab'] = array(
            'label' => __('Custom Product Options', 'my_text_domain'),
            'target' => 'wcpa_product-meta-tab',
            'priority' => 90
        );
        return $product_data_tabs;
    }

    public function woocommerce_process_product_meta_fields_save($post_id) {
        // This is the case to save custom field data of checkbox. You have to do it as per your custom fields
        $meta_field = array();
        $form_order= array();
        if (isset($_POST[WCPA_PRODUCT_META_FIELD])) {
            foreach ($_POST[WCPA_PRODUCT_META_FIELD] as $v) {
                $form_id = (int) sanitize_text_field($v);
                $meta_field[] =  $form_id;
                if(isset($_POST['wcpa_product_meta_order']) && isset($_POST['wcpa_product_meta_order'][$form_id]) && $_POST['wcpa_product_meta_order'][$form_id]!==''){
                    // null and 0 need to be treated as different, if value is null, it will order based on the form default order
                    $form_order[$form_id] = (int) sanitize_text_field($_POST['wcpa_product_meta_order'][$form_id]);
                }

            }
        }
//        asort($form_order);
        update_post_meta($post_id, WCPA_PRODUCT_META_KEY, $meta_field);
        update_post_meta($post_id, 'wcpa_product_meta_order', $form_order);

        if (isset($_POST['wcpa_exclude_global_forms'])) {
            update_post_meta($post_id, 'wcpa_exclude_global_forms', true);
        } else {
            update_post_meta($post_id, 'wcpa_exclude_global_forms', false);
        }

        if (!wcpa_get_option('wcpa_cf_bind_external',false) && isset($_POST['wcpa_product_cf']) && is_array($_POST['wcpa_product_cf'])) {
            $wcpa_product_cf = $_POST['wcpa_product_cf'];
            $custom_fields = wcpa_get_option('product_custom_fields');
            $cf_prefix = wcpa_get_option('wcpa_cf_prefix','wcpa_pcf_');
            foreach($custom_fields as $k=>$field){
                if(isset($wcpa_product_cf[$k]) && !empty($wcpa_product_cf[$k])){

                    update_post_meta($post_id, $cf_prefix.$field['name'], $wcpa_product_cf[$k]);
                }else{
                    delete_post_meta($post_id, $cf_prefix.$field['name']);
                }
            }
        }

    }

    public function add_my_custom_product_data_fields() {
        $cf_prefix = wcpa_get_option('wcpa_cf_prefix','wcpa_pcf_');
        global $post;
        $ml = new WCPA_Ml();
        $meta_class = '';
        if ($ml->is_active() && $ml->current_language()!==false && !$ml->is_default_lan() ) {
            $meta_class = 'wcpa_wpml_pro_meta';
        }
        ?>
        <!-- id below must match target registered in above add_my_custom_product_data_tab function -->
        <div id="wcpa_product-meta-tab" class="panel woocommerce_options_panel <?php echo $meta_class; ?>">
            <?php
            if ($ml->is_active() && $ml->current_language()!==false && !$ml->is_default_lan()) {
                echo '<p class="wcpa_editor_message">' . sprintf(__('You can manage form fields from base language only.')) . '</p>';
            }
            ?>
            <h4> <?php _e('Select Form', 'wcpa-text-domain') ?></h4>
            <?php
            $meta_field = get_post_meta($post->ID, WCPA_PRODUCT_META_KEY, true);
            $form_order = get_post_meta($post->ID, 'wcpa_product_meta_order', true);

            $forms = get_posts(array('post_type' => WCPA_POST_TYPE, 'posts_per_page' => -1));

            if ($ml->is_active()) {
                $forms = $ml->get_original_forms();
            }
            $show_order_number = wcpa_get_option('wcpa_show_form_order',false);
            foreach ($forms as $v) {
                $checked = '';
                if ($meta_field && is_array($meta_field) && in_array($v->ID, $meta_field)) {
                    $checked = 'checked="checked"';
                }
                if($form_order && isset($form_order[$v->ID])){
                    $form_order_val= $form_order[$v->ID];
                }else{
                    $form_order_val= '';
                }
                echo '<p><input type="checkbox" class="checkbox" ' . $checked . ' name="' . WCPA_PRODUCT_META_FIELD . '[]" id="wcpa_product_meta_' . $v->ID . '" value="' . $v->ID . '"">'
                . '<label for="wcpa_product_meta_' . $v->ID . '" class="description">' . $v->post_title . '(' . $v->ID . ')</label>';
                if($show_order_number){
                    echo '<input type="text" class="wcpa_order" placeholder="'.__('Enter order (Keep blank for default)','wcpa-text-domain').'" name="wcpa_product_meta_order['.$v->ID.']" value="'.$form_order_val.'">';
                }
                echo '</p>';
            }
            ?>
            <h4> <?php _e('Configurations', 'wcpa-text-domain') ?></h4>
            <?php
            $checked = '';

            echo '<p><input type="checkbox" class="checkbox" ' . checked(get_post_meta($post->ID, 'wcpa_exclude_global_forms', true), true, false) . ' name="wcpa_exclude_global_forms" id="wcpa_exclude_global_forms" value="1">'
            . '<label for="wcpa_exclude_global_forms" class="description">Exclude/Override globally assigned form</label></p>';
            ?>

            <h4> <?php _e('Custom Fields', 'wcpa-text-domain') ?></h4>
            <div class="options_group">
            <?php
                $custom_fields = wcpa_get_option('product_custom_fields');
                if(is_array($custom_fields)) {
                    foreach ($custom_fields as $k => $field) {
                        ?>
                        <p class="form-field  ">
                            <label for=""><?php echo $field['name']; ?></label>
                            <input type="text" name="wcpa_product_cf[<?php echo $k; ?>]" <?php echo (wcpa_get_option('wcpa_cf_bind_external',false)?'readonly="readonly"':''); ?>
                                   value="<?php echo get_post_meta($post->ID, $cf_prefix . $field['name'], true) ?>"
                                   placeholder="Enter value">
                        </p>
                        <?php
                    }
                }
            ?>

            </div>
        </div>
        <?php
    }

    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

}
