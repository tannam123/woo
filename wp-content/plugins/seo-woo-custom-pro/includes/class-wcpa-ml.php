<?php

if (!defined('ABSPATH'))
    exit;

class WCPA_Ml
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
    public $default_lang;
    public $current_lang;
    private $_active = false;

    public function __construct()
    {

        if (class_exists('SitePress')) {
            $this->_active = 'wpml';
            $this->default_lang = apply_filters('wpml_default_language', NULL);
            $this->current_lang = apply_filters('wpml_current_language', NULL);
        } else if (defined('POLYLANG_VERSION') && function_exists('pll_default_language')) {
            $this->_active = 'polylang';
            $this->default_lang = pll_default_language();
            $this->current_lang = pll_current_language();
        }
    }

    /**
     *
     *
     * Ensures only one instance of WCPA is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WCPA instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    public function is_active()
    {
        return $this->_active !== false;
    }

    public function is_new_post($post_id)
    {
        if ($this->base_form($post_id) === 0) {
            if ($this->_active === 'wpml') {
                isset($_GET['trid']) ? false : true;
            } else if ($this->_active === 'polylang') {
                return isset($_GET['from_post']) ? false : true;

            }
        }
        return false;
    }

    public function base_form($post_id)
    {
        if ($this->_active === 'wpml') {
            $base_id = apply_filters('wpml_original_element_id', null, $post_id);
        } else if ($this->_active === 'polylang') {
            $base_id = pll_get_post($post_id, pll_default_language());
        }

        return (int)$base_id;
    }

    public function is_default_lan()
    {
        return ($this->current_lang === $this->default_lang);
    }

    public function is_duplicating($post_id)
    {
        if ($this->base_form($post_id) === 0) {
            if ($this->_active === 'wpml' && isset($_GET['trid'])) {
                return true;
            } else if ($this->_active === 'polylang' && isset($_GET['from_post'])) {
                return true;
            }
        }

        return false;
    }

    public function default_fb_meta($post_id)
    {
        $value = null;
        if ($this->_active === 'wpml') {
            $my_duplications = apply_filters('wpml_get_element_translations', null, $_GET['trid']);
            if (isset($my_duplications[$this->default_lang]->element_id)) {
                $value = get_post_meta($my_duplications[$this->default_lang]->element_id, WCPA_FORM_META_KEY, true);
            } else if (is_array($my_duplications)) {
                $value = get_post_meta(array_values($my_duplications)[0]->element_id, WCPA_FORM_META_KEY, true);
            }
        } else if ($this->_active === 'polylang') {
            $base_form = $this->base_form($_GET['from_post']);
            $value = get_post_meta($base_form, WCPA_FORM_META_KEY, true);

            return $value;
        }
        return $value;
    }

    public function default_language()
    {
        return $this->default_lang;
    }

    public function current_language()
    {
        return $this->current_lang;
    }

    public function get_original_forms()
    {

        if ($this->_active === 'wpml') {
            $forms = get_posts(array('post_type' => WCPA_POST_TYPE, 'posts_per_page' => -1));
            $forms_original = array();
            foreach ($forms as $p) {
                $trid = apply_filters('wpml_element_trid', null, $p->ID);
                if ($this->base_form($p->ID) === (int)$p->ID) {
                    $forms_original[] = $p;
                }
            }
            return $forms_original;
        } else if ($this->_active === 'polylang') {
            $forms = get_posts(array(
                'post_type' => WCPA_POST_TYPE,
                'lang' => pll_default_language(),
                'posts_per_page' => -1));
            return $forms;
        }
    }

    public function lang_object_ids($object_id, $type)
    {
        if (is_array($object_id)) {
            $translated_object_ids = array();
            foreach ($object_id as $id) {
//
                if ($this->_active === 'wpml') {
                    $translated_object_ids[] = apply_filters('wpml_object_id', $id, $type, true);
                } else if ($this->_active === 'polylang') {
                    $p_id = pll_get_post($id);
                    if ($p_id) {
                        $translated_object_ids[] = $p_id;
                    } else {
                        $translated_object_ids[] = $id;
                    }
                }
            }
            return array_unique($translated_object_ids);
        } else {
            if ($this->_active === 'wpml') {
                return apply_filters('wpml_object_id', $object_id, $type, true);
            } else if ($this->_active === 'polylang') {
                $p_id = pll_get_post($object_id);
                if ($p_id) {
                    return $p_id;
                } else {
                    return $object_id;
                }
            }
        }
    }

    public function sync_data($post_id)
    {
        $base_id = $this->base_form($post_id);
        $wcpa_drct_prchsble = get_post_meta($base_id, 'wcpa_drct_prchsble', true);

        if ($base_id === (int)$post_id) { // update all other items only if base form is updating
            //update all langs
            if ($this->_active === 'wpml') {
                $trid = apply_filters('wpml_element_trid', null, $post_id);
                $my_duplications = apply_filters('wpml_get_element_translations', null, $trid);
                if (is_array($my_duplications)) {
                    foreach ($my_duplications as $item) {
                        if ((int)$item->element_id !== (int)$base_id) {
                            $fb_data_json = $this->merge_meta($base_id, $item->element_id);
                            update_post_meta($item->element_id, WCPA_FORM_META_KEY, $fb_data_json);

                            update_post_meta($item->element_id, 'wcpa_drct_prchsble', $wcpa_drct_prchsble);

                            $settings = $this->merge_settings($base_id, $item->element_id);
                            update_post_meta($item->element_id, WCPA_META_SETTINGS_KEY, $settings);
                        }
                    }
                }
            } else if ($this->_active === 'polylang') {
                $langs = pll_languages_list();

                foreach ($langs as $v) {
                    $p = pll_get_post($base_id, $v);
                    if ($p && $p !== $base_id) {
                        $fb_data_json = $this->merge_meta($base_id, $p);
                        update_post_meta($p, WCPA_FORM_META_KEY, $fb_data_json);
                        update_post_meta($p, 'wcpa_drct_prchsble', $wcpa_drct_prchsble);
                        $settings = $this->merge_settings($base_id, $p);
                        update_post_meta($p, WCPA_META_SETTINGS_KEY, $settings);
                    }
                }
            }
        } else if ($post_id !== 0) {
            // merge data with baselang
            $fb_data_json = $this->merge_meta($base_id, $post_id);
            update_post_meta($post_id, WCPA_FORM_META_KEY, $fb_data_json);
            update_post_meta($post_id, 'wcpa_drct_prchsble', $wcpa_drct_prchsble);
            $settings = $this->merge_settings($base_id, $post_id);
            update_post_meta($post_id, WCPA_META_SETTINGS_KEY, $settings);
        }
    }

    public function merge_meta($base_id, $tran_id)
    {

        $original_json = get_post_meta($base_id, WCPA_FORM_META_KEY, true);
        $trans_json = get_post_meta($tran_id, WCPA_FORM_META_KEY, true);

        $original = json_decode($original_json);
        $trans = json_decode($trans_json);

        if (is_array($original) && is_array($trans)) {
            foreach ($original as $k => $val) {
                foreach ($trans as $v) {
                    if ($v->elementId == $val->elementId) {
                        $this->merge_data($val, $v);
                        break;
                    }
                }
            }
        }

        return wp_slash(json_encode($original));
    }

    public function merge_data(&$base_data, $trans_data)
    {
        $keys = array(
            'label',
            'description',
            'placeholder',
            'value',
            'wpml_sync',
	        'other_text'
        );
        $options = array(
            'label',
            'value',
            'image',
            'color',
            'tooltip'
        );
        foreach ($keys as $key => $val) {
            if (isset($trans_data->{$val}) && !wcpa_empty($trans_data->{$val})) {
                $base_data->{$val} = $trans_data->{$val};
            }
        }

        if (isset($trans_data->values) && (!isset($trans_data->wpml_sync) || !$trans_data->wpml_sync)) { //$trans_data->values
            foreach ($trans_data->values as $k => $v) {  // $trans_data->values as $k=>$v ( )
                foreach ($options as $ke => $va) { //   0=>label, 1=>value,2=>image
                    if (isset($v->{$va}) && !wcpa_empty($v->{$va})) { // $trans_data->values items, $item->label, $item->value, so on
                        $base_data->values[$k]->{$va} = $v->{$va};
                    }
                }
            }
        }

        if (isset($base_data->relations) && is_array($base_data->relations)) {

            foreach ($base_data->relations as $l => $rel) {
                if (isset($rel->rules) && is_array($rel->rules)) {
                    foreach ($rel->rules as $i => $rule) {
                        if (isset($trans_data->relations[$l]->rules[$i]->rules->cl_val) && !wcpa_empty($trans_data->relations[$l]->rules[$i]->rules->cl_val)) {
                            $rule->rules->cl_val = $trans_data->relations[$l]->rules[$i]->rules->cl_val;
                        }
                    }
                }
            }
        }
    }

    public function merge_settings($base_id, $tran_id)
    {
        $original = get_post_meta($base_id, WCPA_META_SETTINGS_KEY, true);
        $trans = get_post_meta($tran_id, WCPA_META_SETTINGS_KEY, true);
        $settings = [
            'options_total_label' => 'text',
            'options_product_label' => 'text',
            'total_label' => 'text'
        ];

        foreach ($settings as $k => $v) {
            if (isset($trans[$k]) && !wcpa_empty($trans[$k])) {
                $original[$k] = $trans[$k];
            }
        }
        return $original;
    }

    public function settings_to_wpml()
    {
        //   WCPA_SETTINGS_KEY

        $settings = [
            'options_total_label' => 'Options Price Label',
            'options_product_label' => 'Product Price Label',
            'total_label' => 'Total Label',
            'add_to_cart_text' => 'Add to cart button text',
            'fee_label' => 'Fee Label',
            'price_prefix_label' => 'Price Prefix',
            'file_button_text' => 'Choose File',
            'file_droppable_action_text' => 'Droppable File Upload Action Text',
            'file_droppable_desc_text' => 'Droppable File Upload Description Text'
        ];
        //WMPL
        /**
         * register strings for translation
         */
        if (function_exists('icl_register_string')) {
            foreach ($settings as $k => $v) {
                icl_register_string(WCPA_TEXT_DOMAIN, false, wcpa_get_option($k));
            }
        }
//        if (function_exists('pll_register_string')) {
//            foreach ($settings as $k => $v) {
//                $string = wcpa_get_option($k);
//                if ($string !== '') {
//                    pll_register_string('wcpa_settings_' . $k, $string, WCPA_PLUGIN_NAME);
//                }
//            }
//        }


        //\WMPL
    }

    public function settings_to_ml_poly()
    {
        $settings = [
            'options_total_label' => 'Options Price Label',
            'options_product_label' => 'Product Price Label',
            'total_label' => 'Total Label',
            'add_to_cart_text' => 'Add to cart button text',
            'fee_label' => 'Fee Label',
            'price_prefix_label' => 'Price Prefix',
            'file_button_text' => 'Choose File',
            'file_droppable_action_text' => 'Droppable File Upload Action Text',
            'file_droppable_desc_text' => 'Droppable File Upload Description Text'
        ];
        if (function_exists('pll_register_string')) {
            foreach ($settings as $k => $v) {
                $string = wcpa_get_option($k);
                if ($string !== '') {
                    pll_register_string('wcpa_settings_' . $k, $string, WCPA_PLUGIN_NAME);
                }
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
