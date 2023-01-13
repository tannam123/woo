<?php
if (is_array($meta_data) && count($meta_data)) {
    ?>
    <table>
        <tr>
            <th><?php _e('Options', 'wcpa-text-domain') ?></th>
            <th><?php _e('Value', 'wcpa-text-domain') ?></th>
            <th><?php _e('Cost', 'wcpa-text-domain') ?></th>
            <th></th>
        </tr>

        <?php
        foreach ($meta_data as $k => $data) {
            if(!is_array($data)){
                continue;
            }
            if (in_array($data['type'], array('checkbox-group', 'select', 'radio-group', 'image-group', 'color-group', 'productGroup')) && is_array($data['value'])) {
                $label_printed = false;
                foreach ($data['value'] as $l => $v) {
                    ?>
                    <tr class="item_wcpa">
                        <td class="name">
                            <?php
                            echo $label_printed ? '' : $data['label'];
                            $label_printed = true;
                            ?>
                        </td>

                        <td class="value" >
                            <div class="view">
                                <?php
                                if ($data['type'] == 'image-group') {
                                    echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong> ' . __($v['label'], 'wcpa-text-domain') . '<br>';
                                    if (isset($v['image']) && $v['image'] !== FALSE) {
                                        $img_size_style = ((isset($data['form_data']->disp_size_img) && $data['form_data']->disp_size_img > 0) ? 'style="width:' . $data['form_data']->disp_size_img . 'px"' : '');

                                        echo ' <img class="wcpa_img" '.$img_size_style.'  src="' . $v['image'] . '" />';
                                    } else
                                    if (isset($v['value']) && $v['value'] !== FALSE) {
                                        echo ' ' . $v['value'];
                                    }
                                } else if ($data['type'] == 'productGroup') {
                                    if($v){
                                        $edit_url = admin_url( 'post.php?post=' . $v->get_id() ) . '&action=edit';
                                        $pro_image = '';
                            
                                        if ( $v->get_image_id() ) {
                                            $pro_image = wp_get_attachment_url( $v->get_image_id() );
                                        } 
                                    
                                        if ( $pro_image == '' ) {
                                            $pro_image = wc_placeholder_img_src( 'woocommerce_thumbnail' );
                                        }
                                        echo "<div class='wcpa_order_details_meta_line_product'>";
                                        if ($pro_image && isset($data['form_data']->show_image) && $data['form_data']->show_image) {
                                            $img_size_style = ((isset($data['form_data']->disp_size_img) && $data['form_data']->disp_size_img > 0) ? 'style="width:' . $data['form_data']->disp_size_img . 'px"' : '');

                                            echo ' <img class="wcpa_img" '.$img_size_style.'  src="' . $pro_image . '" />';
                                        } 
                                        echo '<a href="'.$edit_url.'" target="_blank">'.$v->get_title().'</a>';
                                        $quantity = (isset($data['quantities'][$l]) && !empty($data['quantities'][$l])) ? $data['quantities'][$l] : 1;
                                        echo '<span class="wcpa_productGroup_order_qty">x '.$quantity.'</span>';
                                        echo "</div>";
                                    }
                                } else if ($data['type'] == 'color-group') {
                                    echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong> ' . __($v['label'], 'wcpa-text-domain') . '<br>';
                                    echo '<strong>' . __('Value:', 'wcpa-text-domain') . '</strong> ' . '<span style="color:' . $v['color'] . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . $v['value'];
                                } else if (isset($v['i'])) {
                                    echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong> ' . __($v['label'], 'wcpa-text-domain') . '<br>';
                                    echo '<strong>' . __('Value:', 'wcpa-text-domain') . '</strong> ' . $v['value'];
                                } else {
                                    echo $v;
                                }
                                ?>

                            </div>
                            <div class="edit" style="display: none;">
                                <?php
                                if ($data['type'] == 'image-group') {
                                    ?>
                                    <?php echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong>'; ?>
                                    <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]" 
                                    value="<?php echo $v['label'] ?>"> <br>
                                    <?php
                                    if (isset($v['image']) && $v['image'] !== FALSE) {
                                        echo __('Value:', 'wcpa-text-domain') . '<input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                        value="' . $v['image'] . '">';
                                    } else
                                    if (isset($v['value']) && $v['value'] !== FALSE) {
                                        echo __('Value:', 'wcpa-text-domain') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                        value="' . $v['value'] . '">';
                                    }
                                } else if ($data['type'] == 'productGroup') {
                                    $qts = array();
                                    if(isset($data['quantities']) && !empty($data['quantities'])) {
                                        $qts = $data['quantities'];
                                    }
                                    $current_qty = isset($qts[$l]) ? $qts[$l] : 1;
                                    echo __('Product ID:', 'wcpa-text-domain') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                    value="' . $v->get_id() . '">';
                                    echo __('Quantity:', 'wcpa-text-domain') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][quantity]" 
                                    value="' . $current_qty . '">';
                    
                                } else if (isset($v['i'])) {
                                        ?>
                                <?php echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong>'; ?>  <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]" 
                                        value="<?php echo $v['label'] ?>"> <br>
                                <?php echo '<strong>' . __('Value:', 'wcpa-text-domain') . '</strong>'; ?> <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][value]" 
                                        value="<?php echo $v['value'] ?>">
                                        <?php
                                    } else {
                                        ?>
                                <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]" value="<?php echo $v ?>">

                            <?php }
                                ?>


                            </div>
                        </td>
                        <td class="item_cost" width="1%">

                            <?php
                            if (isset($data['form_data']->enablePrice) && $data['form_data']->enablePrice &&
                                    (!isset($data['is_fee']) || $data['is_fee'] === false)) {
                                ?>
                                <div class="view">
                                    <?php echo isset($data['price'][$l]) ? $data['price'][$l] : '0'; ?>
                                </div>
                                <div class="edit" style="display: none;">
                                    <input type="text"
                                           data-price="<?php echo (isset($data['price'][$l]) ? $data['price'][$l] : '0') ?>"
                                           class="wcpa_has_price" 
                                           name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]"
                                           value="<?php echo (isset($data['price'][$l]) ? $data['price'][$l] : '0'); ?>">
                                </div>
                                <?php
                            }
                            ?>
                        </td>
                        </td>

                        <td class="wc-order-edit-line-item" width="1%">
                            <div class = "wc-order-edit-line-item-actions edit" style="display: none;">
                                <a class="wcpa_delete-order-item tips" href="#" data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
            } else if ($data['type'] == 'file') {
                ?>
                <tr class="item_wcpa">

                    <td class="name"><?php echo $data['label']; ?></td>
                    <td class="value" >
                        <div class="view">
                            <?php
                            if(isset($data['multiple']) && ($data['multiple'])){
                                if(isset($data['value'])){
                                    foreach($data['value'] as $dt) {
                                        if (isset($dt['url'])) {
                                            $display = '<a href="' . $dt['url'] . '"  target="_blank" download="' . $dt['file_name'] . '">';
                                            if (in_array($dt['type'], array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'))) {
                                                $display .= '<img class="wcpa_img" style="max-width:100%;" src="' . $dt['url'] . '" />';
                                            } else {
                                                $display .= '<img class="wcpa_icon" src="' . wp_mime_type_icon($dt['type']) . '" />';
                                            }
                                            $display .= $dt['file_name'] . '</a>';
                                            echo $display;
                                        } else {
                                            echo $dt;
                                        }
                                    }
                                }
                            } else {
                                if (isset($data['value']['url'])) {
                                    $display = '<a href="' . $data['value']['url'] . '"  target="_blank" download="' . $data['value']['file_name'] . '">';
                                    if (in_array($data['value']['type'], array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'))) {
                                        $display .= '<img class="wcpa_img" style="max-width:100%;" src="' . $data['value']['url'] . '" />';
                                    } else {
                                        $display .= '<img class="wcpa_icon" src="' . wp_mime_type_icon($data['value']['type']) . '" />';
                                    }
                                    $display .= $data['value']['file_name'] . '</a>';
                                    echo $display;
                                } else {
                                    echo $data['value'];
                                }
                            }
                            ?>
                        </div>
                        <div class="edit" style="display: none;">
                            <?php
                            if(isset($data['multiple']) && ($data['multiple'])){
                                if(isset($data['value'])){
                                    $index = 0;
                                    foreach($data['value'] as $dt) {
                                        if($dt){
                                            echo '<strong>' . __('File URL:', 'wcpa-text-domain') . '</strong>';
                                            if (isset($dt['url'])) {
                                                echo '<input type="text" 
                                                    name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $index . ']"  value="' . $dt['url'] . '">';
                                            } else {
                                                echo '<input type="text" 
                                                    name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $index . ']"  value="' . $dt . '">';
                                            }
                                            $index++;
                                        }
                                    }
                                }
                            } else {
                                if (isset($data['value']['url'])) {
                                    echo '<input type="text" 
                                name="wcpa_meta[value][' . $item_id . '][' . $k . ']"  value="' . $data['value']['url'] . '">';
                                } else {
                                    echo '<input type="text" 
                                name="wcpa_meta[value][' . $item_id . '][' . $k . ']"  value="' . ($data['value']) . '">';
                                }
                            }
                            ?>

                        </div>
                    </td>
                    <td class="item_cost" width="1%">
                        <?php
                        if (isset($data['form_data']->enablePrice) && $data['form_data']->enablePrice) {
                            ?>
                            <div class="view">
                                <?php echo wcpa_price($data['price'], false, ['currency' => $order->get_currency()]); ?>
                            </div>
                            <div class="edit" style="display: none;">
                                <input type="text"
                                       data-price="<?php echo $data['price']; ?>"
                                       class="wcpa_has_price" 
                                       name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]" 
                                       value="<?php echo $data['price'] ?>">
                            </div>
                            <?php
                        }
                        ?>
                    </td>

                    <td class = "wc-order-edit-line-item" width = "1%">
                        <div class = "wc-order-edit-line-item-actions edit" style="display: none;">
                            <a class="wcpa_delete-order-item tips" href="#" data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                        </div>
                    </td>
                </tr>
                <?php
            } else if ($data['type'] === 'placeselector') {
                ?>
                <tr class="item_wcpa">
                    <td class="name"><?php echo $data['label']; ?></td>
                    <td class="value" >
                        <div class="view">
                            <?php
                            if (!empty($data['value']['formated'])) {
                                $display = $data['value']['formated'] . '<br>';
                                if (!empty($data['value']['splited']['street_number'])) {
                                    $display .= __('Street address:', 'wcpa-text-domain') . ' ' . $data['value']['splited']['street_number'] . ' ' . $data['value']['splited']['route'] . ' <br>';
                                }
                                if (!empty($data['value']['splited']['locality'])) {
                                    $display .= __('City:', 'wcpa-text-domain') . ' ' . $data['value']['splited']['locality'] . '<br>';
                                }
                                if (!empty($data['value']['splited']['administrative_area_level_1'])) {
                                    $display .= __('State:', 'wcpa-text-domain') . ' ' . $data['value']['splited']['administrative_area_level_1'] . '<br>';
                                }
                                if (!empty($data['value']['splited']['postal_code'])) {
                                    $display .= __('Zip code:', 'wcpa-text-domain') . ' ' . $data['value']['splited']['postal_code'] . '<br>';
                                }
                                if (!empty($data['value']['splited']['country'])) {
                                    $display .= __('Country:', 'wcpa-text-domain') . ' ' . $data['value']['splited']['country'] . '<br>';
                                }
                                if (isset($data['value']['cords']['lat']) && !empty($data['value']['cords']['lat'])) {
                                    $display .= __('Latitude:', 'wcpa-text-domain') . ' ' . $data['value']['cords']['lat'] . '<br>';
                                    $display .= __('Longitude:', 'wcpa-text-domain') . ' ' . $data['value']['cords']['lng'] . '<br>';
                                    $display .= '<a href="https://www.google.com/maps/?q=' . $data['value']['cords']['lat'] . ',' . $data['value']['cords']['lng'] . '" target="_blank">' . __('View on map', 'wcpa-text-domain') . '</a> <br>';
                                }
                                echo $display;
                            }
                            ?>
                        </div>
                        <div class="edit" style="display: none;">

                            <input type="text" 
                                   name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][formated]" 
                                   value="<?php echo $data['value']['formated'] ?>"> <br><br>
                                   <?php
                                   $name = 'wcpa_meta[value][' . $item_id . '][' . $k . ']';
                                   echo __('Street address:', 'wcpa-text-domain') . '
              <input type="text" class="street_number" name="' . $name . '[street_number]" value="' . (isset($data['value']['splited']['street_number']) ? $data['value']['splited']['street_number'] : '') . '"  >
             <input type="text" class="route" name="' . $name . '[route]" value="' . (isset($data['value']['splited']['route']) ? $data['value']['splited']['route'] : '') . '" > <br>
            ' . __('City:', 'wcpa-text-domain') . '<input  type="text" name="' . $name . '[locality]" value="' . (isset($data['value']['splited']['locality']) ? $data['value']['splited']['locality'] : '') . '" ><br>
           ' . __('State:', 'wcpa-text-domain') . '<input type="text"  name="' . $name . '[administrative_area_level_1]" value="' . (isset($data['value']['splited']['administrative_area_level_1']) ? $data['value']['splited']['administrative_area_level_1'] : '') . '" ><br>
            ' . __('Zip code:', 'wcpa-text-domain') . '<input type="text"  name="' . $name . '[postal_code]" value="' . (isset($data['value']['splited']['postal_code']) ? $data['value']['splited']['postal_code'] : '') . '"   ><br>
           ' . __('Country:', 'wcpa-text-domain') . '<input type="text" name="' . $name . '[country]" value="' . (isset($data['value']['splited']['country']) ? $data['value']['splited']['country'] : '') . '" ><br>
           ' . __('Latitude:', 'wcpa-text-domain') . '<input type="text"  name="' . $name . '[lat]" value="' . (isset($data['value']['cords']['lat']) ? $data['value']['cords']['lat'] : '') . '" ><br>
           ' . __('Longitude:', 'wcpa-text-domain') . '<input  type="text" name="' . $name . '[lng]" value="' . (isset($data['value']['cords']['lng']) ? $data['value']['cords']['lng'] : '') . '" >';
                                   ?>
                        </div>
                    </td>
                    <td class="item_cost" width="1%">
                        <?php
                        if (isset($data['form_data']->enablePrice) && $data['form_data']->enablePrice) {
                            ?>
                            <div class="view">
                                <?php echo wcpa_price($data['price'][0], false, ['currency' => $order->get_currency()]); ?>
                            </div>
                            <div class="edit" style="display: none;">
                                <input type="text"
                                       data-price="<?php echo $data['price'][0]; ?>"
                                       class="wcpa_has_price" 
                                       name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]" 
                                       value="<?php echo $data['price'][0] ?>">
                            </div>
                            <?php
                        }
                        ?>
                    </td>

                    <td class = "wc-order-edit-line-item" width = "1%">
                        <div class = "wc-order-edit-line-item-actions edit" style="display: none;">
                            <a class="wcpa_delete-order-item tips" href="#" data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                        </div>
                    </td>
                </tr>
                <?php
            } else {
                ?>
                <tr class="item_wcpa">

                    <td class="name">

                        <?php
                        if ($data['type'] == 'hidden' && empty($data['label'])) {
                            echo $data['label'] . '[hidden]';
                        } else {
                            echo $data['label'];
                        }
                        ?>
                    </td>
                    <td class="value" >
                        <div class="view">

                            <?php
                            if ($data['type'] == 'color') {
                                echo '<span style = "color:' . $data['value'] . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . $data['value'];
                            } else {
                                echo nl2br($data['value']);
                            }
                            ?>
                        </div>

                        <div class="edit" style="display: none;">
                            <?php
                            if ($data['type'] == 'paragraph' || $data['type'] == 'header') {
                                echo $data['value'];
                                echo '<input type="hidden" 
                                       name="wcpa_meta[value][' . $item_id . '][' . $k . ']" 
                                       value="1">';
                            } else if($data['type'] == 'textarea' ) {
                                ?>
                                <textarea  name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]" ><?php echo ($data['value']) ?></textarea>
                                <?php
                            }
                            else {
                                ?>
                                <input type="text" 
                                       name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]" 
                                       value="<?php echo htmlspecialchars($data['value']) ?>">
                                       <?php
                                   }
                                   ?>

                        </div>
                    </td>
                    <td class="item_cost" width="1%">
                        <?php
                        if (isset($data['form_data']->enablePrice) && $data['form_data']->enablePrice) {
                            ?>
                            <div class="view">
                                <?php echo wcpa_price($data['price'], false, ['currency' => $order->get_currency()]); ?>
                            </div>
                            <div class="edit" style="display: none;">
                                <input type="text"
                                       data-price="<?php echo $data['price']; ?>"
                                       class="wcpa_has_price" 
                                       name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]" 
                                       value="<?php echo $data['price'] ?>">
                            </div>
                            <?php
                        }
                        ?>
                    </td>

                    <td class = "wc-order-edit-line-item" width = "1%">
                        <div class = "wc-order-edit-line-item-actions edit" style="display: none;">
                            <a class="wcpa_delete-order-item tips" href="#" data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>


            <?php
        }
        ?>
        <tr>
            <!--   /* dummy field , it will help to iterate through all data for removing last item*/-->
        <input type="hidden" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k + 99; ?>]" value="">

        </tr>
    </table>

    <?php
}



