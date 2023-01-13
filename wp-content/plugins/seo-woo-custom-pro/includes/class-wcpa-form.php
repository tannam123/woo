<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class WCPA_Form
 */
class WCPA_Form
{

    public $_token;
    public $settings = array();
    /**
     * Constructor function
     */
    private $cart_error = false;
    private $data = null;
    private $relations = array();
    private $price_depends = array();
    private $label_depends = array();
    private $product = false;
    private $tax_per_unit = false;
    private $conversion_unit = [false, false];
    private $submited_data = array();
    private $has_custom_fields = false;

    public function __construct()
    {
        $this->_token = WCPA_TOKEN;
        $custom_fields = wcpa_get_option('product_custom_fields');
        if (is_array($custom_fields) && !empty($custom_fields)) {
            $this->has_custom_fields = true;
        }
    }

    public function get_form($form_id = false)
    {
        $this->data = array();
        if ($form_id) {
            $json_string = get_post_meta($form_id, WCPA_FORM_META_KEY, true);

            $this->data = json_decode($json_string);
        } else {
            $post_ids = get_posts(array('post_type' => WCPA_POST_TYPE, 'fields' => 'ids', 'posts_per_page' => -1));
            foreach ($post_ids as $id) {
                $json_string = get_post_meta($id, WCPA_FORM_META_KEY, true);
                $json_encoded = json_decode($json_string);
                if ($json_encoded && is_array($json_encoded)) {
                    $this->data = array_unique(array_merge($this->data, $json_encoded));
                }
            }
        }
    }

    public function is_wcpa_product($p_id)
    {
        $post_ids_main = array('full' => [], 'direct_purchasable' => []);
        $post_ids_main['direct_purchasable'] = get_posts(
            array(
                'fields' => 'ids',
                'post_type' => WCPA_POST_TYPE,
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'wcpa_drct_prchsble',
                        'value' => true,
                        'type' => 'BOOLEAN',
                    )
                )
            )
        );

        $post_ids_main['full'] = get_posts(
            array(
                'fields' => 'ids',
                'post_type' => WCPA_POST_TYPE,
                'posts_per_page' => -1
            )
        );

        foreach ($post_ids_main as $key => $post_ids) {
            if ($post_ids && count($post_ids)) {


            }
        }

    }

    public function get_wcpa_products()
    {
        global $wpdb;
        $pro_ids_main = get_transient(WCPA_PRODUCTS_TRANSIENT_KEY);

        if (false === $pro_ids_main) {

            $post_ids_main = array('full' => [], 'direct_purchasable' => []);

            $post_ids_main['direct_purchasable'] = get_posts(
                array(
                    'fields' => 'ids',
                    'post_type' => WCPA_POST_TYPE,
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'wcpa_drct_prchsble',
                            'value' => true,
                            'type' => 'BOOLEAN',
                        )
                    )
                )
            );

            $post_ids_main['full'] = get_posts(
                array(
                    'fields' => 'ids',
                    'post_type' => WCPA_POST_TYPE,
                    'posts_per_page' => -1
                )
            );

            foreach ($post_ids_main as $key => $post_ids) {
                if ($post_ids && count($post_ids)) {

                    $query = "SELECT 
distinct object_id from $wpdb->term_relationships
 where term_taxonomy_id"
                        . " in (select tr.term_taxonomy_id from $wpdb->term_relationships as tr left join $wpdb->term_taxonomy as tt on(tt.term_taxonomy_id=tr.term_taxonomy_id) where tr.object_id in (" . implode(',', $post_ids) . ")"
                        . "and  tt.taxonomy = 'product_cat')";

                    $pro_ids = $wpdb->get_col($query);
                    //  if (count($pro_ids)) {
//                        $pro_ids = get_posts(
//                            array(
//                                'fields' => 'ids',
//                                'post_type' => 'product',
//                                'numberposts' => -1,
//                                'include' => $pro_ids,
//                                'meta_query' => array(
//                                    'relation' => 'OR',
//                                    array(
//                                        'key' => 'wcpa_exclude_global_forms',
//                                        'value' => false,
//                                        'type' => 'BOOLEAN',
//                                    ),
//                                    array(
//                                        'key' => 'wcpa_exclude_global_forms',
//                                        'compare' => 'NOT EXISTS'
//                                    ),
//                                )
//                            )
//                        );
                    //   }
                    $exluded_ids = get_posts(
                        array(
                            'fields' => 'ids',
                            'post_type' => 'product',
                            'numberposts' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => 'wcpa_exclude_global_forms',
                                    'value' => '1',
                                    'type' => 'BOOLEAN',
                                )
                            )
                        )
                    );

                    $pro_ids = array_diff($pro_ids, $exluded_ids);

                    $temp = array_reduce($post_ids, function ($a, $b) {
                        return $a . " `meta_value` LIKE '%:$b;%' OR";
                    });
                    $temp .= trim($temp, 'OR');
                    $pro_ids2 = $wpdb->get_col("SELECT post_id  from $wpdb->postmeta WHERE meta_key = '" . WCPA_PRODUCT_META_KEY . "' and ($temp)");

                    if ($pro_ids2) {
                        $pro_ids2 = array_map('intval', $pro_ids2);

                        $pro_ids = array_unique(array_merge($pro_ids, $pro_ids2));
                    }
                } else {
                    $pro_ids = array();
                }
                $pro_ids_main[$key] = $pro_ids;
            }
//            $pro_ids_main['offset'] = $offset;
            set_transient(WCPA_PRODUCTS_TRANSIENT_KEY, $pro_ids_main, 12 * HOUR_IN_SECONDS);
        }

