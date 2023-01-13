<?php

namespace DgoraWcas;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Blocks {
	public function init() {
		add_action( 'init', function () {
			$this->registerBlocks();

			add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'hideLegacyWidgetBlock' ) );
		} );
	}

	private function registerBlocks() {
		register_block_type(
			DGWT_WCAS_DIR . 'build/blocks/search',
			array(
				'render_callback' => array( $this, 'renderCallback' ),
			)
		);

		register_block_type(
			DGWT_WCAS_DIR . 'build/blocks/search-nav',
			array(
				'render_callback' => array( $this, 'renderCallback' ),
			)
		);
	}

	/**
	 * Render FiboSearch blocks content
	 *
	 * @param $attributes
	 * @param $content
	 * @param $block
	 *
	 * @return string
	 */
	public function renderCallback( $attributes, $content, $block ) {
		$normalizedAttributes = array();

		$isBackend = defined( 'REST_REQUEST' ) && REST_REQUEST && filter_input( INPUT_GET, 'context', FILTER_SANITIZE_STRING ) === 'edit';

		if ( isset( $attributes['inheritPluginSettings'] ) && ! $attributes['inheritPluginSettings'] ) {
			if ( isset( $attributes['layout'] ) ) {
				$normalizedAttributes['layout'] = esc_attr( $attributes['layout'] );
			}
			if ( isset( $attributes['darkenedBackground'] ) ) {
				$normalizedAttributes['darken_bg'] = esc_attr( $attributes['darkenedBackground'] );
			}
			if ( isset( $attributes['mobileOverlay'] ) ) {
				$normalizedAttributes['mobile_overlay'] = esc_attr( $attributes['mobileOverlay'] );
			}
			if ( isset( $attributes['iconColor'] ) ) {
				$normalizedAttributes['icon_color'] = esc_attr( $attributes['iconColor'] );
			}

			if ( $isBackend ) {
				if ( isset( $attributes['layout'] ) && $attributes['layout'] === 'icon-flexible' ) {
					ob_start();

					echo '<div class="dgwt-wcas-show-on-preview-desktop">';
					$normalizedAttributes['layout'] = 'classic';
					echo do_shortcode( '[fibosearch ' . $this->getAttributesString( $normalizedAttributes ) . ']' );
					echo '</div>';

					echo '<div class="dgwt-wcas-show-on-preview-tablet dgwt-wcas-show-on-preview-mobile">';
					$normalizedAttributes['layout'] = 'icon';
					echo do_shortcode( '[fibosearch ' . $this->getAttributesString( $normalizedAttributes ) . ']' );
					echo '</div>';

					return ob_get_clean();
				} else if ( isset( $attributes['layout'] ) && $attributes['layout'] === 'icon-flexible-inv' ) {
					ob_start();

					echo '<div class="dgwt-wcas-show-on-preview-desktop">';
					$normalizedAttributes['layout'] = 'icon';
					echo do_shortcode( '[fibosearch ' . $this->getAttributesString( $normalizedAttributes ) . ']' );
					echo '</div>';

					echo '<div class="dgwt-wcas-show-on-preview-tablet dgwt-wcas-show-on-preview-mobile">';
					$normalizedAttributes['layout'] = 'classic';
					echo do_shortcode( '[fibosearch ' . $this->getAttributesString( $normalizedAttributes ) . ']' );
					echo '</div>';

					return ob_get_clean();
				}
			}
		}

		return do_shortcode( '[fibosearch ' . $this->getAttributesString( $normalizedAttributes ) . ']' );
	}

	public function hideLegacyWidgetBlock( $widgetTypes ) {
		$widgetTypes[] = 'dgwt_wcas_ajax_search';

		return $widgetTypes;
	}

	private function getAttributesString( $attributes ) {
		$attributesStringArr = array_map( function ( $key, $value ) {
			return $key . '="' . $value . '"';
		}, array_keys( $attributes ), $attributes );

		return implode( ' ', $attributesStringArr );
	}
}
