<?php
// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// validate name field
$value_name = stripslashes($post_data['form_name']);
if ( strlen($value_name)<2 ) {
	$error_class['form_name'] = true;
	$error = true;
}
$form_data['form_name'] = $value_name;

// validate email field
$value_email = $post_data['form_email'];
if ( empty($value_email) ) {
	$error_class['form_email'] = true;
	$error = true;
}
$form_data['form_email'] = $value_email;

// validate subject field
if ($subject_setting != 'yes') {
	$value_subject = stripslashes($post_data['form_subject']);
	if ( strlen($value_subject)<2 ) {
		$error_class['form_subject'] = true;
		$error = true;
	}
	$form_data['form_subject'] = $value_subject;
}

// validate sum field
if ($sum_setting != 'yes') {
	$value_sum = stripslashes($post_data['form_sum']);
	$value_hidden = base64_decode( stripslashes($post_data['form_sum_hidden']) );
	if ( is_numeric($value_sum) && is_numeric($value_hidden) ) {
		$hidden_one = substr($value_hidden, 0, 1);
		$hidden_two = substr($value_hidden, -1);
	} else {
		$hidden_one = 1;
		$hidden_two = 1;
	}
	$result = $hidden_one + $hidden_two;
	if ( $value_sum != $result ) {
		$error_class['form_sum'] = true;
		$error = true;
	}
	$form_data['form_sum'] = $value_sum;
}

// validate message field
$value_message = stripslashes($post_data['form_message']);
if ($link_setting == 'allow') {
	 $count = 99;
} elseif ($link_setting == 'disallow') {
	$count = 0;
} else {
	$count = 1;
}
if ( strlen($value_message)<10 ) {
	$error_class['form_message'] = true;
	$error = true;
} elseif ( (substr_count($value_message, 'http')>$count) || (substr_count($value_message, 'www')>$count) ) {
	$error_class['form_links'] = true;
	$error = true;
}
$form_data['form_message'] = $value_message;

// validate first honeypot field
$value_first_name = stripslashes($post_data['form_first_name']);
if ( strlen($value_first_name)>0 ) {
	$error = true;
}
$form_data['form_firstname'] = $value_first_name;

// validate second honeypot field
$value_last_name = stripslashes($post_data['form_last_name']);
if ( strlen($value_last_name)>0 ) {
	$error = true;
}
$form_data['form_lastname'] = $value_last_name;

// validate time token
$value_token = base64_decode( stripslashes($post_data['form_token']) );
$minimum = 3;
if ( is_numeric($value_token) && (time() - $value_token < $minimum) ) {
	$error = true;
}
$form_data['form_token'] = $value_token;

// validate privacy field
if ($privacy_setting != 'yes') {
	$value_privacy = $post_data['form_privacy'];
	if ( $value_privacy !=  'yes' ) {
		$error_class['form_privacy'] = true;
		$error = true;
	}
	$form_data['form_privacy'] = $value_privacy;
}
