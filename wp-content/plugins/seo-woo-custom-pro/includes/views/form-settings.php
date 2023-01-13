<?php
$ml = new WCPA_Ml();
$fb_class = "";
if ($ml->is_active()) {

    if (!$ml->is_default_lan()) {
        echo '<p class="wcpa_editor_message">' . __('You can change Content/String settings only from here. All other configurations are taking from base language') . '</p>';
        $fb_class = 'wcpa_wpml_set ';
    }
}
?>
<ul class="wcpa_g_set_tabs <?php echo $fb_class; ?>">
    <li><a href="#wcpa_disp_settings " class="active"><?php _e('Display Settings', 'wcpa-text-domain'); ?></a></li>
    <li><a href="#wcpa_price_settings"><?php _e('Pricing Settings', 'wcpa-text-domain'); ?></a></li>
    <li><a href="#wcpa_content_settings"><?php _e('Contents/Strings', 'wcpa-text-domain'); ?></a></li>
    <li><a href="#wcpa_other_settings"><?php _e('Other settings', 'wcpa-text-domain'); ?></a></li>
    <li><a href="#wcpa_import_export"><?php _e('Export Form', 'wcpa-text-domain'); ?></a></li>
</ul>
<div class="wcpa_g_set_tabcontents   <?php echo $fb_class; ?>">
    <div id="wcpa_disp_settings" class="wcpa_tabcontent">

        <div class="options_group">
            <input type="checkbox" name="disp_use_global" id="disp_use_global" value="1"
                <?php checked(wcpa_get_post_meta($object->ID, 'disp_use_global', true)); ?> >
            <label for="disp_use_global"><?php _e('Use Global settings', 'wcpa-text-domain'); ?></label>
        </div>
        <div class="options_group <?php echo (wcpa_get_post_meta($object->ID, 'disp_use_global', true) == true) ? 'disable' : '' ?>">

            <h3><?php _e('Price', 'wcpa-text-domain') ?></h3>

            <ul>
                <li>
                    <input type="checkbox" name="disp_show_field_price" id="disp_show_field_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'disp_show_field_price', wcpa_get_option('disp_show_field_price', true))); ?>>
                    <label for="disp_show_field_price"><?php _e('Show price against each fields', 'wcpa-text-domain'); ?></label>
                </li>
                <li>
                    <input type="checkbox" name="disp_hide_options_price" id="disp_hide_options_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'disp_hide_options_price', wcpa_get_option('disp_hide_options_price', false))); ?>>
                    <label for="disp_hide_options_price"><?php _e('Hide options price ( applicable for fields having options)', 'wcpa-text-domain'); ?></label>
                </li>
            </ul>
        </div>
        <div class="options_group <?php echo (wcpa_get_post_meta($object->ID, 'disp_use_global', true)==true)?'disable':'' ?>">
            <h3><?php _e('Price Summary Section', 'wcpa-text-domain') ?></h3>
            <ul>
                <li>
                    <input type="checkbox" name="disp_summ_show_total_price" id="disp_summ_show_total_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'disp_summ_show_total_price', wcpa_get_option('disp_summ_show_total_price', true))); ?>>
                    <label for="disp_summ_show_total_price">
                        <?php _e('Show Total', 'wcpa-text-domain') ?> </label>
                </li>
                <li>
                    <input type="checkbox" name="disp_summ_show_product_price" id="disp_summ_show_product_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'disp_summ_show_product_price', wcpa_get_option('disp_summ_show_product_price', true))); ?>>
                    <label for="disp_summ_show_product_price">
                        <?php _e('Show Product price', 'wcpa-text-domain') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="disp_summ_show_option_price" id="disp_summ_show_option_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'disp_summ_show_option_price', wcpa_get_option('disp_summ_show_option_price', true))); ?>>
                    <label for="disp_summ_show_option_price">
                        <?php _e('Show Options Price', 'wcpa-text-domain') ?>
                    </label>
                </li>
            </ul>

        </div>
    </div>
    <div id="wcpa_price_settings" class="wcpa_tabcontent" style="display: none">
        <div class="options_group">
            <ul>
                <li>
                    <input type="checkbox" name="pric_overide_base_price" id="pric_overide_base_price"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'pric_overide_base_price', false)); ?> >
                    <label for="pric_overide_base_price">
                        <?php _e(' Override product base price if options price is higher', 'wcpa-text-domain') ?>
                    </label>
                </li>
                <li>
                    <input type="checkbox" name="pric_overide_base_price_if_gt_zero"
                           id="pric_overide_base_price_if_gt_zero"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'pric_overide_base_price_if_gt_zero', false)); ?> >
                    <label for="pric_overide_base_price_if_gt_zero">
                        <?php _e(' Override product base price if options price is greater than zero', 'wcpa-text-domain') ?>
                    </label>
                </li>

                <li>
                    <input type="checkbox" name="pric_overide_base_price_fully"
                           id="pric_overide_base_price_fully"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'pric_overide_base_price_fully', false)); ?> >
                    <label for="pric_overide_base_price_fully">
                        <?php _e('Override product base price fully. It can cause the price of product zero in case no options selected', 'wcpa-text-domain') ?>
                    </label>
                </li>

                
                <li>
                    <input type="checkbox" name="pric_use_as_fee" id="pric_use_as_fee"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'pric_use_as_fee', false)); ?> >
                    <label for="pric_use_as_fee">
                        <?php _e('Set this form price as Fee - Fee will be counted once irrespective of the quantity', 'wcpa-text-domain') ?>
                    </label>
                </li>
                <li style="opacity: .5">
                    <input type="checkbox" name="exclude_from_discount" id="exclude_from_discount"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'exclude_from_discount', false)); ?> >
                    <label for="exclude_from_discount">
                        <?php _e('Exclude this form price from applying discounts', 'wcpa-text-domain') ?>
                    </label>
                </li>
                <li style="opacity: .5">
                    <input type="checkbox" name="pric_cal_option_once" id="pric_cal_option_once"
                           value="1" <?php checked(wcpa_get_post_meta($object->ID, 'pric_cal_option_once', false)); ?> >
                    <label for="pric_cal_option_once">
                        <?php _e('Count the options price only once for a product irrespective of the quantity', 'wcpa-text-domain') ?>
                        <br>
                        <smal><?php _e('Please use fee instead of this feature, This wont be no longer supporting','wcpa-text-domain'); ?></smal>
                    </label>
                </li>

            </ul>
        </div>
    </div>
    <div id="wcpa_content_settings" class="wcpa_tabcontent" style="display: none">
        <div class="options_group">

            <input type="checkbox" name="cont_use_global" id="cont_use_global" value="1"
                <?php checked(wcpa_get_post_meta($object->ID, 'cont_use_global', true)); ?> >
            <label for="cont_use_global"><?php _e('Use Global settings', 'wcpa-text-domain'); ?></label>
        </div>
        <div class="options_group <?php echo (wcpa_get_post_meta($object->ID, 'cont_use_global', true)==true)?'disable':'' ?>">
            <ul>
                <li>

                    <label for="options_total_label"> <?php
                        _e('Options Price Label:', 'wcpa-text-domain');
                        ?></label>
                    <input type="text" name="options_total_label" id="options_total_label"
                           value="<?php echo wcpa_get_post_meta($object->ID, 'options_total_label', wcpa_get_option('options_total_label', 'Options Price')); ?>">
                </li>
                <li>

                    <label for="options_product_label"> <?php
                        _e('Product Price Label:', 'wcpa-text-domain');
                        ?></label>
                    <input type="text" name="options_product_label" id="options_product_label"
                           value="<?php echo wcpa_get_post_meta($object->ID, 'options_product_label', wcpa_get_option('options_product_label', 'Product Price')); ?>"

                    >
                </li>
                <li>

                    <label for="total_label"><?php
                        _e('Total Label:', 'wcpa-text-domain');
                        ?>  </label>
                    <input type="text" name="total_label" id="total_label"
                           value="<?php echo wcpa_get_post_meta($object->ID, 'total_label', wcpa_get_option('total_label', 'Total')); ?>">
                </li>

                <li>
                    <label for="fee_label"><?php
                        _e('Fee:', 'wcpa-text-domain');
                        ?>  </label>
                    <input type="text" name="fee_label" id="fee_label"
                           value="<?php echo wcpa_get_post_meta($object->ID, 'fee_label', wcpa_get_option('fee_label', 'Fee')); ?>">
                </li>
            </ul>
        </div>
    </div>
    <div id="wcpa_other_settings" class="wcpa_tabcontent" style="display: none">
        <div class="options_group">
            <ul>
                <li>
                    <input type="checkbox" name="wcpa_drct_prchsble" id="wcpa_drct_prchsble" value="1"
                        <?php checked(get_post_meta($object->ID, 'wcpa_drct_prchsble', true)); ?> >
                    <label for="wcpa_drct_prchsble"> <?php
                        _e('Allow direct purchase from archive/listing pages without selecting options', 'wcpa-text-domain');
                        ?><br>

                    </label>
                    <small>
                        <?php
                        _e('Please ensure you have not added any mandatory fields, else it will throw validation errors', 'wcpa-text-domain');
                        ?>
                    </small>
                </li>
                <li>
                    <input type="checkbox" name="enable_recaptcha" id="enable_recaptcha" value="1"
                        <?php checked(wcpa_get_post_meta($object->ID, 'enable_recaptcha', false)); ?> >
                    <label for="enable_recaptcha">
                        <?php _e('Enable reCAPTCHA for this forms', 'wcpa-text-domain'); ?> <br>
                    </label>
                    <small><?php echo sprintf(__('You need to configure the reCAPTCHA keys at %s. Enabling Captcha is not recommended always, Avoid using captcha if there is no partcular reason', 'wcpa-text-domain'), '<a href="' . admin_url('options-general.php?page=wcpa_settings') . '" target="_blank">Settings</a>'); ?> </small>

                </li>
                <li>
                    <input type="checkbox" name="bind_quantity" id="bind_quantity" value="1"
                        <?php checked(wcpa_get_post_meta($object->ID, 'bind_quantity', false)); ?> >
                    <label for="bind_quantity">
                        <?php _e('Bind field with quantity', 'wcpa-text-domain'); ?> <br>
                    </label>
                    <small><?php echo sprintf(__('You can use custom formula for binding to quantity ', 'wcpa-text-domain'), '<a href="' . admin_url('options-general.php?page=wcpa_settings') . '" target="_blank">Settings</a>'); ?> </small>

                    <input type="text" name="quantity_bind_formula" id="quantity_bind_formula"
                           value="<?php echo wcpa_get_post_meta($object->ID, 'quantity_bind_formula', wcpa_get_option('quantity_bind_formula', '')); ?>">
                    <small><?php echo sprintf(__('If you want to bind a field with quantity, You can use %s', 'wcpa-text-domain'), '<span class="wcpa_var_hilight">{field.element_id_of_the_field.value}</span>'); ?> </small>

                </li>
            </ul>
        </div>

    </div>
    <div id="wcpa_import_export" class="wcpa_tabcontent" style="display: none">
        <div class="options_group">
            <ul>
                <li>
                    <div>
                        <label> <?php
                            _e('You can copy this data to export the form', 'wcpa-text-domain');
                            ?></label>
                        <textarea onclick="this.focus();this.select()" style="width:80%" readonly="readonly"><?php
                            echo wcpa_export_form($object->ID);
                            ?></textarea>
                    </div>
                </li>

            </ul>
        </div>
    </div>


</div>
<div style="clear: both">

</div>
