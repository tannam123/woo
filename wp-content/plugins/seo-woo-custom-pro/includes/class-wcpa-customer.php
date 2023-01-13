<?php

if (!defined('ABSPATH'))
    exit;

class WCPA_Customer {

    /**
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;
    private static $_customer = null;

    public function __construct() {
        $this->_session = WC()->session;
    }

    public function get_session_cookie() {
        $user_coockie = $this->_session->get_session_cookie();
        return isset($user_coockie[3]) ? $user_coockie[3] : false;
    }

    public function upload_directory_base($temp = false) {
        if ($temp) {
            return WCPA_UPLOAD_DIR . '/wcpa_temp/' . md5($this->get_session_cookie());
        } else {
            return WCPA_UPLOAD_DIR . '/' . md5($this->get_session_cookie());
        }
    }

    /**
     *
     *
     * Ensures only one instance of WCPA is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main WCPA instance
     */
    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Installation. Runs on activation.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function install() {
        $this->_log_version_number();
    }

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number() {
        update_option($this->_token . '_version', $this->_version);
    }

}
