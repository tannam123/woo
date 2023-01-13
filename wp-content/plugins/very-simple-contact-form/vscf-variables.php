<?php
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// sum
$vscf_rand_one = vscf_random_number_one();
$vscf_rand_two = vscf_random_number_two();
$vscf_rand_salt = mt_rand();
$hidden_value = base64_encode($vscf_rand_one.$vscf_rand_salt.$vscf_rand_two);

// get custom settings from settingspage
$list_submissions_setting = get_option('vscf-setting-2');
$auto_reply_setting = get_option('vscf-setting-3');
$subject_setting = get_option('vscf-setting-23');
$sum_setting = get_option('vscf-setting-24');
$link_setting = get_option('vscf-setting-25');
$privacy_setting = get_option('vscf-setting-4');
$ip_address_setting = get_option('vscf-setting-19');
$anchor_setting = get_option('vscf-setting-21');

// get custom labels from settingspage
$name_label = get_option('vscf-setting-5');
$email_label = get_option('vscf-setting-6');
$subject_label = get_option('vscf-setting-7');
$message_label = get_option('vscf-setting-9');
$privacy_label = get_option('vscf-setting-18');
$submit_label = get_option('vscf-setting-10');
$error_name_label = get_option('vscf-setting-11');
$error_email_label = get_option('vscf-setting-13');
$error_subject_label = get_option('vscf-setting-20');
$error_sum_label = get_option('vscf-setting-26');
$error_message_label = get_option('vscf-setting-12');
$error_links_label = get_option('vscf-setting-8');

// get custom messages from settingspage
$server_error_message = get_option('vscf-setting-15');
$thank_you_message = get_option('vscf-setting-16');
$auto_reply_message = get_option('vscf-setting-17');

// name label
$value = $name_label;
if (empty($vscf_atts['label_name'])) {
	if (empty($value)) {
		$name_label = __( 'Name', 'very-simple-contact-form' );
	} else {
		$name_label = $value;
	}
} else {
	$name_label = $vscf_atts['label_name'];
}

// email label
$value = $email_label;
if (empty($vscf_atts['label_email'])) {
	if (empty($value)) {
		$email_label = __( 'Email', 'very-simple-contact-form' );
	} else {
		$email_label = $value;
	}
} else {
	$email_label = $vscf_atts['label_email'];
}

// subject label
$value = $subject_label;
if (empty($vscf_atts['label_subject'])) {
	if (empty($value)) {
		$subject_label = __( 'Subject', 'very-simple-contact-form' );
	} else {
		$subject_label = $value;
	}
} else {
	$subject_label = $vscf_atts['label_subject'];
}

// message label
$value = $message_label;
if (empty($vscf_atts['label_message'])) {
	if (empty($value)) {
		$message_label = __( 'Message', 'very-simple-contact-form' );
	} else {
		$message_label = $value;
	}
} else {
	$message_label = $vscf_atts['label_message'];
}

// privacy label
$value = $privacy_label;
if (empty($vscf_atts['label_privacy'])) {
	if (empty($value)) {
		$privacy_label = __( 'I consent to having this website collect my personal data via this form.', 'very-simple-contact-form' );
	} else {
		$privacy_label = $value;
	}
} else {
	$privacy_label = $vscf_atts['label_privacy'];
}

// submit label
$value = $submit_label;
if (empty($vscf_atts['label_submit'])) {
	if (empty($value)) {
		$submit_label = __( 'Submit', 'very-simple-contact-form' );
	} else {
		$submit_label = $value;
	}
} else {
	$submit_label = $vscf_atts['label_submit'];
}

// error name label
$value = $error_name_label;
if (empty($vscf_atts['error_name'])) {
	if (empty($value)) {
		$error_name_label = __( 'Please enter at least 2 characters', 'very-simple-contact-form' );
	} else {
		$error_name_label = $value;
	}
} else {
	$error_name_label = $vscf_atts['error_name'];
}

// error email label
$value = $error_email_label;
if (empty($vscf_atts['error_email'])) {
	if (empty($value)) {
		$error_email_label = __( 'Please enter a valid email', 'very-simple-contact-form' );
	} else {
		$error_email_label = $value;
	}
} else {
	$error_email_label = $vscf_atts['error_email'];
}

// error subject label
$value = $error_subject_label;
if (empty($vscf_atts['error_subject'])) {
	if (empty($value)) {
		$error_subject_label = __( 'Please enter at least 2 characters', 'very-simple-contact-form' );
	} else {
		$error_subject_label = $value;
	}
} else {
	$error_subject_label = $vscf_atts['error_subject'];
}

// error sum label
$value = $error_sum_label;
if (empty($vscf_atts['error_sum'])) {
	if (empty($value)) {
		$error_sum_label = __( 'Please enter the correct result', 'very-simple-contact-form' );
	} else {
		$error_sum_label = $value;
	}
} else {
	$error_sum_label = $vscf_atts['error_sum'];
}

// error message label
$value = $error_message_label;
if (empty($vscf_atts['error_message'])) {
	if (empty($value)) {
		$error_message_label = __( 'Please enter at least 10 characters', 'very-simple-contact-form' );
	} else {
		$error_message_label = $value;
	}
} else {
	$error_message_label = $vscf_atts['error_message'];
}

// error links label
$value = $error_links_label;
if (empty($vscf_atts['error_links'])) {
	if (empty($value)) {
		$error_links_label = __( 'Please reduce number of links', 'very-simple-contact-form' );
	} else {
		$error_links_label = $value;
	}
} else {
	$error_links_label = $vscf_atts['error_links'];
}

// server error message
$value = $server_error_message;
if (empty($vscf_atts['message_error'])) {
	if (empty($value)) {
		$server_error_message= __( 'Error! Could not send form. This might be a server issue.', 'very-simple-contact-form' );
	} else {
		$server_error_message = $value;
	}
} else {
	$server_error_message = $vscf_atts['message_error'];
}

// thank you message
$value = $thank_you_message;
if (empty($vscf_atts['message_success'])) {
	if (empty($value)) {
		$thank_you_message = __( 'Thank you! You will receive a response as soon as possible.', 'very-simple-contact-form' );
	} else {
		$thank_you_message = $value;
	}
} else {
	$thank_you_message = $vscf_atts['message_success'];
}

// auto reply message
$value = $auto_reply_message;
if (empty($vscf_atts['auto_reply_message'])) {
	if (empty($value)) {
		$auto_reply_message = __( 'Thank you! You will receive a response as soon as possible.', 'very-simple-contact-form' );
	} else {
		$auto_reply_message = $value;
	}
} else {
	$auto_reply_message = $vscf_atts['auto_reply_message'];
}

// form anchor
if ($anchor_setting == 'yes') {
	$anchor_begin = '<div id="vscf-anchor">';
	$anchor_end = '</div>';
} else {
	$anchor_begin = '';
	$anchor_end = '';
}
