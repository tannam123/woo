<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if (!function_exists('wcpa_price')) {

    /**
     * @return string
     */
    function wcpa_price($price, $no_style = 0, $args = array(), $class = 'price_value')
    {


        extract(array(
            'ex_tax_label' => false,
            'currency' => isset($args['currency']) ? $args['currency'] : '',
            'decimal_separator' => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals' => wc_get_price_decimals(),
            'price_format' => get_woocommerce_price_format()));
        if ($decimal_separator) {
            $decimal_separator = trim($decimal_separator);
            $price = str_replace($decimal_separator, '.', $price);
        }

        //$unformatted_price = $price;
        $negative = $price < 0;
        $price = floatval($negative ? $price * -1 : $price);
//
        $price = apply_filters('raw_woocommerce_price', $price);

        $price = number_format($price, $decimals, $decimal_separator, $thousand_separator);


        $formatted_price = ($negative ? '-' : '') . sprintf($price_format, '<ins><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">' .
         get_woocommerce_currency_symbol($currency) . '</span>', '<span class="' . $class . '">' . $price . '</span></bdi></span></ins>');

        $return = '<span class="wcpa_price">'. $formatted_price . '</span>';
        if ($no_style) {
            $return = html_entity_decode(($negative ? '-' : '') . sprintf($price_format, get_woocommerce_currency_symbol($currency), $price), ENT_COMPAT, 'UTF-8');
        }

        return $return;
    }

}

