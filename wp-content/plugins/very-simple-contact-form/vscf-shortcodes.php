<?php
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// shortcode for page
function vscf_shortcode($vscf_atts) {
	// attributes
	$vscf_atts = shortcode_atts(array(
		'class' => '',
		'email_to' => '',
		'from_header' => '',
		'prefix_subject' => '',
		'subject' => '',
		'label_name' => '',
		'label_email' => '',
		'label_subject' => '',
		'label_message' => '',
		'label_privacy' => '',
		'label_submit' => '',
		'error_name' => '',
		'error_email' => '',
		'error_subject' => '',
		'error_sum' => '',
		'error_message' => '',
		'error_links' => '',
		'message_success' => '',
		'message_error' => '',
		'auto_reply_message' => ''
	), $vscf_atts);

	// initialize variables
	$form_data = array(
		'form_name' => '',
		'form_email' => '',
		'form_subject' => '',
		'form_sum' => '',
		'form_sum_hidden' => '',
		'form_message' => '',
		'form_privacy' => '',
		'form_first_name' => '',
		'form_last_name' => '',
		'form_token' => ''
	);
	$error = false;
	$sent = false;
	$fail = false;

	// include variables
	include 'vscf-variables.php';

	// set nonce field
	$vscf_nonce_field = wp_nonce_field( 'vscf_nonce_action', 'vscf_nonce', true, false );

	// set time token field
	$vscf_token_field = base64_encode( time() );

	// set name and id of submit button
	$submit_name_id = 'vscf_send';

	// set form class
	if ( empty($vscf_atts['class']) ) {
		$custom_class = '';
	} else {
		$custom_class = ' '.sanitize_key($vscf_atts['class']);
	}
	$form_class = 'vscf-shortcode'.$custom_class.'';

	// processing form
	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['vscf_send']) && isset( $_POST['vscf_nonce'] ) && wp_verify_nonce( $_POST['vscf_nonce'], 'vscf_nonce_action' ) ) {
		// sanitize input
		if ($subject_setting != 'yes') {
			$subject_value = $_POST['vscf_subject'];
		} else {
			$subject_value = '';
		}
		if ($sum_setting != 'yes') {
			$sum_value = $_POST['vscf_sum'];
			$sum_value_hidden = $_POST['vscf_sum_hidden'];
		} else {
			$sum_value = '';
			$sum_value_hidden = '';
		}		
		if ($privacy_setting != 'yes') {
			$privacy_value = $_POST['vscf_privacy'];
		} else {
			$privacy_value = '';
		}
		$post_data = array(
			'form_name' => sanitize_text_field($_POST['vscf_name']),
			'form_email' => sanitize_email($_POST['vscf_email']),
			'form_subject' => sanitize_text_field($subject_value),
			'form_sum' => sanitize_text_field($sum_value),
			'form_sum_hidden' => sanitize_text_field($sum_value_hidden),
			'form_message' => sanitize_textarea_field($_POST['vscf_message']),
			'form_privacy' => sanitize_key($privacy_value),
			'form_first_name' => sanitize_text_field($_POST['vscf_first_name']),
			'form_last_name' => sanitize_text_field($_POST['vscf_last_name']),
			'form_token' => sanitize_text_field($_POST['vscf_token'])
		);

		// include validation
		include 'vscf-validate.php';

		// include sending and saving form submission
		include 'vscf-submission.php';
	}

	// include form
	include 'vscf-form.php';

	// after form validation
	if ($sent == true) {
		return '<script type="text/javascript">window.location="'.vscf_redirect_success().'"</script>';
	} elseif ($fail == true) {
		return '<script type="text/javascript">window.location="'.vscf_redirect_error().'"</script>';
	}

	// display form or the result of submission
	if ( isset( $_GET['vscf-sh'] ) ) {
		if ( sanitize_key($_GET['vscf-sh']) == 'success' ) {
			return $anchor_begin . '<p class="vscf-info">'.esc_attr($thank_you_message).'</p>' . $anchor_end;
		} elseif ( sanitize_key($_GET['vscf-sh']) == 'fail' ) {
			return $anchor_begin . '<p class="vscf-info">'.esc_attr($server_error_message).'</p>' . $anchor_end;
		}	
	} else {
		if ($error == true) {
			return $anchor_begin .$email_form. $anchor_end;
		} else {
			return $email_form;
		}
	}	   		
} 
add_shortcode('contact', 'vscf_shortcode');

