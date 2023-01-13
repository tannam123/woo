<?php
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// sending and saving form submission
if ($error == false) {
	// hook to support plugin Contact Form DB
	do_action( 'vscf_before_send_mail', $form_data );
	// site name
	$blog_name = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES);
	// email variables
	$email_admin = get_option('admin_email');
	$email_settingspage = get_option('vscf-setting-22');
	$email_to_attribute = $vscf_atts['email_to'];
	$from_header_attribute = $vscf_atts['from_header'];
	$from_header = vscf_from_header();
	$to = '';
	// admin email address
	if ( !empty($email_to_attribute) ) {
		if (strpos($email_to_attribute, ',') !== false) {
			$email_list_clean = array();
			$email_list = explode(',', $email_to_attribute);
			foreach ( $email_list as $email_single ) {
				$email_clean = sanitize_email( $email_single );
				if ( is_email( $email_clean ) ) {
					$email_list_clean[] = $email_clean;
				}
			}
			if ( count($email_list_clean) < 6 ) {
				$to = implode(',', $email_list_clean);
			}
		} else {
			$email_clean = sanitize_email( $email_to_attribute );
			if ( is_email( $email_clean ) ) {
				$to = $email_clean;
			}
		}
	}
	if ( empty($to) ) {
		if ( is_email($email_settingspage) ) {
			$to = $email_settingspage;
		} else {
			$to = $email_admin;
		}
	}
	// from email header
	if ( is_email($from_header_attribute) ) {
		$from = $from_header_attribute;
	} elseif ( is_email($from_header) ) {
		$from = $from_header;
	} elseif ( is_email($email_settingspage) ) {
		$from = $email_settingspage;
	} else {
		$from = $email_admin;
	}
	// reply to email address
	if ( is_email($email_settingspage) ) {
		$reply_to = $email_settingspage;
	} else {
		$reply_to = $email_admin;
	}
	// subject
	if (!empty($vscf_atts['prefix_subject'])) {
		$prefix = $vscf_atts['prefix_subject'];
	} else {
		$prefix = $blog_name;
	}
	if (!empty($vscf_atts['subject'])) {
		$subject = $vscf_atts['subject'];
	} elseif ($subject_setting != 'yes') {
		$subject = "(".$prefix.") " . $form_data['form_subject'];
	} else {
		$subject = $prefix;
	}
	if ((!empty($vscf_atts['subject'])) && ($subject_setting != 'yes')) {
		$subject_in_content = $form_data['form_subject']."\r\n\r\n";
	} else {
		$subject_in_content = '';
	}
	// auto reply message
	$reply_message = htmlspecialchars_decode($auto_reply_message, ENT_QUOTES);
	// show or hide privacy consent
	if ($privacy_setting != 'yes') {
		$privacy_consent = "\r\n\r\n".sprintf( __( 'Privacy consent: %s', 'very-simple-contact-form' ), $privacy_label );
	} else {
		$privacy_consent = '';
	}
	// show or hide ip address
	if ($ip_address_setting == 'yes') {
		$ip_address = '';
	} else {
		$ip_address = "\r\n\r\n".sprintf( __( 'IP: %s', 'very-simple-contact-form' ), vscf_get_the_ip() );
	}
	// save form submission in database
	if ($list_submissions_setting == 'yes') {
		$vscf_post_information = array(
			'post_title' => wp_strip_all_tags($subject),
			'post_content' => $form_data['form_name']."\r\n\r\n".$form_data['form_email']."\r\n\r\n".$subject_in_content.$form_data['form_message'].$privacy_consent.$ip_address,
			'post_type' => 'submission',
			'post_status' => 'pending',
			'meta_input' => array( "name_sub" => $form_data['form_name'], "email_sub" => $form_data['form_email'] )
		);
		$post_id = wp_insert_post($vscf_post_information);
	}
	// mail
	$content = $form_data['form_name']."\r\n\r\n".$form_data['form_email']."\r\n\r\n".$subject_in_content.$form_data['form_message'].$privacy_consent.$ip_address;
	$headers = "Content-Type: text/plain; charset=UTF-8" . "\r\n";
	$headers .= "From: ".$form_data['form_name']." <".$from.">" . "\r\n";
	$headers .= "Reply-To: <".$form_data['form_email'].">" . "\r\n";
	$auto_reply_content = $reply_message."\r\n\r\n".$form_data['form_name']."\r\n\r\n".$form_data['form_email']."\r\n\r\n".$subject_in_content.$form_data['form_message'];
	$auto_reply_headers = "Content-Type: text/plain; charset=UTF-8" . "\r\n";
	$auto_reply_headers .= "From: ".$blog_name." <".$from.">" . "\r\n";
	$auto_reply_headers .= "Reply-To: <".$reply_to.">" . "\r\n";

	if( wp_mail($to, wp_strip_all_tags($subject), $content, $headers) ) {
		if ($auto_reply_setting == 'yes') {
			wp_mail($form_data['form_email'], wp_strip_all_tags($subject), $auto_reply_content, $auto_reply_headers);
		}
		$sent = true;
	} else {
		$fail = true;
	}
}
