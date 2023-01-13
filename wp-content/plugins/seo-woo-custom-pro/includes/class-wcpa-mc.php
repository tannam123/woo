<?php

if (!defined('ABSPATH'))
    exit;

class WCPA_MC
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    private $conversion_unit = false;

    public function __construct()
    {


    }

    public function get_con_unit($product, $to_currency = false, $from_currency = false, $toMultiplyWithShopPrice = false, $isCart = false)
    {
        $this->conversion_unit = apply_filters('wcpa_conversion_unit', false, $product, $to_currency, $from_currency);

        if ($this->conversion_unit === false) {


            $this->conversion_unit = 1;

            if ($this->conversion_unit == 1) {
                $view_price = $product->get_price('view');
                $edit_price = $product->get_price('edit');

                if ($view_price && $edit_price && $edit_price > 0 && $view_price > 0) {
                    if(!wcpa_get_option('remove_discount_from_fields', false)){
                        $this->conversion_unit = $view_price / $edit_price;
                    } else {
                        $this->conversion_unit = 1;
                    }
                } else {
                    $this->conversion_unit = 1;
                }
            }
            if ($this->conversion_unit == 1 && !$isCart) {
                $this->conversion_unit = floatval(apply_filters('wcml_raw_price_amount', 100000) / 100000);
            }
            if ($this->conversion_unit == 1) {
                if ($from_currency === false) {
                    $from_currency = apply_filters('wcpa_cs_product_base_currency', get_option('woocommerce_currency'), $product->get_id());
                }
                if ($to_currency === false) {
                    $to_currency = get_woocommerce_currency();
                }

                if (wc_get_price_decimals() == 0) {
                    $converted_amount = apply_filters('wc_aelia_cs_convert', 1, $from_currency, $to_currency, 2);
                } else {
                    $converted_amount = apply_filters('wc_aelia_cs_convert', 1, $from_currency, $to_currency);
                }


                $this->conversion_unit = $converted_amount;
            }


            if ($this->conversion_unit == 1 && function_exists('wcpbc_the_zone')) {
                $wcpbc = wcpbc_the_zone();
                $converted_amount = 1;
                if (is_callable($wcpbc, 'get_exchange_rate_price') || method_exists($wcpbc, 'get_exchange_rate_price')) {
                    $converted_amount = $wcpbc->get_exchange_rate_price(1);
                }

                $this->conversion_unit = $converted_amount;
            }

            global $WOOCS;

            if ($this->conversion_unit == 1 && $WOOCS !== null && $toMultiplyWithShopPrice === false) {
                if (method_exists($WOOCS, 'woocs_exchange_value')) {
                    $res = $WOOCS->woocs_exchange_value(1);
                    $this->conversion_unit = $res;
                }
            }


            return $this->conversion_unit;
        } else {
            return $this->conversion_unit;
        }

    }

    public function mayBeConvert($price, $to_currency = false, $from_currency = false,$section=false)
    { // some currency convert plugin will not take care the conversion automatically always
        // in such case it can handle based on the currency converter plugin used

        if ($from_currency === false) {
            $from_currency = get_option('woocommerce_currency');
        }
        if ($to_currency === false) {
            $to_currency = get_woocommerce_currency();
        }

        $price = apply_filters('wc_aelia_cs_convert', $price, $from_currency, $to_currency);
        if (function_exists('wcpbc_the_zone')) {
            $wcpbc = wcpbc_the_zone();
            if (is_callable($wcpbc, 'get_exchange_rate_price') || method_exists($wcpbc, 'get_exchange_rate_price')) {
                $price = $wcpbc->get_exchange_rate_price($price);

            }

        }


        if ($section=='add_fee' && function_exists('wmc_get_price')) {
          $price = wmc_get_price($price);

        }
        return $price;
    }


}