//dividing with 60 will give the execution time in minutes otherwise seconds

        return $pro_ids_main;
    }

    public function ajax_upload($details)
    {

        $response = [['status' => false, 'message' => '']];
        if (isset($_FILES['wcpa_file']["error"]) && $_FILES['wcpa_file']["error"] != 4) {
            $status = true;
            $error_message = '';
            $product_id = $details->product_id;
            $this->get_forms_by_product($product_id);
            $v = $this->find_element_by_id($details->element_id);
            if ($v === false || $v->type !== 'file') {
                $response = ['status' => false, 'message' => _('Invalid')];

                return $response;
            }
            if ($_FILES['wcpa_file']["error"] != UPLOAD_ERR_OK && $_FILES['wcpa_file']["error"] != UPLOAD_ERR_NO_FILE) {
                if ($_FILES['wcpa_file']["error"] == UPLOAD_ERR_INI_SIZE) {
                    $error_message = __('The uploaded file exceeds the maximum upload limit', 'wcpa-text-domain');
                } else if (in_array($_FILES['wcpa_file']["error"], array(
                    UPLOAD_ERR_INI_SIZE,
                    UPLOAD_ERR_FORM_SIZE
                ))) {
                    $error_message = __('The uploaded file exceeds the maximum upload limit', 'wcpa-text-domain');
                } else {
                    $error_message = __('The uploaded file error', 'wcpa-text-domain');
                }
            } else if (isset($_FILES['wcpa_file']) && $_FILES['wcpa_file']["error"] === UPLOAD_ERR_OK) {

                if (isset($v->uploadsize) && $v->uploadsize && ($_FILES['wcpa_file']["size"] > ($v->uploadsize * 1024 * 1024))) {
                    $status = false;
                    $error_message = (sprintf(__('File exceeds maximum upload size limit of %s MB', 'wcpa-text-domain'), $v->uploadsize));
                }

                if (isset($v->minuploadsize) && $v->minuploadsize && ($_FILES['wcpa_file']["size"] < ($v->minuploadsize * 1024 * 1024))) {
                    $status = false;
                    $error_message = (sprintf(__('File is too small in size, Need %s MB or above', 'wcpa-text-domain'), $v->minuploadsize));
                }

                $validate = wp_check_filetype_and_ext($_FILES['wcpa_file']['tmp_name'], $_FILES['wcpa_file']['name']);
                

                if (isset($v->exts_supported) && !$this->validate_file_with_config($v->exts_supported, $_FILES['wcpa_file']['name'])) {
                    $status = false;
                    $error_message = __('The uploaded file type is not supported', 'wcpa-text-domain');
                }
                
                if ($status === false) {
                    $response = ['status' => $status, 'message' => $error_message];
                } else {
                    $file = $this->handle_upload_ajax($v);
                    if ($file['status'] === false) {
                        $response = ['status' => false, 'message' => $file['error']];
                    } else {
                        $response = ['status' => true, 'message' => '', 'file' => $file['file']];
                    }
                }

                return $response;
            }
        }
        $response = [['status' => false, 'message' => $_FILES['wcpa_file']["error"]]];

        return $response;
    }

    public function get_forms_by_product($product_id = false)
    {


        if (($this->data === null || empty($this->data)) && $product_id) {
            $this->data = array();
            $this->cart_error = WCPA_Front_End::get_cart_error($product_id);

            $post_ids = $this->get_form_ids($product_id);
            $prod = wc_get_product($product_id);
            $pric_overide_base_price = false;
            $pric_overide_base_price_if_gt_zero = false;
            $pric_overide_base_price_fully = false;
            $pric_cal_option_once = false;
            $pric_use_as_fee = false;
            $render_after_acb = false;
            $render_before_acb = false;

            $enable_recaptcha = false;

            $bind_quantity = false;
            $quantity_bind_formula = false;


            if (wcpa_get_option('form_loading_order_by_date') === true) {

                if (is_array($post_ids) && count($post_ids)) {
                    $post_ids = get_posts(array(
                        'posts_per_page' => -1,
                        'include' => $post_ids,
                        'fields' => 'ids',
                        'post_type' => WCPA_POST_TYPE,
                        'posts_per_page' => -1
                    ));
                }
            }

            $post_ids = $this->re_order_forms($post_ids, $product_id);

            foreach ($post_ids as $id) {
                if (get_post_status($id) == 'publish') {

                    $json_string = get_post_meta($id, WCPA_FORM_META_KEY, true);

                    $json_encoded = json_decode($json_string);


                    if ($pric_cal_option_once === false) {
                        $pric_cal_option_once = wcpa_get_post_meta($id, 'pric_cal_option_once', false);
                    }
                    if ($enable_recaptcha === false) {
                        $enable_recaptcha = wcpa_get_post_meta($id, 'enable_recaptcha', false);
                    }

                    if ($pric_use_as_fee === false) {
                        $pric_use_as_fee = wcpa_get_post_meta($id, 'pric_use_as_fee', false);
                    }

                    if ($pric_overide_base_price === false) {
                        $pric_overide_base_price = wcpa_get_post_meta($id, 'pric_overide_base_price', false);
                        if ($pric_overide_base_price === '') {
                            $pric_overide_base_price = false;
                        } else if ($pric_overide_base_price === '1') {
                            $pric_overide_base_price = true;
                        }
                    }
                    if ($pric_overide_base_price_if_gt_zero === false) {
                        $pric_overide_base_price_if_gt_zero = wcpa_get_post_meta($id, 'pric_overide_base_price_if_gt_zero', false);
                        if ($pric_overide_base_price_if_gt_zero === '') {
                            $pric_overide_base_price_if_gt_zero = false;
                        } else if ($pric_overide_base_price_if_gt_zero === '1') {
                            $pric_overide_base_price_if_gt_zero = true;
                        }

                    }

                    if ($pric_overide_base_price_fully === false) {
                        $pric_overide_base_price_fully = wcpa_get_post_meta($id, 'pric_overide_base_price_fully', false);
                        if ($pric_overide_base_price_fully === '') {
                            $pric_overide_base_price_fully = false;
                        } else if ($pric_overide_base_price_fully === '1') {
                            $pric_overide_base_price_fully = true;
                        }


                    }


                    if ($render_after_acb === false) {
                        $render_after_acb = wcpa_get_post_meta($id, 'render_after_acb', false);
                    }


                    if ($bind_quantity === false) {
                        $bind_quantity = wcpa_get_post_meta($id, 'bind_quantity', false);
                        if ($bind_quantity) {
                            $quantity_bind_formula = wcpa_get_post_meta($id, 'quantity_bind_formula', false);
                            if (empty($quantity_bind_formula) || trim($quantity_bind_formula) == '') {
                                $bind_quantity = false;
                            }
                        }
                    }

                    $cont_global = wcpa_get_post_meta($id, 'cont_use_global', true);
                    $form_rules = [
                        'pric_cal_option_once' => wcpa_get_post_meta($id, 'pric_cal_option_once', false),
                        'exclude_from_discount' => wcpa_get_post_meta($id, 'exclude_from_discount', false),
                        'pric_use_as_fee' => wcpa_get_post_meta($id, 'pric_use_as_fee', false),
                        'fee_label' => ($cont_global ? wcpa_get_option('fee_label', 'Fee', true) : wcpa_get_post_meta($id, 'fee_label', wcpa_get_option('fee_label', 'Fee'))),
                        'render_after_acb' => wcpa_get_post_meta($id, 'render_after_acb', false),
                        'disp_hide_options_price' => wcpa_get_post_meta($id, 'disp_hide_options_price', false),
                    ];


                    if ($json_encoded && is_array($json_encoded)) {
                        // check for external forms

                        $json_encoded = $this->appendGlobalForm($json_encoded);

                        foreach ($json_encoded as $_k => $v) {

                            if ($this->has_custom_fields) {
                                if (isset($v->label)) {
                                    $v->label = $this->replace_custom_field($v->label, $prod);
                                }
                                if (isset($v->value)) {
                                    $v->value = $this->replace_custom_field($v->value, $prod);
                                }
                                if (isset($v->placeholder)) {
                                    $v->placeholder = $this->replace_custom_field($v->placeholder, $prod);
                                }
                                if (isset($v->description)) {
                                    $v->description = $this->replace_custom_field($v->description, $prod);
                                }
                                if (isset($v->price)) {
                                    $v->price = $this->replace_custom_field($v->price, $prod);
                                }
                                if (isset($v->values) && is_array($v->values)) {
                                    foreach ($v->values as $e) {
                                        if (isset($e->label)) {
                                            $e->label = $this->replace_custom_field($e->label, $prod);
                                        }
                                        if (isset($e->value)) {
                                            $e->value = $this->replace_custom_field($e->value, $prod);
                                        }
                                        if (isset($e->price)) {
                                            $e->price = $this->replace_custom_field($e->price, $prod);
                                        }
                                    }
                                }
                            }

                            if (isset($v->enableCl) && $v->enableCl && isset($v->relations) && is_array($v->relations)) {

                                foreach ($v->relations as $val) {

                                    foreach ($val->rules as $k) {
                                        if (!empty($k->rules->cl_field)) {
                                            if (!isset($this->relations[$k->rules->cl_field])) {
                                                $this->relations[$k->rules->cl_field] = array();
                                            }
                                            if ($this->has_custom_fields && isset($k->rules->cl_val) && !empty($k->rules->cl_val)) {
                                                if (is_string($k->rules->cl_val)) {
                                                    $k->rules->cl_val = $this->replace_custom_field($k->rules->cl_val, $prod);
                                                } else {
                                                    if (isset($k->rules->cl_val->value) && is_string($k->rules->cl_val->value)) {
                                                        $k->rules->cl_val->value = $this->replace_custom_field($k->rules->cl_val->value, $prod);
                                                    }

                                                }

                                            }
                                            if ($k->rules->cl_field === 'attribute' && $k->rules->cl_relation) {
                                                $atr = wc_get_attribute($k->rules->cl_relation);
                                                if($atr){
                                                    $term = get_term_by('id', $k->rules->cl_val->value, $atr->slug);
                                                    $k->rules->cl_val = isset($term->slug) ? $term->slug : '';
                                                    $k->rules->cl_relation = sanitize_title($atr->slug);
                                                }
                                            }
                                            $this->relations[$k->rules->cl_field][] = (isset($v->elementId) ? $v->elementId : false);
                                        }
                                    }
                                }
                            }
                            if ($pric_use_as_fee === false) {
                                if (isset($v->enablePrice) && $v->enablePrice && isset($v->use_as_fee) && $v->use_as_fee) {
                                    $pric_use_as_fee = true;
                                }
                            }

                            /*
                             * paragraph formula support for label
                             * */
                            if ($v->type === 'paragraph') {
                                if ($matches = $this->check_field_price_dependency($v->label)) {
                                    foreach ($matches as $match) {
                                        if (!isset($this->label_depends[$match])) {
                                            $this->label_depends[$match] = array();
                                        }
                                        if (isset($v->elementId)) {
                                            if (!in_array($v->elementId, $this->label_depends[$match])) {
                                                $this->label_depends[$match][] = $v->elementId;
                                            }
                                        }
                                    }
                                }
                            }
                            /**/

                            /*
                             * statictext formula support for label
                             * */
                            if ($v->type === 'statictext') {
                                if(isset($v->value)){
                                    if ($matches = $this->check_field_price_dependency($v->value)) {
                                        foreach ($matches as $match) {
                                            if (!isset($this->label_depends[$match])) {
                                                $this->label_depends[$match] = array();
                                            }
                                            if (isset($v->elementId)) {
                                                if (!in_array($v->elementId, $this->label_depends[$match])) {
                                                    $this->label_depends[$match][] = $v->elementId;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            /**/

                            if (isset($v->enablePrice) && $v->enablePrice && isset($v->pricingType) && $v->pricingType === 'custom') {

                                if (isset($v->priceOptions) && $v->priceOptions == 'different_for_all') {

                                    foreach ($v->values as $e) {

                                        if ($matches = $this->check_field_price_dependency($e->price)) {
                                            foreach ($matches as $match) {
                                                if (!isset($this->price_depends[$match])) {
                                                    $this->price_depends[$match] = array();
                                                }
                                                if (isset($v->elementId)) {
                                                    if (!in_array($v->elementId, $this->price_depends[$match])) {
                                                        $this->price_depends[$match][] = $v->elementId;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else if (isset($v->price)) {
                                    if ($matches = $this->check_field_price_dependency($v->price)) {
                                        foreach ($matches as $match) {
                                            if (!isset($this->price_depends[$match])) {
                                                $this->price_depends[$match] = array();
                                            }
                                            if (isset($v->elementId)) {
                                                if (!in_array($v->elementId, $this->price_depends[$match])) {
                                                    $this->price_depends[$match][] = $v->elementId;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $v->form_id = $id;
                            $v->form_rules = $form_rules;
                        }

                        $this->data = array_merge($this->data, $json_encoded);
                    }
                }
            }

            if ($bind_quantity) {
                if ($matches = $this->check_field_price_dependency($quantity_bind_formula)) {
                    foreach ($matches as $match) {
                        if (!isset($this->price_depends[$match])) {
                            $this->price_depends[$match] = array();
                        }
                        if (isset($v->elementId)) {
                            if (!in_array($v->elementId, $this->price_depends[$match])) {
                                $this->price_depends[$match][] = $v->elementId;
                            }
                        }
                    }
                }
            }
        }


        $this->data = apply_filters('wcpa_product_form_fields', $this->data, $product_id);


        if (empty($this->settings) && isset($id)) {
            $dis_global = wcpa_get_post_meta($id, 'disp_use_global', true);
            $cont_global = wcpa_get_post_meta($id, 'cont_use_global', true);
            $this->settings = [
                'disp_use_global' => $dis_global,
                'disp_show_field_price' => ($dis_global ? wcpa_get_option('disp_show_field_price', true) : wcpa_get_post_meta($id, 'disp_show_field_price', wcpa_get_option('disp_show_field_price', true))),
                'disp_summ_show_total_price' => ($dis_global ? wcpa_get_option('disp_summ_show_total_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_total_price', wcpa_get_option('disp_summ_show_total_price', true))),
                'disp_summ_show_product_price' => ($dis_global ? wcpa_get_option('disp_summ_show_product_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_product_price', wcpa_get_option('disp_summ_show_product_price', true))),
                'disp_summ_show_option_price' => ($dis_global ? wcpa_get_option('disp_summ_show_option_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_option_price', wcpa_get_option('disp_summ_show_option_price', true))),
                'pric_overide_base_price' => $pric_overide_base_price,
                'pric_overide_base_price_if_gt_zero' => $pric_overide_base_price_if_gt_zero,
                'pric_overide_base_price_fully' => $pric_overide_base_price_fully,
                'pric_cal_option_once' => $pric_cal_option_once,
                'pric_use_as_fee' => $pric_use_as_fee,
                'render_after_acb' => $render_after_acb,
                'enable_recaptcha' => ($enable_recaptcha ? $enable_recaptcha : wcpa_get_option('enable_recaptcha', false)),
                'bind_quantity' => $bind_quantity,
                'quantity_bind_formula' => $quantity_bind_formula,
                'disp_hide_options_price' => wcpa_get_post_meta($id, 'disp_hide_options_price', false),
                'cont_use_global' => $cont_global,
                'options_total_label' => ($cont_global ? wcpa_get_option('options_total_label', 'Options Price', true) : wcpa_get_post_meta($id, 'options_total_label', wcpa_get_option('options_total_label', 'Options Price'))),
                'options_product_label' => ($cont_global ? wcpa_get_option('options_product_label', 'Product Price', true) : wcpa_get_post_meta($id, 'options_product_label', wcpa_get_option('options_product_label', 'Product Price', true))),
                'total_label' => ($cont_global ? wcpa_get_option('total_label', 'Total', true) : wcpa_get_post_meta($id, 'total_label', wcpa_get_option('total_label', 'Total'))),
                'fee_label' => ($cont_global ? wcpa_get_option('fee_label', 'Fee', true) : wcpa_get_post_meta($id, 'fee_label', wcpa_get_option('fee_label', 'Fee'))),
                'thumb_image' => false
            ];
        }
    }

    public function get_form_ids($product_id)
    {
        $key_1_value = get_post_meta($product_id, 'wcpa_exclude_global_forms', true);
        $post_ids = array();
        $ml = new WCPA_Ml();
        if (empty($key_1_value)) {

            $post_ids = get_posts(
                array(
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'ids',
                            'include_children' => false,
                            'terms' => wp_get_object_terms($product_id, 'product_cat', array(
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'fields' => 'ids'
                            ))
                        )
                    ),
                    'fields' => 'ids',
                    'post_type' => WCPA_POST_TYPE,
                    'posts_per_page' => -1
                )
            );
        }
        $form_ids_set2 = maybe_unserialize(get_post_meta($product_id, WCPA_PRODUCT_META_KEY, true));

        if ($form_ids_set2 && is_array($form_ids_set2)) {
            $post_ids = array_unique(array_merge($post_ids, $form_ids_set2));
        }

        if ($ml->is_active()) {
            $post_ids = $ml->lang_object_ids($post_ids, 'post', false);
        }

        return $post_ids;
    }

    /**
     * @param $ids
     * @param $p_id
     *
     * @return array
     */
    public function re_order_forms($ids, $p_id)
    {


        $form_order = get_post_meta($p_id, 'wcpa_product_meta_order', true);


        if ($form_order && is_array($form_order)) {
            $ids_new = array();
            $form_order_new = array();
            foreach ($ids as $id) {
                if (isset($form_order[$id])) {
                    $form_order_new[$id] = $form_order[$id];
                }
            }
            arsort($form_order_new);

            foreach ($form_order_new as $form_id => $order) {
                $index = array_search($form_id, $ids);
                if ($index !== false) {
                    unset($ids[$index]); // remove item at index 0
                    $ids = array_values($ids); // 'reindex' array
                    $length = count($ids);
                    if ($order <= 0) {
                        $pos = 0;
                    } else if ($order > $length) {
                        $pos = $length;
                    } else {
                        $pos = $order - 1;
                    }

                    array_splice($ids, $pos, 0, $form_id);
                }

            }
        }

        return $ids;
    }

    public function appendGlobalForm($json_encoded, $depth = 0)
    {
        $_json_encoded = array();
        $hav_placeselector = false;
        $ml = new WCPA_Ml();
        foreach ($json_encoded as $v) {
            if ($v->type == 'formselector' && isset($v->form_id) && is_numeric($v->form_id)) {
                if ($ml->is_active()) {
                    $form_id = $ml->lang_object_ids($v->form_id, 'post', false);
                } else {
                    $form_id = $v->form_id;
                }
                $json_string = get_post_meta($form_id, WCPA_FORM_META_KEY, true);
                $json_encoded_sub = json_decode($json_string);
                if (is_array($json_encoded_sub) && $json_encoded_sub) {
                    foreach ($json_encoded_sub as $v_sub) {
                        $_json_encoded[] = $v_sub;
                    }
                    $hav_placeselector = true;
                }


            } else {
                $_json_encoded[] = $v;
            }

        }

        if ($hav_placeselector && $depth < 2) {
            $depth++;
            $_json_encoded = $this->appendGlobalForm($_json_encoded, $depth);
        }

        return $_json_encoded;
    }

    public function replace_custom_field($string = '', $prod = false)
    {
        $cf_prefix = wcpa_get_option('wcpa_cf_prefix', 'wcpa_pcf_');

        if (is_string($string) && preg_match_all('/\{(\s)*?wcpa_pcf_([^}]*)}/', $string, $matches)) {
            $pro_id = $prod->get_parent_id();
            if ($pro_id == 0) {
                $pro_id = $prod->get_id();
            }
            foreach ($matches[2] as $k => $match) {

                $cf_value = get_post_meta($pro_id, $cf_prefix . trim($match), true);
                if ($cf_value == '' || $cf_value == false) {
                    $custom_fields = wcpa_get_option('product_custom_fields');
                    if (is_array($custom_fields)) {
                        foreach ($custom_fields as $cf) {
                            if ($cf['name'] == trim($match)) {
                                $cf_value = $cf['value'];
                                break;
                            }
                        }
                    }


                }
                if ($cf_value !== '' || $cf_value !== false) {
                    $string = str_replace($matches[0][$k], $cf_value, $string);
                }

            }
        }


        return $string;
    }

    public function check_field_price_dependency($price_formula)
    {
        $matches = false;

        if (preg_match_all('/\{(\s)*?field\.([^}]*)}/', $price_formula, $matches)) {
            $ids = array();
            foreach ($matches[2] as $match) {
                $ele = explode('.', $match);
                if (is_array($ele) && count($ele) > 1 && in_array($ele[1], [
                        'value',
                        'price',
                        'count',
                        'days',
                        'seconds',
                        'timestamp'
                    ])) {
                    $ids[] = $ele[0];
                }
            }

            return array_unique($ids);
        } else {
            return false;
        }
    }

    private function find_element_by_id($element_id)
    {

        if (count($this->data)) {
            foreach ($this->data as $v) {
                if ($element_id == $v->elementId) {
                    return $v;
                }
            }
        }

        return false;
    }

    public function validate_file_with_config($exts, $file_name)
    {
        $supported_extions = false;
        if (!empty($exts)) {
            $supported_extions = preg_split("/[\s,]+/", $exts);
            if (is_array($supported_extions)) {
                $supported_extions = array_filter(array_map('trim', $supported_extions));
                if (count($supported_extions) == 0) {
                    $supported_extions = false;
                }
            } else {
                $supported_extions = false;
            }
        }
        if ($supported_extions !== false) {
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            if (!in_array($ext, $supported_extions)) {
                return false;
            }
        }

        return true;
    }

    public function handle_upload_ajax($v)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $uploadedfile = $_FILES['wcpa_file'];
        $uploadedfile_name = $_FILES['wcpa_file']["name"];
        $upload_overrides = array('test_form' => false);
        add_filter('upload_dir', array($this, 'upload_dir_temp'));
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        remove_filter('upload_dir', array($this, 'upload_dir_temp'));
        if ($movefile && !isset($movefile['error'])) {
            $file_name = explode('/' . WCPA_UPLOAD_DIR . '/', $movefile['file']);
            $movefile['file'] = $file_name[1];

            return ['status' => true, 'file' => array_merge($movefile, array('file_name' => $uploadedfile_name))];
        } else {
            /**
             * Error generated by _wp_handle_upload()
             * @see _wp_handle_upload() in wp-admin/includes/file.php
             */
            return ['status' => false, 'error' => $movefile['error']];

            //echo $movefile['error'];
        }
    }

    public function check_label_has_formula($price_formula)
    {
        $matches = false;

        if (preg_match_all('/\{(\s)*?field\.([^}]*)}/', $price_formula, $matches)) {
            $ids = array();
            foreach ($matches[2] as $match) {
                $ele = explode('.', $match);
                if (is_array($ele) && count($ele) > 1 && in_array($ele[1], [
                        'value',
                        'price',
                        'count',
                        'days',
                        'seconds',
                        'timestamp'
                    ])) {
                    $ids[] = $ele[0];
                }
            }

            return array_unique($ids);
        } else {
            return false;
        }
    }

    public function validate_form_data($product_id = false, $qty = 1)
    {
        $this->get_forms_by_product($product_id);
        $status = true;
        if (isset($this->settings['enable_recaptcha']) && $this->settings['enable_recaptcha'] === true) {
            if ($this->is_recaptcha_valid() !== true) {
                $status = false;
                $this->add_cart_error(__('Please verify you are not a bot', 'wcpa-text-domain'));
                WCPA_Front_End::set_cart_error($product_id, !$status);

                return $status;
            }
        }

        $this->process_cl_logic($product_id);

        if($this->data){
            foreach ($this->data as $v) {
                if (!isset($v->cl_status) || $v->cl_status === 'visible') {
                    if ($v->type != 'file' && isset($v->required) && $v->required && (!isset($_REQUEST[$v->name]) || $_REQUEST[$v->name] == "" || (is_string($_REQUEST[$v->name]) && trim($_REQUEST[$v->name]) == ""))) {
                        $status = false;
                        /* translators: %s: field label */
                        $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));

                    }
                    if (isset($v->min_options) && $v->min_options > 0 && isset($_REQUEST[$v->name])) {
                        if (is_array($_REQUEST[$v->name])) {
                            if (count($_REQUEST[$v->name]) < $v->min_options) {
                                $status = false;

                                $this->add_cart_error(sprintf(__('You have to select minimum %s items for  field %s', 'wcpa-text-domain'), $v->min_options, $v->label));
                            }
                        } else {
                            if (empty($_REQUEST[$v->name])) {
                                $status = false;
                                $this->add_cart_error(sprintf(__('You have to select minimum %s items for  field %s', 'wcpa-text-domain'), $v->min_options, $v->label));
                            }
                        }
                    }
                    if (isset($v->max_options) && $v->max_options > 0 && isset($_REQUEST[$v->name])) {
                        if (is_array($_REQUEST[$v->name])) {
                            if (count($_REQUEST[$v->name]) > $v->max_options) {
                                $status = false;
                                $this->add_cart_error(sprintf(__('You can select only maximum %s items for  field %s', 'wcpa-text-domain'), $v->max_options, $v->label));
                            }
                        }
                    }
                    if ($v->type == 'file') {
                        if(isset($_FILES[$v->name]["name"]) && is_array($_FILES[$v->name]["name"]) && !empty($_FILES[$v->name]["name"])){
                            $total_files = count($_FILES[$v->name]["name"]);
                            for($i=0;$i<$total_files;$i++){
                                if (isset($v->ajax_upload) && $v->ajax_upload === true && isset($_REQUEST[$v->name . '_ajax']) && !empty($_REQUEST[$v->name . '_ajax'])) {
                                    $temp = explode('||', $_REQUEST[$v->name . '_ajax']);
                                    if (isset($v->required) && $v->required && (empty($temp[0]))) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));
                                    }
                                } else {
                                    if (isset($v->required) && $v->required && (!isset($_FILES[$v->name]['name'][$i]) || $_FILES[$v->name]["error"][$i] == UPLOAD_ERR_NO_FILE)) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));
                                    } else if (isset($_FILES[$v->name]['name'][$i]) && $_FILES[$v->name]["error"][$i] != UPLOAD_ERR_OK && $_FILES[$v->name]["error"][$i] != UPLOAD_ERR_NO_FILE) {
                                        $status = false;
                                        if ($_FILES[$v->name]["error"][$i] == UPLOAD_ERR_INI_SIZE) {
                                            $error_message = __('The uploaded file exceeds the maximum upload limit for field %s', 'wcpa-text-domain');
                                        } else if (in_array($_FILES[$v->name]["error"][$i], array(
                                            UPLOAD_ERR_INI_SIZE,
                                            UPLOAD_ERR_FORM_SIZE
                                        ))) {
                                            $error_message = __('The uploaded file exceeds the maximum upload limit for field %s', 'wcpa-text-domain');
                                        } else {
                                            $error_message = __('The uploaded file error for field %s', 'wcpa-text-domain');
                                        }

                                        $this->add_cart_error(sprintf($error_message, $v->label));
                                    } else if (isset($_FILES[$v->name]['name'][$i]) && $_FILES[$v->name]["error"][$i] === UPLOAD_ERR_OK) {
                                        if (isset($v->uploadsize) && $v->uploadsize && ($_FILES[$v->name]["size"][$i] > ($v->uploadsize * 1024 * 1024))) {
                                            $status = false;
                                            $this->add_cart_error(sprintf(__('Field %s exceeds maximum upload size limit of %s MB', 'wcpa-text-domain'), $v->label, $v->uploadsize));
                                        }
                                        if (isset($v->minuploadsize) && $v->minuploadsize && ($_FILES['wcpa_file']["size"][$i] < ($v->minuploadsize * 1024 * 1024))) {
                                            $status = false;
                                            $this->add_cart_error(sprintf(__('File is too small in size, Need %s MB or above', 'wcpa-text-domain'), $v->minuploadsize));
                                        }
                                        $validate = wp_check_filetype_and_ext($_FILES[$v->name]['tmp_name'][$i], $_FILES[$v->name]['name'][$i]);
                                        if (!$validate['ext']) {
                                            $status = false;
                                            $this->add_cart_error(sprintf(__('The uploaded file type is not supported for field %s', 'wcpa-text-domain'), $v->label));
                                        }

                                        if (isset($v->exts_supported) && !$this->validate_file_with_config($v->exts_supported, $_FILES[$v->name]['name'][$i])) {
                                            $status = false;
                                            $this->add_cart_error(sprintf(__('The uploaded file type is not supported for field %s', 'wcpa-text-domain'), $v->label));
                                        }
                                    }
                                }
                            }
                        } else {
                            if (isset($v->ajax_upload) && $v->ajax_upload === true && (isset($_REQUEST[$v->name . '_ajax']) || isset($_REQUEST[$v->name . '_ajaxmultiple'])) && (!empty($_REQUEST[$v->name . '_ajax']) || !empty($_REQUEST[$v->name . '_ajaxmultiple']))) {
                                if(isset($_REQUEST[$v->name . '_ajaxmultiple']) && !empty($_REQUEST[$v->name . '_ajaxmultiple'])){
                                    $tempData = str_replace("\\", "",$_REQUEST[$v->name . '_ajaxmultiple']);
                                    $upFiles = json_decode($tempData);
                                    if($upFiles){
                                    $count = 0;
                                        foreach($upFiles as $upFile) {
                                            if($upFile) {
                                                $temp = explode('||', $upFile);
                                                if (isset($v->required) && $v->required && (!empty($temp[0]))) {
                                                    $count++;
                                                }
                                            }
                                        }
                                        if(($count==0) && isset($v->required) && $v->required) {
                                            $status = false;
                                            $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));
                                        }
                                    }
                                } else {
                                    $temp = explode('||', $_REQUEST[$v->name . '_ajax']);
                                    if (isset($v->required) && $v->required && (empty($temp[0]))) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));
                                    }
                                }
                            } else {
                                if (isset($v->required) && $v->required && (!isset($_FILES[$v->name]) || $_FILES[$v->name]["error"] == UPLOAD_ERR_NO_FILE)) {
                                    $status = false;
                                    $this->add_cart_error(sprintf(__('Field %s is required', 'wcpa-text-domain'), $v->label));
                                } else if (isset($_FILES[$v->name]) && $_FILES[$v->name]["error"] != UPLOAD_ERR_OK && $_FILES[$v->name]["error"] != UPLOAD_ERR_NO_FILE) {
                                    $status = false;
                                    if ($_FILES[$v->name]["error"] == UPLOAD_ERR_INI_SIZE) {
                                        $error_message = __('The uploaded file exceeds the maximum upload limit for field %s', 'wcpa-text-domain');
                                    } else if (in_array($_FILES[$v->name]["error"], array(
                                        UPLOAD_ERR_INI_SIZE,
                                        UPLOAD_ERR_FORM_SIZE
                                    ))) {
                                        $error_message = __('The uploaded file exceeds the maximum upload limit for field %s', 'wcpa-text-domain');
                                    } else {
                                        $error_message = __('The uploaded file error for field %s', 'wcpa-text-domain');
                                    }

                                    $this->add_cart_error(sprintf($error_message, $v->label));
                                } else if (isset($_FILES[$v->name]) && $_FILES[$v->name]["error"] === UPLOAD_ERR_OK) {
                                    if (isset($v->uploadsize) && $v->uploadsize && ($_FILES[$v->name]["size"] > ($v->uploadsize * 1024 * 1024))) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('Field %s exceeds maximum upload size limit of %s MB', 'wcpa-text-domain'), $v->label, $v->uploadsize));
                                    }
                                    if (isset($v->minuploadsize) && $v->minuploadsize && ($_FILES['wcpa_file']["size"] < ($v->minuploadsize * 1024 * 1024))) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('File is too small in size, Need %s MB or above', 'wcpa-text-domain'), $v->minuploadsize));
                                    }
                                    $validate = wp_check_filetype_and_ext($_FILES[$v->name]['tmp_name'], $_FILES[$v->name]['name']);
                                    if (!$validate['ext']) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('The uploaded file type is not supported for field %s', 'wcpa-text-domain'), $v->label));
                                    }

                                    if (isset($v->exts_supported) && !$this->validate_file_with_config($v->exts_supported, $_FILES[$v->name]['name'])) {
                                        $status = false;
                                        $this->add_cart_error(sprintf(__('The uploaded file type is not supported for field %s', 'wcpa-text-domain'), $v->label));
                                    }
                                }
                            }
                        }
                    } else if ($v->type == 'text' && isset($v->allowed_chars) && !empty($v->allowed_chars) && isset($_REQUEST[$v->name]) && !empty($_REQUEST[$v->name])) {
                        $allowed_chars = $v->allowed_chars;
                        $value_filtered = $_REQUEST[$v->name];
                        if ($allowed_chars[0] != '/') {
                            $allowed_chars = '/' . $allowed_chars . '/i';
                        } else {
                            $allowed_chars = preg_replace('/\/g/', '/', $allowed_chars);
                        }
                        try {
                            $value_filtered = preg_replace($allowed_chars, '', $value_filtered);// remove all allowed characters and check if any left
                        } catch (Exception $e) {
                            $value_filtered = '';
                        }
                        if (trim($value_filtered) !== '') {
                            $status = false;
                            $this->add_cart_error(sprintf(__('Characters %s is not supported for field %s', 'wcpa-text-domain'), $value_filtered, $v->label));

                        }


                    } else if ($v->type == 'number') {
                        $val = $_REQUEST[$v->name];
                        if (isset($v->max) && $v->max !== '' && $val !== '') {
                            if ($val > $v->max) {
                                $status = false;
                                $this->add_cart_error(sprintf(__('Value must be less than or equal to %d for field %s ', 'wcpa-text-domain'), $v->max, $v->label));
                            }

                        }
                        if (isset($v->min) && $v->min !== '') {
                            if ($val < $v->min && $val !== '') {
                                $status = false;
                                $this->add_cart_error(sprintf(__('Value must be greater than or equal to %d for field %s ', 'wcpa-text-domain'), $v->min, $v->label));
                            }

                        }
                    } else if($v->type == 'productGroup') {
                        $product_ids = array();
                        $quantities = array();
                        $multiple = false;
                        if ((isset($v->multiple) && $v->multiple)) {
                            $multiple = true;
                        }
                        if(isset($_REQUEST[$v->name])){
                            if($multiple){
                                $product_ids = array_map('intval', $_REQUEST[$v->name]);
                            } else {
                                $product_ids = array(intval($_REQUEST[$v->name]));
                            }
                            $quantities_unfiltered = array();
                            if(isset($_REQUEST[$v->name.'_quantity'])){
                                $quantities_unfiltered = array_map('intval', $_REQUEST[$v->name.'_quantity']);
                                if(!$multiple) {
                                    foreach($v->values as $k=>$vl) {
                                        if(in_array(intval($vl->value), $product_ids)) {
                                            $quantities_unfiltered =  array($quantities_unfiltered[$k]);
                                        }
                                    }
                                }
                            } else {
                                if(!empty($product_ids)){
                                    foreach($product_ids as $key=>$pid) {
                                        $quantities_unfiltered[$key] =  1; //Set Quantity if not set from front end
                                    }
                                }
                            }

                            if(!empty($product_ids)) {
                                foreach($product_ids as $k=>$pid) {
                                    $quantities[$k] = $quantities_unfiltered[$k];
                                }
                                $_REQUEST[$v->name.'_quantity'] = $quantities;
                            }


                            $new_product_array = array();
                            if(!empty($product_ids)){
                                $product_ids = array_map('intval', $product_ids);
                                $args = array(
                                    'post_status' => 'publish',
                                    'include' => $product_ids,
                                    'posts_per_page' => -1,
                                );
                                
                                $product_array = wc_get_products($args);
                                // Reorder $product_array
                                foreach($product_array as $val) {
                                    if (false !== $key = array_search($val->get_id(), $product_ids)) {
                                        $new_product_array[$key] = $val;
                                    }
                                }
                                ksort($new_product_array);
                            }

                            //Quantity depend
                            if(
                                !(isset($v->independentQuantity) && $v->independentQuantity)
                            ) {
                                $quantities = array_map(function ($q) use ($qty) { return $q * $qty ; } , $quantities);
                            }
                            
                            if(!empty($new_product_array)) {
                                $_REQUEST[$v->name] = $new_product_array;
                                foreach($new_product_array as $i=>$p) {
                                    $validate = wcpa_validate_product_field($p, $quantities[$i]);
                                    if($validate['error']) {
                                        $status = false;
                                        $this->add_cart_error($validate['message']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        WCPA_Front_End::set_cart_error($product_id, !$status);

        return $status;
    }

    public function is_recaptcha_valid()
    {

        // Make sure this is an acceptable type of submissions
        if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
            try {
                $url = 'https://www.google.com/recaptcha/api/siteverify';
                $data = [
                    'secret' => wcpa_get_option('recaptcha_secret_key', ''),
                    'response' => $captcha
                ];
                $options = [
                    'http' => [
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data)
                    ]
                ];
                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);

                return json_decode($result)->success;
            } catch (Exception $e) {
                return null;
            }
        } // Not a POST request, set a 403 (forbidden) response code.
        else {
            return false;
        }
    }

    private function add_cart_error($message)
    {
        wc_add_notice($message, 'error');
    }

    public function process_cl_logic($product_id)
    {

        $this->get_forms_by_product($product_id);
        $processed_ids = array();
        if($this->data){
            foreach ($this->data as $l => $v) {
                $processed_ids[] = isset($v->elementId) ? $v->elementId : false;
                if (isset($v->enableCl) && $v->enableCl && isset($v->relations) && is_array($v->relations)) {
                    $evel_str = '';

                    foreach ($v->relations as $val) {
                        if (is_array($val->rules) && count($val->rules)) {
                            $evel_str .= '(';
                            foreach ($val->rules as $k) {
                                $evel_str .= '(';
                                if ($this->eval_relation($k->rules, $product_id)) {
                                    $evel_str .= ' true ';
                                } else {
                                    $evel_str .= ' false ';
                                }
                                $evel_str .= ') ' . (($k->operator !== false) ? $k->operator : '') . ' ';
                            }

                            if (count($val->rules) > 0) {
                                preg_match_all('/\(.*\)/', $evel_str, $match);
                                $evel_str = $match[0][0] . ' ';
                            }

                            $evel_str .= ') ' . (($val->operator !== false) ? $val->operator : '') . ' ';
                        }
                    }
                    if (count($v->relations) > 0) {
                        preg_match_all('/\(.*\)/', $evel_str, $match);
                        $evel_str = $match[0][0] . ' ';
                    }

                    $evel_str = str_replace(['and', 'or'], ['&&', '||'], $evel_str);

                    $result = eval('return ' . $evel_str . ';');
                    $status = (isset($this->data[$l]->cl_status) ? $this->data[$l]->cl_status : false);

                    if ($result === true) {
                        if ($v->cl_rule === 'show') {
                            $this->data[$l]->cl_status = 'visible';
                        } else {
                            $this->data[$l]->cl_status = 'hidden';
                        }
                    } else {
                        if ($v->cl_rule === 'show') {
                            $this->data[$l]->cl_status = 'hidden';
                        } else {
                            $this->data[$l]->cl_status = 'visible';
                        }
                    }

                    if (isset($this->relations[$v->elementId]) && $status !== $this->data[$l]->cl_status) {
                        foreach ($this->relations[$v->elementId] as $id) {
                            if (in_array($id, $processed_ids)) {
                                //go back and iterate again
                                $this->process_cl_logic($product_id);
                            }
                        }
                        //start processing logic from start again if this element is related with any other logic
                    }
                }
            }
        }
    }

    public function eval_relation($rules, $product_id)
    {
        $cf_prefix = wcpa_get_option('wcpa_cf_prefix', 'wcpa_pcf_');

        if (!isset($rules->cl_field) || empty($rules->cl_field)) {
            return false;
        }
        if ($rules->cl_relation === '0') {
            return false;
        }
        if ($rules->cl_field === 'quantity') {
//            $field = 'quantity';
            $field = (object)['type' => 'quantity'];
        } else if ($rules->cl_field === 'attribute') {
//            $field = 'attribute';
            $field = (object)['type' => 'attribute'];
            $product = wc_get_product($product_id);
        } else if ($rules->cl_field === 'stock_quantity') {
            $product = wc_get_product($product_id);
            $field = (object)['type' => 'stock_quantity'];
        } else if ($rules->cl_field === 'stock_status') {

            $product = wc_get_product($product_id);
            $field = (object)['type' => 'stock_status'];

        } else if ($rules->cl_field === 'product_ids') {
            $product = wc_get_product($product_id);

            $field = (object)['type' => 'product_ids'];
        } else if ($rules->cl_field === 'custom_field') {
            $product = wc_get_product($product_id);
            $field = (object)['type' => 'custom_field'];

        } else {
            $field = array_filter($this->data, function ($e) use ($rules) {
                if (isset($e->elementId) && !empty($e->elementId) && $rules->cl_field === $e->elementId) {
                    return true;
                } else {
                    return false;
                }
            });
        }


        if (is_array($field)) {
            $field = reset($field);
        } else if (!in_array($field->type, [
            'quantity',
            'attribute',
            'stock_status',
            'stock_quantity',
            'custom_field',
            'product_ids'
        ])) {
            return false;
        }
        if ($field) {
            $val = array();
            $is_visible = true;
            if (isset($field->enableCl) && $field->enableCl) {
                if (isset($field->cl_status) && $field->cl_status === 'visible') {
                    $is_visible = true;
                } else if (isset($field->cl_status) && $field->cl_status === 'hidden') {
                    $is_visible = false;
                } else if ($field->cl_rule === 'show') {
                    $is_visible = false;
                } else {
                    $is_visible = true;
                }
            }

            if ($is_visible) {
                if ($field->type === 'quantity') {
                    if (isset($_REQUEST['quantity'])) {
                        $val[] = (int)$_REQUEST['quantity'];
                    }
                } else if ($field->type === 'stock_status') {
                    $val[] = $product->get_stock_status('edit');
                } else if ($field->type === 'product_ids') {
                    $val[] = $product->get_id();
                } else if ($field->type === 'stock_quantity') {
                    $val[] = $product->get_stock_quantity('edit');
                } else if ($field->type === 'custom_field') {

                    $product_custom_fields = wcpa_get_option('product_custom_fields');
                    $product_cfs = array();
                    if (is_array($product_custom_fields)) {
                        foreach ($product_custom_fields as $cf) {
                            if (get_post_meta($product_id, $cf_prefix . $cf['name'], true)) {
                                $product_cfs['wcpa_pcf_' . $cf['name']] = get_post_meta($product_id, $cf_prefix . $cf['name'], true);
                            } else {
                                $product_cfs['wcpa_pcf_' . $cf['name']] = $cf['value'];
                            }
                        }
                    }
                    if (isset($product_cfs['wcpa_pcf_' . $rules->cl_relation])) {
                        $val[] = $product_cfs['wcpa_pcf_' . $rules->cl_relation];
                    }

                } else if ($field->type === 'attribute') {
                    $name = 'attribute_' . $rules->cl_relation;
                    if (isset($_REQUEST[$name])) {
                        $val[] = $_REQUEST[$name];
                    }

                    $product_attributes = $this->get_pro_attr_list($product);
                    if (isset($product_attributes[$rules->cl_relation])) {
                        $val = $product_attributes[$rules->cl_relation]['value'];
                    }
                    if (empty($val)) {
                        return false;
                    }
                } else if (in_array($field->type, ['text', 'color', 'hidden'])) {
                    if (isset($_REQUEST[$field->name])) {
                        $val[] = strtolower($_REQUEST[$field->name]);
                    }
                } else if (in_array($field->type, ['file'])) {
                    if (isset($field->ajax_upload) && $field->ajax_upload === true && (isset($_REQUEST[$field->name . '_ajax']) && !empty($_REQUEST[$field->name . '_ajax']) || isset($_REQUEST[$field->name . '_ajaxmultiple']) && !empty($_REQUEST[$field->name . '_ajaxmultiple']))) {
                        if(isset($_REQUEST[$field->name . '_ajax']) && !empty($_REQUEST[$field->name . '_ajax'])){
                            $temp = explode('||', $_REQUEST[$field->name . '_ajax']);
                            $val[] = strtolower($temp[1]);
                        } else if(isset($_REQUEST[$field->name . '_ajaxmultiple']) && !empty($_REQUEST[$field->name . '_ajaxmultiple'])){
                            $tempData = str_replace("\\", "",$_REQUEST[$field->name . '_ajaxmultiple']);
                            $files = json_decode($tempData);
                            if($files){
                                $file_count = 0;
                                foreach($files as $file) {
                                    if($file){
                                        $file_count++;
                                        $temp = explode('||', $file);
                                        $val[] = strtolower($temp[1]);
                                    }
                                }

                            }
                        }   
                    } else if ($_FILES[$field->name]["error"] != 4) {
                        $val[] = strtolower($_FILES[$field->name]["name"]);
                    }
                } else if (in_array($field->type, ['number'])) {
                    if (isset($_REQUEST[$field->name])) {
                        $val[] = (float)$_REQUEST[$field->name];
                    }
                } else if (in_array($field->type, ['textarea'])) {
                    if (isset($_REQUEST[$field->name])) {
                        $val[] = strtolower($_REQUEST[$field->name]);
                    }
                } else if (in_array($field->type, [
                        'checkbox-group',
                        'radio-group',
                        'select',
                        'image-group',
                        'color-group',
                        'productGroup'
                    ]) && isset($_REQUEST[$field->name])) {
                    if (is_array($_REQUEST[$field->name])) {
                        $_values = $_REQUEST[$field->name];

                        if (in_array($rules->cl_relation, [
                            'contains',
                            'not_contains',
                            'starts_with',
                            'ends_with'
                        ])) {
                            array_walk($_values, function (&$a, $b) {
                                strtolower(str_replace('WCPAOTH ', '', $a));
                            }); // using this array_walk method to preserve the keys
                            $val = $_values;
                        } else {
                            foreach ($_values as $l => $v) {
                                if (substr($v, 0, 8) === "WCPAOTH ") {
                                    $val[] = 'other';
                                } else {
                                    $val[] = strtolower($v);
                                }
                            }
                        }

                    } else {
                        if (in_array($rules->cl_relation, [
                            'contains',
                            'not_contains',
                            'starts_with',
                            'ends_with'
                        ])) {
                            $val[] = strtolower(str_replace('WCPAOTH ', '', $_REQUEST[$field->name]));
                        } else {
                            if (substr($_REQUEST[$field->name], 0, 8) === "WCPAOTH ") {
                                $val[] = 'other';
                            } else {
                                $val[] = strtolower($_REQUEST[$field->name]);
                            }
                        }
                    }

                } else if (in_array($field->type, ['date', 'datetime-local'])) {
                    if (isset($_REQUEST[$field->name])) {
                        if (in_array($rules->cl_relation, [
                            'contains',
                            'not_contains',
                            'starts_with',
                            'ends_with'
                        ])) {
                            $val[] = strtolower($_REQUEST[$field->name]);
                        } else {
                            $dateTemp = date_create_from_format(__(get_option('date_format'), 'wcpa-text-domain'), $_REQUEST[$field->name]);
                            if ($dateTemp !== false) {
                                if ($field->type == 'date') {
                                    $dateTemp->setTime(0, 0, 0);
                                }
                                $val[] = $dateTemp->getTimestamp();
                            }
//                            $val[] = strtotime($_REQUEST[$field->name]);

                        }
                    }
                } else if (in_array($field->type, ['time'])) {
                    if (isset($_REQUEST[$field->name])) {
                        if (in_array($rules->cl_relation, [
                            'contains',
                            'not_contains',
                            'starts_with',
                            'ends_with'
                        ])) {
                            $val[] = strtolower($_REQUEST[$field->name]);
                        } else {
                            $val[] = strtotime($_REQUEST[$field->name]);
                        }
                    }
                }
            }
            $rel_val = false;
            if ($field->type === 'attribute') {
                $rel_val = isset($rules->cl_val->value) ? $rules->cl_val->value : $rules->cl_val;
                if (in_array($rel_val, $val)) {
                    return true;
                } else {
                    return false;
                }
            }
            if ($field->type === 'custom_field') {
                $rel_val = isset($rules->cl_val->value) ? $rules->cl_val->value : $rules->cl_val;
                if (in_array($rel_val, $val)) {
                    return true;
                } else {
                    return false;
                }
            }

            if (count($val) === 0) {
                switch ($rules->cl_relation) {
                    case 'is_empty':
                        return true;
                    case 'is_not_empty':
                        return false;
                    default:
                        return false;
                }
            }


            if (isset($rules->cl_val) && ($field->type == 'quantity' || $field->type == 'stock_quantity')) {
                $rel_val = (float)(((isset($rules->cl_val->value)) ? $rules->cl_val->value : $rules->cl_val));
            } else if (isset($rules->cl_val) && $field->type == 'product_ids') {
                $rel_val = (((isset($rules->cl_val->value)) ? $rules->cl_val->value : $rules->cl_val));
                $rel_val = preg_split('/[\ \n\,]+/', $rel_val);
                $rel_val = array_map('intval', $rel_val);
            } else if (isset($rules->cl_val) && $field->type == 'image-group') {
                $rel_val = strtolower(((isset($rules->cl_val->i)) ? $rules->cl_val->i : $rules->cl_val));
            } else if ($field->type == 'datetime-local' || $field->type == 'date' || $field->type == 'time') {
                if (in_array($rules->cl_relation, ['contains', 'not_contains', 'starts_with', 'ends_with'])) {
                    $rel_val = strtolower($rules->cl_val);
                } else {
                    $rel_val = strtotime($rules->cl_val);
                }
            } else if (isset($rules->cl_val) && $field->type == 'number') {
                $rel_val = (float)(((isset($rules->cl_val->value)) ? $rules->cl_val->value : $rules->cl_val));
            } else if (isset($rules->cl_val)) {
                $rel_val = strtolower((((isset($rules->cl_val->value)) ? $rules->cl_val->value : $rules->cl_val)));
            }
            $val = array_map(function ($v) {
                return wp_unslash($v);
            }, $val);


            switch ($rules->cl_relation) {
                case 'is':

                    if (in_array($rel_val, $val)) {
                        return true;
                    } else {
                        return false;
                    }
                case 'is_not':
                    if (in_array($rel_val, $val)) {
                        return false;
                    } else {
                        return true;
                    }
                case 'is_in':
                    if (is_array($rel_val)) {
                        foreach ($rel_val as $rel_v) {
                            if (in_array($rel_v, $val)) {
                                return true;
                            }
                        }
                    }

                    return false;

                case 'is_not_in':
                    if (is_array($rel_val)) {
                        foreach ($rel_val as $rel_v) {
                            if (in_array($rel_v, $val)) {
                                return false;
                            }
                        }
                    } else {
                        return false;
                    }

                    return true;

                case 'is_empty':
                    if (count($val) === 0 || reset($val) === '') {
                        return true;
                    } else {
                        return false;
                    }
                case 'is_not_empty':
                    if (count($val) === 0 || reset($val) === '') {
                        return false;
                    } else {
                        return true;
                    }
                case 'is_greater':
                    foreach ($val as $v) {
                        if ($v > $rel_val) {
                            return true;
                        }
                    }

                    return false;
                case 'is_lessthan':
                    foreach ($val as $v) {
                        if ($v >= $rel_val) {
                            return false;
                        }
                    }

                    return true;
                case 'is_greater_or_eqaul':
                    foreach ($val as $v) {
                        if ($v >= $rel_val) {
                            return true;
                        }
                    }

                    return false;
                case 'is_lessthan_or_eqal':
                    foreach ($val as $v) {
                        if ($v > $rel_val) {
                            return false;
                        }
                    }

                    return true;
                case 'contains':
                    foreach ($val as $v) {
                        if (strpos($v, $rel_val) !== false) {
                            return true;
                        }
                    }

                    return false;

                case 'not_contains':
                    foreach ($val as $v) {
                        if (strpos($v, $rel_val) !== false) {
                            return false;
                        }
                    }

                    return true;
                case 'starts_with':
                    foreach ($val as $v) {
                        if (strpos($v, $rel_val) === 0) {
                            return true;
                        }
                    }

                    return false;
                case 'ends_with':
                    foreach ($val as $v) {
                        $strlen = strlen($v);
                        $testlen = strlen($rel_val);
                        if ($testlen <= $strlen) {
                            if (substr_compare($v, $rel_val, $strlen - $testlen, $testlen) === 0) {
                                return true;
                            }
                        }
                    }

                    return false;
                case 'date_is':
                case 'time_is':
                    if (in_array($rel_val, $val)) {
                        return true;
                    } else {
                        return false;
                    }

                case 'date_is_before':
                case 'time_is_before':
                    foreach ($val as $v) {
                        if ($v >= $rel_val) {
                            return false;
                        }
                    }

                    return true;
                case 'date_is_after':
                case 'time_is_after':
                    foreach ($val as $v) {
                        if ($v > $rel_val) {
                            return true;
                        }
                    }

                    return false;
            }
        } else {
            return false;
        }

        return false;
    }

    public function get_pro_attr_list($pro = false)
    {
        if ($pro === false) {
            $pro = $this->product;
        }
        $attributes = $pro->get_attributes();

        $product_attributes = [];
        foreach ($attributes as $attribute) {
            $values = array();

            if ($attribute->is_taxonomy()) {
                if ($attribute->get_variation()) {
                    continue;// exclude normal variations
                }
                $attribute_values = wc_get_product_terms($pro->get_id(), $attribute->get_name(), array('fields' => 'all'));

                foreach ($attribute_values as $attribute_value) {
                    $value_slug = esc_html($attribute_value->slug);

                    $values[] = $value_slug;

                }
            } else {
                $values = $attribute->get_options();

            }

            $product_attributes[sanitize_title_with_dashes($attribute->get_name())] = array(
                'label' => $attribute->get_name(),
                'value' => $values,
            );
        }

        return $product_attributes;
    }

    public function order_again_item_data($item)
    {
        $product_id = $item->get_product_id();
        $this->get_forms_by_product($product_id);
        $this->set_product($product_id);
        $meta_data = $item->get_meta(WCPA_ORDER_META_KEY);

        $this->submited_data = array();
        foreach ($this->data as $k => $v) {

            $form_data = clone $v;
            unset($form_data->values); //avoid saving large number of data
            unset($form_data->className); //avoid saving no use data
            unset($form_data->relations); //avoid saving no use data

            if (!in_array($v->type, array('separator'))) {
                $meta = false;
                if (is_array($meta_data)) {
                    if (isset($v->name)) {
                        $meta = $this->find_meta_by_name($v->name, $meta_data);
                    }
                    if ($meta == false) {
                        $meta = $this->find_meta_by_name($v->elementId, $meta_data);
                    }
                }


                if ($meta && is_array($meta['value']) && in_array($v->type, [
                        'select',
                        'checkbox-group',
                        'radio-group',
                        'image-group',
                        'color-group',
                        'productGroup'
                    ])) {
                    //check the options are selectable
                    $value_data = array(); // $meta['value'];

                    foreach ($meta['value'] as $j => $_v) {
                        $flag = false;
                        if (is_array($_v)) {
                            foreach ($v->values as $newkey => $_v2) {
                                if ((isset($_v2->value) && ($_v2->value === $_v['value']
                                            || $_v2->value === wp_unslash($_v['value'])))
                                    || (isset($_v2->image) && $_v2->image === $_v['value'])) {
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                        if ($flag === false) {
                            if (isset($v->other) && $v->other && !isset($_v['value']['other'])) {
                                $value_data['other'] = $_v;
                                $value_data['other']['i'] = 'other';
                                $value_data['other']['label'] = 'Other';
                            }
                        } else {
                            $value_data[$newkey] = $_v;
                            $value_data[$newkey]['i'] = $newkey;
                        }
                    }
                    if ($v->type !== 'checkbox-group' && (!isset($v->multiple) || !$v->multiple)) {//no multi option
                        if (count($value_data) > 1 && isset($value_data['other'])) {
                            unset($value_data['other']);
                        }

                        $value_data = array_slice($value_data, 0, 1, true);
                    }
                } else {
                    $value_data = $meta ? $meta['value'] : false;
                }
                if (isset($meta['value'])) {

                    if (in_array($v->type, array('paragraph', 'header'))) {
                        $label = WCPA_EMPTY_LABEL;
                    } else {
                        $label = (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL;
                    }
                    $this->submited_data[] = array(
                        'type' => $v->type,
                        'name' => isset($v->name) ? $v->name : $v->elementId,
                        'label' => $label,
                        'value' => $value_data,

                        'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                        'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                        'price' => $this->element_price($v, $product_id, $value_data, $value_data),
                        'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                        'form_data' => $form_data
                    );
                }
            }
        }
        $this->price_depends_update($product_id);

        return $this->submited_data;
    }

    public function set_product($product)
    {
        if (is_object($product)) {
            $this->product = $product;
        } else {
            $this->product = wc_get_product($product);
        }
    }

    public function find_meta_by_name($name, $meta_data)
    {

        $arr = array_filter($meta_data, function ($v) use ($name) {
            return $v['name'] === $name;
        });

        if ($arr !== false) {
            return reset($arr);
        } else {
            return false;
        }
    }

    public function element_price($v, $product_id, $values_data = false, $value = false, $return_formula = false, $repeat = false)
    {
        if (!isset($v->enablePrice) || !$v->enablePrice) {
            return false;
        }
        if (!$this->product) {
            $this->set_product($product_id);
        }
        $product_price = $this->product->get_price('edit');
        if (!isset($v->price)) {
            $v->price = 0;
        }
        $count = 0;
        switch ($v->type) {
            case 'text':
            case 'date':
            case 'number':
            case 'color':
            case 'time':
            case 'datetime-local':
            case 'textarea':
                $value = (($value === false) ? trim($_REQUEST[$v->name]) : $value);
//                return $this->calculate_price($v->pricingType, $value, $v->price, $product_price, $return_formula, $repeat);
                $count = ($value == '') ? 0 : 1;

                return $this->calculate_price(
                    [
                        'pricingType' => $v->pricingType,
                        'value' => $value,
                        'excl_chars_frm_length' => isset($v->excl_chars_frm_length) ? $v->excl_chars_frm_length : "",
                        'excl_chars_frm_length_is_regex' => isset($v->excl_chars_frm_length_is_regex) ? $v->excl_chars_frm_length_is_regex : false,
                        'count' => $count,
                        'price' => $v->price,
                        'product_price' => $product_price
                    ],
                    $return_formula, $repeat);
            case 'paragraph':
            case 'header':
            case 'statictext':
                $value = 1;
//                return $this->calculate_price($v->pricingType, $value, $v->price, $product_price, $return_formula, $repeat);
                $count = ($value == '') ? 0 : 1;

                return $this->calculate_price(
                    [
                        'pricingType' => $v->pricingType,
                        'value' => $value,
                        'count' => $count,
                        'price' => $v->price,
                        'product_price' => $product_price
                    ],
                    $return_formula, $repeat);
            case 'file':
                $count = 1;
                if($values_data !== false ){
                    if(is_array($values_data)) {
                        $count = count($values_data);
                    } 
                }
                //  return $this->calculate_price($v->pricingType, true, $v->price, $product_price, $return_formula, $repeat);
                return $this->calculate_price([
                    'pricingType' => $v->pricingType,
                    'value' => true,
                    'count' => $count,
                    'price' => $v->price,
                    'product_price' => $product_price
                ], $return_formula, $repeat);
            case 'placeselector':
                $value = (($value === false) ? trim($_REQUEST[$v->name]) : $value);
                //   return array($this->calculate_price($v->pricingType, $value, $v->price, $product_price, $return_formula, $repeat));
                $count = ($value == '') ? 0 : 1;

                return array(
                    $this->calculate_price([
                        'pricingType' => $v->pricingType,
                        'value' => $value,
                        'count' => $count,
                        'price' => $v->price,
                        'product_price' => $product_price
                    ], $return_formula, $repeat)
                );

            case 'select':
                if ($values_data === false) {
                    $value = (($value === false) ? trim($_REQUEST[$v->name]) : $value);
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                } else {
                    $value = $values_data;
                }

                $elem_price = array();
                $price = 0;
                foreach ($value as $k => $val) {
                    if ($v->priceOptions === 'different_for_all') {
                        foreach ($v->values as $l => $e) {
                            if ($l === $k) {
                                $price = $e->price;
                                break;
                            }
                        }
//                            $price = $val->price;
                    } else if ($v->priceOptions === 'fixed_for_all') {
                        $price = $v->price;
                    }
                    $count = ($value == '') ? 0 : (is_array($value) ? count($value) : 1);
                    //$elem_price[$k] = $this->calculate_price($v->pricingType, (isset($val['value']) ? $val['value'] : $val), $price, $product_price, $return_formula, $repeat);
                    $elem_price[$k] = $this->calculate_price(
                        [
                            'pricingType' => $v->pricingType,
                            'value' => (isset($val['value']) ? $val['value'] : $val),
                            'count' => $count,
                            'price' => $price,
                            'product_price' => $product_price
                        ], $return_formula, $repeat);
                }

                return $elem_price;

            case 'radio-group':
                if ($values_data === false) {
                    $value = (($value === false) ? trim($_REQUEST[$v->name]) : $value);
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                } else {
                    $value = $values_data;
                }


                $price = 0;
                foreach ($value as $l => $val) {
                    if (isset($v->values[$l])) {
                        $price = $v->values[$l]->price;
                    }
                }
                if ($v->priceOptions == 'different_for_all') {
                    $price = $price;
                } else if ($v->priceOptions == 'fixed_for_all') {
                    $price = $v->price;
                }


//                return array($l => $this->calculate_price($v->pricingType, (isset($val['value']) ? $val['value'] : $val), $price, $product_price, $return_formula, $repeat));
                return array(
                    $l => $this->calculate_price(
                        [
                            'pricingType' => $v->pricingType,
                            'value' => (isset($val['value']) ? $val['value'] : $val),
                            'price' => $price,
                            'count' => 1,
                            'product_price' => $product_price
                        ], $return_formula, $repeat)
                );

            case 'checkbox-group':
                $elem_price = false;
                $value = (($value === false) ? $_REQUEST[$v->name] : $value);
                if (is_array($value)) {
                    $elem_price = array();
                    $count = count($value);
                    foreach ($v->values as $k => $val) {
                        if ($v->priceOptions == 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions == 'fixed_for_all') {
                            $price = $v->price;
                        }

                        if (isset($value[$k])) {
//                            $elem_price[$k] = $this->calculate_price($v->pricingType, $val->value, $price, $product_price, $return_formula, $repeat);
                            $elem_price[$k] = $this->calculate_price(
                                [
                                    'pricingType' => $v->pricingType,
                                    'value' => $val->value,
                                    'count' => $count,
                                    'price' => $price,
                                    'product_price' => $product_price
                                ], $return_formula, $repeat);
                        }
                    }
                    if ($v->priceOptions == 'fixed_for_all') { // other option has price if it is fixed for all
                        $price = $v->price;
                        if (isset($value['other'])) {
//                            $elem_price['other'] = $this->calculate_price($v->pricingType, $value['other'], $price, $product_price, $return_formula, $repeat);
                            $elem_price['other'] = $this->calculate_price(
                                [
                                    'pricingType' => $v->pricingType,
                                    'value' => $value['other'],
                                    'count' => $count,
                                    'price' => $price,
                                    'product_price' => $product_price
                                ], $return_formula, $repeat);
                        }
                    }
                }

                return $elem_price;
            case 'color-group':
                $elem_price = false;
                if ($values_data === false) {
                    $value = (($value === false) ? ($_REQUEST[$v->name]) : $value);
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                } else {
                    $value = $values_data;
                }


                $elem_price = array();

                if (is_array($value)) {
                    $count = count($value);
                    $elem_price = array();
                    foreach ($v->values as $k => $val) {
                        if ($v->priceOptions == 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions == 'fixed_for_all') {
                            $price = $v->price;
                        }

                        if (isset($value[$k])) {
//                            $elem_price[$k] = $this->calculate_price($v->pricingType, $val->value, $price, $product_price, $return_formula, $repeat);
                            $elem_price[$k] = $this->calculate_price(
                                [
                                    'pricingType' => $v->pricingType,
                                    'value' => $val->value,
                                    'count' => $count,
                                    'price' => $price,
                                    'product_price' => $product_price
                                ], $return_formula, $repeat);
                        }
                    }
                    if ($v->priceOptions == 'fixed_for_all') { // other option has price if it is fixed for all
                        $price = $v->price;
                        if (isset($value['other'])) {
//                            $elem_price['other'] = $this->calculate_price($v->pricingType, $value['other']['value'], $price, $product_price, $return_formula, $repeat);
                            $elem_price['other'] = $this->calculate_price(
                                [
                                    'pricingType' => $v->pricingType,
                                    'value' => $value['other']['value'],
                                    'count' => $count,
                                    'price' => $price,
                                    'product_price' => $product_price
                                ], $return_formula, $repeat);
                        }
                    }
                }


                return $elem_price;
            case 'image-group':
                $elem_price = false;

                if ($values_data === false) {
                    $value = (($value === false) ? (isset($_REQUEST[$v->name])) : $value);
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                } else {
                    $value = $values_data;
                }

                $elem_price = array();
                $price = 0;
                $count = count($value);
                foreach ($value as $k => $val) {
                    if ($v->priceOptions == 'different_for_all') {
                        foreach ($v->values as $l => $e) {
                            if ($l === $k) {
                                $price = $e->price;
                                break;
                            }
                        }
                    } else if ($v->priceOptions == 'fixed_for_all') {
                        $price = $v->price;
                    }

                    $elem_price[$k] = $this->calculate_price([
                        'pricingType' => $v->pricingType,
                        'value' => 1,
                        'count' => $count,
                        'price' => $price,
                        'product_price' => $product_price
                    ], $return_formula, $repeat);
                }

                return $elem_price;

            case 'productGroup':
                $elem_price = false;
                $productArray = array();

                if ($values_data === false) {
                    return $elem_price;
                } else {
                    $productArray = $values_data;
                }
                
                $value =  isset($productArray['values']) ? $productArray['values'] : array();
                $quantities = isset($productArray['quantities']) ? $productArray['quantities'] : array();

                $elem_price = array();
                $price = 0;
                if($value){
                    foreach ($value as $k => $val) {
                        if (isset($v->enablePrice) && $v->enablePrice){
                            $prod_price = $val->get_price('edit');
                            $pricingType = ($v->priceOptions == 'product_price') ?  'fixed' : $v->pricingType;
                            if ($v->priceOptions == 'different_for_all') {
                                foreach ($v->values as $l => $e) {
                                    if ($l === $k) {
                                        $price = $e->price;
                                        break;
                                    }
                                }
                            } else if ($v->priceOptions == 'fixed_for_all') {
                                $price = $v->price;
                            } else if ($v->priceOptions == 'product_price') {
                                $price = $prod_price;
                            }

                            $elem_price[$k] = $this->calculate_price([
                                'pricingType' => $pricingType,
                                'value' => 1,
                                'count' => 1,
                                'price' => $price,
                                'product_price' => $product_price,
                                'option_price' => $prod_price,
                                'quantity'=> $quantities[$k]
                            ], $return_formula, $repeat);
                        } else {
                            $elem_price[$k] = floatval($val->get_price('edit')); 
                        }
                    }
                }
                return $elem_price;
        }
    }

    public function calculate_price($args, $return_formula = false, $repeat = false)
    {
        $pricingType = $args['pricingType'];
        $value = $args['value'];
        $price = $args['price'];
        $option_price = (isset($args['option_price']) && $args['option_price']) 
                                ? $args['option_price'] 
                                : false;
        $product_price = (isset($args['option_price']) && $args['option_price']) 
                                ? $args['option_price'] 
                                : $args['product_price'];
        $quantity = isset($args['quantity']) ? $args['quantity'] : 1;

        if (isset($args['count'])) {
            $count = $args['count'];
        } else {
            if (!empty($value)) {
                $count = 1;
            } else {
                $count = 0;
            }

        }
        if ($pricingType !== 'custom') {
            if (!wcpa_price_is_numeric($price)) {
                return 0;
            } else {
                $price = wcpa_price_to_float($price);
            }
        }

        $elem_price = 0;


        switch ($pricingType) {
            case 'per_car':
                if ($value) {
                    $value_filtered = $value;
                    if (isset($args['excl_chars_frm_length']) && $args['excl_chars_frm_length'] !== '') {
                        $exclude_chars = $args['excl_chars_frm_length'];
                        if ($args['excl_chars_frm_length_is_regex']) {
                            if ($exclude_chars[0] != '/') {
                                $exclude_chars = '/' . $exclude_chars . '/i';
                            } else {
                                $exclude_chars = preg_replace('/\/g/', '/', $exclude_chars);
                            }
                            try {
                                $value_filtered = preg_replace($exclude_chars, '', $value_filtered);
                            } catch (Exception $e) {
                                $value_filtered = $value;
                            }


                        } else {
                            $exclude_chars = str_replace('\s', ' ', $exclude_chars);
                            $value_filtered = str_replace(str_split($exclude_chars), '', $value_filtered);
                        }
                    }
                    $elem_price = mb_strlen($value_filtered) * $price;
                }
                break;
            case 'fixed':
                if ($value || $value == '0' || $value === 0) {
                    $elem_price = $price * 1;
                } else {
                    $elem_price = 0;
                }

                break;
            case 'multiply':
                if ($value) {
                    $elem_price = (wcpa_price_is_numeric($value) ? wcpa_price_to_float($value) : 1) * $price; //added + sign to convert to int/float value
                } else {
                    $elem_price = 0;
                }
                if ($elem_price < 0) {
                    $elem_price = 0; // not allow to set -ve, use custom formula for setting negative
                }

                break;
            case 'custom':

                $elem_price = $this->process_custom_formula($value, $price, $count, $product_price, $return_formula, $repeat, false, $option_price);
                if ($return_formula) {
                    if(!in_array($elem_price, array('dependency', false), true)){
                        return '('.$elem_price.')*'.$quantity;
                    } else {
                        return $elem_price;
                    }
                }

                break;
            case 'percentage':
                if ($value) {
                    $elem_price = ($price * $product_price) / 100;
                } else {
                    $elem_price = 0;
                }
                break;
        }
        if(!in_array($elem_price, array('dependency', false), true)){
            $elem_price *= $quantity; 
        }
        return  apply_filters('wcml_raw_price_amount', $elem_price);
        
    }

    public function process_custom_formula($value, $price, $count, $product_price, $return_formula, $repeat = false, $isLabel = false, $option_price = false)
    {
        $cf_prefix = wcpa_get_option('wcpa_cf_prefix', 'wcpa_pcf_');
        if (!is_string($value) && isset($value['value'])) {
            $value = $value['value'];
        }
        $str_length = mb_strlen($value);

//        $unixTimestamp = strtotime($value);
        $date = date_create_from_format(__(get_option('date_format'), 'wcpa-text-domain'), $value);

        if ($date !== false) {
            $unixTimestamp = $date->getTimestamp();
            $days = floor($unixTimestamp / (60 * 60 * 24));
            $seconds = $unixTimestamp;
        } else {
            $days = 0;
            $seconds = 0;
        }
        $today = [
            'days' => floor(current_time('timestamp') / (60 * 60 * 24)),
            'seconds' => current_time('timestamp')
        ];
        if (is_numeric($value) || $isLabel) {
            $value_replace = $value;
        } else {
            $value_replace = '"' . $value . '"';
        }
        $formula = str_replace(
            [
                '{this.value}',
                '{value}',
                '{product_price}',
                '{this.value.length}',
                '{value.length}',
                '{this.count}',
                '{count}',
                '{option_price}',
                '{this.option_price}',
                '{days}',
                '{seconds}',
                '{timestamp}',
                '{today.days}',
                '{today.seconds}',
                'Math.'
            ],
            [
                $value_replace,
                $value_replace,
                $product_price,
                $str_length,
                $str_length,
                $count,
                $count,
                $option_price,
                $option_price,
                $days,
                $seconds,
                $seconds,
                $today['days'],
                $today['seconds'],
                ''
            ], $price);

        $formula = preg_replace(
            [
                '/JSON\.parse\((.*?)\)/'
            ],
            [
                'json_decode($1,true)'
            ], $formula);

        if (preg_match_all('/\{(\s)*?wcpa_pcf_([^}]*)}/', $formula, $matches)) {
            foreach ($matches[2] as $k => $match) {
                $pro_id = $this->product->get_parent_id();
                if ($pro_id == 0) {
                    $pro_id = $this->product->get_id();
                }
                $cf_value = get_post_meta($pro_id, $cf_prefix . trim($match), true);
                if ($cf_value == '' || $cf_value == false) {
                    $custom_fields = wcpa_get_option('product_custom_fields');
                    if (is_array($custom_fields)) {
                        foreach ($custom_fields as $cf) {
                            if ($cf['name'] == trim($match)) {
                                $cf_value = $cf['value'];
                                break;
                            }
                        }
                    }


                }
                if ($cf_value == '' || $cf_value == false) {
                    $cf_value = 0;
                }
                $formula = str_replace($matches[0][$k], $cf_value, $formula);
            }
        }
        if (preg_match_all('/\{(\s)*?field\.([^}]*)}/', $formula, $matches)) {


            foreach ($matches[2] as $k => $match) {


                $ele = explode('.', $match);
                if (is_array($ele) && count($ele) > 1 && in_array($ele[1], [
                        'value',
                        'price',
                        'count',
                        'option_price',
                        'selected',
                        'days',
                        'seconds',
                        'timestamp'
                    ])) {
                    $sub_data = $this->find_submited_data_by_id($ele[0]);

                    if ($sub_data === false) {
                        if ($repeat) {// if the itreation is repeating, and the elemet is not available, need to set as zero
                            $formula = str_replace($matches[0][$k], 0, $formula);
                        } else {

                            return 'dependency';
                        }
                    } else {
                        if ($ele[1] == 'price') {
                            if (isset($sub_data['form_data']->cl_status) && $sub_data['form_data']->cl_status === 'hidden') {
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (!isset($sub_data['price']) || $sub_data['price'] === false) { // no pricing enabled , set elem_price as 0
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (isset($sub_data['price']) && ($sub_data['price'] === 'dependency') || (is_array($sub_data['price']) && in_array('dependency', $sub_data['price'], true))) {
                                return 'dependency';
                            } else if (isset($sub_data['price']) && $sub_data['price'] !== false) {
                                if (is_array($sub_data['price'])) {
                                    $p = array_sum($sub_data['price']);
                                } else {
                                    $p = $sub_data['price'];
                                }

                                $formula = str_replace($matches[0][$k], $p, $formula);
                            }
                        } else if ($ele[1] == 'value') {
                            if (isset($sub_data['form_data']->cl_status) && $sub_data['form_data']->cl_status === 'hidden') {
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (!isset($sub_data['value']) || $sub_data['value'] === false || $sub_data['value'] === null || $sub_data['value'] === '') { // no pricing enabled , set elem_price as 0
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (isset($sub_data['value']) && ($sub_data['value'] === 'dependency') || (is_array($sub_data['value']) && in_array('dependency', $sub_data['value'], true))) {
                                return 'dependency';
                            } else if (isset($sub_data['value']) && $sub_data['value'] !== false) {
                                if (is_array($sub_data['value'])) {
                                    if (count($sub_data['value']) == 1) {
                                        $p_temp = array_values($sub_data['value'])[0];
                                        $p_temp = is_array($p_temp) ? $p_temp['value'] : $p_temp;
                                        if (is_numeric($p_temp)) {
                                            $p = (float)$p_temp;
                                        } else {
                                            $p = $p_temp;
                                        }
                                    } else {
                                        $p_sum = 0.0;
                                        $_i = -1;
                                        foreach ($sub_data['value'] as $_p) {
                                            $_i++;

                                            if (is_array($_p)) {
                                                if (is_numeric($_p['value'])) {
                                                    $p_sum += (float)$_p['value'];
                                                } else if ($_i == 0) {
                                                    $p_sum = $_p['value'];
                                                    break;
                                                }
                                            } else {
                                                if (is_numeric($_p)) {
                                                    $p_sum += (float)$_p;
                                                } else if ($_i == 0) {
                                                    $p_sum = $_p;
                                                    break;
                                                }
                                            }
                                        }
                                        $p = $p_sum;
                                    }
                                } else {
                                    $p = $sub_data['value'];
                                }
                                if (isset($ele[2])) {
                                    if ($ele[2] == 'length') {
                                        $p = mb_strlen($p);
                                    }
                                }
                                if (is_numeric($p) || $isLabel) {
                                    $formula = str_replace($matches[0][$k], $p, $formula);
                                } else {

                                    $formula = str_replace($matches[0][$k], "'" . $p . "'", $formula);
                                }
                            }
                        } else if ($ele[1] == 'count') {
                            if (is_array($sub_data['value'])) {
                                if($sub_data['type']=='productGroup') {
                                    if(!empty($sub_data['quantities']) && isset($sub_data['quantities'])){
                                        $_count = array_sum($sub_data['quantities']);
                                    } else {
                                        $_count = count($sub_data['value']);
                                    }
                                } else {
                                    $_count = count($sub_data['value']);
                                }
                            } else {
                                $_count = empty($sub_data['value']) ? 0 : 1;
                            }
                            $formula = str_replace($matches[0][$k], $_count, $formula);
                        } else if ($ele[1] == 'selected') {
                            if (is_array($sub_data['value'])) {
                                $_count = count($sub_data['value']);
                            } else {
                                $_count = empty($sub_data['value']) ? 0 : 1;
                            }
                            $formula = str_replace($matches[0][$k], $_count, $formula);
                        } else if ($ele[1] == 'days' || $ele[1] == 'seconds' || $ele[1] == 'timestamp') {

                            if (isset($sub_data['form_data']->cl_status) && $sub_data['form_data']->cl_status === 'hidden') {
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (!isset($sub_data['value']) || $sub_data['value'] === false || $sub_data['value'] === null || $sub_data['value'] === '') { // no pricing enabled , set elem_price as 0
                                $formula = str_replace($matches[0][$k], 0, $formula);
                            } else if (isset($sub_data['value']) && ($sub_data['value'] === 'dependency') || (is_array($sub_data['value']) && in_array('dependency', $sub_data['value'], true))) {
                                return 'dependency';
                            } else if (isset($sub_data['value']) && $sub_data['value'] !== false) {
                                if (is_array($sub_data['value'])) {
                                    $formula = str_replace($matches[0][$k], 0, $formula);
                                } else {

//                                    $unixTimestamp = strtotime($sub_data['value']);
                                    $date = date_create_from_format(__(get_option('date_format'), 'wcpa-text-domain'), $sub_data['value']);

                                    if ($date !== false) {
                                        $unixTimestamp = $date->getTimestamp();
                                        $days = floor($unixTimestamp / (60 * 60 * 24));
                                        $seconds = $unixTimestamp;
                                    } else {
                                        $days = 0;
                                        $seconds = 0;
                                    }
                                    $p = ($ele[1] == 'days') ? $days : $seconds;
                                    $formula = str_replace($matches[0][$k], $p, $formula);

                                }

                            }

                        }

                        // break; commented out this break, not sure why it required, it causing error if there are multiple field reference in forumula
                    }
                } else {
                    $formula = str_replace($matches[0][$k], 0, $formula); //replace with zero if the matching string has issues to avoid eval error
                }
            }
        }


        try {
            $_formula = str_replace(['{quantity}'], [1], $formula);
            if ($isLabel) {
                return $formula;
            }

            $elem_price = eval('return ' . $_formula . ';');
            if ($return_formula) { // this formula returns for calculate price when quantity updated
                return $formula;//str_replace(['{this.value}', '{value}', '{product_price}', '{this.value.length}', '{value.length}', '{this.count}'], [$value, $value, $product_price, $str_length, $str_length, $count], $price);

            }
        } catch (Throwable $t) {
            if ($return_formula) {
                return false;
            }
            $elem_price = 0;
        }
        if ($elem_price == null || $elem_price == false) {
            $elem_price = 0;
        }

        return $elem_price;
    }

    private function find_submited_data_by_id($element_id)
    {
        if (count($this->submited_data)) {
            foreach ($this->submited_data as $data) {
                if ($element_id == $data['form_data']->elementId) {
                    return $data;
                }
            }
        }

        return false;
    }

    public function is_quantity_dependent($v, $product_id, $values_data = false, $value = false, $repeat = false)
    {
        if (!isset($v->enablePrice) || !$v->enablePrice) {
            return false;
        }

        if ($v->pricingType === 'custom') {
            $formula = $this->element_price($v, $product_id, $values_data, $value, true, $repeat);

            if (is_array($formula)) {
                foreach ($formula as $f) {
                    if (strpos($f, '{quantity}') !== false) {
                        return $formula;
                    }
                }
            } else if (strpos($formula, '{quantity}') !== false) {
                return $formula;
            }
        }

        return false;
    }

    /*
      Sanitize and return user input data based on the type of field
     */

    public function price_depends_update($product_id)
    {
        $counter = false;

        foreach ($this->submited_data as $k => $data) {

            if ($data['price'] === 'dependency' || (is_array($data['price']) && in_array('dependency', $data['price'], true))) {

                if (isset($data['form_data']->elementId)) {
                    $v = $this->find_element_by_id($data['form_data']->elementId);

                    $this->submited_data[$k]['price'] = $this->element_price($v, $product_id, $data['value'], $data['value'], false, true);
                    $this->submited_data[$k]['quantity_depend'] = $this->is_quantity_dependent($v, $product_id, $data['value']);

                    if ($this->submited_data[$k]['price'] === 'dependency' || (is_array($this->submited_data[$k]['price']) && in_array('dependency', $this->submited_data[$k]['price'], true))) {
                        $counter = true;
                    }
                }
            }
        }

        foreach ($this->submited_data as $k => $data) {
            if (isset($data['form_data']->elementId)) {
                $v = $this->find_element_by_id($data['form_data']->elementId);

                $this->submited_data[$k]['quantity_depend'] = $this->is_quantity_dependent($v, $product_id, $data['value'], false, true);
            }
        }

        if ($counter) {
            $this->price_depends_update($product_id);
        }
    }

    /**
     * Get currency switcher infomrations, Like cunversin unit, this can be used
     *  to update price for order meta details, if the wcpa field price shows wrongy converted
     * As of now it using with WPML currency switcher
     * @return
     */
    public function getCurrSwitch()
    {
        $wpmlCurrency = apply_filters('wcml_raw_price_amount', 1);

        return $wpmlCurrency;
    }

    public function submited_data($product_id, $variation_id = 0)
    {
        global $woocommerce;
        $this->submited_data = array();
        $base_variation = false;
        $condition_check_id = $product_id;

        /**Avoid Resubmission of data */
        /** Trying to edit ? - Please test productGroup add to cart in ajax and non ajax using variation and non variation*/
        if(isset($_REQUEST['variation_id']) && $_REQUEST['variation_id'] != 0 ) {
            if(intval($_REQUEST['variation_id']) != $variation_id) {
                return $this->submited_data;
            } else {
                $condition_check_id = $variation_id;
            }
            // Check Variation Id == Product ID
            if(isset($_REQUEST['product_id']) && intval($_REQUEST['product_id']) == $_REQUEST['variation_id']) {
                $base_variation = true;
            }
        }
        
        $condition_check_id = $base_variation ? $condition_check_id : $product_id;

        if(isset($_REQUEST['add-to-cart'])) {
            if(intval($_REQUEST['add-to-cart'])!=$condition_check_id) {
                return $this->submited_data;
            } 
        } else if(isset($_REQUEST['product_id'])) {
            if(intval($_REQUEST['product_id'])!=$condition_check_id) {
                return $this->submited_data;
            }
        }
        /** End of Avoid Resubmission of data */
        
           

        $this->get_forms_by_product($product_id);

        $this->process_cl_logic($product_id);

        if($variation_id && $variation_id !==0) {
            $product_id = $variation_id;
        }

        $this->set_product($product_id);
        $thumb_image = false;
        if (count($this->data)) {

            if (!isset($_POST['wcpa_field_key_checker']) && is_array($_POST) && isset($_POST['add-to-cart'])) {

                $item = $this->findKey($_POST, 'wcpa_field_key_checker');

                if (is_array($item)) {
                    $_POST = array_merge($item, $_POST);
                    $_REQUEST = array_merge($item, $_REQUEST);
                }
            }
        }

        $hide_empty = wcpa_get_option('hide_empty_data', false);
        $zero_as_empty = false;
        if ($hide_empty) {
            $zero_as_empty = apply_filters('wcpa_zero_as_empty', false);
        }

        $allowed = array(
            'a' => array(// on allow a tags
                'href' => true, // and those anchors can only have href attribute
                'target' => true,
                'class' => true,// and those anchors can only have href attribute
                'style' => true
            ),
            'b' => array('style' => true, 'class' => true),
            'strong' => array('style' => true, 'class' => true),
            'i' => array('style' => true, 'class' => true),
            'img' => array('style' => true, 'class' => true, 'src' => true),
            'span' => array('style' => true, 'class' => true),
            'p' => array('style' => true, 'class' => true)
        );
        foreach ($this->data as $k => $v) {

            $form_data = clone $v;
            unset($form_data->values); //avoid saving large number of data
            unset($form_data->className); //avoid saving no use data
            unset($form_data->relations); //avoid saving no use data


            if (!in_array($v->type, array('separator')) && (!isset($v->cl_status) || $v->cl_status == 'visible')) {

                if ($v->type == 'file') {
                    if (isset($v->ajax_upload) && $v->ajax_upload === true && (isset($_REQUEST[$v->name . '_ajax']) || isset($_REQUEST[$v->name . '_ajaxmultiple'])) && (!empty($_REQUEST[$v->name . '_ajax']) || !empty($_REQUEST[$v->name . '_ajaxmultiple']))) {

                        if(isset($_REQUEST[$v->name . '_ajaxmultiple']) && !empty($_REQUEST[$v->name . '_ajaxmultiple'])) {
                            $tempData = str_replace("\\", "",$_REQUEST[$v->name . '_ajaxmultiple']);
                            $files = json_decode($tempData);
                            if($files){
                                $values = array();
                                $file_count = 0;
                                foreach($files as $file) {
                                    if($file){
                                        $file_count++;
                                        $temp = explode('||', $file);
                                        if(isset($v->max_file_count) && !empty($v->max_file_count)){
                                            if(intval($v->max_file_count) >= $file_count){
                                                $uplaoded_file = $this->move_file($v, $temp);
                                                $values[] = $uplaoded_file;
                                            }
                                        } else {
                                            $uplaoded_file = $this->move_file($v, $temp);
                                            $values[] = $uplaoded_file;
                                        }
                                    }
                                }
                                $this->submited_data[] = array(
                                    'type' => $v->type,
                                    'multiple' => true,
                                    'name' => $v->name,
                                    'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                                    'value' => $values,
                                    'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                                    'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                                    'price' => (!empty($values)) ? $this->element_price($v, $product_id, $values) : false,
                                    'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                                    'cur_swit' => $this->getCurrSwitch(),
                                    'form_data' => $form_data
                                );
                            }
                        } else {
                            if(isset($_REQUEST[$v->name . '_ajax'])){
                                $temp = explode('||', $_REQUEST[$v->name . '_ajax']);

                                $uplaoded_file = $this->move_file($v, $temp);
                                $this->submited_data[] = array(
                                    'type' => $v->type,
                                    'name' => $v->name,
                                    'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                                    'value' => $uplaoded_file,
                                    'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                                    'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                                    'price' => (isset($uplaoded_file['url']) && !empty($uplaoded_file['url'])) ? $this->element_price($v, $product_id) : false,
                                    'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                                    'cur_swit' => $this->getCurrSwitch(),
                                    'form_data' => $form_data
                                );
                            }
                        }
                    } else {
                        if(isset($_FILES[$v->name]['name']) && !empty($_FILES[$v->name]['name']) && is_array($_FILES[$v->name]['name'])){
                            $countOfFiles = count($_FILES[$v->name]['name']);
                            if($countOfFiles) {
                                $pass_files_error_check = false;
                                for($i=0;$i<$countOfFiles;$i++){
                                    if (isset($_FILES[$v->name]["error"][$i]) && $_FILES[$v->name]["error"][$i] != 4) {
                                        $pass_files_error_check = true;
                                    }
                                }

                                if($pass_files_error_check){
                                    $uplaoded_files = $this->handle_upload($v, true);
                                    $values = array();
                                    if($uplaoded_files){
                                        $file_count = 0;
                                        foreach($uplaoded_files as $uplaoded_file){
                                            if($uplaoded_file){
                                                $file_count++;
                                                if(isset($v->max_file_count) && !empty($v->max_file_count)){
                                                    if(intval($v->max_file_count) >= $file_count){
                                                        $values[] = $uplaoded_file;
                                                    }
                                                } else {
                                                    $values[] = $uplaoded_file;
                                                }
                                            }
                                        }
                                    }
                                    if(!empty($values)){
                                        $this->submited_data[] = array(
                                            'type' => $v->type,
                                            'name' => $v->name,
                                            'multiple' => true,
                                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                                            'value' => $values,
                                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                                            'price' => (!empty($values)) ? $this->element_price($v, $product_id, $values) : false,
                                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                                            'cur_swit' => $this->getCurrSwitch(),
                                            'form_data' => $form_data
                                        );
                                    }
                                }
                            }
                        } else {
                            if (isset($_FILES[$v->name]["error"]) && $_FILES[$v->name]["error"] != 4) {
                                $uplaoded_file = $this->handle_upload($v);
                                $this->submited_data[] = array(
                                    'type' => $v->type,
                                    'name' => $v->name,
                                    'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                                    'value' => $uplaoded_file,
                                    'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                                    'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                                    'price' => (isset($uplaoded_file['url']) && !empty($uplaoded_file['url'])) ? $this->element_price($v, $product_id) : false,
                                    'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                                    'cur_swit' => $this->getCurrSwitch(),
                                    'form_data' => $form_data
                                );
                            }
                        }
                    }
                } else if (in_array($v->type, array('paragraph', 'header'))) {
                    if (isset($v->show_in_checkout) && $v->show_in_checkout == true) {
                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->elementId,
                            'label' => WCPA_EMPTY_LABEL,
                            'value' => (isset($v->label)) ? $v->label : '',
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else {
                        continue;
                    }
                } else if (in_array($v->type, array('statictext'))) {
                    if (isset($v->show_in_checkout) && $v->show_in_checkout == true) {
                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->elementId,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => (isset($v->value)) ? $v->value : '',
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else {
                        continue;
                    }
                } else if (isset($_REQUEST[$v->name]) && !($hide_empty && $_REQUEST[$v->name] === '') && !($hide_empty && $zero_as_empty && ($_REQUEST[$v->name] === 0 || $_REQUEST[$v->name] === '0'))
                    ) {

                    if (in_array($v->type, ['select', 'radio-group'])) {
                        $values = $_REQUEST[$v->name]; // $this->sanitize_values($v);
                        $values_data = array();
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        foreach ($values as $l => $val) {
                            $item = false;
                            foreach ($v->values as $j => $_v) {
                                if ($_v->value === $val || addslashes($_v->value) === $val) {
                                    $item = $_v;
                                    break;
                                }
                            }
                            if ($item === false) {
                                $item = new stdClass;
                                $val = str_replace('WCPAOTH ', '', $val);
                                if (isset($v->other_text) && !empty($v->other_text)) {
                                    $item->label = $v->other_text;
                                } else {
                                    $item->label = 'Other'; // no i18n applied here, will show at displying end
                                }

                                $j = 'other';
                            }

                            if (isset($v->enable_product_image) && $v->enable_product_image) {
                                if (isset($item->pimage_id) && $item->pimage_id > 0) {
                                    $thumb_image = $item->pimage_id;
                                }
                            }
                            $values_data[$j] = array(
                                'i' => $j,
                                'value' => $this->sanitize_values($val),
                                'label' => isset($item->label) ? $this->sanitize_values($item->label) : false
                            );
                        }

                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $values_data,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id, $values_data),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id, $values_data),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else if (in_array($v->type, ['checkbox-group'])) {
                        $values = $this->sanitize_values($v);
                        $values_data = array();
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        if ($hide_empty && empty($values)) {
                            continue;
                        }
                        foreach ($values as $l => $val) {

                            $item = isset($v->values[$l]) ? $v->values[$l] : false;
                            if ($item === false) {
                                $item = new stdClass;
                                $val = str_replace('WCPAOTH ', '', $val);

                                if (isset($v->other_text) && !empty($v->other_text)) {
                                    $item->label = $v->other_text;
                                } else {
                                    $item->label = 'Other'; // no i18n applied here, will show at displaying end
                                }
                            }


                            if (isset($v->enable_product_image) && $v->enable_product_image) {
                                if (isset($item->pimage_id) && $item->pimage_id > 0) {
                                    $thumb_image = $item->pimage_id;
                                }
                            }

                            $values_data[$l] = array('i' => $l, 'value' => $val, 'label' => $item->label);
                        }

                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $values_data,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id, $values_data),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else if (in_array($v->type, ['image-group'])) {
                        $values = $this->sanitize_values($v);

                        $values_data = array();
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        if ($hide_empty && empty($values)) {
                            continue;
                        }
                        if ((isset($v->multiple) && $v->multiple)) {
                            $is_multiple = true;
                        } else {
                            $is_multiple = false;
                        }
                        if ($is_multiple) {
                            foreach ($values as $l => $val) {
                                $item = isset($v->values[$l]) ? $v->values[$l] : false;
                                if ($item === false) {
                                    $item = new stdClass;

                                    if (isset($v->other_text) && !empty($v->other_text)) {
                                        $item->label = $v->other_text;
                                    } else {
                                        $item->label = 'Other'; // no i18n applied here, will show at displaying end
                                    }

                                    $val = str_replace('WCPAOTH ', '', $val);
                                }

                                if (isset($v->show_as_product_image) && $v->show_as_product_image) {
                                    if (isset($item->image_id) && $item->image_id > 0) {
                                        $thumb_image = $item->image_id;
                                    }
                                }


                                $values_data[$l] = array(
                                    'i' => $l,
                                    'value' => $val,
                                    'image_id' => (isset($item->image_id)) ? $item->image_id : false,
                                    'label' => isset($item->label) ? $item->label : false,
                                    'image' => isset($item->image) ? $item->image : false
                                );
                            }
                        } else {
                            foreach ($values as $l => $val) {
                                $item = false;
                                foreach ($v->values as $j => $_v) {
                                    if ((string)$j === $val) {//
                                        $item = $_v;
                                        break;
                                    }
                                }
                                if ($item === false) {
                                    $item = new stdClass;
                                    $val = str_replace('WCPAOTH ', '', $val);
                                    if (isset($v->other_text) && !empty($v->other_text)) {
                                        $item->label = $v->other_text;
                                    } else {
                                        $item->label = 'Other'; // no i18n applied here, will show at displaying end
                                    }

                                    $j = 'other';
                                }
                                if (isset($v->show_as_product_image) && $v->show_as_product_image) {
                                    if (isset($item->image_id) && $item->image_id > 0) {
                                        $thumb_image = $item->image_id;
                                    }
                                }
                                $values_data[$j] = array(
                                    'i' => $j,
                                    'value' => $val,
                                    'image_id' => (isset($item->image_id)) ? $item->image_id : false,
                                    'label' => isset($item->label) ? $item->label : false,
                                    'image' => isset($item->image) ? $item->image : false
                                );
                            }
                        }

                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $values_data,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id, $values_data),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id, $values_data),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else if(in_array($v->type, ['productGroup'])){
                        $values = $this->sanitize_values($v);
                        $quantities = $_REQUEST[$v->name.'_quantity'];

                        if ($hide_empty && empty($values) && empty($quantities)) {
                            continue;
                        }
                        if(!empty($values)){
                            if(count($values)>0) {
                                $this->submited_data[] = array(
                                    'type' => $v->type,
                                    'name' => $v->name,
                                    'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                                    'value' => $values,
                                    'quantities' => $quantities,
                                    'independent' => (isset($v->independent) && $v->independent) ? true : false,
                                    'independ_quantity' => (isset($v->independentQuantity) && $v->independentQuantity) ? true : false,
                                    'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                                    'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                                    'price' => $this->element_price($v, $product_id, array('values'=>$values,'quantities'=>$quantities)),
                                    'quantity_depend' => $this->is_quantity_dependent($v, $product_id, $values),
                                    'cur_swit' => $this->getCurrSwitch(),
                                    'form_data' => $form_data
                                );
                            }
                        }
                       
                    } else if (in_array($v->type, ['placeselector'])) {
                        $value = array(
                            'formated' => $this->sanitize_values($v),
                            'splited' => array(),
                            'cords' => array()
                        );
                        $splited = [
                            'street_number',
                            'route',
                            'locality',
                            'administrative_area_level_1',
                            'postal_code',
                            'country'
                        ];
                        foreach ($splited as $fl_name) {
                            if (isset($_REQUEST[$v->name . '_' . $fl_name])) {
                                $value['splited'][$fl_name] = $this->sanitize_values($_REQUEST[$v->name . '_' . $fl_name]);
                            }
                        }
                        if (isset($_REQUEST[$v->name . '_lat'])) {
                            $value['cords']['lat'] = $this->sanitize_values($_REQUEST[$v->name . '_lat']);
                        }
                        if (isset($_REQUEST[$v->name . '_lng'])) {
                            $value['cords']['lng'] = $this->sanitize_values($_REQUEST[$v->name . '_lng']);
                        }
                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $value,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else if (in_array($v->type, ['color-group'])) {
                        $values = $this->sanitize_values($v);

                        $values_data = array();
                        if (!is_array($values)) {
                            $values = array($values);
                        }
                        if ((isset($v->multiple) && $v->multiple)) {
                            $is_multiple = true;
                        } else {
                            $is_multiple = false;
                        }
                        if ($is_multiple) {
                            foreach ($values as $l => $val) {
                                $item = isset($v->values[$l]) ? $v->values[$l] : false;
                                if ($item === false) {
                                    $item = new stdClass;
                                    $item->label = 'Other';

                                    if (isset($v->other_text) && !empty($v->other_text)) {
                                        $item->label = $v->other_text;
                                    } else {
                                        $item->label = 'Other'; // no i18n applied here, will show at displaying end
                                    }

                                    $val = str_replace('WCPAOTH ', '', $val);
                                }

                                if (isset($v->enable_product_image) && $v->enable_product_image) {
                                    if (isset($item->pimage_id) && $item->pimage_id > 0) {
                                        $thumb_image = $item->pimage_id;
                                    }
                                }
                                $values_data[$l] = array(
                                    'i' => $l,
                                    'value' => $val,
                                    'label' => isset($item->label) ? $item->label : false,
                                    'color' => isset($item->color) ? $item->color : false
                                );
                            }
                        } else {
                            foreach ($values as $l => $val) {
                                $item = false;
                                foreach ($v->values as $j => $_v) {
                                    if ((string)$_v->value === $val) {//
                                        $item = $_v;
                                        break;
                                    }
                                }
                                if ($item === false) {
                                    $item = new stdClass;
                                    $val = str_replace('WCPAOTH ', '', $val);


                                    if (isset($v->other_text) && !empty($v->other_text)) {
                                        $item->label = $v->other_text;
                                    } else {
                                        $item->label = 'Other'; // no i18n applied here, will show at displaying end
                                    }


                                    $j = 'other';
                                }
                                if (isset($v->enable_product_image) && $v->enable_product_image) {
                                    if (isset($item->pimage_id) && $item->pimage_id > 0) {
                                        $thumb_image = $item->pimage_id;
                                    }
                                }
                                $values_data[$j] = array(
                                    'i' => $j,
                                    'value' => $val,
                                    'label' => isset($item->label) ? $item->label : false,
                                    'color' => isset($item->color) ? $item->color : false
                                );
                            }
                        }

                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $values_data,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id, $values_data),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id, $values_data),
                            'cur_swit' => $this->getCurrSwitch(),
                            'form_data' => $form_data
                        );
                    } else {
                        $field_value = $this->sanitize_values($v);
                        $this->submited_data[] = array(
                            'type' => $v->type,
                            'name' => $v->name,
                            'label' => (isset($v->label)) ? (($v->label == '') ? WCPA_EMPTY_LABEL : $v->label) : WCPA_EMPTY_LABEL,
                            'value' => $field_value,
                            'is_fee' => (isset($v->use_as_fee) && $v->use_as_fee) ? true : false,
                            'is_show_price' => (isset($v->is_show_price) && $v->is_show_price) ? true : false,
                            'price' => $this->element_price($v, $product_id),
                            'quantity_depend' => $this->is_quantity_dependent($v, $product_id),
                            'cur_swit' => $this->getCurrSwitch(),
                            'map_to_checkout' => (isset($v->mapToCheckout) && $v->mapToCheckout) ? true : false,
                            'map_checkout_details' => array(
                                                        'parent' => isset($v->mapToCheckoutFieldParent) ? $v->mapToCheckoutFieldParent : '',
                                                        'field' => isset($v->mapToCheckoutField) ? $v->mapToCheckoutField : '',
                                                        'value' => $field_value
                                                    ),
                            'form_data' => $form_data
                        );
                    }
                }
            }
        }

        $this->price_depends_update($product_id);
        $this->labelFormulaUpdate($product_id);


        $this->settings['thumb_image'] = $thumb_image;


        return $this->submited_data;
    }

    public function findKey($array, $keySearch)
    {

        foreach ($array as $key => $item) {

            if (!empty($item) && is_string($item)) {
                parse_str($item, $item);
            }

            if ($key == $keySearch) {

                return $array;
            } elseif (is_array($item) && ($data = $this->findKey($item, $keySearch)) !== false) {

                return $data;
            }
        }

        return false;
    }

    public function move_file($v, $file)
    {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }


        $uploadedfile_name = $file[1];

        $customer = new WCPA_Customer();
        $file_directory = $customer->upload_directory_base();
        if (WCPA_UPLOAD_CUSTOM_BASE_DIR == false) {
            $upload = wp_upload_dir();
        } else {
            $upload = WCPA_UPLOAD_CUSTOM_BASE_DIR;
            $upload['path'] = ABSPATH . '/' . WCPA_UPLOAD_CUSTOM_BASE_DIR;
            $upload['url'] = get_option('siteurl') . '/' . WCPA_UPLOAD_CUSTOM_BASE_DIR;
        }

        $uploadedfile = $upload['basedir'] . '/' . WCPA_UPLOAD_DIR . '/' . $file[0];

        $upload['subdir'] = '/' . $file_directory;
        $upload['path'] = $upload['basedir'] . '/' . $file_directory;
        $upload['url'] = $upload['baseurl'] . '/' . $file_directory;


        $wp_filetype = wp_check_filetype_and_ext($uploadedfile, $uploadedfile_name);
        
        $ext = empty($wp_filetype['ext']) ? '' : $wp_filetype['ext'];
        $type = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];
        $proper_filename = empty($wp_filetype['proper_filename']) ? '' : $wp_filetype['proper_filename'];
        // Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
        if ($proper_filename) {
            $uploadedfile_name = $proper_filename;
        }

        if ((!$type || !$ext)) {
            $this->add_cart_error(sprintf(__('File %s could not be uploaded.', 'wcpa-text-domain'), $v->label));

            return false;
        }

        $filename = wp_unique_filename($upload['path'], $uploadedfile_name);
        $new_file = $upload['path'] . "/$filename";
        if (!is_dir($upload['path'])) {
            wp_mkdir_p($upload['path']);
        }

        $move_new_file = @copy($uploadedfile, $new_file);

        if (false === $move_new_file) {
            $this->add_cart_error(sprintf(__('File %s could not be uploaded.', 'wcpa-text-domain'), $v->label));

            return false;
        }
        @unlink($uploadedfile);

        $stat = stat(dirname($new_file));
        $perms = $stat['mode'] & 0000666;
        @ chmod($new_file, $perms);
        // Compute the URL.
        $url = $upload['url'] . "/$filename";

        return array(
            'file' => $new_file,
            'url' => $url,
            'type' => $type,
            'file_name' => $uploadedfile_name
        );
    }

    public function handle_upload($v, $multiple = false)
    {

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $files_array = array();

        if($multiple){
            $count_of_files = count($_FILES[$v->name]["name"]);
            if($count_of_files){
                $file_count = 0;
                for($i = 0; $i < $count_of_files ; $i++){
                    if(isset($_FILES[$v->name]['name'][$i])){
                        $file_count++;
                        if(isset($v->max_file_count) && !empty($v->max_file_count)){
                            if(intval($v->max_file_count) >= $file_count){
                                $files_array[] = array(
                                    'tmp_name' => $_FILES[$v->name]['tmp_name'][$i],
                                    'name' => $_FILES[$v->name]['name'][$i],
                                    'size' => $_FILES[$v->name]['size'][$i],
                                    'type' => $_FILES[$v->name]['type'][$i],
                                    'error' => $_FILES[$v->name]['error'][$i],
                                );
                            }
                        } else {
                            $files_array[] = array(
                                'tmp_name' => $_FILES[$v->name]['tmp_name'][$i],
                                'name' => $_FILES[$v->name]['name'][$i],
                                'size' => $_FILES[$v->name]['size'][$i],
                                'type' => $_FILES[$v->name]['type'][$i],
                                'error' => $_FILES[$v->name]['error'][$i],
                            );
                        }
                    }
                }

                if(isset($files_array) && !empty($files_array)){
                    $uploaded_files = array();
                    foreach($files_array as $uploadedfile){
                        $uploadedfile_name = $uploadedfile["name"];
    
                        $upload_overrides = array('test_form' => false);
                        add_filter('upload_dir', array($this, 'upload_dir'));
                        if (!is_uploaded_file($uploadedfile['tmp_name'])) {
                            $uploaded_files[] = false;
                        } else {
                            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
                            remove_filter('upload_dir', array($this, 'upload_dir'));
        
                            if ($movefile && !isset($movefile['error'])) {
                                $uploaded_files[] = array_merge($movefile, array('file_name' => $uploadedfile_name));
                            } else {
                                /**
                                 * Error generated by _wp_handle_upload()
                                 * @see _wp_handle_upload() in wp-admin/includes/file.php
                                 */
                                $this->add_cart_error($movefile['error']);
        
                                $uploaded_files[] = false;
                                //echo $movefile['error'];
                            }
                        }
                    }
                    return $uploaded_files;
                }
            }
        } else {
            $uploadedfile = $_FILES[$v->name];
            $uploadedfile_name = $_FILES[$v->name]["name"];

            $upload_overrides = array('test_form' => false);
            add_filter('upload_dir', array($this, 'upload_dir'));
            if (!is_uploaded_file($uploadedfile['tmp_name'])) {
                return false;
            }
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);


            remove_filter('upload_dir', array($this, 'upload_dir'));

            if ($movefile && !isset($movefile['error'])) {
                return array_merge($movefile, array('file_name' => $uploadedfile_name));
            } else {
                /**
                 * Error generated by _wp_handle_upload()
                 * @see _wp_handle_upload() in wp-admin/includes/file.php
                 */
                $this->add_cart_error($movefile['error']);

                return false;
                //echo $movefile['error'];
            }
        }
    }

    public function sanitize_values($v)
    {

        if (!is_object($v)) {
            return sanitize_text_field($v);
        } else if ((isset($v->name))) {

            if (is_array($_REQUEST[$v->name])) {

                $_values = $_REQUEST[$v->name];
                array_walk($_values, function (&$a, $b) {
                    sanitize_text_field(wp_unslash($a));
                }); // using this array_walk method to preserve the keys
                /* some plugins/themes send checkbox/radio field data even if they are not checked, it can filter using null*/
                $_values = array_filter($_values, function ($value) {
                    return ($value !== null && $value !== false && $value !== '');
                });
                return $_values;
            } else if ($v->type == 'textarea') {
                return sanitize_textarea_field(wp_unslash($_REQUEST[$v->name]));
            } else {
                return sanitize_text_field(wp_unslash($_REQUEST[$v->name]));
            }
        }
    }

    public function labelFormulaUpdate($product_id)
    {
        $allowed = array(
            'a' => array(// on allow a tags
                'href' => true, // and those anchors can only have href attribute
                'target' => true,
                'class' => true,// and those anchors can only have href attribute
                'style' => true
            ),
            'b' => array('style' => true, 'class' => true),
            'strong' => array('style' => true, 'class' => true),
            'i' => array('style' => true, 'class' => true),
            'img' => array('style' => true, 'class' => true, 'src' => true),
            'span' => array('style' => true, 'class' => true),
            'p' => array('style' => true, 'class' => true)
        );
        foreach ($this->submited_data as $k => $data) {
            if (in_array($data['type'], array('paragraph'))) {
                $label = (isset($data['value'])) ? $data['value'] : '';
                if (preg_match('/\#\=(.+?)\=\#/', $label) === 1) {
                    $label = $this->excLabel($data, $product_id);


                    $this->submited_data[$k]['value'] = wp_kses($label['content'], $allowed);
                    $this->submited_data[$k]['quantity_depend_label'] = $label['quantity_depend_label'];
                } else {

                    $this->submited_data[$k]['value'] = wp_kses($this->submited_data[$k]['value'], $allowed);

                }
            }

        }

    }

    public function excLabel($data, $product_id)
    {
        if (!$this->product) {
            $this->set_product($product_id);
        }
        $product_price = $this->product->get_price('edit');

        $out = $label = $data['value'];
        $quantity_depend_label = false;
        if (preg_match_all('/\#\=(.+?)\=\#/', $label, $matches) >= 1) {
            $quantity_depend_label = $out;
            foreach ($matches[1] as $k => $match) {

                $formula = $this->process_custom_formula(1, $match, 1, $product_price, true, true, true);

                if ($formula === '' || $formula === '0') {
                    $out = str_replace($matches[0][$k], '', $out);
                    $quantity_depend_label = str_replace($matches[0][$k], '', $quantity_depend_label);
                } else {
                    $_formula = str_replace(['{quantity}'], [1], $formula);
                    try {


                        $res = eval('return ' . $_formula . ';');
                        if (is_numeric($res) && $res % 1 != 0) {
                            $res = number_format($res, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator());
                        }
                        $out = str_replace($matches[0][$k], $res, $out);

                        if (strpos($formula, '{quantity}') >= 0) {
                            $quantity_depend_label = str_replace($matches[0][$k], "#=" . $formula . "=#", $quantity_depend_label);
                        } else {
                            $quantity_depend_label = str_replace($matches[0][$k], $res, $quantity_depend_label);
                        }
                    } catch (Throwable $t) {
                        $out = str_replace($matches[0][$k], $_formula, $out);
                        if (strpos($formula, '{quantity}') >= 0) {
                            $quantity_depend_label = str_replace($matches[0][$k], "#=" . $formula . "=#", $quantity_depend_label);
                        } else {
                            $quantity_depend_label = str_replace($matches[0][$k], $formula, $quantity_depend_label);
                        }
                    }
                }
            }
        }

        return ['content' => $out, 'quantity_depend_label' => $quantity_depend_label];
    }

    public function upload_dir($upload)
    {

        $customer = new WCPA_Customer();
        $file_directory = $customer->upload_directory_base();

        $upload['subdir'] = '/' . $file_directory;
        $upload['path'] = $upload['basedir'] . '/' . $file_directory;
        $upload['url'] = $upload['baseurl'] . '/' . $file_directory;

        return $upload;
    }

    public function upload_dir_temp($upload)
    {

        $customer = new WCPA_Customer();
        $file_directory = $customer->upload_directory_base(true);
        $upload['subdir'] = '/' . $file_directory;
        $upload['path'] = $upload['basedir'] . '/' . $file_directory;
        $upload['url'] = $upload['baseurl'] . '/' . $file_directory;

        return $upload;
    }

    public function render($after_acb = false, $is_rest = false)
    {

        $cf_prefix = wcpa_get_option('wcpa_cf_prefix', 'wcpa_pcf_');

        $qnty_related = '';
        $attr_related = '';

        $product_data = array();


        if (isset($this->relations['quantity'])) {
            $qnty_related = 'data-qntyrelated=\'' . json_encode($this->relations['quantity']) . '\'';
        }
        if (isset($this->relations['attribute'])
            || isset($this->relations['stock_status'])
            || isset($this->relations['stock_quantity'])
            || isset($this->relations['product_ids'])
            || isset($this->relations['custom_field'])) {

            $attr_related = 'data-attrrelated=\'' . json_encode(array_merge(isset($this->relations['attribute']) ? $this->relations['attribute'] : [],
                    isset($this->relations['stock_status']) ? $this->relations['stock_status'] : [],
                    isset($this->relations['stock_quantity']) ? $this->relations['stock_quantity'] : [],
                    isset($this->relations['product_ids']) ? $this->relations['product_ids'] : [],
                    isset($this->relations['custom_field']) ? $this->relations['custom_field'] : []
                )) . '\'';
        }


        if (!$this->product) {
            global $product;
            $this->product = $product;
        }

        $has_price = false;
        if (count($this->data) === 0) {
            return;
        }

        $filter_default     = false;  
        $wcpa_price   = apply_filters( 'wcpa_product_price', $filter_default, $this->product );

        $product_data['wc_product_price'] = $wcpa_price ? $wcpa_price : ( apply_filters('raw_woocommerce_price', wcpa_get_price_shop($this->product)) / $this->get_con_unit(true) );
        // $product_data['wc_product_price'] = apply_filters('raw_woocommerce_price', wcpa_get_price_shop($this->product)) / $this->get_con_unit(true);
        $product_data['wc_product_regular_price'] = apply_filters('raw_woocommerce_price', wcpa_get_price_shop($this->product, [], true)) / $this->get_con_unit(true);
        $product_data['wc_product_price_default'] = (wcpa_get_option('variation_default_price', false) === false) ? $product_data['wc_product_price'] : wcpa_get_option('variation_default_price', false);// used  in js incase it product price has got changed by variations
        $product_data['product_title'] = '';
        $product_data['con_unit'] = $this->get_con_unit();
        $product_data['product_id'] = $this->product->get_id();
        $product_data['is_variable'] = $this->product->is_type('variable') ? true : false;
        $product_data['stock_status'] = $this->product->get_stock_status('edit');
        $product_data['stock_quantity'] = $this->product->get_stock_quantity('edit');

        // $product_data['product_attributes'] = $this->product->is_type('simple') ? $this->get_pro_attr_list() : [];
        $product_data['product_attributes'] = $this->get_pro_attr_list();

        $product_custom_fields = wcpa_get_option('product_custom_fields');
        $product_cfs = array();
        if (is_array($product_custom_fields)) {
            foreach ($product_custom_fields as $cf) {
                if (get_post_meta($this->product->get_id(), $cf_prefix . $cf['name'], true)) {
                    $product_cfs['wcpa_pcf_' . $cf['name']] = get_post_meta($this->product->get_id(), $cf_prefix . $cf['name'], true);
                } else {
                    $product_cfs['wcpa_pcf_' . $cf['name']] = $cf['value'];
                }
            }
        }
        $product_data['product_cfs'] = $product_cfs;

        $rules = [
            'pric_overide_base_price' => isset($this->settings['pric_overide_base_price']) ? $this->settings['pric_overide_base_price'] : false,
            'pric_overide_base_price_if_gt_zero' => isset($this->settings['pric_overide_base_price_if_gt_zero']) ? $this->settings['pric_overide_base_price_if_gt_zero'] : false,
            'pric_overide_base_price_fully' => isset($this->settings['pric_overide_base_price_fully']) ? $this->settings['pric_overide_base_price_fully'] : false,

            'pric_cal_option_once' => isset($this->settings['pric_cal_option_once']) ? $this->settings['pric_cal_option_once'] : false,
            'pric_use_as_fee' => isset($this->settings['pric_use_as_fee']) ? $this->settings['pric_use_as_fee'] : false,
            'bind_quantity' => isset($this->settings['bind_quantity']) ? $this->settings['bind_quantity'] : false,
            'quantity_bind_formula' => isset($this->settings['quantity_bind_formula']) ? $this->settings['quantity_bind_formula'] : false,
        ];

        if ($is_rest) {// REST api request
            return [
                'rules' => $rules,
                'fields' => $this->data
            ];
        }

        echo '<div class="wcpa_form_outer" ' . $qnty_related . ' ' . $attr_related . ' data-product=\'' . htmlspecialchars(wp_json_encode($product_data), ENT_QUOTES) . '\' data-rules=\'' . json_encode($rules) . '\'>';
        $row_open = false;
        $col_counter = 6;
        $col = 6;


        echo '<input type="hidden" name="wcpa_field_key_checker" value="wcpa" />';
        // if (wcpa_get_option('ajax_add_to_cart', false)) { // if enabled ajax submissions
            // echo '<input type="hidden" name="action" value="woocommerce_add_to_cart" />';
            // echo '<input type="hidden" name="product_id" value="' . $this->product->get_id() . '" />';
        // }


        foreach ($this->data as $v) {


            if (isset($v->col)) {
                $col = (int)$v->col;
            }
            $col_counter += $col;
            if ($col_counter > 6) {
                if ($row_open) {
                    echo '</div>';
                    echo '<div class="wcpa_row">';
                } else {
                    echo '<div class="wcpa_row">';
                    $row_open = true;
                }
                $col_counter = $col;
            }

            $parent_class = 'wcpa_form_item wcpa_type_' . $v->type . ' wcpa_form_id_' . $v->form_id . ' ';
            if (isset($v->className)) {
                $parent_class .= ' ' . $v->className . '_parent ';
            }
            if ($col < 6) {
                $parent_class .= 'wcpa-col-' . $col . ' ';
            }

            $inline_css = '';
            $relation_action = '';
            if (isset($v->enableCl) && $v->enableCl) {
                if ($v->cl_rule == 'show') {
                    $inline_css = 'display:none;';
                }
            }
            if ($v->type == 'hidden') {
                $inline_css = 'display:none;';
            }
            if (!empty($inline_css)) {
                $inline_css = 'style="' . $inline_css . '"';
            }
            $id_text = '';
            if (isset($v->elementId) && !empty($v->elementId)) {
                $id_text .= 'id="' . $v->elementId . '"';
            }
            $relation_data = $this->get_relation_data($v);

            if (!empty($relation_data['related'])) {
                $parent_class .= 'wcpa_has_relation ';
            }
            if (!empty($relation_data['rules'])) {
                $parent_class .= 'wcpa_has_rules ';
            }
            if (isset($relation_data['price_related']) && !empty($relation_data['price_related'])) {
                $parent_class .= 'wcpa_has_price_related ';
            }
            if (isset($relation_data['label_related']) && !empty($relation_data['label_related'])) {
                $parent_class .= 'wcpa_has_label_related ';
            }
            // Validation Class
            // if (isset($v->required) && $v->required) {
                $parent_class .= 'wcpa_validate_field ';
            // }

            echo '<div class="' . $parent_class . '" ' . $inline_css . ' ' . $relation_data['related'] . ''
                . ' ' . $relation_data['rules'] . ' ' . $relation_data['label_related'] . ' ' . $relation_data['price_related'] . '   ' . $id_text . ' data-type="' . $v->type . '" >';

            switch ($v->type) {
                case 'text':
                    $this->render_text($v);
                    break;
                case 'hidden':
                    $this->render_hidden($v);
                    break;
                case 'color':
                    $this->render_color($v);
                    $this->enqueue_colorpicker();
                    break;
                case 'number':
                    $this->render_number($v);
                    break;
                case 'date':
                    $this->render_date($v);

                    $this->enqueue_detetimepicker();
                    break;
                case 'time':
                    $this->render_time($v);
                    $this->enqueue_detetimepicker();
                    break;
                case 'datetime-local':
                    $this->render_datetime($v);
                    $this->enqueue_detetimepicker();
                    break;
                case 'checkbox-group':
                    $this->render_checkbox($v);

                    break;
                case 'radio-group':
                    $this->render_radio($v);
                    break;
                case 'header':
                    $this->render_header($v);
                    break;
                case 'file':
                    $this->render_file($v);
                    break;
                case 'paragraph':
                    $this->render_paragraph($v);
                    break;
                case 'statictext':
                    $this->render_statictext($v);
                    break;
                case 'productGroup':
                    $this->render_productGroup($v);
                    break;
                case 'separator':
                    $this->render_separator($v);
                    break;
                case 'select':
                    $this->render_select($v);
                    break;
                case 'textarea':
                    $this->render_textarea($v);
                    break;
                case 'image-group':
                    $this->render_image($v);
                    break;
                case 'color-group':
                    $this->render_colorgroup($v);
                    break;
                case 'placeselector':
                    $this->render_place($v);
                    $this->enqueue_googlemap();
                    break;
            }
            //$this->{'render_' . $v->type}($v);
            if (isset($v->enablePrice) && $v->enablePrice ) {
                $has_price = true;
                $visible = ($this->settings['disp_show_field_price'] ? '' : 'style="display:none"');
                echo '<span class="wcpa_priceouter" ' . $visible . ' >' . wcpa_price(0) . '</span>';
            }
            echo '</div>';
        }
        if ($row_open) {
            echo '</div>';
            $row_open = false;
        }

        if ($this->settings['enable_recaptcha'] && $this->settings['enable_recaptcha'] === true) {
            echo '<div class="wcpa_row">';
            echo '<div class="g-recaptcha" data-sitekey="' . wcpa_get_option('recaptcha_site_key', '') . '"></div>';
            echo '</div>';
            $this->enqueue_recaptcha();
        }


        echo '</div>';
        if ($has_price) {
            $hook = get_wcpa_display_hook('price_summary');
            add_action($hook[0], array($this, 'price_summary_box'), $hook[1]);
        }

        do_action('wcpa_price_summary_box');
    }

    public function get_con_unit($toMultiplyWithShopPrice = false, $v = false)
    {
        if ($v) {
            if (isset($v->form_rules['exclude_from_discount']) && $v->form_rules['exclude_from_discount']) {
                return 1;
            }
        }
        $index = $toMultiplyWithShopPrice ? 0 : 1;
        // $toMultiplyWithShopPrice the response can be decided based on the currency switcher using
        if ($this->conversion_unit[$index] === false) {
            $mc = new WCPA_MC();
            $this->conversion_unit[$index] = $mc->get_con_unit($this->product, false, false, $toMultiplyWithShopPrice);

            return $this->conversion_unit[$index];
        } else {
            return $this->conversion_unit[$index];
        }
    }

    public function get_relation_data($v, $val = false)
    {
        $related = '';
        $rules = '';
        $price_related = '';
        $label_related = '';

        if (isset($v->elementId) && isset($this->relations[$v->elementId])) {
            $related = 'data-related=\'' . json_encode($this->relations[$v->elementId]) . '\'';
        }
        if (isset($v->elementId) && isset($this->price_depends[$v->elementId])) {
            $price_related = 'data-price_depends=\'' . json_encode($this->price_depends[$v->elementId]) . '\'';
        }

        if (isset($v->elementId) && isset($this->label_depends[$v->elementId])) {
            $label_related = 'data-label_depends=\'' . json_encode($this->label_depends[$v->elementId]) . '\'';
        }
        if (isset($v->elementId) && isset($v->enableCl) && $v->enableCl) {

            $rules = 'data-rules=\'' . htmlspecialchars(json_encode([
                    'rules' => $v->relations,
                    'action' => $v->cl_rule
                ]), ENT_QUOTES) . '\'';
        }


        return [
            'related' => $related,
            'rules' => $rules,
            'price_related' => $price_related,
            'label_related' => $label_related
        ];
    }

    public function render_text($v)
    {
        $data = '';
        $maxlength = '';
        $minlength = '';
        $pattern = '';
        $txt_required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';
        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';


        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        $validation = '';
        
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->pattern) && !empty($v->pattern)) {
            $validation_array['pattern'] = trim($v->pattern);
            $validation_array['patternMessage'] = wcpa_get_validation_message('patternError', $v, trim($v->pattern));
        }
        if (isset($v->allowed_chars) && !empty($v->allowed_chars)) {
            $validation_array['allowed_chars_enable'] = true;
            $validation_array['allowed_chars'] = htmlspecialchars($v->allowed_chars, ENT_QUOTES);
            $validation_array['allowed_charsMessage'] = wcpa_get_validation_message('allowedCharsError', $v);
        }
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->maxlength) && !empty($v->maxlength)) {
            $validation_array['maxlength'] = $v->maxlength;
            $validation_array['maxlengthMessage'] =  htmlspecialchars(wcpa_get_validation_message('maxlengthError', $v, $v->maxlength), ENT_QUOTES);
        }
        if (isset($v->minlength) && !empty($v->minlength)) {
            $validation_array['minlength'] = $v->minlength;
            $validation_array['minlengthMessage'] =  htmlspecialchars(wcpa_get_validation_message('minlengthError', $v, $v->minlength), ENT_QUOTES);
        }
        if (isset($v->charleft) && $v->charleft) {
            $validation_array['charleft'] = $v->charleft;
            $validation_array['charleftMessage'] =  htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';
        
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }


        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);


        if (isset($v->maxlength)) {
            $maxlength = 'maxlength="' . $v->maxlength . '"';
        }
        if (isset($v->minlength)) {
            $minlength = 'minlength="' . $v->minlength . '"';
        }

        if (isset($v->pattern) && !empty($v->pattern)) {
            $pattern = 'pattern="' . trim($v->pattern) . '"';
        }
        echo '<input  ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . ' type="' . $v->subtype . '"'
            . '  id="' . $name . '" '
            . '' . $placeholder . ''
            . ' ' . $className . ''
            . ' name="' . $name . '"'
            . ' value="' . $this->default_value($v) . '" '
            . '' . $maxlength . ' '
            . '' . $minlength . ' '
            . '' . $txt_required . ''
            . ' ' . $price_data . ' ' . $validation . ' ' . $pattern . ' />';

        if (isset($v->charleft) && $v->charleft && isset($v->maxlength) && !empty($v->maxlength)) {
            $char_count = (int)$v->maxlength - strlen($this->default_value($v));
            if($char_count >= 0) {
                echo '<div class="wcpa_char_left_message"><p>'.
                        htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v, $char_count), ENT_QUOTES)
                    .'</p></div>';
            }
        }
    }

    public function get_price_data($v, $val = false, $productData = false)
    {
        $data = false;
        //per character => per_car
        //percentage of total amount => percentage
        // multiply by number  => multiply
        $consider_tax = wcpa_get_option('consider_product_tax_conf', true);
        if (isset($v->enablePrice) && $v->enablePrice && (isset($v->price) || isset($val->price))) {

            if ($this->tax_per_unit === false) {
                if ($consider_tax) {
                    $this->tax_per_unit = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => 1,
                    ));
                } else {
                    $this->tax_per_unit = 1;
                }

            }
            $is_fee = false;
            if (isset($v->form_rules['pric_use_as_fee']) && $v->form_rules['pric_use_as_fee'] === true) {
                $is_fee = true;
            } else {
                $is_fee = isset($v->use_as_fee) && $v->use_as_fee;
            }

            $is_show_price = isset($v->is_show_price) && $v->is_show_price;

            if (in_array($v->type, array(
                'select',
                'checkbox-group',
                'radio-group',
                'image-group',
                'color-group',
                'productGroup'
            ))) {
                $pricingType = ($v->priceOptions == 'product_price') ? 'fixed' : $v->pricingType;
                if ($v->priceOptions == 'different_for_all') {
                    $price = $val->price;
                } else if ($v->priceOptions == 'fixed_for_all') {
                    $price = $v->price;
                } else if ($v->priceOptions == 'product_price') {
                    $price = $productData->get_price('edit');
                }
                if ($pricingType !== 'percentage' && $pricingType !== 'custom') {

                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price,
                    ));

                    $price = apply_filters('raw_woocommerce_price', $price);
                    if ($this->get_con_unit(false) !== $this->get_con_unit(true)) { // it already may have applied the currncy
                        $price /= $this->get_con_unit(false);
                    }
                }
                if (in_array($v->type, array('productGroup')) && $productData){
                    $data = 'data-price=\'' . json_encode(array(
                        'priceoptions' => $v->priceOptions,
                        'type' => $v->type,
                        'value' => $price,
                        'pricingType' => $pricingType,
                        'excl_chars_frm_length' => (isset($v->excl_chars_frm_length) ? htmlspecialchars($v->excl_chars_frm_length, ENT_QUOTES) : ''),
                        'excl_chars_frm_length_is_regex' => (isset($v->excl_chars_frm_length_is_regex) ? $v->excl_chars_frm_length_is_regex : false),
                        'taxpu' => $this->tax_per_unit,
                        'con_unit' => $this->get_con_unit(),
                        'is_fee' => $is_fee,
                        'is_show_price' => $is_show_price,
                        'option_price' => $productData->get_price('edit'),
                    )) . '\'';
                } else {
                    $data = 'data-price=\'' . json_encode(array(
                            'priceoptions' => $v->priceOptions,
                            'type' => $v->type,
                            'value' => $price,
                            'pricingType' => $pricingType,
                            'excl_chars_frm_length' => (isset($v->excl_chars_frm_length) ? htmlspecialchars($v->excl_chars_frm_length, ENT_QUOTES) : ''),
                            'excl_chars_frm_length_is_regex' => (isset($v->excl_chars_frm_length_is_regex) ? $v->excl_chars_frm_length_is_regex : false),
                            'taxpu' => $this->tax_per_unit,
                            'con_unit' => $this->get_con_unit(),
                            'is_fee' => $is_fee,
                            'is_show_price' => $is_show_price
                        )) . '\'';
                }
            } else {
                $price = $v->price;

                if ($v->pricingType !== 'percentage' && $v->pricingType !== 'custom') {// in such cases $price will be string or percentage value, not be actual price
                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price,
                    ));
                    $price = apply_filters('raw_woocommerce_price', $price);
                    $price = apply_filters('wcml_raw_price_amount', $price);
                    if ($this->get_con_unit(false) !== $this->get_con_unit(true)) { // it already may have applied the currncy
                        $price /= $this->get_con_unit(false);
                    }
                }

                $data = 'data-price=\'' . htmlspecialchars(json_encode(array(
                        'value' => $price,
                        'type' => $v->type,
                        'pricingType' => $v->pricingType,
                        'excl_chars_frm_length' => (isset($v->excl_chars_frm_length) ? ($v->excl_chars_frm_length) : ''),
                        'excl_chars_frm_length_is_regex' => (isset($v->excl_chars_frm_length_is_regex) ? $v->excl_chars_frm_length_is_regex : false),
                        'taxpu' => $this->tax_per_unit,
                        'is_fee' => $is_fee,
                        'is_show_price' => $is_show_price,
                        'con_unit' => $this->get_con_unit()
                    )), ENT_QUOTES) . '\'';
            }
        } 

        return $data;
    }

    public function label_section($v)
    {
        $name = (isset($v->name)) ? $v->name : '';
        $label = '';
        if (isset($v->label) && $v->label) {

            echo '<label for="' . $name . '">';
            $label .= $v->label;

            if (isset($v->required) && $v->required) {
                $label .= '<span class="required_ast">*</span>';
            }
            if (isset($v->desc_type) && $v->desc_type) {
                $label .= '<span class="wcpa_tooltip_icon" >?';
                if (isset($v->description)) {
                    $label .= '<span class="wcpa_tooltip">' . $v->description;
                    $label .= '</span>';
                }
                $label .= '</span>';
            }

            $label = apply_filters('wcpa_field_label', $label, $v);
            echo $label;
            echo '</label>';
        }
        if ((!isset($v->desc_type) || !$v->desc_type) && isset($v->description)) {
            echo '<span class="wcpa_helptext">' . $v->description;
            echo '</span>';
        }
    }

    private function default_value($v)
    {

        $default_value = '';
        switch ($v->type) {
            case 'text':
            case 'date':
            case 'number':
            case 'color':
            case 'textarea':
            case 'hidden':
            case 'time':
            case 'datetime-local':
                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value = $this->sanitize_values($v);
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) { // using get if there is any value passed using url/get method
                    $default_value = $this->sanitize_values($v);
                } else if (isset($v->value)) {
                    $default_value = htmlspecialchars($v->value);
                }


                break;
            case 'file':
                $ajax_upload = (isset($v->ajax_upload) && $v->ajax_upload) ? true : false;
                if ($ajax_upload) {
                    if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name . '_ajax'])) { // if there is a validation error, it has persist the user entered values,
                        $default_value = $this->sanitize_values($_POST[$v->name . '_ajax']);
                    }
                }
                break;
            case 'select':
                $default_value = array();
                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value = $this->sanitize_values($v);
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) {
                    $default_value = $this->sanitize_values($v);
                } else if ($v->values && !(isset($_REQUEST['add-to-cart']) && $this->cart_error)) { // if it is direct product page load, not add-to-cart has set
                    foreach ($v->values as $k => $val) {
                        if (isset($val->selected)) {
                            $default_value[$k] = $val->value;
                        }
                    }
                }

                break;
            case 'checkbox-group':
            case 'radio-group':

                $default_value = array();
                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value = $this->sanitize_values($v);
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) {
                    $default_value = $this->sanitize_values($v);
                } else if ($v->values && !(isset($_REQUEST['add-to-cart']) && $this->cart_error)) { // if it is direct product page load, not add-to-cart has set
                    foreach ($v->values as $k => $val) {
                        if (isset($val->selected)) {
                            $default_value[$k] = $val->selected;
                        }
                    }
                }

                break;
            case 'image-group':
            case 'productGroup':

                $default_value = array();
                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value = $this->sanitize_values($v);
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) {
                    $default_value = $this->sanitize_values($v);
                } else if ($v->values && !(isset($_REQUEST['add-to-cart']) && $this->cart_error)) { // if it is direct product page load, not add-to-cart has set
                    foreach ($v->values as $k => $val) {
                        if (isset($val->selected)) {
                            $default_value[$k] = $val->selected;
                        }
                    }
                }
                break;

            case 'color-group':
                $default_value = array();
                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value = $this->sanitize_values($v);
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) {
                    $default_value = $this->sanitize_values($v);
                } else if ($v->values && !(isset($_REQUEST['add-to-cart']) && $this->cart_error)) { // if it is direct product page load, not add-to-cart has set
                    foreach ($v->values as $k => $val) {
                        if (isset($val->selected)) {
                            $default_value[$k] = $val->selected;
                        }
                    }
                }
                break;
            case 'placeselector':
                $default_value = array(
                    'formated' => '',
                    'splited' => array(),
                    'cords' => array()
                );
                $splited = [
                    'street_number',
                    'route',
                    'locality',
                    'administrative_area_level_1',
                    'postal_code',
                    'country'
                ];

                if ($this->cart_error && (isset($v->name)) && isset($_POST[$v->name])) { // if there is a validation error, it has persist the user entered values,
                    $default_value['formated'] = $this->sanitize_values($v);
                    foreach ($splited as $fl_name) {
                        if (isset($_POST[$v->name . '_' . $fl_name])) {
                            $default_value['splited'][$fl_name] = $_POST[$v->name . '_' . $fl_name];
                        } else {
                            $default_value['splited'][$fl_name] = '';
                        }
                    }
                    if (isset($_POST[$v->name . '_lat'])) {
                        $default_value['cords']['lat'] = $_POST[$v->name . '_lat'];
                    } else {
                        $default_value['cords']['lat'] = '';
                    }
                    if (isset($_POST[$v->name . '_lng'])) {
                        $default_value['cords']['lng'] = $_POST[$v->name . '_lng'];
                    } else {
                        $default_value['cords']['lng'] = '';
                    }
                } elseif ((isset($v->name)) && isset($_GET[$v->name])) {
                    $default_value['formated'] = $this->sanitize_values($v);
                    foreach ($splited as $fl_name) {
                        if (isset($_POST[$v->name . '_' . $fl_name])) {
                            $default_value['splited'][$fl_name] = $_POST[$v->name . '_' . $fl_name];
                        } else {
                            $default_value['splited'][$fl_name] = '';
                        }
                    }
                    if (isset($_POST[$v->name . '_lat'])) {
                        $default_value['cords']['lat'] = $_POST[$v->name . '_lat'];
                    } else {
                        $default_value['cords']['lat'] = '';
                    }
                    if (isset($_POST[$v->name . '_lng'])) {
                        $default_value['cords']['lng'] = $_POST[$v->name . '_lng'];
                    } else {
                        $default_value['cords']['lng'] = '';
                    }
                } else {
                    $default_value['formated'] = isset($v->value) ? $v->value : '';
                    foreach ($splited as $fl_name) {
                        $default_value['splited'][$fl_name] = '';
                    }
                    $default_value['cords']['lat'] = '';

                    $default_value['cords']['lng'] = '';
                }

                break;
        }

        return $default_value;
    }

    public function render_hidden($v)
    {
        $name = (isset($v->name)) ? $v->name : '';
        $value = (isset($v->value)) ? $v->value : '';

        echo '<input type="hidden"  id="' . $name . '"  name="' . $name . '" value="' . $this->default_value($v) . '" />';
    }

    public function render_color($v)
    {
        $data = '';
        $maxlength = '';
        $txt_required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';
        $className = 'wcpa_colorpicker ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);

        echo '<input type="color"  id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $this->default_value($v) . '" ' . $txt_required . ' ' . $price_data . '  ' . $validation . '  />';
    }

    public function enqueue_colorpicker()
    {
        wp_enqueue_script($this->_token . '-colorpicker');
    }

    public function render_number($v)
    {
        $num_required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';

        $pattern = '';
        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->pattern) && !empty($v->pattern)) {
            $validation_array['pattern'] = trim($v->pattern);
            $validation_array['patternMessage'] = wcpa_get_validation_message('patternError', $v, trim($v->pattern));
        }
        if (isset($v->max) && !empty($v->max)) {
            $validation_array['max'] = trim($v->max);
            $validation_array['maxMessage'] = wcpa_get_validation_message('maxValueError', $v, trim($v->max));
        }
        if (isset($v->min) && !empty($v->min)) {
            $validation_array['min'] = trim($v->min);
            $validation_array['minMessage'] = wcpa_get_validation_message('minValueError', $v, trim($v->min));
        }
        if (isset($v->maxlength) && !empty($v->maxlength)) {
            $validation_array['maxlength'] = $v->maxlength;
            $validation_array['maxlengthMessage'] = htmlspecialchars(wcpa_get_validation_message('maxlengthError', $v, $v->maxlength), ENT_QUOTES);
        }
        if (isset($v->minlength) && !empty($v->minlength)) {
            $validation_array['minlength'] = $v->minlength;
            $validation_array['minlengthMessage'] =  htmlspecialchars(wcpa_get_validation_message('minlengthError', $v, $v->minlength), ENT_QUOTES);
        }
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->charleft) && $v->charleft) {
            $validation_array['charleft'] = $v->charleft;
            $validation_array['charleftMessage'] =  htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $max = (isset($v->max)) ? 'max="' . $v->max . '"' : '';
        $min = (isset($v->min)) ? 'min="' . $v->min . '"' : '';
        $name = (isset($v->name)) ? $v->name : '';
        $step = (isset($v->step)) ? 'step="' . $v->step . '"' : 'step="any"';
        if (isset($v->pattern) && !empty($v->pattern)) {
            $pattern = 'pattern="' . trim($v->pattern) . '"';
        }
        $this->label_section($v);

        echo '<input ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . '   type="' . $v->type . '"  id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $this->default_value($v) . '" ' . $max . ' ' . $min . ' ' . $step . ' ' . $num_required . ' ' . $price_data . ' ' . $pattern . ' ' . $validation . ' />';
        
        if (isset($v->charleft) && $v->charleft && isset($v->maxlength) && !empty($v->maxlength)) {
            $char_count = (int)$v->maxlength - strlen($this->default_value($v));
            if($char_count >= 0) {
                echo '<div class="wcpa_char_left_message"><p>'.
                        htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v, $char_count), ENT_QUOTES)
                    .'</p></div>';
            }
        }
    }

    public function render_date($v)
    {
        $required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';


        $name = (isset($v->name)) ? $v->name : '';
        $className = 'wcpa_datepicker ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        $config_data = '';
        $date_pic_conf = (object)[];
        if (isset($v->date_pic_conf) && $v->date_pic_conf) {
            $date_pic_conf = json_decode(trim(do_shortcode($v->date_pic_conf)));

        }
        if (isset($v->min_date) && $v->min_date && $v->min_date != '' && !isset($date_pic_conf->minDate)) {
            if ($v->min_date == 'today') {
                @$date_pic_conf->minDate = 0;
            } else if ($v->min_date == 'tomorrow') {
                $date_pic_conf->minDate = '+1970/01/02';
                if (!isset($date_pic_conf->startDate)) {
                    $date_pic_conf->startDate = $date_pic_conf->minDate;
                }
            } else if ($v->min_date == 'yesterday') {
                $date_pic_conf->minDate = '-1970/01/02';
            }


        }
        if (isset($v->max_date) && $v->max_date && $v->max_date != '' && !isset($date_pic_conf->maxDate)) {
            if ($v->max_date == 'today') {
                $date_pic_conf->maxDate = 0;
            } else if ($v->max_date == 'tomorrow') {
                $date_pic_conf->maxDate = '+1970/01/02';
            } else if ($v->max_date == 'yesterday') {
                $date_pic_conf->maxDate = '-1970/01/02';
            }
        }
        if ($date_pic_conf) {
            $config_data = 'data-dpconf=\'' . json_encode($date_pic_conf) . '\'';
        }

        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $this->label_section($v);

        echo '<input autocomplete="off" ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . ' type="text" ' . $price_data . ' id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $this->default_value($v) . '" ' . $required . ' ' . $config_data . ' ' . $validation . ' />';
    }

    public function enqueue_detetimepicker()
    {

        wp_enqueue_script($this->_token . '-datetime');
    }

    public function render_time($v)
    {
        $required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';


        $name = (isset($v->name)) ? $v->name : '';
        $className = 'wcpa_timepicker ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        $config_data = '';
        if (isset($v->date_pic_conf) && $v->date_pic_conf) {
            $config_data = 'data-dpconf=\'' . trim(do_shortcode($v->date_pic_conf)) . '\'';


        }
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $this->label_section($v);

        echo '<input autocomplete="off" ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . ' type="text" ' . $price_data . ' id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $this->default_value($v) . '" ' . $required . ' ' . $config_data . ' ' . $validation . ' />';
    }

    public function render_datetime($v)
    {
        $required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';


        $name = (isset($v->name)) ? $v->name : '';
        $className = 'wcpa_datetimepicker ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        $config_data = '';

        $date_pic_conf = (object)[];
        if (isset($v->date_pic_conf) && $v->date_pic_conf) {
            $date_pic_conf = json_decode(trim(do_shortcode($v->date_pic_conf)));
        }
        if (isset($v->min_date) && $v->min_date && $v->min_date != '' && !isset($date_pic_conf->minDate)) {
            if ($v->min_date == 'today') {
                $date_pic_conf->minDate = 0;
            } else if ($v->min_date == 'tomorrow') {
                $date_pic_conf->minDate = '+1970/01/02';
            } else if ($v->min_date == 'yesterday') {
                $date_pic_conf->minDate = '-1970/01/02';
            }
        }
        if (isset($v->max_date) && $v->max_date && $v->max_date != '' && !isset($date_pic_conf->maxDate)) {
            if ($v->max_date == 'today') {
                $date_pic_conf->maxDate = 0;
            } else if ($v->max_date == 'tomorrow') {
                $date_pic_conf->maxDate = '+1970/01/02';
            } else if ($v->max_date == 'yesterday') {
                $date_pic_conf->maxDate = '-1970/01/02';
            }
        }
        if ($date_pic_conf) {
            $config_data = 'data-dpconf=\'' . json_encode($date_pic_conf) . '\'';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $this->label_section($v);

        echo '<input autocomplete="off" ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . ' type="text" ' . $price_data . ' id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $this->default_value($v) . '" ' . $required . ' ' . $config_data . ' ' . $validation . ' />';
    }

    public function render_checkbox($v)
    {
        global $wcpa_field_counter;
        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);
        $chk_required = (isset($v->required)) ? 'required="required"' : '';


        $className = 'checkbox-group ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        if (isset($v->inline) && $v->inline) {
            $className .= 'checkbox-inline ';
        }
        $price_data = '';
        if (isset($v->required) && $v->required) {
            $className .= 'wcpa_required ';
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->min_options) && !empty($v->min_options)) {
            $validation_array['min'] = $v->min_options;
            $validation_array['minMessage'] =   wcpa_get_validation_message('minFieldsError', $v, $v->min_options);
        }
        if (isset($v->max_options) && !empty($v->max_options)) {
            $validation_array['max'] = $v->max_options;
            $validation_array['maxMessage'] =  wcpa_get_validation_message('maxFieldsError', $v, $v->max_options);
        }
        if (isset($v->other) && $v->other) {
            $validation_array['other'] = $v->other;
            $validation_array['otherRequiredMessage'] =  wcpa_get_validation_message('otherFieldError', $v);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $default_value = $this->default_value($v);

        if (!wcpa_empty($default_value) && $default_value !== false) {
            $chk_required = '';
        }
        if (isset($v->enable_product_image) && $v->enable_product_image) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }
        if ($v->values && !empty($v->values)) {
            echo '<div ' . $className . ' ' . $validation . '>';
            foreach ($v->values as $k => $val) {


                $is_selected = isset($default_value[$k]) && $default_value[$k] !== false;

                $chkd = ($is_selected) ? 'checked="checked"' : '';
                $price = '';
                // if ($v->enablePrice && $v->priceOptions == 'different_for_all') {
                //     $price = ' ( ' . wcpa_price($val->price, true) . ')';
                // }
                $option_class = '';
                if (isset($v->enablePrice) && $v->enablePrice) {
                    $option_class .= 'wcpa_has_price ';
                }
                if (isset($v->enable_product_image) && $v->enable_product_image) {
                    $option_class .= 'wcpa_update_product_image ';
                }

                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $label = $val->label;
                if (isset($v->enablePrice) && $v->enablePrice) {
                    if (
                        (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)
                        && (
                            $v->pricingType === 'fixed' || $v->pricingType === 'custom'
                        )) {

                        if ($v->priceOptions == 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions == 'fixed_for_all') {
                            $price = $v->price;
                        }
                        if (!wcpa_price_is_numeric($price)) {
                            $price = false;
                        }
                        if ($price !== false) {
                            $price = wcpa_get_price_shop($this->product, array(
                                'qty' => 1,
                                'price' => $price
                            ));
                            if (($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false)) {
                                $label = $val->label;
                            } else {
                                $label = $val->label . ' ' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true, $v), 1));
                            }


                        }
                    }

                    $price_data = $this->get_price_data($v, $val);
                }
                $image_data = $this->get_pimage_data($v, $val);
                echo '<div class="wcpa_checkbox">
                    <input  ' . $image_data . '  name="' . $name . '[' . $k . ']" ' . $option_class . ' id="' . $name . '_' . $wcpa_field_counter . '_' . $k . '" value="' . $val->value . '" type="checkbox" ' . $chkd . ' ' . $price_data . ' ' . $chk_required . '" >
                            
                    <label for="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"><span class="wcpa_check"></span>' . $label . '</label>';

                if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                    echo '<span class="wcpa_tooltip_icon" >?';
                    echo '<span class="wcpa_tooltip">' . $val->tooltip . '</span>';
                    echo '</span>';
                }
                echo '</div>';
                $chk_required = '';
            }
            if (isset($v->other) && $v->other) {
                $option_class = '';
                $price_data = false;
                if (isset($v->other_text) && !empty($v->other_text)) {
                    $label = $v->other_text;
                } else {
                    $label = __('Other', 'wcpa-text-domain');
                }

                if (isset($v->enablePrice) && $v->enablePrice && $v->priceOptions == 'fixed_for_all') {

                    $price = $v->price;
                    $option_class .= 'wcpa_has_price ';
                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price
                    ));
                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        $label = $label . '(' . wcpa_price($price * $this->get_con_unit(true), 1) . ')';
                    }
                    $price_data = $this->get_price_data($v);
                }
                $checked = '';
                $other_val = '';
                $is_selected = isset($default_value['other']) ? true : false;

                if ($is_selected) {
                    $checked = 'checked="checked"';
                    $other_val = str_replace('WCPAOTH ', '', $default_value['other']);
                }

                //$option_class key used for adding .wcpa_has_price class
                $option_class .= 'wcpa_other';
                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                echo '<div class="wcpa_checkbox">
                    <input name="' . $name . '[other]" ' . $option_class . '" '
                    . 'id="' . $name . '_' . $wcpa_field_counter . '_other" value="' . (($other_val == '') ? '' : 'WCPAOTH ' . $other_val) . '" type="checkbox"  ' . $price_data . ' ' . $checked . '>
                    <label for="' . $name . '_' . $wcpa_field_counter . '_other"><span class="wcpa_check"></span>' . $label . '</label>
                        <input type="text" name="' . $name . '_other_val" class="wcpa_other_val" value="' . $other_val . '" >
                    </div>';
            }
            if (isset($v->enableClearSelection) && $v->enableClearSelection) {
                echo '<a href="#" class="wcpa_clearSelection" data-element="' . $v->elementId . '">' . __('Clear selection', 'wcpa-text-domain') . '</a>';

            }
            echo '</div>';
        }
        $wcpa_field_counter++;
    }

    public function field_option_price($price)
    {
        return str_replace('price', $price, wcpa_get_option('field_option_price_format', '(price)'));
    }

    public function get_pimage_data($v, $val)
    {
        $image_data = '';

        if (isset($v->enable_product_image) && $v->enable_product_image) {
            if (isset($val->pimage_id) && $val->pimage_id > 0) {
                $image_data = 'data-image=\'' . json_encode(wc_get_product_attachment_props($val->pimage_id) + ['image_id' => $val->pimage_id]) . '\'';
            } else if ($val->pimage) {
                $props = [
                    'title' => $val->label,
                    'caption' => '',
                    'url' => $val->pimage,
                    'alt' => $val->label,
                    'src' => $val->pimage,
                    'srcset' => false,
                    'sizes' => false,
                    'src_w' => '',

                    'full_src' => $val->pimage,
                    'full_src_w' => '',
                    'full_src_h' => '',
                    'gallery_thumbnail_src' => $val->pimage,
                ];

                $image_data = 'data-image=\'' . json_encode($props) . '\'';
            }

        }

        return $image_data;

    }

    public function render_radio($v)
    {
        global $wcpa_field_counter;
        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);
        $default_value = $this->default_value($v);
        $chk_required = (isset($v->required)) ? 'required="required"' : '';
        $className = 'radio-group ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }

        if (isset($v->inline) && $v->inline) {
            $className .= 'radio-inline ';
        }
        if (isset($v->required) && $v->required) {
            $className .= 'wcpa_required ';
        }

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        $price_data = '';
        $is_field_checked = false;
        if (!wcpa_empty($default_value) && $default_value !== false) {
            $chk_required = '';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->other) && $v->other) {
            $validation_array['other'] = $v->other;
            $validation_array['otherRequiredMessage'] =  wcpa_get_validation_message('otherFieldError', $v);
        }
        $validation .=  json_encode($validation_array) . '\'';

        if (isset($v->enable_product_image) && $v->enable_product_image) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }

        if ($v->values && !empty($v->values)) {
            echo '<div ' . $className . ' ' . $validation . ' >';
            foreach ($v->values as $k => $val) {
                if (is_array($default_value)) {
                    $is_selected = (isset($default_value[$k]) && $default_value[$k] !== false) ? true : false;
                } else {
                    $is_selected = ($default_value === $this->sanitize_values($val->value)) ? true : false;
                }

                if (!$is_field_checked && $is_selected) {
                    $is_field_checked = true;
                }
                $price = '';
                $option_class = '';
                if (isset($v->enablePrice) && $v->enablePrice) {
                    $option_class .= 'wcpa_has_price ';
                }

                if (isset($v->enable_product_image) && $v->enable_product_image) {
                    $option_class .= 'wcpa_update_product_image ';
                }

                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $label = $val->label;
                if (isset($v->enablePrice) && $v->enablePrice) {

                    if ((isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false) && (
                            $v->pricingType === 'fixed' || $v->pricingType === 'custom'
                        )) {


                        if ($v->priceOptions == 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions == 'fixed_for_all') {
                            $price = $v->price;
                        }
                        if (!wcpa_price_is_numeric($price)) {
                            $price = false;
                        }
                        if ($price !== false) {
                            $price = wcpa_get_price_shop($this->product, array(
                                'qty' => 1,
                                'price' => $price
                            ));

                            if (($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false)) {
                                $label = $val->label;
                            } else {
                                $label = $val->label . ' ' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true), 1));
                            }


                        }
                    }

                    $price_data = $this->get_price_data($v, $val);
                }

                $image_data = $this->get_pimage_data($v, $val);

                echo ' <div class="wcpa_radio">
                    <input name="' . $name . '" ' . $image_data . ' ' . $option_class . ' id="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"'
                    . ' value="' . htmlspecialchars($val->value) . '" ' . $price_data . '  type="radio" ' . (($is_selected !== false) ? 'checked="checked"' : '') . ' ' . $chk_required . ' >
                    <label for="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"><span class="wcpa_check"></span>' . $label . '</label>';
                if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                    echo '<span class="wcpa_tooltip_icon" >?';
                    echo '<span class="wcpa_tooltip">' . $val->tooltip . '</span>';
                    echo '</span>';
                }
                echo '</div>';
                $chk_required = ''; //requied need to set only for first item
            }
            if (isset($v->other) && $v->other) {
                $option_class = '';

                if (isset($v->other_text) && !empty($v->other_text)) {
                    $label = $v->other_text;
                } else {
                    $label = __('Other', 'wcpa-text-domain');
                }

                if (isset($v->enablePrice) && $v->enablePrice && $v->priceOptions == 'fixed_for_all') {
                    $price = $v->price;
                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price
                    ));
                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        $label = $label . '(' . wcpa_price($price * $this->get_con_unit(true), 1) . ')';
                    }
                    $price_data = $this->get_price_data($v);
                    $option_class .= 'wcpa_has_price ';
                } else {
                    $price_data = '';
                }
                $checked = '';
                $other_val = '';

                if (!wcpa_empty($default_value) && !$is_field_checked) {
                    $is_selected = true;
                } else {
                    $is_selected = false;
                }


                if ($is_selected === true) {
                    $checked = 'checked="checked"';
                    $other_val = str_replace('WCPAOTH ', '', $default_value);
                }

                //$option_class key used for adding .wcpa_has_price class
                $option_class .= 'wcpa_other';
                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }

                echo ' <div class="wcpa_radio">
                    <input name="' . $name . '" ' . $option_class . ' id="' . $name . '_' . $wcpa_field_counter . '_other" '
                    . ' ' . $price_data . ' '
                    . 'value="' . (($other_val === '') ? '' : 'WCPAOTH ' . $other_val) . '" type="radio"  ' . $checked . '>
                    <label for="' . $name . '_' . $wcpa_field_counter . '_other"><span class="wcpa_check"></span>' . $label . '</label>
                        
                 <input type="text" name="' . $name . '_other_val" class="wcpa_other_val" value="' . $other_val . '" >

                    </div>';
            }
            if (isset($v->enableClearSelection) && $v->enableClearSelection) {
                echo '<a href="#" class="wcpa_clearSelection" data-element="' . $v->elementId . '">' . __('Clear selection', 'wcpa-text-domain') . '</a>';

            }
            echo '</div>';
        }
        $wcpa_field_counter++;
    }

    public function render_header($v)
    {

        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }


        if ($v->subtype == 'h1') {
            echo '<h1 ' . $className . '>' . $v->label . '</h1>';
        } else if ($v->subtype == 'h2') {
            echo '<h2 ' . $className . '>' . $v->label . '</h2>';
        } else if ($v->subtype == 'h3') {
            echo '<h3 ' . $className . '>' . $v->label . '</h3>';
        }
    }

    public function render_file($v)
    {
        $data = '';
        $maxlength = '';
        $txt_required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? $v->placeholder : '';
        $ajax_upload = isset($v->ajax_upload) && $v->ajax_upload;
        $className = ''; $max_file_count = '';
        $multiple_enabled = isset($v->multiple_upload) && $v->multiple_upload;
        $droppable = isset($v->droppable) && $v->droppable;
        
        $multipleFile = ''; $multiple_fileName_postfix = '';
        $multipleClass = ''; $DropContainerClass = 'wcpa_single_upload';
        if($multiple_enabled){
            $multipleFile = 'multiple';
            $multiple_fileName_postfix = '[]';
            $multipleClass = 'wcpa_multiple';
            $DropContainerClass = 'wcpa_multiple_upload';
            if(isset($v->max_file_count) && !empty($v->max_file_count)){
                $max_file_count = ' data-maxFiles=\''.$v->max_file_count.'\' '; 
            }
        }
        //if(isset($v->showUploadPreview) && $v->showUploadPreview) {
            $DropContainerClass .= ' wcpa_drop_preview';
        //}

        if($droppable && $ajax_upload ){
            $className .= 'wcpa_drag_file_input ';
        }

        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if($multipleClass) {
            $className .= $multipleClass.' ';
        }


        $name = (isset($v->name)) ? $v->name : '';
        $ajax_upload_data = '';
        $custom_style = isset($v->custom_style) && $v->custom_style;
        if ($ajax_upload) {
            if($droppable){
                $className .= 'wcpa_ajax_upload ';
            } else {
                $className .= 'wcpa_ajax_upload wcpa_file_custom ';
            }
            $ajax_upload_data = 'data-details=\'' . json_encode([
                    'element_id' => $v->elementId,
                    'name' => $name,
                    'product_id' => $this->product->get_id(),
                    'required' => (isset($v->required)) ? true : false
                ]) . '\'';
        } else if ($custom_style) {
            $className .= 'wcpa_file_custom ';
        } else {
            $className .= 'wcpa_file_default ';
        }

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        //        if ($v->label) {
        //            echo '<label for="' . $name . '">' . $v->label;
        //            if (isset($v->required) && $v->required) {
        //                echo '<span class="required_ast">*</span>';
        //            }
        //            echo '</label>';
        //        }
        $this->label_section($v);
        if (isset($v->maxlength)) {
            $maxlength = 'maxlength="' . $v->maxlength . '"';
        }

        $validation='';
        if (
            (isset($v->required) && $v->required) ||
            (isset($v->max_file_count) && !empty($v->max_file_count)) ||
            (isset($v->minuploadsize) && !empty($v->minuploadsize)) ||
            (isset($v->uploadsize) && !empty($v->uploadsize)) ||
            (isset($v->exts_supported) && !empty($v->exts_supported))
        ) {
            $validation_array= array();
            $validation = 'data-validation=\'';
            if (isset($v->required) && $v->required) {
                $validation_array['required'] = true;
                $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
            }
            if (isset($v->max_file_count) && !empty($v->max_file_count)) {
                $validation_array['maxFileCount'] = $v->max_file_count;
                $validation_array['maxFileCountMessage'] =  wcpa_get_validation_message('maxFileCountError', $v, $v->max_file_count);
            }
            if (isset($v->uploadsize) && !empty($v->uploadsize)) {
                $validation_array['maxFileSize'] = $v->uploadsize * 1024 * 1024;
                $validation_array['maxFileSizeMessage'] =  wcpa_get_validation_message('maxFileSizeError', $v, wcpa_formatBytes($v->uploadsize * 1024 *1024));
            }
            if (isset($v->minuploadsize) && !empty($v->minuploadsize)) {
                $validation_array['minFileSize'] = $v->minuploadsize * 1024 * 1024;
                $validation_array['minFileSizeMessage'] =  wcpa_get_validation_message('minFileSizeError', $v, wcpa_formatBytes($v->minuploadsize * 1024 *1024));
            }
            if (isset($v->exts_supported) && !empty($v->exts_supported)) {
                $validation_array['extensionSupportEnable'] = true;
                $validation_array['extensions'] = trim($v->exts_supported);
                $validation_array['extensionsInvalidMessage'] = wcpa_get_validation_message('fileExtensionError', $v, str_replace(',', ', ', trim($v->exts_supported)));
            }
            $validation .=  json_encode($validation_array) . '\'';
        }


        if ($ajax_upload) {
            $default_value = $this->default_value($v);
            if (!empty($default_value)) {
                $txt_required = '';
                $temp = explode('||', $default_value);
                $placeholder = $temp[1];
            }
            // Adding Drag And Drop Feature
            if($droppable){
                if($placeholder==''){
                    $placeholder = __('Drag And Drop Files Here', 'wcpa-text-domain');
                }
                $action_text = wcpa_get_option('file_droppable_action_text', __( 'Browse', 'wcpa-text-domain' ));
                $drop_desc = str_replace('{action}', '%s', wcpa_get_option('file_droppable_desc_text', __( 'or %s to choose a file', 'wcpa-text-domain' )));

                echo '<div class="wcpa_drag_drop '.$DropContainerClass.'" ' . $ajax_upload_data . ' ' . $max_file_count . '>';
                echo '<input type="hidden" id="' . $name . '" '.$className.'  name="' . $name . '_ajax'.$multipleFile.'"  value="' . $default_value . '" ' . $maxlength . ' ' . $txt_required . ' ' . $price_data . ' ' . $validation . ' />';
                echo '<div class="wcpa-upload-area">';
                echo '<div class="upload-area-title-thumb-style"><div class="upload-area-title-thumb-inner"><span>'.$action_text.'</span></div></div>';
                echo '<div class="upload-area-title wcpa_center">'.$placeholder;
                echo '<div class="wcpa_drop_text">'. __('Drop', 'wcpa-text-domain') .'</div>';
                echo '<div class="upload-area-subtitle">'. str_replace('%s', '<span>' . $action_text . '</span>', $drop_desc) .'</div>';
                echo '</div></div>';
                echo '<div class="wcpa-upload-message"></div></div>';
            } else if($multiple_enabled){
                echo '<label class="wcpa_file_outer" for="' . $name . '"><span class="wcpa_file_name">' . $placeholder . '</span><span class="wcpa_file_wrap" >' . wcpa_get_option('file_button_text', __('Choose File', 'wcpa-text-domain'), true) . '<input type="file"  id="' . $name . '"  ' . $className . ' name="' . $name . '"  ' . $maxlength . ' ' . $txt_required . ' ' . $price_data . '  ' . $ajax_upload_data . ' ' . $multipleFile . ' ' . $max_file_count . ' /></span></label>';
                echo '<input type="hidden" class="wcpa_file_hidden"  name="' . $name . '_ajaxmultiple"  value="' . $default_value . '" ' . $validation . ' />';
                echo '<div class="wcpa_remove_file">'. __('Remove all', 'wcpa-text-domain') .'</div>';

                // Removed Preview DIV from the common and added here seperately to achieve progress bar visible
                $no_preview_class = (isset($v->showUploadPreview) && $v->showUploadPreview) ? '' : 'wcpa_no_preview_multiple';
                echo '<div class="wcpa_upload_preview '.$no_preview_class.'"></div>';
            } else {
                echo '<label class="wcpa_file_outer" for="' . $name . '"><span class="wcpa_file_name">' . $placeholder . '</span><span class="wcpa_file_wrap" >' . wcpa_get_option('file_button_text', __('Choose File', 'wcpa-text-domain'), true) . '<input type="file"  id="' . $name . '"  ' . $className . ' name="' . $name . '"  ' . $maxlength . ' ' . $txt_required . ' ' . $price_data . '  ' . $ajax_upload_data  . ' /></span></label>';
                echo '<input type="hidden" class="wcpa_file_hidden"  name="' . $name . '_ajax"  value="' . $default_value . '" ' . $validation . ' />';
                echo '<div class="wcpa_progress" style="' . (empty($default_value) ? 'width:0;' : 'width:100%;') . '" ><span>' . __('Remove', 'wcpa-text-domain') . '</span></div>';
            }
        } else if ($custom_style) {
            echo '<label class="wcpa_file_outer" for="' . $name . '"><span class="wcpa_file_name">' . $placeholder . '</span><span class="wcpa_file_wrap" >' . wcpa_get_option('file_button_text', __('Choose File', 'wcpa-text-domain'), true) . '<input type="file"  id="' . $name . '"  ' . $className . ' name="' . $name . $multiple_fileName_postfix . '"  ' . $maxlength . ' ' . $txt_required . ' ' . $price_data . ' '.$multipleFile.' ' . $max_file_count . ' ' . $validation . ' /></span></label>';
        } else {
            echo '<input type="file"  id="' . $name . '" ' . $placeholder . ' ' . $className . ' name="' . $name . $multiple_fileName_postfix . '"  ' . $maxlength . ' ' . $txt_required . ' ' . $price_data . '  ' . $ajax_upload_data . ' '.$multipleFile.' ' . $max_file_count . ' ' . $validation . ' />';
        }
        if (
            isset($v->showUploadPreview) && 
            $v->showUploadPreview && 
            !( $droppable ) &&
            !($ajax_upload && $multiple_enabled)
        ) {
            echo '<div class="wcpa_upload_preview"></div>';
        }
    }

    public function render_paragraph($v)
    {

        $className = 'wcpa_paragraph_block ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }

        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
        }


        $price_data = $this->get_price_data($v);
        $formulaLabel = '';
        $label = do_shortcode(nl2br($v->label));
        if (preg_match('/\#\=(.+?)\=\#/', $v->label) === 1) {
            $formulaLabel = 'data-label=\'' . htmlspecialchars(json_encode(['label' => $label]), ENT_QUOTES) . '\'';
            $className .= 'wcpa_has_label_formula wcpa_hide ';
        }


        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        if ($v->subtype == 'div') {
            echo '<div ' . $formulaLabel . ' ' . $className . ' ' . $price_data . '>' . $label . '</div>';
        } else if ($v->subtype == 'address') {
            echo '<address ' . $formulaLabel . '  ' . $className . ' ' . $price_data . '>' . $label . '</address>';
        } else if ($v->subtype == 'blockquote') {
            echo '<blockquote ' . $formulaLabel . ' ' . $className . ' ' . $price_data . '>' . $label . '</blockquote>';
        } else if ($v->subtype == 'output') {
            echo '<output ' . $formulaLabel . ' ' . $className . ' ' . $price_data . '>' . $label . '</output>';
        } else if ($v->subtype == 'canvas') {
            echo '<canvas ' . $formulaLabel . ' ' . $className . ' ' . $price_data . '>' . $label . '</canvas>';
        } else {
            echo '<p ' . $formulaLabel . ' ' . $className . ' ' . $price_data . '>' . $label . '</p>';
        }
    }

    
    public function render_statictext($v)
    {

        $className = 'wcpa_statictext_block ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }

        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
        }

        $price_data = $this->get_price_data($v);
        $formulaValue = '';
        $value = isset($v->value) ? do_shortcode(nl2br($v->value)) : '';
        if($value){
            if (preg_match('/\#\=(.+?)\=\#/', $v->value) === 1) {
                $formulaValue = 'data-value=\'' . htmlspecialchars(json_encode(['value' => $value]), ENT_QUOTES) . '\'';
                $className .= 'wcpa_has_value_formula wcpa_hide ';
            }
        }


        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        if(isset($v->inline) && !$v->inline) {
            $this->label_section($v);
            echo '<p ' . $formulaValue . ' ' . $className . ' ' . $price_data . '>' . $value . '</p>';
        } else {
            $label ='';
            if(isset($v->label)) {
                $label = '<span class="wcpa_' . $v->type . '_label">' . $v->label . '</span>';
            }
            echo '<p ' . $formulaValue . ' ' . $className . ' ' . $price_data . '>'.$label. '<span class="wcpa_' . $v->type . '_label_separator"> : </span>' . $value . '</p>';
        }
    }


    public function render_productGroup($v) {
        global $wcpa_field_counter;
        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);
        $chk_required = (isset($v->required)) ? 'required="required"' : '';
        $className = 'wcpa_productGroup ';
        $check_type = "radio";
        
        if ((isset($v->multiple) && $v->multiple)) {
            $is_multiple = true;
            $check_type = "checkbox";
            $className .= 'wcpa_multiselect ';
        } else {
            $is_multiple = false;
        }

        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->required) && $v->required) {
            $className .= 'wcpa_required ';
        }
        if(isset($v->show_image) && $v->show_image){
            if (isset($v->img_selection_type) && $v->img_selection_type) {
                $className .= 'wcpa_product_sel_type_' . $v->img_selection_type . ' ';
            } else {
                $className .= 'wcpa_product_sel_type_outline ';
            }
            if (isset($v->disp_type) && $v->disp_type) {
                $className .= 'wcpa_product_disp_type_' . $v->disp_type . ' ';
            } else {
                $className .= 'wcpa_product_disp_type_default ';
            }
            if (isset($v->img_preview) && $v->img_preview) {
                $className .= 'wcpa_product_img_preview ';
                if(isset($v->img_preview_disable_mobile) && $v->img_preview_disable_mobile) {
                    $className .= 'wcpa_product_img_preview_disable_mobile ';
                }
            }

            if (isset($v->enable_popup) && $v->enable_popup != 'no') {
                $className .= 'wcpa_product_enable_popup_' . $v->enable_popup . ' ';
            }
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation = '';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->min_options) && !empty($v->min_options)) {
            $validation_array['min'] = $v->min_options;
            $validation_array['minMessage'] =   wcpa_get_validation_message('minFieldsError', $v, $v->min_options);
        }
        if (isset($v->max_options) && !empty($v->max_options)) {
            $validation_array['max'] = $v->max_options;
            $validation_array['maxMessage'] =  wcpa_get_validation_message('maxFieldsError', $v, $v->max_options);
        }
        $validation_array['qty'] = true;
        $validation_array['qtyMessage'] =  wcpa_get_validation_message('quantityRequiredError', $v);

        $validation .=  json_encode($validation_array) . '\'';

        $default_value = $this->default_value($v);

        $img_size_style = ((isset($v->disp_size_img) && $v->disp_size_img > 0) ? 'style="width:' . $v->disp_size_img . 'px"' : '');

        if (!wcpa_empty($default_value) && $default_value !== false) {
            $chk_required = '';
        }
        $product_ids = array();
        if ($v->values && !empty($v->values)) {
            foreach ($v->values as $k => $val) {
                if(isset($val) && !empty($val)){
                    $product_ids[] = intval($val->value);
                }
            }
        }
        $new_product_array = array();
        if(!empty($product_ids)){
            $args = array(
                'post_status' => 'publish',
                'include' => $product_ids,
                'posts_per_page' => -1,
            );
            
            $product_array = wc_get_products($args);
            // Reorder $product_array
            foreach($product_array as $val) {
                if (false !== $key = array_search($val->get_id(), $product_ids)) {
                    $new_product_array[$key] = $val;
                }
            }
            ksort($new_product_array);
        }


        if ($v->values && !empty($v->values)) {
            echo '<div ' . $className . ' ' . $validation . '>';
            foreach ($v->values as $k => $val) {
                $val_error = false;
                $val_msg = '';
                if(
                    !empty($new_product_array) && $val &&
                    !empty($val->value) && isset($val->value)
                    && isset($new_product_array[$k]) && !empty($new_product_array[$k])
                ){
                    $addon_product = $new_product_array[$k];
                    $validation = wcpa_validate_product_field($addon_product);
                    if($validation['error']) {
                        $val_msg = $validation['message'];
                        $val_error = true;
                    }
        
                    if(!$val_error){
                        $prod_price = $addon_product->get_price('edit');

                        $is_selected = isset($default_value[$k]) && $default_value[$k] !== false;

                        $chkd = ($is_selected) ? 'checked="checked"' : '';
                        $disabled = (!$is_selected) ? 'disabled="disabled"' : '';
                        $price = '';
                        $option_class = '';
                        if (isset($v->enablePrice) && $v->enablePrice) {
                            $option_class .= 'wcpa_has_price ';
                        }

                        if (!empty($option_class)) {
                            $option_class = 'class="' . $option_class . '"';
                        }
                        $label = $addon_product->get_title();
                        if (isset($v->enablePrice) && $v->enablePrice) {
                            if ((isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                                if ($v->priceOptions == 'different_for_all') {
                                    $price = $val->price;
                                } else if ($v->priceOptions == 'fixed_for_all') {
                                    $price = $v->price;
                                } else if($v->priceOptions == 'product_price'){
                                    $price = $prod_price;
                                }
                                if(
                                    $v->pricingType == 'percentage' && 
                                    $v->priceOptions !== 'product_price'
                                ) {
                                    $price = $price * $prod_price /100;
                                }
                                if (!wcpa_price_is_numeric($price)) {
                                    $price = false;
                                }
                                if ($price !== false) {
                                    $price = wcpa_get_price_shop($this->product, array(
                                        'qty' => 1,
                                        'price' => $price
                                    ));
                                    if (!(($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false))) {
                                        $label = $label . ' ' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true, $v), 1));
                                    }
                                }
                                
                            }
                            $price_data = $this->get_price_data($v, $val, $addon_product);
                        } 

                        $pro_image = '';
                        $pro_image_src = '';
                        $option_cont_class = 'wcpa_productGroup_item';
                        if(isset($v->show_image) && $v->show_image){
                            if ( $addon_product->get_image_id() ) {
                            $pro_image = wp_get_attachment_url( $addon_product->get_image_id() );
                            } 
                        
                            if ( ! $pro_image ) {
                            $pro_image = wc_placeholder_img_src( 'woocommerce_thumbnail' );
                            }

                            if($pro_image !== ''){
                                $img_wrapper = false;
                                if (isset($v->enable_popup) && $v->enable_popup != 'no') {
                                    $img_wrapper = true;
                                }
                                if ($img_wrapper) {
                                    $pro_image_src .= '<div class="wcpa_image_wrap">';
                                }
                                $pro_image_src .= '<img src="'.$pro_image.'" '.$img_size_style.' data-src="' . $pro_image . '">
                                <span class="wcpa_img_mag" data-src="' . $pro_image . '" data-title="' .$label. '">+</span>';
                                $option_cont_class .= ' wcpa_productGroup_option_img';
                                if ($img_wrapper) {
                                    $pro_image_src .= '</div>';
                                }
                            } 
                        } else {
                            $option_cont_class .= ' wcpa_'.$check_type;
                        }
                        
                        echo '<div class="'.$option_cont_class.'">
                            <input  name="' . $name . (($is_multiple) ? '['.$k.']': '').'"'. $option_class . ' id="' . $name . '_' . $wcpa_field_counter . '_' . $k . '" value="' . $val->value . '" type="'.$check_type.'" ' . $chkd . ' ' . $price_data . ' ' . $chk_required . ' >
                                    
                            <label for="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"><span class="wcpa_check"></span>'.$pro_image_src. $label . '</label>';

                        if (isset($v->enable_quantity) && $v->enable_quantity) {
                            echo '<input type="number" min="1" step="1" value="1" class="wcpa_product_quantity" name="' . $name . '_quantity['.$k.']" '.$disabled.'>';
                        }
                        if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                            echo '<span class="wcpa_tooltip_icon" >?';
                            echo '<span class="wcpa_tooltip">' . $val->tooltip . '</span>';
                            echo '</span>';
                        }
                        echo '</div>';
                        $chk_required = '';
                    } else {
                        if(current_user_can( 'manage_options' )) {
                            echo '<div class="wcpa_product_error">'.$val_msg.'</div>';
                        }
                    }
                } else {
                    $val_msg = __( 'Sorry something happened, Product Id mentioned may be wrong/repeated.', 'wcpa-text-domain' );
                    if(current_user_can( 'manage_options' )) {
                        echo '<div class="wcpa_product_error">'.$val_msg.'</div>';
                    }
                }
            }
            
            if (isset($v->enableClearSelection) && $v->enableClearSelection) {
                echo '<a href="#" class="wcpa_clearSelection" data-element="' . $v->elementId . '">' . __('Clear selection', 'wcpa-text-domain') . '</a>';
            }
            echo '</div>';
        }
        $wcpa_field_counter++;
    }


    public function render_separator($v)
    {

        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        $style = '';
        if (isset($v->height) && $v->height > 0) {
            $style .= 'style="height:' . $v->height . 'px"';
        }
        echo '<div ' . $className . ' ' . $style . '>&nbsp;</div>';
    }

    public function render_select($v)
    {

        if ((isset($v->multiple) && $v->multiple)) {
            $is_multiple = true;
        } else {
            $is_multiple = false;
        }
        $name = (isset($v->name)) ? $v->name : '';
        if ($is_multiple) {
            $name = $name . '[]';
        }

        $individual_sumo_enable = false;
        if(isset($v->enable_sumo) && !empty($v->enable_sumo) && $v->enable_sumo){
            $individual_sumo_enable =true;
        }

        $sumo = wcpa_get_option('use_sumo_selector', false);
        if (($sumo && $is_multiple) || $individual_sumo_enable ) {
            wp_enqueue_style($this->_token . '-sumoselector');
            wp_enqueue_script($this->_token . '-sumoselector');
        }

        $data = '';
        $default_value = $this->default_value($v);

        $sel_required = (isset($v->required)) ? 'required="required"' : '';
        $multiple = ($is_multiple) ? 'multiple="true"' : '';

        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
        }
        if (isset($v->enable_product_image) && $v->enable_product_image) {
            $className .= 'wcpa_update_product_image ';
            wp_enqueue_script('wc-add-to-cart-variation');
        }
        
        if($individual_sumo_enable) {
            $className .= 'wcpa_use_sumo_individual ';
        } else if ($sumo) {
            $className .= 'wcpa_use_sumo ';
        }
        if ($is_multiple) {
            $className .= 'wcpa_multiple ';
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $validation='';
        if (isset($v->required) && $v->required) {
            $validation_array= array();
            $validation = 'data-validation=\'';
            if (isset($v->required) && $v->required) {
                $validation_array['required'] = true;
                $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
            }
            $validation .=  json_encode($validation_array) . '\'';
        }

        $this->label_section($v);


        if ($v->values && !empty($v->values)) {
            $sumoPlaceHolder = '';
            if ($sumo && isset($v->placeholder) && $v->placeholder != '') {
                $sumoPlaceHolder = $v->placeholder;
                $sumoPlaceHolder = 'data-placeholder="' . $sumoPlaceHolder . '"';
            }
            echo '<div class="select" ><select ' . $sumoPlaceHolder . '   name="' . $name . '" ' . $className . ' ' . $multiple . ' ' . $sel_required . ' ' . $validation . '>';
            if ((!$is_multiple || !$sumo) && isset($v->placeholder) && $v->placeholder != '') {

                echo '<option value="" >' . $v->placeholder . '</option>';
            }

            foreach ($v->values as $k => $val) {
                $label = $val->label;
                if (isset($v->enablePrice) && $v->enablePrice) {
                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        if ($v->priceOptions === 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions === 'fixed_for_all') {
                            $price = $v->price;
                        }
                        $price = wcpa_get_price_shop($this->product, array(
                            'qty' => 1,
                            'price' => $price
                        ));
                        if (($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false)) {
                            $label = $val->label;
                        } else {
                            $label = $val->label . ' ' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true), 1));
                        }


                    }

                    $price_data = $this->get_price_data($v, $val);
                }

                $image_data = $this->get_pimage_data($v, $val);

                if (is_array($default_value)) {
                    $is_selected = in_array($val->value, $default_value);
                } else {
                    $is_selected = $default_value === $this->sanitize_values($val->value);
                }

                // $selectedchk = ($is_selected) ? 'selected="selected"' : '';
                // $selectedchk = ($val->selected)?'selected="selected"':'';

                $selectedchk = ($is_selected) ? 'selected="selected"' : '';


                $option_print = '<option ' . $image_data . ' ' . $price_data . ' value="' . htmlspecialchars($val->value) . '" ' . $selectedchk . '>' . $label . '</option>';
                $option_print = apply_filters('wcpa_select_option_print', $option_print, $label, $val->value);
                echo $option_print;
            }
            echo '</select>';

            if (!$is_multiple || $sumo) {
                echo '<div class="select_arrow"></div>';
            }
            echo '</div>';
        }
    }

    public function render_textarea($v)
    {
        $maxlength = '';
        $maxlength = (isset($v->maxlength)) ? 'maxlength="' . $v->maxlength . '"' : '';
        $minlength = (isset($v->minlength)) ? 'minlength="' . $v->minlength . '"' : '';
        $title = (isset($v->title)) ? 'title="' . $v->title . '"' : '';
        $txtarea_required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';

        $name = (isset($v->name)) ? $v->name : '';
        $rows = (isset($v->rows)) ? 'rows="' . $v->rows . '"' : '';

        $className = '';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }

        $validation = '';
    
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->allowed_chars) && !empty($v->allowed_chars)) {
            $validation_array['allowed_chars_enable'] = true;
            $validation_array['allowed_chars'] = htmlspecialchars($v->allowed_chars, ENT_QUOTES);
            $validation_array['allowed_charsMessage'] = wcpa_get_validation_message('allowedCharsError', $v);
        }
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->maxlength) && !empty($v->maxlength)) {
            $validation_array['maxlength'] = $v->maxlength;
            $validation_array['maxlengthMessage'] = htmlspecialchars(wcpa_get_validation_message('maxlengthError', $v, $v->maxlength), ENT_QUOTES);
        }
        if (isset($v->minlength) && !empty($v->minlength)) {
            $validation_array['minlength'] = $v->minlength;
            $validation_array['minlengthMessage'] =  htmlspecialchars(wcpa_get_validation_message('minlengthError', $v, $v->minlength), ENT_QUOTES);
        }
        if (isset($v->charleft) && $v->charleft) {
            $validation_array['charleft'] = $v->charleft;
            $validation_array['charleftMessage'] =  htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v), ENT_QUOTES);
        }
        $validation .=  json_encode($validation_array) . '\'';

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }


        $this->label_section($v);

        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }

        echo '<textarea ' . ((isset($v->disable_manual_input) && $v->disable_manual_input) ? 'readonly="readonly"' : '') . ' ' . $validation . ' id="' . $name . '"  ' . $rows . ' ' . $placeholder . ' ' . $className . ' name="' . $name . '" ' . $maxlength . ' ' . $minlength . ' ' . $title . ' ' . $txtarea_required . ' ' . $price_data . ' >' . $this->default_value($v) . '</textarea>';
        
        
        if (isset($v->charleft) && $v->charleft && isset($v->maxlength) && !empty($v->maxlength)) {
            $char_count = (int)$v->maxlength - strlen($this->default_value($v));
            if($char_count >= 0) {
                echo '<div class="wcpa_char_left_message"><p>'.
                        htmlspecialchars(wcpa_get_validation_message('charleftMessage', $v, $char_count), ENT_QUOTES)
                    .'</p></div>';
            }
        }
    }

    public function render_image($v)
    {
        global $wcpa_field_counter;
        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);
        if ((isset($v->multiple) && $v->multiple)) {
            $is_multiple = true;
        } else {
            $is_multiple = false;
        }

        $validation='';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->min_options) && !empty($v->min_options)) {
            $validation_array['min'] = $v->min_options;
            $validation_array['minMessage'] =  wcpa_get_validation_message('minFieldsError', $v, $v->min_options);
        }
        if (isset($v->max_options) && !empty($v->max_options)) {
            $validation_array['max'] = $v->max_options;
            $validation_array['maxMessage'] =  wcpa_get_validation_message('maxFieldsError', $v, $v->max_options);
        }
        if (isset($v->other) && $v->other) {
            $validation_array['other'] = $v->other;
            $validation_array['otherRequiredMessage'] =  wcpa_get_validation_message('otherFieldError', $v);
        }
        $validation .=  json_encode($validation_array) . '\'';

        $className = 'image-group ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        if (isset($v->inline) && $v->inline) {
            $className .= 'image-inline ';
        }
        $check_type = 'radio';
        if ($is_multiple) {
            $className .= 'wcpa_multiselect ';
            $check_type = 'checkbox';
        }
        if (isset($v->required) && $v->required) {
            $className .= 'wcpa_required ';
        }
        if (isset($v->img_selection_type) && $v->img_selection_type) {
            $className .= 'wcpa_sel_type_' . $v->img_selection_type . ' ';
        } else {
            $className .= 'wcpa_sel_type_outline ';
        }

        if (isset($v->disp_type) && $v->disp_type) {
            $className .= 'wcpa_disp_type_' . $v->disp_type . ' ';
        } else {
            $className .= 'wcpa_disp_type_default ';
        }
        if (isset($v->img_preview) && $v->img_preview) {
            $className .= 'wcpa_img_preview ';
            if(isset($v->img_preview_disable_mobile) && $v->img_preview_disable_mobile) {
                $className .= 'wcpa_product_img_preview_disable_mobile ';
            }
        }

        if (isset($v->enable_popup) && $v->enable_popup != 'no') {
            $className .= 'wcpa_enable_popup_' . $v->enable_popup . ' ';
        }


        $chk_required = (isset($v->required)) ? 'required="required"' : '';

        $price_data = '';

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }


        $default_value = $this->default_value($v);

        if (!wcpa_empty($default_value) && $default_value !== false) {
            $chk_required = '';
        }

        $is_field_checked = false;
        if (isset($v->show_as_product_image) && $v->show_as_product_image) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }

        if ($v->values && !empty($v->values)) {
            echo '<div ' . $className . ' ' . $validation . '>';
            foreach ($v->values as $k => $val) {

                if (is_array($default_value)) {
                    $is_selected = (isset($default_value[$k]) && $default_value[$k] !== false) ? true : false;
                } else {
                    $is_selected = ($default_value === (string)$k) ? true : false;
                }

                if (!$is_field_checked && $is_selected) {
                    $is_field_checked = true;
                }
                $checked = ($is_selected !== false) ? 'checked="checked"' : '';
                $price = '';

                $option_class = ' ';
                $image_data = '';
                if (isset($v->enablePrice) && $v->enablePrice) {
                    $option_class .= 'wcpa_has_price ';
                }
                if (isset($v->show_as_product_image) && $v->show_as_product_image) {
                    $option_class .= 'wcpa_update_product_image ';

                    if (isset($val->image_id) && $val->image_id > 0) {
                        $image_data = 'data-image=\'' . json_encode(wc_get_product_attachment_props($val->image_id) + ['image_id' => $val->image_id]) . '\'';
                    } else {
                        $props = [
                            'title' => $val->label,
                            'caption' => '',
                            'url' => $val->image,
                            'alt' => $val->label,
                            'src' => $val->image,
                            'srcset' => false,
                            'sizes' => false,
                            'src_w' => '',
                            'full_src' => $val->image,
                            'full_src_w' => '',
                            'full_src_h' => '',
                            'gallery_thumbnail_src' => $val->image,
                        ];


                        $image_data = 'data-image=\'' . json_encode($props) . '\'';
                    }


                }

                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $label = $val->label;
                if (isset($v->enablePrice) && $v->enablePrice) {

                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        if ($v->priceOptions === 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions === 'fixed_for_all') {
                            $price = $v->price;
                        }
                        $price = wcpa_get_price_shop($this->product, array(
                            'qty' => 1,
                            'price' => $price
                        ));

                        if (($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false)) {
                            $label = $val->label;
                        } else {
                            $label = $val->label . ' <span class="wcpa_opt_price">' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true), 1)) . '</span>';
                        }

                    }
                    $price_data = $this->get_price_data($v, $val);
                }
                $field_name = (($check_type === 'radio') ? $name : $name . '[' . $k . ']');
                $img_size_style = ((isset($v->disp_size_img) && $v->disp_size_img > 0) ? 'style="width:' . $v->disp_size_img . 'px"' : '');
                echo '<div class="wcpa_image" >
                    <input type="' . $check_type . '" ' . $image_data . ' id="' . $name . '_' . $wcpa_field_counter . '_' . $k . '" ' . $checked . ' name="' . $field_name . '" value="' . $k . '" ' . $option_class . '  ' . $price_data . ' ' . $chk_required . ' >';
                $img_wrapper = false;
                if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                    $img_wrapper = true;
                }
                if (isset($v->enable_popup) && $v->enable_popup != 'no') {
                    $img_wrapper = true;
                }
                if ($img_wrapper) {
                    echo '<div class="wcpa_image_wrap">';
                }
                $img_src = $val->image;

                if (isset($val->image_id) && $val->image_id > 0 && (isset($v->disp_size_img) && $v->disp_size_img > 0)) {
                    $img_obj = wp_get_attachment_image_src($val->image_id, [$v->disp_size_img, 0]);
                    if ($img_obj) {
                        $img_src = $img_obj[0];
                    }

                }
                echo '<img src="' . $img_src . '" alt="' . $val->label . '" ' . $img_size_style . ' data-src="' . $val->image . '" attrfor="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"  />
                    <span class="wcpa_img_mag" data-src="' . $val->image . '" >+</span>';
                if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                    echo '<p class="wcpa_image_desc" >';
                    echo $val->tooltip;
                    echo '</p>';
                }
                if ($img_wrapper) {
                    echo '</div>';
                }
                echo '<label for="' . $name . '_' . $wcpa_field_counter . '_' . $k . '"  >' . $label . '</label>';

                echo '</div>';
                $chk_required = '';
            }
            if (isset($v->other) && $v->other) {
                $option_class = '';

                if (isset($v->other_text) && !empty($v->other_text)) {
                    $label = $v->other_text;
                } else {
                    $label = __('Other', 'wcpa-text-domain');
                }
                if (isset($v->enablePrice) && $v->enablePrice && $v->priceOptions == 'fixed_for_all') {
                    $price = $v->price;
                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price
                    ));
                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        $label = $label . '(' . wcpa_price($price * $this->get_con_unit(), 1) . ')';
                    }

                    $price_data = $this->get_price_data($v);
                    $option_class .= 'wcpa_has_price ';
                } else {
                    $price_data = '';
                }
                $checked = '';
                $other_val = '';

                if (!wcpa_empty($default_value) && !$is_field_checked) {
                    $is_selected = true;
                } else {
                    $is_selected = false;
                }

                // $is_selected = isset($default_value['other']) ? $default_value['other'] : false;

                if ($is_selected === true && !wcpa_empty($default_value)) {
                    $checked = 'checked="checked"';
                    if (is_array($default_value)) {
                        $other_val = str_replace('WCPAOTH ', '', $default_value['other']);
                    } else {
                        $other_val = str_replace('WCPAOTH ', '', $default_value);
                    }
                }

                //$option_class key used for adding .wcpa_has_price class
                $option_class .= 'wcpa_other';
                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $field_name = (($check_type == 'radio') ? $name : $name . '[other]');
                echo '<div class="wcpa_image_other">
                    <div class="wcpa_' . $check_type . '">
                    <input name="' . $field_name . '" ' . $option_class . ' 
                    id="' . $name . '_' . $wcpa_field_counter . '_other" value="' . (($other_val === '') ? '' : 'WCPAOTH ' . $other_val) . '"  type="' . $check_type . '"  ' . $price_data . ' ' . $checked . '>
                 <label for="' . $name . '_' . $wcpa_field_counter . '_other"><span class="wcpa_check"></span>' . $label . '</label>';

                echo '<input type="text" name="' . $name . '_other_val" class="wcpa_other_val" value="' . $other_val . '" >
                   </div> </div>';
            }
            echo '</div>';
        }
        $wcpa_field_counter++;
    }

    public function render_colorgroup($v)
    {
        global $wcpa_field_counter;
        $name = (isset($v->name)) ? $v->name : '';
        $this->label_section($v);
        if ((isset($v->multiple) && $v->multiple)) {
            $is_multiple = true;
        } else {
            $is_multiple = false;
        }

        $className = 'color-group ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        if (isset($v->inline) && $v->inline) {
            $className .= 'color-inline ';
        }
        $check_type = 'radio';
        if ($is_multiple) {
            $className .= 'wcpa_multiselect ';
            $check_type = 'checkbox';
        }
        if (isset($v->required) && $v->required) {
            $className .= 'wcpa_required ';
        }

        $chk_required = (isset($v->required)) ? 'required="required"' : '';

        if (isset($v->selection_type) && $v->selection_type) {
            $className .= 'wcpa_sel_type_' . $v->selection_type . ' ';
        }

        $price_data = '';

        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }

        $default_value = $this->default_value($v);

        if (!wcpa_empty($default_value) && $default_value !== false) {
            $chk_required = '';
        }
        $is_field_checked = false;

        $validation='';
        $validation_array= array();
        $validation = 'data-validation=\'';
        if (isset($v->required) && $v->required) {
            $validation_array['required'] = true;
            $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
        }
        if (isset($v->min_options) && !empty($v->min_options)) {
            $validation_array['min'] = $v->min_options;
            $validation_array['minMessage'] =  wcpa_get_validation_message('minFieldsError', $v, $v->min_options);
        }
        if (isset($v->max_options) && !empty($v->max_options)) {
            $validation_array['max'] = $v->max_options;
            $validation_array['maxMessage'] =  wcpa_get_validation_message('maxFieldsError', $v, $v->max_options);
        }
        if (isset($v->other) && $v->other) {
            $validation_array['other'] = $v->other;
            $validation_array['otherRequiredMessage'] =  wcpa_get_validation_message('otherFieldError', $v);
        }
        $validation .=  json_encode($validation_array) . '\'';

        if (isset($v->enable_product_image) && $v->enable_product_image) {
            wp_enqueue_script('wc-add-to-cart-variation');
        }

        if ($v->values && !empty($v->values)) {
            echo '<div ' . $className . ' ' . $validation . '>';
            $is_lable_empty = true;

            foreach ($v->values as $k => $val) {
                if ($val->label !== '') {
                    $is_lable_empty = false;
                    break;
                }
            }

            foreach ($v->values as $k => $val) {

                if (is_array($default_value)) {
                    $is_selected = (isset($default_value[$k]) && $default_value[$k] !== false) ? true : false;
                } else {
//                    $is_selected = ($default_value === (string) $k) ? true : false;
                    $is_selected = ($default_value === $this->sanitize_values($val->value)) ? true : false;
                }

                if (!$is_field_checked && $is_selected) {
                    $is_field_checked = true;
                }
                $checked = ($is_selected !== false) ? 'checked="checked"' : '';
                $price = '';

                $option_class = '';
                if (isset($v->enablePrice) && $v->enablePrice) {
                    $option_class .= 'wcpa_has_price ';
                }
                if (isset($v->enable_product_image) && $v->enable_product_image) {
                    $option_class .= 'wcpa_update_product_image ';
                }


                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $label = $val->label;
                if (isset($v->enablePrice) && $v->enablePrice) {

                    if ($v->pricingType === 'fixed' && (isset($v->form_rules['disp_hide_options_price']) && $v->form_rules['disp_hide_options_price'] === false)) {
                        if ($v->priceOptions === 'different_for_all') {
                            $price = $val->price;
                        } else if ($v->priceOptions === 'fixed_for_all') {
                            $price = $v->price;
                        }
                        $price = wcpa_get_price_shop($this->product, array(
                            'qty' => 1,
                            'price' => $price
                        ));

                        if (($price - 0) < 0.0000001 && wcpa_get_option('wcpa_hide_option_price_zero', false)) {
                            $label = $val->label;
                        } else {
                            $label = $val->label . ' ' . $this->field_option_price(wcpa_price($price * $this->get_con_unit(true), 1));
                        }

                    }
                    $price_data = $this->get_price_data($v, $val);
                }
                $field_name = (($check_type === 'radio') ? $name : $name . '[' . $k . ']');
                $size = '';
                if (isset($v->disp_size) && $v->disp_size > 10) {
                    $size .= 'height:' . $v->disp_size . 'px;';
                    if (isset($v->show_label_inside) && $v->show_label_inside) {
                        $size .= 'min-width:' . $v->disp_size . 'px;line-height:' . ($v->disp_size - 2) . 'px;';
                    } else {
                        $size .= 'width:' . $v->disp_size . 'px;';
                    }
                }

                $image_data = $this->get_pimage_data($v, $val);

                echo '<div class="wcpa_color" >
                        <input type="' . $check_type . '" ' . $image_data . '  id="' . $name . '_' . $wcpa_field_counter . '_' . $k . '" ' . $checked . ' name="' . $field_name . '" value="' . htmlspecialchars($val->value) . '" ' . $option_class . '  ' . $price_data . ' ' . $chk_required . ' >
                        <label  for="' . $name . '_' . $wcpa_field_counter . '_' . $k . '">';
                $chk_required = '';
                if (isset($v->show_label_inside) && $v->show_label_inside) {
                    if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                        echo '<div class="wcpa_color_wrap"><div>';
                    }
                    echo '<span class="label_inside disp_' . $v->disp_type . ' ' . wcpa_colorClass($val->color) . ' ' . ((isset($v->adjust_width) && $v->adjust_width) ? 'wcpa_adjustwidth' : '') . '"'
                        . 'style="background-color:' . $val->color . ';' . $size . '" title="' . $val->label . '"  >'
                        . '' . $label . '</span>';
                    if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                        echo '<p class="wcpa_color_desc" >';
                        echo $val->tooltip;
                        echo '</p></div></div>';
                    }
                } else {
                    if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                        echo '<div class="wcpa_color_wrap"><div>';
                    }
                    echo '<span class="disp_' . $v->disp_type . ' ' . wcpa_colorClass($val->color) . '  " style="background-color:' . $val->color . ';' . $size . '" title="' . $val->label . '"  ></span>';
                    if (isset($v->options_tooltip) && $v->options_tooltip && isset($val->tooltip) && $val->tooltip != '') {
                        echo '<p class="wcpa_color_desc" >';
                        echo $val->tooltip;
                        echo '</p></div></div>';
                    }
                    echo '<label for="color-nvm">' . $label . ' ' . (($label === '' && !$is_lable_empty) ? '&nbsp;' : '') . '</label>';
                }

                echo '</label>';

                echo '</div>';
            }
            if (isset($v->other) && $v->other) {
                $option_class = '';

                if (isset($v->other_text) && !empty($v->other_text)) {
                    $label = $v->other_text;
                } else {
                    $label = __('Other', 'wcpa-text-domain');
                }
                if (isset($v->enablePrice) && $v->enablePrice && $v->priceOptions == 'fixed_for_all') {
                    $price = $v->price;
                    $price = wcpa_get_price_shop($this->product, array(
                        'qty' => 1,
                        'price' => $price
                    ));
                    if ($v->pricingType === 'fixed') {
                        $label = $label . '(' . wcpa_price($price * $this->get_con_unit(true), 1) . ')';
                    }

                    $price_data = $this->get_price_data($v);
                    $option_class .= 'wcpa_has_price ';
                } else {
                    $price_data = '';
                }
                $checked = '';
                $other_val = '';

                if (!wcpa_empty($default_value) && !$is_field_checked) {
                    $is_selected = true;
                } else {
                    $is_selected = false;
                }

                // $is_selected = isset($default_value['other']) ? $default_value['other'] : false;

                if ($is_selected === true && !wcpa_empty($default_value)) {
                    $checked = 'checked="checked"';
                    if (is_array($default_value)) {
                        $other_val = str_replace('WCPAOTH ', '', $default_value['other']);
                    } else {
                        $other_val = str_replace('WCPAOTH ', '', $default_value);
                    }
                }

                //$option_class key used for adding .wcpa_has_price class
                $option_class .= 'wcpa_other';
                if (!empty($option_class)) {
                    $option_class = 'class="' . $option_class . '"';
                }
                $field_name = (($check_type == 'radio') ? $name : $name . '[other]');
                echo '<div class="wcpa_color_other">
                    <div class="wcpa_' . $check_type . '">
                    <input name="' . $field_name . '" ' . $option_class . '" '
                    . 'id="' . $name . '_' . $wcpa_field_counter . '_other" value="' . (($other_val === '') ? '' : 'WCPAOTH ' . $other_val) . '"  type="' . $check_type . '"  ' . $price_data . ' ' . $checked . '>
                 <label for="' . $name . '_' . $wcpa_field_counter . '_other"><span class="wcpa_check"></span>' . $label . '</label>';

                echo '<input type="text" name="' . $name . '_other_val" class="wcpa_other_val" value="' . $other_val . '" >
                   </div> </div>';
            }
            echo '</div>';
        }
        $wcpa_field_counter++;
    }

    public function render_place($v)
    {

        $required = (isset($v->required)) ? 'required="required"' : '';
        $placeholder = (isset($v->placeholder)) ? 'placeholder="' . $v->placeholder . '"' : '';

        $name = (isset($v->name)) ? $v->name : '';

        $className = 'wcpa_google_place ';
        if (isset($v->className)) {
            $className .= $v->className . ' ';
        }
        $price_data = '';
        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        if (!empty($className)) {
            $className = 'class="' . $className . '"';
        }
        
        $validation='';
        if (isset($v->required) && $v->required) {
            $validation_array= array();
            $validation = 'data-validation=\'';
            if (isset($v->required) && $v->required) {
                $validation_array['required'] = true;
                $validation_array['requiredMessage'] = htmlspecialchars(wcpa_get_validation_message('requiredError', $v), ENT_QUOTES);
            }
            $validation .=  json_encode($validation_array) . '\'';
        }

        $this->label_section($v);

        if (isset($v->enablePrice) && $v->enablePrice) {
            $className .= 'wcpa_has_price ';
            $price_data = $this->get_price_data($v);
        }
        $default_value = $this->default_value($v);
        echo '<input type="text" id="' . $name . '"   ' . $placeholder . ' ' . $className . ' name="' . $name . '" value="' . $default_value['formated'] . '" ' . $required . ' ' . $price_data . ' ' . $validation . ' >';
        if (isset($v->showaddfields) && $v->showaddfields) {
            echo '<div class="wcpa_place_compons"><table>
            <tr>
              <td class="label">' . __('Street address', 'wcpa-text-domain') . '</td>
              <td class="slimField"><input type="text" class="street_number" name="' . $name . '_street_number" value="' . $default_value['splited']['street_number'] . '"  disabled="true"></td>
              <td class="wideField" colspan="2"><input type="text"  class="route" name="' . $name . '_route" value="' . $default_value['splited']['route'] . '" disabled="true"></td>
            </tr>
            <tr>
              <td class="label">' . __('City', 'wcpa-text-domain') . '</td>
              <td  colspan="3"><input type="text"  class="locality" name="' . $name . '_locality" value="' . $default_value['splited']['locality'] . '" disabled="true"></td>
            </tr>
            <tr>
              <td class="label">' . __('State', 'wcpa-text-domain') . '</td>
              <td class="slimField"><input type="text"    class="administrative_area_level_1" name="' . $name . '_administrative_area_level_1" value="' . $default_value['splited']['administrative_area_level_1'] . '" disabled="true"></td>
              <td class="label postal_code">' . __('Zip code', 'wcpa-text-domain') . '</td>
              <td class="wideField"><input  type="text"  class="postal_code"  name="' . $name . '_postal_code" value="' . $default_value['splited']['postal_code'] . '"   disabled="true"></td>
            </tr>
            <tr>
              <td class="label">' . __('Country', 'wcpa-text-domain') . '</td>
              <td  colspan="3"><input  type="text"  class="country" name="' . $name . '_country" value="' . $default_value['splited']['country'] . '" disabled="true"></td>
            </tr>
          </table></div>';
        }
        $cord_display = 'style="display:none"';
        if (isset($v->showmap) && $v->showmap) {
            $cord_display = '';
        }
        echo '<div class="wcpa_cords wcpa_place_compons" ' . $cord_display . '><table> <tr>
        <td class="label">' . __('Latitude:', 'wcpa-text-domain') . '</td>
        <td ><input type="text" class="wcpa_lat" name="' . $name . '_lat"  value="' . $default_value['cords']['lat'] . '" readonly="readonly"></td>
         <td class="label">' . __('Longitude:', 'wcpa-text-domain') . '</td>
        <td ><input  type="text" class="wcpa_lng"  name="' . $name . '_lng"  value="' . $default_value['cords']['lng'] . '"   readonly="readonly"></td>
      </tr></table></div>';
        if (isset($v->showmap) && $v->showmap) {
            $map_height = '';
            if (isset($v->map_height) && $v->map_height > 10) {
                $map_height = 'style="height:' . $v->map_height . 'px"';
            }
            echo '<div class="wcpa_map" ' . $map_height . '></div>';
        }
    }

    public function enqueue_googlemap()
    {
        wp_enqueue_script($this->_token . '-googlemapplace');
    }

    public function enqueue_recaptcha()
    {
        wp_enqueue_script($this->_token . '-recaptcha');
    }

    public function price_summary_box()
    {
        if (!$this->product) {
            global $product;
            $this->product = $product;
        }

        $summary_order = array('option_price', 'product_price', 'total_price');

        // filter to change the order of price summary display
        $summary_order = apply_filters( 'wcpa_price_summary_order', $summary_order );

        $html = '';
        if(!empty($summary_order)){
            foreach($summary_order as $order) {
                switch($order) {
                    case 'option_price' : 
                        if (
                            $this->settings['disp_summ_show_option_price'] && 
                            $this->settings['pric_overide_base_price'] === false && 
                            $this->settings['pric_overide_base_price_if_gt_zero'] === false && 
                            $this->settings['pric_overide_base_price_fully'] === false
                        ) {
                            $html .= '<li class="wcpa_options_total">
                                <span>' . $this->settings['options_total_label'] . ':'
                                . ' </span><span class="wcpa_price_outer ">' . wcpa_price(0) . '</span></li>';
                        }
                        break;
                    case 'product_price': 
                        if (
                            $this->settings['disp_summ_show_product_price'] && 
                            $this->settings['pric_overide_base_price'] === false && 
                            $this->settings['pric_overide_base_price_if_gt_zero'] === false && 
                            $this->settings['pric_overide_base_price_fully'] === false
                        ) {

                            $html .= ' <li class="wcpa_product_total">
                                <span>' . $this->settings['options_product_label'] . ':'
                                . ' </span><span class="wcpa_price_outer "> ' 
                                . ((wcpa_get_option('show_strike_product_price', false) && $this->product->is_on_sale()) 
                                    ? '<span class="wcpa_price_strike_outer">' . wcpa_price(wcpa_get_price_shop($this->product, [], true), 0, [], 'wcpa_price_strike') . '</span>' 
                                    : '') . ' ' . wcpa_price(wcpa_get_price_shop($this->product)) . '</span></li>';
                        }
                        break;
                    case 'total_price': 
                        if ($this->settings['disp_summ_show_total_price']) {
                            $html .= ' <li class="wcpa_total">
                                <span class="wcpa_price_outer ">' . wcpa_price(0) . '</span></li>';
                        }
                        break;
                    default: 
                        $html .= '';
                }
            }
        }
        
        
        if ($html !== '') {
            echo '<div class="wcpa_price_summary"><ul>';
            echo $html;
            echo ' </ul></div>';
        }

        $hook = get_wcpa_display_hook('price_summary');
        remove_action($hook[0], array($this, 'price_summary_box'), $hook[1]);
    }

    public function __call($name, $arguments)
    {
        return null;
    }

}