<?php
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// contact form
$email_form = '<form id="vscf" class="'.$form_class.'" method="post">
	<div class="form-group vscf-name-group">
		<label for="vscf_name">'.esc_attr($name_label).': <span class="'.(isset($error_class['form_name']) ? "vscf-error" : "vscf-hide").'" >'.esc_attr($error_name_label).'</span></label>
		<input type="text" name="vscf_name" id="vscf_name" '.(isset($error_class['form_name']) ? ' class="form-control vscf-error"' : ' class="form-control"').' value="'.esc_attr($form_data['form_name']).'" aria-required="true" />
	</div>
	<div class="form-group vscf-email-group">
		<label for="vscf_email">'.esc_attr($email_label).': <span class="'.(isset($error_class['form_email']) ? "vscf-error" : "vscf-hide").'" >'.esc_attr($error_email_label).'</span></label>
		<input type="email" name="vscf_email" id="vscf_email" '.(isset($error_class['form_email']) ? ' class="form-control vscf-error"' : ' class="form-control"').' value="'.esc_attr($form_data['form_email']).'" aria-required="true" />
	</div>
	'. (($subject_setting != 'yes') ? '
		<div class="form-group vscf-subject-group">
			<label for="vscf_subject">'.esc_attr($subject_label).': <span class="'.(isset($error_class['form_subject']) ? "vscf-error" : "vscf-hide").'" >'.esc_attr($error_subject_label).'</span></label>
			<input type="text" name="vscf_subject" id="vscf_subject" '. (isset($error_class['form_subject']) ? ' class="form-control vscf-error"' : ' class="form-control"').' value="'.esc_attr($form_data['form_subject']).'" aria-required="true" />
		</div>
	' : '') .'
	'. (($sum_setting != 'yes') ? '
	<div class="form-group vscf-sum-group">
		<label for="vscf_sum"><span>'.esc_attr($vscf_rand_one).'</span> + <span>'.esc_attr($vscf_rand_two).'</span> = <span class="'.(isset($error_class['form_sum']) ? "vscf-error" : "vscf-hide").'" >'.esc_attr($error_sum_label).'</span></label>
		<input type="text" name="vscf_sum" id="vscf_sum" '.(isset($error_class['form_sum']) ? ' class="form-control vscf-error"' : ' class="form-control"').' value="'.esc_attr($form_data['form_sum']).'" aria-required="true" />
		<input type="hidden" name="vscf_sum_hidden" id="vscf_sum_hidden" value="'.esc_attr($hidden_value).'" />
	</div>	
	' : '') .'
	<div class="form-group vscf-hide">
		<label for="vscf_first_name">'.esc_attr__( 'Please ignore this field', 'very-simple-contact-form' ).'</label>
		<input type="text" name="vscf_first_name" id="vscf_first_name" class="form-control" value="'.esc_attr($form_data['form_first_name']).'" />
	</div>
	<div class="form-group vscf-hide">
		<label for="vscf_last_name">'.esc_attr__( 'Please ignore this field', 'very-simple-contact-form' ).'</label>
		<input type="text" name="vscf_last_name" id="vscf_last_name" class="form-control" value="'.esc_attr($form_data['form_last_name']).'" />
	</div>
	<div class="form-group vscf-message-group">
		<label for="vscf_message">'.esc_attr($message_label).': <span class="'.( ( isset($error_class['form_message']) || isset($error_class['form_links']) ) ? "vscf-error" : "vscf-hide").'" >'.(isset($error_class['form_links']) ? esc_attr($error_links_label) : esc_attr($error_message_label)).'</span></label>
		<textarea name="vscf_message" id="vscf_message" rows="10" '.( ( isset($error_class['form_message']) || isset($error_class['form_links']) ) ? ' class="form-control vscf-error"' : ' class="form-control"').' aria-required="true">'.esc_textarea($form_data['form_message']).'</textarea>
	</div>
	<div class="form-group vscf-hide">
		<input type="hidden" name="vscf_token" id="vscf_token" class="form-control" value="'.esc_attr($vscf_token_field).'" />
	</div>
	'. (($privacy_setting != 'yes') ? '
		<div class="form-group vscf-privacy-group">
			<input type="hidden" name="vscf_privacy" id="vscf_privacy_hidden" value="no" />
			<input type="checkbox" name="vscf_privacy" id="vscf_privacy" class="custom-control-input" value="yes" '.checked( esc_attr($form_data['form_privacy']), "yes", false ).' />
			<label for="vscf_privacy" '.(isset($error_class['form_privacy']) ? 'class="vscf-error"' : '').'>'.esc_attr($privacy_label).'</label>
		</div>
	' : '') .'
	<div class="form-group vscf-hide">
		'.$vscf_nonce_field.'
	</div>
	<div class="form-group vscf-submit-group">
		<button type="submit" name="'.$submit_name_id.'" id="'.$submit_name_id.'" class="btn btn-primary">'.esc_attr($submit_label).'</button>
	</div>
</form>';
