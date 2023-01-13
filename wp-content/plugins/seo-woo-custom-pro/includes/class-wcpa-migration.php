<?php

if (!defined('ABSPATH'))
    exit;

class WCPA_Migration {

    public function check_has_to_migrate() {
        $old_version = get_option('custom-product-options_version');
       if ($old_version) {
            return true;
        } else {
            $count_posts = wp_count_posts('cpo_pt_forms');
            foreach ($count_posts as $v) {
                if ($v > 0) {
                    return true;
                }
            }
            return FALSE;
        }
    }

    public function check() {
        if ($this->check_has_to_migrate()) {
            add_action('admin_notices', array($this, 'migration_notice'));
        }
    }

    function migration_notice() {
        $class = 'notice notice-error';
        $message = __('<strong>Woocommerce Custom Product Addons</strong> found data from some old version, Please fix this by going '
                . '<a href="' . admin_url('options-general.php?page=wcpa_settings&view=migration') . '">here</a>', WCPA_TEXT_DOMAIN);

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), ($message));
    }

    public function version_migration() {
        $responses = array();
        $responses[] = $this->post_type('cpo_pt_forms', WCPA_POST_TYPE);
        $responses[] = $this->product_meta('_cpo_product_meta', WCPA_PRODUCT_META_KEY);
        foreach ($responses as $v) {
            if ($v === FALSE) {
                return $responses;
            }
        }
        delete_option('woo-custom-product-addons_version');
        return $responses;
    }

    private function product_meta($from, $to) {
        global $wpdb;
        $response = false;
        $form_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta where meta_key='$from'");
        if ($form_count) {
            $result = $wpdb->query(
                    $wpdb->prepare(
                            "UPDATE  $wpdb->postmeta
                    SET `meta_key` = %s
		 WHERE meta_key = %s
		", $to, $from));

            if ($result !== FALSE) {
                $response = array('status' => true, 'message' => 'Updated ' . $result . ' product meta');
            } else {
                $response = array('status' => false, 'message' => 'Failed to update product meta');
            }
        } else {

            $response = array('status' => true, 'message' => 'Found no products to migrate');
        }
        //
        return $response;
    }

    private function post_type($from, $to) {
        global $wpdb;
        $response = false;
        $form_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts where post_type='$from'");
        if ($form_count) {
            $result = $wpdb->query(
                    $wpdb->prepare(
                            "UPDATE  $wpdb->posts
                    SET `post_type` = '$to',
                    `guid` = REPLACE(`guid`, %s, %s)
		 WHERE post_type = %s
		", $from, $to, $from));
            $result2 = $wpdb->query(
                    "UPDATE  $wpdb->postmeta
                    SET `meta_key` = '" . WCPA_FORM_META_KEY . "'
		 WHERE meta_key = 'cpo_fb-editor-json'
		");
            if ($result !== FALSE) {
                $response = array('status' => true, 'message' => 'Updated ' . $result . ' forms');
            } else {
                $response = array('status' => false, 'message' => 'Failed to migrate forms');
            }
        } else {
            $response = array('status' => true, 'message' => 'Found no forms to migrate');
        }
        //
        return $response;
    }

}
