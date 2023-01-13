<?php

namespace DgoraWcas\Integrations\Plugins\WPRocket;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Integration with WP Rocket
 *
 * Plugin URL: https://wp-rocket.me/
 * Author: WP Media
 */
class WPRocket {
	public function init() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return;
		}
		if ( version_compare( WP_ROCKET_VERSION, '3.7' ) < 0 ) {
			return;
		}

		add_filter( 'rocket_delay_js_exclusions', array( $this, 'excludedJs' ) );
	}

	/**
	 * Adding our scripts to the list of excluded from delay loading
	 *
	 * @param array $excluded
	 *
	 * @return array
	 */
	public function excludedJs( $excluded ) {
		$excluded[] = 'jquery-migrate-js';
		$excluded[] = 'jquery-core-js';
		$excluded[] = 'dgwt-wcas';

		return $excluded;
	}
}
