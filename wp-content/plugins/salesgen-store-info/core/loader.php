<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SALESGEN_STOREINFO_Loader' ) ) {
	final class SALESGEN_STOREINFO_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Member Variable
		 *
		 * @var utils
		 */
		public $utils = null;

		public $plugin_slug = '';

		public $updater = null;

		public $valid = -1;
		/**
		 *  Initiator
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

				do_action( 'salesgen_si_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			global $wpdb;
			$this->define_constants();

			// Activation hook.
			register_activation_hook( SALESGEN_SI_SLUG, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( SGSI_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );
			
			add_filter( 'salesgen_module_loaders', array( $this, 'loader_plugin' ), 10 );
		}

		function loader_plugin( $loaders ) {
			$loaders[ SALESGEN_SI_SHORT_SLUG ] = 'SALESGEN_STOREINFO_Loader';
			return $loaders;
		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants() {
			define( 'SALESGEN_SI_VER', SGSI );
			define( 'SALESGEN_SI_DIR', plugin_dir_path( SGSI_FILE ) );
			define( 'SALESGEN_SI_CORE_DIR', plugin_dir_path( SGSI_FILE ) . 'core/' );			
			define( 'SALESGEN_SI_URL', plugin_dir_url( SGSI_FILE ) );

			define( 'SALESGEN_SI_SLUG', 'salesgen-store-info/salesgen-store-info.php');
			define( 'SALESGEN_SI_SHORT_SLUG', 'salesgen-store-info');
			define( 'SALESGEN_SI_INS', SALESGEN_SI_SHORT_SLUG . '_ins');
			define( 'SALESGEN_SI_LS', SALESGEN_SI_SHORT_SLUG . '_ls');
		}

		public function activation_reset() {
			global $wpdb;

			if ( !get_option( SALESGEN_SI_INS, false ) ) update_option( SALESGEN_SI_INS, time());

			$modules = get_option('sg_modules', array());

			if ( !isset( $modules[ SALESGEN_SI_SLUG ] ) ) {
				$modules[ SALESGEN_SI_SLUG ] = 1;
				update_option( 'sg_modules', $modules );
			}
			
		}

		public function deactivation_reset() {
			
			$modules = get_option('sg_modules', array());

			if ( isset( $modules[ SALESGEN_SI_SLUG ] ) ) {
				$modules[ SALESGEN_SI_SLUG ] = 0;
				update_option( 'sg_modules', $modules );
			}
		}


		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {
			
			$this->load_core_files();
			$this->load_modules();

			do_action( 'salesgen_si_init' );
		}


		/**
		 * Load core files.
		 */
		public function load_core_files() {
			require_once SALESGEN_SI_CORE_DIR . 'updater.php';
			$this->updater = new SalesGen_Updater( SALESGEN_SI_SLUG, SGSI );
		}

		public function load_modules() {

			if ( strpos(SALESGEN_SI_DIR, base64_decode('c2FsZXNnZW4=')) > 0 && file_exists(SALESGEN_SI_DIR . 'modules/store-info/module.php') ) {
				include_once SALESGEN_SI_DIR . 'modules/store-info/module.php';
			}
		}

	}

	SALESGEN_STOREINFO_Loader::get_instance();
}