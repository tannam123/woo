<div class="wrap wcpa_settings">
    <div id="icon-options-general" class="icon32"></div>
    <h1><?php echo WCPA_PLUGIN_NAME; ?></h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">

                        <div class="inside ">
                            <form method="post" id="wcpa_settings_main" action="">
                                <?php wp_nonce_field('wcpa_save_settings', 'wcpa_nonce'); ?>
                                <ul class="wcpa_g_set_tabs ">
                                    <li><a href="#wcpa_disp_settings" class="active">
                                            <span class="icon_display"></span>
                                            <?php _e('Display Settings', 'wcpa-text-domain'); ?></a></li>
                                    <!-- <li> <a href="#wcpa_price_settings">Price Settings</a> </li> -->
                                    <li><a href="#wcpa_content_settings">
                                            <span class="icon_content"></span>
                                            <?php _e('Contents/Strings', 'wcpa-text-domain'); ?></a></li>
                                    <li><a href="#wcpa_other_settings">
                                            <span class="icon_other"></span>
                                            <?php _e('Other Settings', 'wcpa-text-domain'); ?></a></li>
                                    <li><a href="#wcpa_advanced_settings">
                                            <span class="icon_other"></span>
                                            <?php _e('Advanced Settings', 'wcpa-text-domain'); ?></a></li>
                                    <li><a href="#wcpa_import_export">
                                            <span class="icon_import"></span>
                                            <?php _e('Import/Export', 'wcpa-text-domain'); ?></a></li>
                                    <li><a href="#wcpa_license_key">
                                            <span class="icon_license"></span>
                                            <?php _e('License Key', 'wcpa-text-domain'); ?></a></li>
                                </ul>
                                <div class="wcpa_g_set_tabcontents">
                                    <div id="wcpa_disp_settings" class="wcpa_tabcontent">

                                        <div class="options_group">
                                            <h3><?php _e('Price', 'wcpa-text-domain') ?></h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="disp_show_field_price"
                                                           id="disp_show_field_price"
                                                           value="1" <?php checked(wcpa_get_option('disp_show_field_price', true)); ?>>
                                                    <label for="disp_show_field_price"><?php _e('Show price against each fields', 'wcpa-text-domain'); ?>
                                                    </label>
                                                </li>
                                            </ul>
                                            <h3><?php _e('Price Summary Section', 'wcpa-text-domain') ?></h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="disp_summ_show_total_price"
                                                           id="disp_summ_show_total_price"
                                                           value="1" <?php checked(wcpa_get_option('disp_summ_show_total_price', true)); ?>>
                                                    <label for="disp_summ_show_total_price">
                                                        <?php _e('Show Total', 'wcpa-text-domain') ?> </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="disp_summ_show_product_price"
                                                           id="disp_summ_show_product_price"
                                                           value="1" <?php checked(wcpa_get_option('disp_summ_show_product_price', true)); ?>>
                                                    <label for="disp_summ_show_product_price">
                                                        <?php _e('Show Product Price', 'wcpa-text-domain') ?> </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="disp_summ_show_option_price"
                                                           id="disp_summ_show_option_price"
                                                           value="1" <?php checked(wcpa_get_option('disp_summ_show_option_price', true)); ?>>
                                                    <label for="disp_summ_show_option_price">
                                                        <?php _e('Show Options Price', 'wcpa-text-domain') ?>
                                                    </label>
                                                </li>
                                            </ul>
                                            <h3> <?php _e('Custom options data', 'wcpa-text-domain') ?> </h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="show_meta_in_cart"
                                                           id="show_meta_in_cart"
                                                           value="1" <?php checked(wcpa_get_option('show_meta_in_cart', true)); ?>>
                                                    <label for="show_meta_in_cart"> <?php _e('Show in Cart', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_meta_in_checkout"
                                                           id="show_meta_in_checkout"
                                                           value="1" <?php checked(wcpa_get_option('show_meta_in_checkout', true)); ?>>
                                                    <label for="show_meta_in_checkout">
                                                        <?php _e('Show in Checkout', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_meta_in_order"
                                                           id="show_meta_in_order"
                                                           value="1" <?php checked(wcpa_get_option('show_meta_in_order', true)); ?>>
                                                    <label for="show_meta_in_order">
                                                        <?php _e('Show in Order', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                            </ul>
                                            <h3> <?php _e('Show or Hide Price In', 'wcpa-text-domain') ?> </h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="show_price_in_cart"
                                                           id="show_price_in_cart"
                                                           value="1" <?php checked(wcpa_get_option('show_price_in_cart', true)); ?>>
                                                    <label for="show_price_in_cart"> <?php _e('Show in cart', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_price_in_checkout"
                                                           id="show_price_in_checkout"
                                                           value="1" <?php checked(wcpa_get_option('show_price_in_checkout', true)); ?>>
                                                    <label for="show_price_in_checkout">
                                                        <?php _e('Show in Checkout', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_price_in_order"
                                                           id="show_price_in_order"
                                                           value="1" <?php checked(wcpa_get_option('show_price_in_order', true)); ?>>
                                                    <label for="show_price_in_order">
                                                        <?php _e('Show in Order', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_price_in_order_meta"
                                                           id="show_price_in_order_meta"
                                                           value="1" <?php checked(wcpa_get_option('show_price_in_order_meta', true)); ?>>
                                                    <label for="show_price_in_order_meta">
                                                        <?php _e('Add in Order Meta( Price will be saved along with order meta, Third party plugins will be using this data)', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                            </ul>
                                            <h3> <?php _e('Hide  Zero Price', 'wcpa-text-domain') ?> </h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="wcpa_hide_option_price_zero"
                                                           id="wcpa_hide_option_price_zero"
                                                           value="1" <?php checked(wcpa_get_option('wcpa_hide_option_price_zero', false)); ?>>
                                                    <label for="wcpa_hide_option_price_zero"> <?php _e('Hide Zero In option prices', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="cart_hide_price_zero"
                                                           id="cart_hide_price_zero"
                                                           value="1" <?php checked(wcpa_get_option('cart_hide_price_zero', false)); ?>>
                                                    <label for="cart_hide_price_zero">
                                                        <?php _e('Hide Zero in cart', 'wcpa-text-domain'); ?>  </label>
                                                </li>
                                            </ul>
                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>

                                    <div id="wcpa_content_settings" class="wcpa_tabcontent" style="display: none">
                                        <div class="options_group">
                                            <h3><?php _e('Price Summary Section Labels', 'wcpa-text-domain') ?></h3>
                                            <ul>
                                                <li>

                                                    <label for="options_total_label"> <?php
                                                        _e('Options Price Label:', 'wcpa-text-domain'); ?>
                                                    </label>
                                                    <input type="text" name="options_total_label"
                                                           id="options_total_label"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('options_total_label', 'Options Price')); ?>">
                                                </li>
                                                <li>

                                                    <label for="options_product_label"> <?php
                                                        _e('Product Price Label:', 'wcpa-text-domain');
                                                        ?></label>
                                                    <input type="text" name="options_product_label"
                                                           id="options_product_label"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('options_product_label', 'Product Price')); ?>">
                                                </li>
                                                <li>

                                                    <label for="total_label"><?php
                                                        _e('Total Label:', 'wcpa-text-domain');
                                                        ?> </label>
                                                    <input type="text" name="total_label" id="total_label"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('total_label', 'Total')); ?>">
                                                </li>
                                                <li>
                                                    <label for="fee_label"><?php
                                                        _e('Fee Label:', 'wcpa-text-domain');
                                                        ?> </label>
                                                    <input type="text" name="fee_label" id="fee_label"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('fee_label', 'Fee')); ?>">
                                                </li>
                                                <li>
                                                    <label style="display: block" for="field_option_price_format"><?php
                                                        _e('Format for showing price in field options:', 'wcpa-text-domain');
                                                        ?> </label>
                                                    <input style="display: inline-block" type="text"
                                                           name="field_option_price_format"
                                                           id="field_option_price_format"
                                                           placeholder="(price)"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('field_option_price_format', '(price)')); ?>">
                                                    <span class="wcpa_var_hilight"><?php _e('Preview:', 'wcpa-text-domain');
                                                        echo str_replace('price', wcpa_price(10), htmlspecialchars(wcpa_get_option('field_option_price_format', '(price)'))); ?></span>
                                                </li>

                                            </ul>
                                        </div>

                                        <div class="options_group section">
                                            <h3><?php _e('Error Messages On Validation', 'wcpa-text-domain'); ?></h3>
                                            <?php
                                            $wcpa_validation = wcpa_get_option('wcpa_validation_strings',array());
                                             ?>
                                            <ul>
                                                <li>
                                                    <label for="validation_requiredError">
                                                        <?php _e('Error on required field validation', 'wcpa-text-domain'); ?>
                                                    </label>
                                                    <input type="text" name="validation_requiredError" id="validation_requiredError"
                                                           value="<?php echo htmlspecialchars(isset($wcpa_validation['validation_requiredError'])
                                                                                ? $wcpa_validation['validation_requiredError']
                                                                                : __( 'Field is required', 'wcpa-text-domain' )); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_allowedCharsError">
                                                        <?php _e('Error on allowed characters validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{characters}', __('%s - Display characters not allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_allowedCharsError" id="validation_allowedCharsError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{characters}',
                                                                            isset($wcpa_validation['validation_allowedCharsError']) 
                                                                                ? $wcpa_validation['validation_allowedCharsError'] 
                                                                                : __( 'Characters %s is not supported', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_patternError">
                                                        <?php _e('Error on pattern validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{pattern}', __('%s - To display pattern', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_patternError" id="validation_patternError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{pattern}',
                                                                            isset($wcpa_validation['validation_patternError'])
                                                                                ? $wcpa_validation['validation_patternError']
                                                                                : __( 'Pattern not matching', 'wcpa-text-domain' ))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_minlengthError">
                                                        <?php _e('Error on minimum length validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{minlength}', __('%s - To display minimum length allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_minlengthError" id="validation_minlengthError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{minlength}', 
                                                                                isset($wcpa_validation['validation_minlengthError'])
                                                                                    ? $wcpa_validation['validation_minlengthError']
                                                                                    : __('Minimum %s characters required', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_maxlengthError">
                                                        <?php _e('Error on maximum length validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{maxlength}', __('%s - To display maximum length allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_maxlengthError" id="validation_maxlengthError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{maxlength}', 
                                                                                isset($wcpa_validation['validation_maxlengthError'])
                                                                                    ? $wcpa_validation['validation_maxlengthError']
                                                                                    : __('Maximum %s characters allowed', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_minValueError">
                                                        <?php _e('Error on minimum value validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{minvalue}', __('%s - To display minimum value allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_minValueError" id="validation_minValueError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{minvalue}', 
                                                                                isset($wcpa_validation['validation_minValueError'])
                                                                                    ? $wcpa_validation['validation_minValueError']
                                                                                    : __('Minimum value is %s', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_maxValueError">
                                                        <?php _e('Error on maximum value validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{maxvalue}', __('%s - To display maximum value allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_maxValueError" id="validation_maxValueError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{maxvalue}', 
                                                                                isset($wcpa_validation['validation_maxValueError'])
                                                                                    ? $wcpa_validation['validation_maxValueError']
                                                                                    : __('Maximum value is %s', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_minFieldsError">
                                                        <?php _e('Error on minimum fields validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{minfield}', __('%s - To display minimum fields allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_minFieldsError" id="validation_minFieldsError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{minfield}', 
                                                                                    isset($wcpa_validation['validation_minFieldsError'])
                                                                                        ? $wcpa_validation['validation_minFieldsError']
                                                                                        : __('Select minimum %s fields', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_maxFieldsError">
                                                        <?php _e('Error on maximum fields validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{maxfield}', __('%s - To display maximum fields allowed', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_maxFieldsError" id="validation_maxFieldsError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{maxfield}', 
                                                                                    isset($wcpa_validation['validation_maxFieldsError'])
                                                                                        ? $wcpa_validation['validation_maxFieldsError']
                                                                                        : __('Select maximum %s fields', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_maxFileCountError">
                                                        <?php _e('Error on maximum files count validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{maxfilecount}', __('%s - To display maximum file count', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_maxFileCountError" id="validation_maxFileCountError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{maxfilecount}', 
                                                                                    isset($wcpa_validation['validation_maxFileCountError'])
                                                                                        ? $wcpa_validation['validation_maxFileCountError']
                                                                                        : __('Maximum %s files allowed', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_maxFileSizeError">
                                                        <?php _e('Error on maximum file size validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{maxfilesize}', __('%s - To display maximum file size', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_maxFileSizeError" id="validation_maxFileSizeError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{maxfilesize}', 
                                                                                    isset($wcpa_validation['validation_maxFileSizeError'])
                                                                                        ? $wcpa_validation['validation_maxFileSizeError']
                                                                                        : __('Maximum file size should be %s', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_minFileSizeError">
                                                        <?php _e('Error on minimum file size validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{minfilesize}', __('%s - To display minimum file size', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_minFileSizeError" id="validation_minFileSizeError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{minfilesize}', 
                                                                                    isset($wcpa_validation['validation_minFileSizeError'])
                                                                                        ? $wcpa_validation['validation_minFileSizeError']
                                                                                        : __('Minimum file size should be %s', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_fileExtensionError">
                                                        <?php _e('Error on file extension support validation', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo str_replace('%s', '{fileextensions}', __('%s - To display allowed extension', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_fileExtensionError" id="validation_fileExtensionError"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{fileextensions}', 
                                                                                    isset($wcpa_validation['validation_fileExtensionError'])
                                                                                        ? $wcpa_validation['validation_fileExtensionError']
                                                                                        : __('Unsupported file extension found. use from ( %s )', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_quantityRequiredError">
                                                        <?php _e('Error on quantity validation error', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo __('Error product quantity validation', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_quantityRequiredError" id="validation_quantityRequiredError"
                                                           value="<?php echo htmlspecialchars(
                                                                                    isset($wcpa_validation['validation_quantityRequiredError'])
                                                                                        ? $wcpa_validation['validation_quantityRequiredError']
                                                                                        : __('Please enter a valid quantity', 'wcpa-text-domain')
                                                                                    ); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_otherFieldError">
                                                        <?php _e('Error on other value validation error', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo __('Error other value required validation', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_otherFieldError" id="validation_otherFieldError"
                                                           value="<?php echo htmlspecialchars(
                                                                                    isset($wcpa_validation['validation_otherFieldError'])
                                                                                        ? $wcpa_validation['validation_otherFieldError']
                                                                                        : __('Other value required', 'wcpa-text-domain')
                                                                                    ); ?>">
                                                </li>
                                                <li>
                                                    <label for="validation_charleftMessage">
                                                        <?php _e('Message for characters left', 'wcpa-text-domain'); ?>
                                                        <br><small><?php echo __('Message to display in characters left validation', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="validation_charleftMessage" id="validation_charleftMessage"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{charleft}', 
                                                                                    isset($wcpa_validation['validation_charleftMessage'])
                                                                                        ? $wcpa_validation['validation_charleftMessage']
                                                                                        : __('%s characters left', 'wcpa-text-domain'))); ?>">
                                                </li>
                                                
                                                
                                            </ul>
                                        </div>

                                        <div class="options_group section">

                                            <ul>
                                                <li>

                                                    <label for="add_to_cart_text">
                                                        <p><?php _e('Add to Cart button text', 'wcpa-text-domain'); ?> </p>
                                                        <small><?php _e('Add to cart button text in archive/product listing page in case product has additional fields', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="add_to_cart_text" id="add_to_cart_text"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('add_to_cart_text', 'Select options')); ?>">
                                                </li>
                                                <li>

                                                    <label for="price_prefix_label">
                                                        <p><?php _e('Product Price prefix', 'wcpa-text-domain'); ?></p>
                                                        <small><?php _e('Set a prefix text before the price in archive and product page. Leave blank if no prefix needed. eg: \'Starting at\' ', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="price_prefix_label" id="price_prefix_label"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('price_prefix_label', '')); ?>">
                                                </li>
                                                <li>

                                                    <label for="file_button_text">
                                                        <p><?php _e('File Upload Button Text', 'wcpa-text-domain'); ?></p>
                                                        <small><?php _e('Change default file upload field button text ', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="file_button_text" id="file_button_text"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('file_button_text', __( 'Choose File', 'wcpa-text-domain' ))); ?>">
                                                </li>
                                                <li>

                                                    <label for="file_droppable_action_text">
                                                        <p><?php _e('Droppable File Upload Action Text', 'wcpa-text-domain'); ?></p>
                                                        <small><?php echo str_replace('%s', '{action}', __('Change default droppable file upload clickable text - %s', 'wcpa-text-domain')); ?> </small>
                                                    </label>
                                                    <input type="text" name="file_droppable_action_text" id="file_droppable_action_text"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('file_droppable_action_text', __( 'Browse', 'wcpa-text-domain' ))); ?>">
                                                </li>

                                                <li>

                                                    <label for="file_droppable_desc_text">
                                                        <p><?php _e('Droppable File Upload Description Text', 'wcpa-text-domain'); ?></p>
                                                        <small><?php _e('Change default droppable file upload description text- Use {action} to insert action in between', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                    <input type="text" name="file_droppable_desc_text" id="file_droppable_desc_text"
                                                           value="<?php echo htmlspecialchars(str_replace('%s', '{action}', wcpa_get_option('file_droppable_desc_text', __( 'or %s to choose a file', 'wcpa-text-domain' )))); ?>">
                                                </li>
                                            </ul>
                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>

                                    <div id="wcpa_other_settings" class="wcpa_tabcontent" style="display: none">
                                        <div class="options_group">
                                            <h3><?php _e('Other Settings', 'wcpa-text-domain') ?></h3>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="form_loading_order_by_date"
                                                           id="form_loading_order_by_date"
                                                           value="1" <?php checked(wcpa_get_option('form_loading_order_by_date', false)); ?>>
                                                    <label for="form_loading_order_by_date" class="text">
                                                        <?php _e('Load form in recency order', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('If a product has assigned multiple forms, it will be loaded based on form created order', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="hide_empty_data" id="hide_empty_data"
                                                           value="1" <?php checked(wcpa_get_option('hide_empty_data', false)); ?>>
                                                    <label for="hide_empty_data" class="text">
                                                        <?php _e('Hide empty fields in cart', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Hide empty fields in cart, checkout and order', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="change_price_as_quantity" id="change_price_as_quantity"
                                                           value="1" <?php checked(wcpa_get_option('change_price_as_quantity', false)); ?>>
                                                    <label for="change_price_as_quantity" class="text">
                                                        <?php _e('Update summary price as quantity change', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('In price summary section, price will be updated as quantity change', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="wcpa_show_form_json" id="wcpa_show_form_json"
                                                           value="1" <?php checked(wcpa_get_option('wcpa_show_form_json', false)); ?>>
                                                    <label for="wcpa_show_form_json" class="text">
                                                        <?php _e('Show JSON code editor for form builder', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('You can see the JSON code and make changes manually for each forms.', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="use_sumo_selector" id="use_sumo_selector"
                                                           value="1" <?php checked(wcpa_get_option('use_sumo_selector', false)); ?>>
                                                    <label for="use_sumo_selector" class="text">
                                                        <?php _e('Use Jquery Sumo Selector for multi selector dropdown', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Sumo Selector is a jQuery plugin for customized dropdown ', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="load_all_scripts" id="load_all_scripts"
                                                           value="1" <?php checked(wcpa_get_option('load_all_scripts', false)); ?>>
                                                    <label for="load_all_scripts" class="text">
                                                        <?php _e('Load all custom plugins scripts(for datepicker,colorpicker,SumoSelect) in all pages', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('By default custom scripts will be loading in product page only. But if you want to use custom fields in quick view popups, it needs to loads scripts in all pages ', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">

                                            <ul>
                                                <li>

                                                    <input type="checkbox" name="ajax_add_to_cart" id="ajax_add_to_cart"
                                                           value="1" <?php checked(wcpa_get_option('ajax_add_to_cart', false)); ?>>
                                                    <label for="ajax_add_to_cart" class="text">
					                                    <?php _e('Enable ajax add to cart in product detail page', 'wcpa-text-domain'); ?>

                                                    </label>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">
                                            <ul>
                                                <li>
                                                    <label class="title" for="add_to_cart_button_class" class="text">
					                                    <?php _e('Additional class for add to cart button in archieve page', 'wcpa-text-domain'); ?>

                                                    </label>
                                                    <input type="text" name="add_to_cart_button_class" id="add_to_cart_button_class"
                                                           value="<?php echo htmlspecialchars(wcpa_get_option('add_to_cart_button_class', 'wcpa_add_to_cart_button')); ?>">
                                                    
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group">
                                            <h3><?php _e('Custom fields for products', 'wcpa-text-domain'); ?> </h3>
                                            <ul>
                                                <li>
                                                    <label for="product_custom_fields">

                                                        <?php _e('This fields can be used in custom price formula with prefix \'wcpa_pcf_\'', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <?php _e('Example: ', 'wcpa-text-domain'); ?>
                                                        <span class="example_slug">{wcpa_pcf_packing_price}</span></label><br>

                                                    <div id="product_custom_fields">
                                                        <span class="custom_field_slug title"><?php _e('Custom Field Slug','wcpa-text-domain') ?></span>
                                                        <span class="default_value title"><?php _e('Default Value','wcpa-text-domain') ?></span>
                                                        <?php
                                                        $custom_fields = wcpa_get_option('product_custom_fields');
                                                        if (is_array($custom_fields)) {
                                                            foreach ($custom_fields as $key => $v) {
                                                                ?>
                                                                <div class="fields">
                                                                    <input type="text"
                                                                           name="product_custom_field_name[<?php echo $key ?>]"
                                                                           placeholder="<?php _e('Custom Field Slug', 'wcpa-text-domain') ?>"
                                                                           value="<?php echo $v['name']; ?>">
                                                                    <input type="text"
                                                                           name="product_custom_field_value[<?php echo $key ?>]"
                                                                           placeholder="<?php _e('Default Value', 'wcpa-text-domain') ?>"
                                                                           value="<?php echo $v['value']; ?>"/>
                                                                    <input type="submit" class="wcpa_rmv_btn"
                                                                           value="Remove">
                                                                    <span class="wcpa_var_hilight"> {wcpa_pcf_<?php echo $v['name']; ?>}</span>
                                                                </div>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div id="product_custom_field_add">
                                                        <input type="text" class="product_custom_field_name"
                                                               placeholder="<?php _e('Custom Field Slug', 'wcpa-text-domain') ?>"
                                                               name="product_custom_field_name[0]" value="">
                                                        <input type="text" class="product_custom_field_value"
                                                               placeholder="<?php _e('Default Value', 'wcpa-text-domain') ?>"
                                                               name="product_custom_field_value[0]" value=""/>
                                                        <input type="submit" class="wcpa_add_btn" value="Add">
                                                    </div>
                                                </li>
                                            </ul>

                                        </div>
                                        
                                        <div class="options_group">
                                            <h3><?php _e('Custom Extensions Support', 'wcpa-text-domain'); ?> </h3>
            
                                            <?php
                                            $wcpa_mimetypes =[];
                                            require(__DIR__.'/../mimetypes.php');
                        
                                            $wcpa_mimetypes = apply_filters( 'wcpa_custom_mime_types', $wcpa_mimetypes );
                                            ?>

                                            <div class="wcpa_extension_container">
                                                <h4><?php _e('Choose to Enable the Extensions', 'wcpa-text-domain'); ?></h4>
                                                <div class="wcpa_extensions">
                                                    <?php $allowed_extensions = wcpa_get_option('wcpa_custom_extensions_choose');
                                                    if($wcpa_mimetypes) {
                                                        foreach($wcpa_mimetypes as $ext=>$mime) { 
                                                            $enabled = '';
                                                            if(isset($allowed_extensions[$ext]) && !empty($allowed_extensions[$ext])){
                                                                $enabled = 'checked';
                                                                $mime = $allowed_extensions[$ext];
                                                            }
                                                            ?>
                                                            <div class="extension">
                                                                <label for="wcpa_extension_<?php echo $ext; ?>">
                                                                    <input type="checkbox" value="<?php echo $ext; ?>" <?php echo $enabled; ?> name="wcpa_custom_extension_choose[]" id="wcpa_extension_<?php echo $ext; ?>">
                                                                    <div class="extensionSelectorInner">
                                                                        <span class="extension_name"><?php echo $ext; ?></span>
                                                                        <span class="mark"></span>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        <?php }
                                                    } ?>
                                                   
                                                </div>

                                                <div class="wcpa_custom_create_extension">
                                                    <h4><?php _e('Add Custom Extensions', 'wcpa-text-domain'); ?></h4>
                                                    <div class="wcpa_ext_field_label">
                                                        <div class="wcpa_ext_label"><?php _e('Extension', 'wcpa-text-domain') ?></div>
                                                        <div class="wcpa_ext_label"><?php _e('MiME Type', 'wcpa-text-domain') ?></div>
                                                    </div>
                                                    <div class="wcpa_add_ext_custom_container">
                                                        <?php
                                                        $custom_extension = wcpa_get_option('wcpa_custom_extensions');
                                                        if($custom_extension){
                                                            $count = 1;
                                                            foreach($custom_extension as $ext => $mime) { ?>
                                                                <div class="ext_fieldWrap">
                                                                    <input type="text" name="wcpa_extension_name[<?php echo $count; ?>]" value="<?php echo $ext; ?>">
                                                                    <input type="text" name="wcpa_extension_mime[<?php echo $count; ?>]" value="<?php echo $mime; ?>">
                                                                    <input type="button" class='wcpa_extension_mime_remove' value='Remove'>
                                                                </div>
                                                            <?php
                                                                $count++;
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="wcpa_add_ext">
                                                        <input type="text" name="wcpa_extension_name[0]" 
                                                                placeholder="<?php _e('Extension', 'wcpa-text-domain') ?>">
                                                        <input type="text" name="wcpa_extension_mime[0]"
                                                                placeholder="<?php _e('MiME Type', 'wcpa-text-domain') ?>">
                                                        <input type="button" class='wcpa_extension_mime_add' value='ADD'>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="options_group textbox_width">

                                            <ul>
                                                <li>
                                                    <label for="recaptcha_site_key" class="title"> <?php
                                                        _e('reCAPTCHA Site Key:', 'wcpa-text-domain');
                                                        ?></label>
                                                    <input type="text" name="recaptcha_site_key" id="recaptcha_site_key"
                                                           value="<?php echo wcpa_get_option('recaptcha_site_key', ''); ?>">
                                                    <div class="tooltip">
                                                        <img src="<?php echo $asset_url; ?>/img/help-circle.png">
                                                        <span class="tooltiptext">
                                                            <?php
                                                            _e(' If you need to use reCAPTCHA in your forms you have to paste reCAPTCHA Site Key here. Tick to enable option shown at: Products-> Custom Product Addons-> Other Settings after pasting both the keys.')
                                                            ?>
                                                        <a href="https://www.google.com/recaptcha/admin"
                                                           target="_blank"><br>Get reCAPTCHA Site Key</a>
                                                        </span>
                                                    </div>

                                                </li>
                                                <li>

                                                    <label for="recaptcha_secret_key" class="title"> <?php
                                                        _e('reCAPTCHA Secret Key:', 'wcpa-text-domain');
                                                        ?></label>
                                                    <input type="text" name="recaptcha_secret_key"
                                                           id="recaptcha_secret_key"
                                                           value="<?php echo wcpa_get_option('recaptcha_secret_key', ''); ?>">
                                                    <div class="tooltip">
                                                        <img src="<?php echo $asset_url; ?>/img/help-circle.png">
                                                        <span class="tooltiptext">If you need to use reCAPTCHA in your forms you have to paste reCAPTCHA Secret Key here. Tick enable option shown at: Products-> Custom Product Addons-> Other Settings after pasting both the keys.
                                                        <a href="https://www.google.com/recaptcha/admin"
                                                           target="_blank"><br>Get reCAPTCHA Secret Key</a>
                                                        </span>
                                                    </div>

                                                </li>
                                            </ul>
                                        </div>
                                        <div class="options_group textbox_width">
                                            <ul>
                                                <li>

                                                    <label for="google_map_api_key" class="title"> <?php
                                                        _e('Google Map API Key:', 'wcpa-text-domain');
                                                        ?></label>
                                                    <div><?php
                                                        _e('Don\'t forget to restrict the API key by site domains.', 'wcpa-text-domain');
                                                        ?></div>
                                                    <input type="text" name="google_map_api_key"
                                                           id="options_total_label"
                                                           value="<?php echo wcpa_get_option('google_map_api_key', ''); ?>">
                                                    <div class="tooltip">
                                                        <img src="<?php echo $asset_url; ?>/img/help-circle.png">
                                                        <span class="tooltiptext">If you need to use Google Maps in your forms, you have to paste Google Map API Key here.
                                                        <a href="https://developers.google.com/maps/documentation/embed/get-api-key"
                                                           target="_blank"><br>Get Google Map API Key</a>
                                                        </span>
                                                    </div>

                                                </li>
                                                <li>
                                                <label for="google_map_countries" class="title"> <?php
                                                    _e('Google Map Place Selector Country Restriction:', 'wcpa-text-domain');
                                                    ?></label>
                                                <div><?php
                                                        _e('Provide Country ISO (2 letter) code for Countries comma seperated. Leave Blank for no retstrictions', 'wcpa-text-domain');
                                                        ?></div>
                                                <input type="text" name="google_map_countries"
                                                    id="google_map_countries"
                                                    value="<?php echo wcpa_get_option('google_map_countries', ''); ?>">
                                                <div class="tooltip">
                                                    <img src="<?php echo $asset_url; ?>/img/help-circle.png">
                                                    <span class="tooltiptext"><?php
                                                      _e('', 'wcpa-text-domain');
                                                   ?>
                                                    </span>
                                                </div>

                                                </li>
                                            </ul>
                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>

                                    <div id="wcpa_advanced_settings" class="wcpa_tabcontent" style="display: none">
                                        <div class="options_group">
                                            <h3><?php _e('Advanced Settings', 'wcpa-text-domain'); ?></h3>
                                            <div class="warning_message"><?php _e('These are experimental features. Editing these settings are not recommended. Change only if necessory.', 'wcpa-text-domain'); ?></div>
                                            <ul>
                                                <li>
                                                    <input type="checkbox" name="consider_product_tax_conf"
                                                        id="consider_product_tax_conf"
                                                        value="1" <?php checked(wcpa_get_option('consider_product_tax_conf', true)); ?>>
                                                    <label for="consider_product_tax_conf" class="text">
                                                        <?php _e('Consider Product Tax Configuration', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : enable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="count_fee_once_in_a_order"
                                                        id="count_fee_once_in_a_order"
                                                        value="1" <?php checked(wcpa_get_option('count_fee_once_in_a_order', false)); ?>>
                                                    <label for="count_fee_once_in_a_order" class="text">
                                                        <?php _e('Consider Fee once in an order', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_fee_in_line_subtotal"
                                                        id="show_fee_in_line_subtotal"
                                                        value="1" <?php checked(wcpa_get_option('show_fee_in_line_subtotal', true)); ?>>
                                                    <label for="show_fee_in_line_subtotal" class="text">
                                                        <?php _e('Show Fee in Sub Total displaying section', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : enable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="wcpa_apply_coupon_to_fee"
                                                        id="wcpa_apply_coupon_to_fee"
                                                        value="1" <?php checked(wcpa_get_option('wcpa_apply_coupon_to_fee', false)); ?>>
                                                    <label for="wcpa_apply_coupon_to_fee" class="text">
                                                        <?php _e('Apply coupon discount also for the Fee', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_assigned_products_in_list"
                                                        id="show_assigned_products_in_list"
                                                        value="1" <?php checked(wcpa_get_option('show_assigned_products_in_list', false)); ?>>
                                                    <label for="show_assigned_products_in_list" class="text">
                                                        <?php _e('Show assigned products list at form listing page', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="show_field_price_x_quantity"
                                                        id="show_field_price_x_quantity"
                                                        value="1" <?php checked(wcpa_get_option('show_field_price_x_quantity', false)); ?>>
                                                    <label for="show_field_price_x_quantity" class="text">
                                                        <?php _e('Show options price multiplied by quantity', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="wcpa_show_form_order"
                                                        id="wcpa_show_form_order"
                                                        value="1" <?php checked(wcpa_get_option('wcpa_show_form_order', false)); ?>>
                                                    <label for="wcpa_show_form_order" class="text">
                                                        <?php _e('Show field for setting priority for forms, in a product', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Default  : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                                <li>
                                                    <input type="checkbox" name="remove_discount_from_fields" id="remove_discount_from_fields"
                                                        value="1" <?php checked(wcpa_get_option('remove_discount_from_fields', false)); ?>>
                                                    <label for="remove_discount_from_fields" class="text">
                                                        <?php _e('Disable applying discounts for form fields', 'wcpa-text-domain'); ?>
                                                        <br>
                                                        <small class="label"><?php _e('Enable this to avoid applying the discount for product form fields, Default : disable', 'wcpa-text-domain'); ?> </small>
                                                    </label>
                                                </li>
                                            </ul>

                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>

                                    <div id="wcpa_import_export" class="wcpa_tabcontent" style="display: none">
                                        <div class="options_group">
                                            <p>
                                            <h3><?php _e('This can be used to import single product form', 'wcpa-text-domain'); ?></h3></p>
                                            <ul>
                                                <li>
                                                    <div>
                                                        <label class="import_descript1"><?php _e('Input the exported data here and press <strong>Import From</strong>', 'wcpa-text-domain'); ?></label>
                                                        <textarea rows="5" id="wcpa_import_form_data"></textarea>
                                                        <?php wp_nonce_field('wcpa_form_import_nonce', 'wcpa_form_import_nonce'); ?>
                                                    </div>
                                                    <button class="button-secondary" id="wcpa_import_form"><?php
                                                        _e('Import Form', 'wcpa-text-domain');
                                                        ?></button>
                                                </li>
                                            </ul>
                                            <p>
                                            <h3><?php _e('Export All Forms', 'wcpa-text-domain'); ?></h3></p>
                                            <ul>
                                                <li>
                                                    <div>
                                                        <a href="<?php echo admin_url('export.php?download=true&content=' . WCPA_POST_TYPE . '&submit=Download+Export+File'); ?>"
                                                           class="button-secondary"><?php
                                                            _e('Export Form', 'wcpa-text-domain');
                                                            ?></a>
                                                    </div>
                                                </li>
                                            </ul>
                                            <p>
                                            <h3><?php _e('Import All Forms', 'wcpa-text-domain'); ?></h3></p>
                                            <div class="import_descript2"><?php _e('You can import the xml file using Wordpress default post import option at <a href="' . admin_url('import.php') . '">Tools&#187;Import</a>', 'wcpa-text-domain'); ?></div>

                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>
                                    <div id="wcpa_license_key" class="wcpa_tabcontent" style="display:none">
                                        <div class="options_group">
                                            <?php
                                            $license = get_option('wcpa_activation_license_key');
                                            $status = get_option('wcpa_activation_license_status');
                                            ?>
                                            <form method="post" action="options.php">

                                                <?php settings_fields('wcpa_license'); ?>

                                                <table class="form-table">
                                                    <tbody>
                                                    <tr valign="top">
                                                        <th scope="row" valign="top" class="lic_heading">
                                                            <?php _e('Plugin License','wcpa-text-domain'); ?>
                                                        </th>
                                                        <th class="lic_heading"><?php _e('Status: ','wcpa-text-domain'); ?>
                                                            <?php if ($status !== false && $status == 'valid') { ?>
                                                                <span style="color:green;"><?php _e('Active','wcpa-text-domain'); ?></span>
                                                                <?php wp_nonce_field('wcpa_deactivate', 'wcpa_nounce'); ?>
                                                            <?php } else { ?>
                                                                <?php wp_nonce_field('wcpa_activate', 'wcpa_nounce'); ?>
                                                                <span style="color:red;"><?php _e('Inactive','wcpa-text-domain'); ?></span>
                                                            <?php } ?>

                                                        </th>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <?php _e('License Key','wcpa-text-domain'); ?>
                                                        <td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <input id="edd_sample_license_key"
                                                                   name="wcpa_activation_license_key" type="text"
                                                                   class="regular-text"
                                                                   value="<?php esc_attr_e($license); ?>"
                                                                   placeholder="<?php _e("Enter your license key here",'wcpa-text-domain'); ?>"/>
                                                        </td>
                                                    </tr>

                                                    <tr valign="top">
                                                        <td>
                                                            <?php if ($status !== false && $status == 'valid') { ?>
                                                                <?php wp_nonce_field('wcpa_deactivate', 'wcpa_nounce'); ?>
                                                                <input type="submit" class="button-secondary"
                                                                       name="wcpa_license_deactivate"
                                                                       value="<?php _e('Deactivate License','wcpa-text-domain'); ?>"/>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <?php wp_nonce_field('wcpa_activate', 'wcpa_nounce'); ?>
                                                                <input type="submit" class="button-secondary"
                                                                       name="wcpa_license_activate"
                                                                       value="<?php _e('Activate License','wcpa-text-domain'); ?>"/>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>

                                            </form>
                                            <?php submit_button(null, 'primary', 'wcpa_save_settings'); ?>
                                        </div>
                                    </div>
                                </div>
                                <!--wcpa_g_set_tabcontents-->

                                <div style="clear: both">

                                </div>


                            </form>

                        </div>
                        <!-- .inside -->
                        <div class="support">
                            <h3>Dedicated Support Team </h3>
                            <p>Our support is what makes us No.1. Apart from the normal support ticket system,
                                we also offer live chat support for 16 hours each day, which makes us unique.
                            </p>
                            <p><a href="https://acowebs.com/support/">Guidelines</a></p>
                            <p><a href="https://support.acowebs.com/portal/newticket">Submit a new Ticket</a></p>
                            <div>

                            </div>
                            <!-- .postbox -->

                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->

                    </div>
                    <!-- post-body-content -->


                    <!-- #postbox-container-1 .postbox-container -->

                </div>
                <!-- #post-body .metabox-holder .columns-2 -->


                <br class="clear">
            </div>
            <!-- #poststuff -->

        </div> <!-- .wrap -->
    </div>
</div>