if (!function_exists('get_wcpa_display_hook')) {
    function get_wcpa_display_hook($arg)
    {
        $hooks = apply_filters('wcpa_display_hooks', [
            "fields" => ["woocommerce_before_add_to_cart_button", 10],
            "price_summary" => ["wcpa_price_summary_box", 10]
        ]);
        return $hooks[$arg];
    }


}
if (!function_exists('wcpa_price_to_float')) {
    function wcpa_price_to_float($price)
    {
        $locale = localeconv();
        $decimals = array(wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point']);
        $price = str_replace($decimals, '.', $price);

        return (float)$price;
    }
}

if (!function_exists('wcpa_price_is_numeric')) {
    function wcpa_price_is_numeric($price)
    {
        $locale = localeconv();
        $decimals = array(wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point']);
        $price = str_replace($decimals, '.', $price);

        return is_numeric($price);
    }
}

if (!function_exists('wcpa_get_price_shop')) {

    function wcpa_get_price_shop($product, $args = array(), $regularPrice = false)
    {
        if (is_array($args) && empty($args)) {
            // request directly to get product price, in that case it need to apply the tax configuration
            $consider_tax = true;
        } else {

            $consider_tax = wcpa_get_option('consider_product_tax_conf', true);
        }


        if (!is_array($args) && $args !== false) {
            $args = array(
                'qty' => 1,
                'price' => $args,
            );
        }
        if (!isset($args['qty']) || empty($args['qty'])) {
            $args['qty'] = 1;
        }
        if (!isset($args['price'])) {
            if ($regularPrice) {
                $args['price'] = $product->get_regular_price();
            } else {
                $args['price'] = $product->get_price();
            }

        }
//        else {
//            $args['price'] = apply_filters('woocommerce_product_get_price', $args['price'], $product);
//        }

        // Remove locale from string.
        if (!is_float($args['price'])) {
            $price = wcpa_price_to_float($args['price']);
        } else {
            $price = $args['price'];
        }


        $qty = (int)$args['qty'];
        if ($price < 0) {
            return $price;
        }
        if ($consider_tax) {
            return 'incl' === get_option('woocommerce_tax_display_shop') ?
                wc_get_price_including_tax($product, array(
                    'qty' => $qty,
                    'price' => $price,
                )) :
                wc_get_price_excluding_tax($product, array(
                    'qty' => $qty,
                    'price' => $price,
                ));
        } else {
            return $price;
        }

    }

}
if (!function_exists('wcpa_get_price_cart')) {

    function wcpa_get_price_cart($product, $args = array())
    {

        if (is_array($args) && empty($args)) {
            // request directly to get product price, in that case it need to apply the tax configuration
            $consider_tax = true;
        } else {

            $consider_tax = wcpa_get_option('consider_product_tax_conf', true);
        }

        if (!is_array($args) && $args !== false) {
            $args = array(
                'qty' => 1,
                'price' => $args,
            );
        }


        if (!isset($args['qty']) || empty($args['qty'])) {
            $args['qty'] = 1;
        }
        if (!isset($args['price'])) {
            $args['price'] = $product->get_price();
        }
//        else {
//            $args['price'] = apply_filters('woocommerce_product_get_price', $args['price'], $product);
//        }

        // Remove locale from string.
        if (!is_float($args['price'])) {
            $price = wcpa_price_to_float($args['price']);
        } else {
            $price = $args['price'];
        }

        $qty = (int)$args['qty'];


        if ($price > 0 && $consider_tax) {
            if (WC()->cart->display_prices_including_tax()) {
                $product_price = wc_get_price_including_tax($product, array(
                    'qty' => $qty,
                    'price' => $price,
                ));
            } else {
                $product_price = wc_get_price_excluding_tax($product, array(
                    'qty' => $qty,
                    'price' => $price
                ));
            }
        } else {
            $product_price = $price;
        }
        return $product_price;
    }

}
if (!function_exists('wcpa_empty')) {

    /**
     * @return string
     */
    function wcpa_empty($var)
    {
        if (is_array($var)) {
            return empty($var);
        } else {
            return ($var === null || $var === false || $var === '');
        }
    }

}
if (!function_exists('wcpa_iscolorLight')) {

    /**
     * @return string
     */
    function wcpa_colorClass($hex)
    {
        $hex = str_replace('#', '', $hex);
        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));
        $color = ((($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000);
        $class = '';
        if ($color > 235) {
            $class .= 'wcpa_clb_border '; // border needed
        }
        if ($color > 210) {
            $class .= 'wcpa_clb_nowhite '; // no white color
        }
        return $class;
    }

}

if(!function_exists('wcpa_validate_product_field')){
    /**
     * Function to validate products
     * @since 4.1.0
     */
    function wcpa_validate_product_field($product, $quantity = 1) {
        $message = '';
        $error = false;
        if(!$quantity) {
            $message = sprintf(__( 'Please enter a valid quantity.', 'wcpa-text-domain' ),$product->get_title());
            $message = apply_filters( 'wcpa_cart_product_invalid_quantity', $message, $product, $quantity );
            $error = true;
        }
        if(! $product->is_purchasable()){
            $message = sprintf(__( 'Sorry, the product %s cannot be purchased.', 'wcpa-text-domain' ),$product->get_title());
            $message = apply_filters( 'wcpa_cart_product_cannot_be_purchased_message', $message, $product );
            $error = true;
        }
        if ( ! $product->is_in_stock() ) {
            $message = sprintf( __( 'The prouct &quot;%s&quot; is out of stock. Cannot be purchased', 'wcpa-text-domain' ), $product->get_name() );	
            $message = apply_filters( 'wcpa_cart_product_out_of_stock_message', $message, $product );
            $error = true;
        }
        if ( ! $product->has_enough_stock( $quantity ) ) {
            $stock_quantity = $product->get_stock_quantity();
            $message = sprintf( __( 'You cannot add the amount of &quot;%1$s&quot; because there is not enough stock (%2$s remaining).', 'wcpa-text-domain' ), $product->get_name(), wc_format_stock_quantity_for_display( $stock_quantity, $product ) );
            $message = apply_filters( 'wcpa_product_not_enough_stock_message', $message, $product, $stock_quantity );
            $error = true;
        }
        if ( $product->managing_stock() ) {
            global $woocommerce;
            $products_qty_in_cart =$woocommerce->cart->get_cart_item_quantities();
            if ( isset( $products_qty_in_cart[ $product->get_stock_managed_by_id() ] ) && ! $product->has_enough_stock( $products_qty_in_cart[ $product->get_stock_managed_by_id() ] + $quantity ) ) {
                $stock_quantity         = $product->get_stock_quantity();
                $stock_quantity_in_cart = $products_qty_in_cart[ $product->get_stock_managed_by_id() ];

                $message = sprintf(
                    '<a href="%s" class="button wc-forward">%s</a> %s',
                    wc_get_cart_url(),
                    __( 'View cart', 'wcpa-text-domain' ),
                    sprintf( __( 'You cannot add the amount &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'wcpa-text-domain' ), wc_format_stock_quantity_for_display( $stock_quantity, $product ), wc_format_stock_quantity_for_display( $stock_quantity_in_cart, $product ) )
                );
                $message = apply_filters( 'wcpa_product_not_enough_stock_already_in_cart_message', $message, $product, $stock_quantity, $stock_quantity_in_cart );
                $error = true;
            }
        }
        return array('message'=> $message, 'error' => $error);
    }
}

if(!function_exists('wcpa_get_validation_message')){
    /**
     * Function to retrieve error message for validation
     * @since 4.1.0
     */
    function wcpa_get_validation_message($field, $v, $replace = '') {
        $v_st = wcpa_get_option('wcpa_validation_strings', array());

        $default_replacer = array(
            'requiredError' => [
                'replace' => '',
                'message' =>  __( 'Field is required', 'wcpa-text-domain' ),
            ],
            'allowedCharsError' => [
                'replace' => '{characters}',
                'message' =>  __( 'Characters %s is not supported','wcpa-text-domain')
            ],
            'patternError' => [
                'replace' => '{pattern}',
                'message' =>  __( 'Pattern not matching','wcpa-text-domain')
            ],
            'maxlengthError' => [
                'replace' => '{maxlength}',
                'message' =>  __('Maximum %s characters allowed','wcpa-text-domain')
            ],
            'minlengthError' => [
                'replace' => '{minlength}',
                'message' =>  __('Minimum %s characters required','wcpa-text-domain')
            ],
            'minValueError' => [
                'replace' => '{minvalue}',
                'message' =>  __('Minimum value is %s','wcpa-text-domain')
            ],
            'maxValueError' => [
                'replace' => '{maxvalue}',
                'message' =>  __('Maximum value is %s','wcpa-text-domain')
            ],
            'minFieldsError' => [
                'replace' => '{minfield}',
                'message' =>  __('Select minimum %s fields','wcpa-text-domain')
            ],
            'maxFieldsError' => [
                'replace' => '{maxfield}',
                'message' =>  __('Select maximum %s fields','wcpa-text-domain')
            ],
            'maxFileCountError' => [
                'replace' => '{maxfilecount}',
                'message' =>  __('Maximum %s files allowed','wcpa-text-domain')
            ],
            'maxFileSizeError' => [
                'replace' => '{maxfilesize}',
                'message' =>  __('Maximum file size should be %s','wcpa-text-domain')
            ],
            'minFileSizeError' => [
                'replace' => '{minfilesize}',
                'message' =>  __('Minimum file size should be %s','wcpa-text-domain')
            ],
            'fileExtensionError' => [
                'replace' => '{fileextensions}',
                'message' =>  __('Unsupported file extension found. use from ( %s )','wcpa-text-domain')
            ], 
            'quantityRequiredError' => [
                'replace' => '',
                'message' =>  __('Please enter a valid quantity','wcpa-text-domain')
            ], 
            'otherFieldError' => [
                'replace' => '',
                'message' =>  __('Other value required','wcpa-text-domain')
            ], 
            'charleftMessage' => [
                'replace' => '{charleft}',
                'message' =>  __('%s characters left','wcpa-text-domain')
            ]
        );

        if($replace){
            $defaultMessage = str_replace( '%s', $replace, 
                ( isset($v_st['validation_'.$field]) && !empty($v_st['validation_'.$field]) ) 
                ? $v_st['validation_'.$field] : $default_replacer[$field]['message']);
            return isset($v->$field) ? str_replace( $default_replacer[$field]['replace'], $replace, $v->$field ) : $defaultMessage;
        } else {
            $defaultMessage = (isset($v_st['validation_'.$field]) && !empty($v_st['validation_'.$field]) ) 
                ? $v_st['validation_'.$field] : $default_replacer[$field]['message'];
            return isset($v->$field) ? str_replace($default_replacer[$field]['replace'], '%s', $v->$field) : $defaultMessage;
        }
    }
}


if (!function_exists('wcpa_get_option')) {

    /**
     * @return string
     */
    function wcpa_get_option($option, $default = false, $translate = false)
    {
        $settings = get_option(WCPA_SETTINGS_KEY);

        $settings = apply_filters('wcpa_configurations', $settings);
        $response = isset($settings[$option]) ? $settings[$option] : $default;
        if ($translate) {
            if (function_exists('pll__')) {
                return pll__($response);
            } else {
                return __($response, 'wcpa-text-domain');
            }
        }
        return $response;
    }

}

if (!function_exists('wcpa_get_pcf')) {//product custom fields
    function wcpa_get_pcf()
    {
        $fields = wcpa_get_option('product_custom_fields', '');
        if (wcpa_empty($fields)) {
            return false;
        } else {

            $fields_array = preg_split('/(\s*,\s*)*,+(\s*,\s*)*/', $fields);
        }
    }
}
if (!function_exists('wcpa_get_post_meta')) {

    /**
     * @return string
     */
    function wcpa_get_post_meta($pos_id, $key, $default = false)
    {

        $settings = get_post_meta($pos_id, WCPA_META_SETTINGS_KEY, true);

        return isset($settings[$key]) ? $settings[$key] : $default;
    }

}
if (!function_exists('wcpa_export_form')) {

    /**
     * @return string
     */
    function wcpa_export_form($post_id)
    {
        $json_value = get_post_meta($post_id, WCPA_FORM_META_KEY, true);

        $export = [
            'form_json' => $json_value,
            'title' => get_the_title($post_id),
            'wcpa_settings' => get_post_meta($post_id, WCPA_META_SETTINGS_KEY, true),
            'other_settings' => [
                'wcpa_drct_prchsble' => get_post_meta($post_id, 'wcpa_drct_prchsble', true)
            ]
        ];

        return base64_encode(serialize($export));
    }

}
if (!function_exists('wcpa_style')) {

    /**
     * @return string
     */
    function wcpa_style($style = array(), $echo = false)
    {

        $o = '';
        foreach ($style as $k => $v) {
            $o .= $k . ':' . $v . ';';
        }

        if ($echo) {
            echo $o;
        } else {
            return $o;
        }
    }

}

if (!function_exists('wcpa_is_wcpa_product')) {

    /**
     * @return string
     */
    function wcpa_is_wcpa_product($product_id)
    {

        $form = new WCPA_Form();
        $products = $form->get_wcpa_products();

        foreach ($products as $pro) {
            return in_array($product_id, $pro);
        }
        return false;
    }

}


if (!function_exists('wcpa_formatBytes')) {

    /**
     * @return string
     */
    function wcpa_formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 
}