// shortcode for widget
function vscf_widget_shortcode($vscf_atts) {
	// attributes
	$vscf_atts = shortcode_atts(array(
		'class' => '',
		'email_to' => '',
		'from_header' => '',
		'prefix_subject' => '',
		'subject' => '',
		'label_name' => '',
		'label_email' => '',
		'label_subject' => '',
		'label_message' => '',
		'label_privacy' => '',
		'label_submit' => '',
		'error_name' => '',
		'error_email' => '',
		'error_subject' => '',
		'error_sum' => '',
		'error_message' => '',
		'error_links' => '',
		'message_success' => '',
		'message_error' => '',
		'auto_reply_message' => ''
	), $vscf_atts);

	// initialize variables
	$form_data = array(
		'form_name' => '',
		'form_email' => '',
		'form_subject' => '',
		'form_sum' => '',
		'form_sum_hidden' => '',
		'form_message' => '',
		'form_privacy' => '',
		'form_first_name' => '',
		'form_last_name' => '',
		'form_token' => ''
	);
	$error = false;
	$sent = false;
	$fail = false;

	// include variables
	include 'vscf-variables.php';

	// set nonce field
	$vscf_nonce_field = wp_nonce_field( 'vscf_widget_nonce_action', 'vscf_widget_nonce', true, false );

	// set time token field
	$vscf_token_field = base64_encode( time() );

	// set name and id of submit button
	$submit_name_id = 'vscf_widget_send';

	// set form class
	if ( empty($vscf_atts['class']) ) {
		$custom_class = '';
	} else {
		$custom_class = ' '.sanitize_key($vscf_atts['class']);
	}
	$form_class = 'vscf-widget'.$custom_class.'';	

	// processing form
	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['vscf_widget_send']) && isset( $_POST['vscf_widget_nonce'] ) && wp_verify_nonce( $_POST['vscf_widget_nonce'], 'vscf_widget_nonce_action' ) ) {
		// sanitize input
		if ($subject_setting != 'yes') {
			$subject_value = $_POST['vscf_subject'];
		} else {
			$subject_value = '';
		}
		if ($sum_setting != 'yes') {
			$sum_value = $_POST['vscf_sum'];
			$sum_value_hidden = $_POST['vscf_sum_hidden'];
		} else {
			$sum_value = '';
			$sum_value_hidden = '';
		}		
		if ($privacy_setting != 'yes') {
			$privacy_value = $_POST['vscf_privacy'];
		} else {
			$privacy_value = '';
		}
		$post_data = array(
			'form_name' => sanitize_text_field($_POST['vscf_name']),
			'form_email' => sanitize_email($_POST['vscf_email']),
			'form_subject' => sanitize_text_field($subject_value),
			'form_sum' => sanitize_text_field($sum_value),
			'form_sum_hidden' => sanitize_text_field($sum_value_hidden),
			'form_message' => sanitize_textarea_field($_POST['vscf_message']),
			'form_privacy' => sanitize_key($privacy_value),
			'form_first_name' => sanitize_text_field($_POST['vscf_first_name']),
			'form_last_name' => sanitize_text_field($_POST['vscf_last_name']),
			'form_token' => sanitize_text_field($_POST['vscf_token'])
		);

		// include validation
		include 'vscf-validate.php';

		// include sending and saving form submission
		include 'vscf-submission.php';
	}

	// include form
	include 'vscf-form.php';

	// after form validation
	if ($sent == true) {
		return '<script type="text/javascript">window.location="'.vscf_widget_redirect_success().'"</script>';
	} elseif ($fail == true) {
		return '<script type="text/javascript">window.location="'.vscf_widget_redirect_error().'"</script>';
	}

	// display form or the result of submission
	if ( isset( $_GET['vscf-wi'] ) ) {
		if ( sanitize_key($_GET['vscf-wi']) == 'success' ) {
			return $anchor_begin . '<p class="vscf-info">'.esc_attr($thank_you_message).'</p>' . $anchor_end;
		} elseif ( sanitize_key($_GET['vscf-wi']) == 'fail' ) {
			return $anchor_begin . '<p class="vscf-info">'.esc_attr($server_error_message).'</p>' . $anchor_end;
		}	
	} else {
		if ($error == true) {
			return $anchor_begin .$email_form. $anchor_end;
		} else {
			return $email_form;
		}
	}	   		
}
add_shortcode('contact-widget', 'vscf_widget_shortcode');
