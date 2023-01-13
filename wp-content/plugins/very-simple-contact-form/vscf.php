<?php
/*
 * Plugin Name: Very Simple Contact Form
 * Description: This is a lightweight plugin to create a customized contact form. Add shortcode [contact] on a page or use the widget to display your form.
 * Version: 12.3
 * Author: Guido
 * Author URI: https://www.guido.site
 * License: GNU General Public License v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: very-simple-contact-form
 * Domain Path: /translation
 */

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// load plugin text domain
function vscf_init() {
	load_plugin_textdomain( 'very-simple-contact-form', false, dirname( plugin_basename( __FILE__ ) ) . '/translation' );
}
add_action( 'plugins_loaded', 'vscf_init' );

// enqueue plugin scripts
function vscf_scripts() {
	wp_enqueue_style('vscf_style', plugins_url('/css/vscf-style.min.css',__FILE__));
	$anchor_setting = get_option('vscf-setting-21');
	if ($anchor_setting == 'yes') {
		wp_enqueue_script('vscf_anchor_script', plugins_url( '/js/vscf-anchor.js' , __FILE__ ), '', '', true);
	}
}
add_action( 'wp_enqueue_scripts', 'vscf_scripts' );

// the sidebar widget
function vscf_register_widget() {
	register_widget( 'vscf_widget' );
}
add_action( 'widgets_init', 'vscf_register_widget' );

// form submissions
$list_submissions_setting = get_option('vscf-setting-2');
if ($list_submissions_setting == 'yes') {
	// create submission post type
	function vscf_custom_postype() {
		$vscf_args = array(
			'labels' => array('name' => __( 'Submissions', 'very-simple-contact-form' )),
			'menu_icon' => 'dashicons-email',
			'public' => false,
			'can_export' => true,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'show_in_rest' => true,
			'capability_type' => 'post',
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
 			'supports' => array( 'title', 'editor' )
		);
		register_post_type( 'submission', $vscf_args );
	}
	add_action( 'init', 'vscf_custom_postype' );

	// dashboard submission columns
	function vscf_custom_columns( $columns ) {
		$columns['name_column'] = __( 'Name', 'very-simple-contact-form' );
		$columns['email_column'] = __( 'Email', 'very-simple-contact-form' );
		$custom_order = array('cb', 'title', 'name_column', 'email_column', 'date');
		foreach ($custom_order as $colname) {
			$new[$colname] = $columns[$colname];
		}
		return $new;
	}
	add_filter( 'manage_submission_posts_columns', 'vscf_custom_columns', 10 );

	function vscf_custom_columns_content( $column_name, $post_id ) {
		if ( 'name_column' == $column_name ) {
			$name = get_post_meta( $post_id, 'name_sub', true );
			echo esc_attr($name);
		}
		if ( 'email_column' == $column_name ) {
			$email = get_post_meta( $post_id, 'email_sub', true );
			echo esc_attr($email);
		}
	}
	add_action( 'manage_submission_posts_custom_column', 'vscf_custom_columns_content', 10, 2 );

	// make name and email column sortable
	function vscf_column_register_sortable( $columns ) {
		$columns['name_column'] = 'name_sub';
		$columns['email_column'] = 'email_sub';
		return $columns;
	}
	add_filter( 'manage_edit-submission_sortable_columns', 'vscf_column_register_sortable' );

	function vscf_name_column_orderby( $vars ) {
		if(is_admin()) {
			if ( isset( $vars['orderby'] ) && 'name_sub' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' => 'name_sub',
					'orderby' => 'meta_value'
				) );
			}
		}
		return $vars;
	}
	add_filter( 'request', 'vscf_name_column_orderby' );

	function vscf_email_column_orderby( $vars ) {
		if(is_admin()) {
			if ( isset( $vars['orderby'] ) && 'email_sub' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
					'meta_key' => 'email_sub',
					'orderby' => 'meta_value'
				) );
			}
		}
		return $vars;
	}
	add_filter( 'request', 'vscf_email_column_orderby' );
}

// add settings link
function vscf_action_links( $links ) {
	$settingslink = array( '<a href="'. admin_url( 'options-general.php?page=vscf' ) .'">'.__('Settings', 'very-simple-contact-form').'</a>' );
	return array_merge( $links, $settingslink );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'vscf_action_links' );

// get ip of user
function vscf_get_the_ip() {
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
		$ip_address = $_SERVER["HTTP_CLIENT_IP"];
	} else {
		$ip_address = $_SERVER["REMOTE_ADDR"];
	}
	return esc_attr($ip_address);
}

// create from email header
function vscf_from_header() {
	$server = esc_url_raw($_SERVER['SERVER_NAME']);
	if ( (substr($server, 0, 4) == "http") || (substr($server, 0, 3) == "www") ) {
		$replace = array("http://" => "", "https://" => "", "www." => "");
		$domain = strtr($server, $replace);
		return esc_attr('wordpress@'.$domain);
	}
}

// create random number for sum
function vscf_random_number_one() {
	$sum_one = mt_rand(1, 9);
	return $sum_one;
}
function vscf_random_number_two() {
	$sum_two = mt_rand(1, 9);
	return $sum_two;
}

// redirect when sending succeeds
function vscf_redirect_success() {
	$current_url = esc_url_raw($_SERVER['REQUEST_URI']);
	if (strpos($current_url, '?') == true) {
		$url_with_param = $current_url."&vscf-sh=success";
	} else {
		if (substr($current_url, -1) == '/') {
			$url_with_param = $current_url."?vscf-sh=success";
		} else {
			$url_with_param = $current_url."/?vscf-sh=success";
		}
	}
	return esc_url_raw($url_with_param);
}

function vscf_widget_redirect_success() {
	$current_url = esc_url_raw($_SERVER['REQUEST_URI']);
	if (strpos($current_url, '?') == true) {
		$url_with_param = $current_url."&vscf-wi=success";
	} else {
		if (substr($current_url, -1) == '/') {
			$url_with_param = $current_url."?vscf-wi=success";
		} else {
			$url_with_param = $current_url."/?vscf-wi=success";
		}
	}
	return esc_url_raw($url_with_param);
}

// redirect when sending fails
function vscf_redirect_error() {
	$current_url = esc_url_raw($_SERVER['REQUEST_URI']);
	if (strpos($current_url, '?') == true) {
		$url_with_param = $current_url."&vscf-sh=fail";
	} else {
		if (substr($current_url, -1) == '/') {
			$url_with_param = $current_url."?vscf-sh=fail";
		} else {
			$url_with_param = $current_url."/?vscf-sh=fail";
		}
	}
	return esc_url_raw($url_with_param);
}

function vscf_widget_redirect_error() {
	$current_url = esc_url_raw($_SERVER['REQUEST_URI']);
	if (strpos($current_url, '?') == true) {
		$url_with_param = $current_url."&vscf-wi=fail";
	} else {
		if (substr($current_url, -1) == '/') {
			$url_with_param = $current_url."?vscf-wi=fail";
		} else {
			$url_with_param = $current_url."/?vscf-wi=fail";
		}
	}
	return esc_url_raw($url_with_param);
}

// include files
include 'vscf-shortcodes.php';
include 'vscf-widget.php';
include 'vscf-options.php';
