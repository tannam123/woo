<?php
$value = get_post_meta($object->ID, WCPA_FORM_META_KEY, true);
$ml = new WCPA_Ml();
$fb_class = "";

if ($ml->is_active()) {
    $my_default_lang = $ml->default_language();
    $my_current_lang = $ml->current_language();

    if ($ml->is_new_post($object->ID)) {
        if (!$ml->is_default_lan()) {
            echo '<p class="wcpa_editor_message">' . sprintf(__('You can\'t create new form in current language (%s), Please switch to your default languge (%s) and try again'), $my_current_lang, $my_default_lang) . '</p>';
            $fb_class = 'wpml_fb wcpa-disable-new';
        }
    } else {
        if (empty($value) || $value == 'null') {
            if ($ml->is_duplicating($object->ID)) {
                //copy value from base form or from any existing lang
                $value = $ml->default_fb_meta($object->ID);
            }
        }

        if (!$ml->is_default_lan()) {
            echo '<p class="wcpa_editor_message">' . __('This editor can use only for translating Labels, Values, Help Text, Place holder and condtional logic value. All other configurations and paramaters will be populating from original form.', 'wcpa-text-domain') . '</p>';
            $fb_class = 'wpml_fb lan-';
        }
    }


}
$json_decode = json_decode($value);
if ($json_decode) {
    $counter = 0;
    foreach ($json_decode as $v) {
        if ($counter > 25) {
            break;
        }
        if (isset($v->type) && $v->type == 'image-group') {
            foreach ($v->values as $vl) {
                if (isset($vl->thumb) && $vl->thumb == '') {
                    $image_attributes = wp_get_attachment_image_src($vl->image_id);
                    if($image_attributes){
	                    $vl->thumb = $image_attributes[0];
                    }else{
	                    $vl->thumb = $vl->image;
                    }

                    $counter++;
                }
            }
        }
    }
}
$value = json_encode($json_decode);
wp_nonce_field('wcpa_meta_box_nonce', 'wcpa_box_nonce');
echo '<div id="wcpa_editor" class="' . $fb_class . '"></div>';
?>
<div style="border-top: 1px solid #c3bcbc;<?php echo((wcpa_get_option('wcpa_show_form_json', false) == true) ? "" : "display:none"); ?>">
    <label><?php _e("Form JSON code",'wcpa-text-domain') ?></label>
<p style="color: red; font-size: 12px"><?php _e("Edit only if you are expert on json. Other wise it can break this form",'wcpa-text-domain') ?></p>
<textarea
          name="wcpa_fb-editor-json" id="wcpa_fb-editor-json"><?php echo $value; ?></textarea>


</div>



