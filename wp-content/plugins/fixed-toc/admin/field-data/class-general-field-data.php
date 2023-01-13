<?php

/**
 * General section field data.
 *
 * @since 3.0.0
 */
class Fixedtoc_Field_General_Section_Data extends Fixedtoc_Field_Section_Data {

	/**
	 * Create section data.
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	protected function create_section_data() {
		$this->enable();
		$this->post_types();
		$this->headings();
		$this->min_headings_num();
		$this->exclude_keywords();
		$this->title_to_id();
		$this->id_prefix();
		$this->in_widget();
		$this->customize_css();
		$this->shortcut();
		$this->fixed_menu_selector();
		$this->scroll_offset();
	}

	/**
	 * Enable FTOC
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function enable() {
		$this->section_data['general_enable'] = array(
			'name'    => 'general_enable',
			'label'   => esc_html__( 'Enable Fixed TOC', 'fixedtoc' ),
			'default' => '1',
			'type'    => 'checkbox',
			'des'     => esc_html__( 'Set to enable or disable TOC by default. Later you can also enable/disable it on every edit page.', 'fixedtoc' )
		);
	}

	/**
	 * Supported post types
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function post_types() {
		$this->section_data['general_post_types'] = array(
			'name'    => 'general_post_types',
			'label'   => esc_html__( 'Post types', 'fixedtoc' ),
			'default' => array( 'post', 'page' ),
			'type'    => 'multi_checkbox',
			'choices' => $this->obj_field_data->get_posttype_choices(),
			'des'     => esc_html__( 'Check the post types to be applied. Multiple choice.', 'fixedtoc' )
		);
	}

	/**
	 * Headings to be accepted
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function headings() {
		$this->section_data['general_h_tags'] = array(
			'name'    => 'general_h_tags',
			'label'   => esc_html__( 'Headings', 'fixedtoc' ),
			'default' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
			'type'    => 'multi_checkbox',
			'choices' => array(
				'h1' => 'Heading 1',
				'h2' => 'Heading 2',
				'h3' => 'Heading 3',
				'h4' => 'Heading 4',
				'h5' => 'Heading 5',
				'h6' => 'Heading 6',
			),
			'des'     => esc_html__( 'Check which HTML headings automatically generated table of contents.', 'fixedtoc' )
		);
	}

	/**
	 * Minimize headings number
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function min_headings_num() {
		$this->section_data['general_min_headings_num'] = array(
			'name'        => 'general_min_headings_num',
			'label'       => esc_html__( 'Display TOC when', 'fixedtoc' ),
			'default'     => '',
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => 'absint',
			'suffix'      => esc_html__( 'or more headings are present.', 'fixedtoc' )
		);
	}

	/**
	 * Exclude Headings.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function exclude_keywords() {
		$this->section_data['general_exclude_keywords'] = array(
			'name'             => 'general_exclude_keywords',
			'label'            => esc_html__( 'Exclude Headings', 'fixedtoc' ),
			'default'          => '',
			'type'             => 'textarea',
			'input_attrs'      => array(
				'rows'  => '10',
				'class' => 'large-text code'
			),
			'meta_input_attrs' => array(
				'rows'  => '5',
				'class' => 'large-text code'
			),
//			'sanitize'         => array( 'Fixedtoc_Validate_Data', 'strip_tags' ),
			'des'              => nl2br( esc_html__( "Specify heading titles to be excluded from the TOC. \nOne title per line.", 'fixedtoc' ) )
		);
	}

	/**
	 * Convert Heading Into ID.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function title_to_id() {
		$this->section_data['general_title_to_id'] = array(
			'name'    => 'general_title_to_id',
			'label'   => esc_html__( 'Convert Heading Into ID', 'fixedtoc' ),
			'default' => '',
			'type'    => 'checkbox',
			'des'     => nl2br( esc_html__( "Automatically convert The heading to HTML id attribute.\nOnly supports English language headings.\nFor example: 'Download Source File' to 'download-source-file' or 'ftoc-download-source-file' if set the 'ftoc' prefix below.", 'fixedtoc' ) )
		);
	}

	/**
	 * Id prefix
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function id_prefix() {
		$this->section_data['general_id_prefix'] = array(
			'name'     => 'general_id_prefix',
			'label'    => esc_html__( 'Prefix', 'fixedtoc' ),
			'default'  => 'ftoc',
			'type'     => 'input',
			'sanitize' => 'sanitize_html_class',
			'des'      => nl2br( esc_html__( "4 or more letters are good. \nIn order to keep the document structure safe, you should set it.", 'fixedtoc' ) )
		);
	}

	/**
	 * Customize CSS
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function customize_css() {
		if ( version_compare( $GLOBALS['wp_version'], '4.9', '>=' ) ) {
			$type        = 'code_editor';
			$input_attrs = [];
//			$input_attrs = array(
//				'aria-describedby' => 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4',
//			);
		} else {
			$type        = 'textarea';
			$input_attrs = array( 'style' => 'height: 100vh;' );
		}

		$this->section_data['general_css'] = array(
			'name'        => 'general_css',
			'label'       => esc_html__( 'Customize CSS:', 'fixedtoc' ),
			'default'     => '',
			'type'        => $type,
			'code_type'   => 'text/css',
			'input_attrs' => $input_attrs,
			'sanitize'    => array( 'Fixedtoc_Validate_Data', 'strip_tags' ),
			'des'         => esc_html__( 'You can add your own CSS here. Only load if the current page has a TOC.', 'fixedtoc' ),
			'transport'   => 'postMessage'
		);
	}

	/**
	 * Display in widget
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function in_widget() {
		/** @noinspection HtmlUnknownTarget */
		$this->section_data['general_in_widget'] = array(
			'name'    => 'general_in_widget',
			'label'   => esc_html__( 'Display In Widget', 'fixedtoc' ),
			'default' => '',
			'type'    => 'checkbox',
			'des'     => sprintf(
				esc_html__( 'Ensure that you have added the Fixed TOC widget in the ', 'fixedtoc' ) .
				'<a href="%s" target="_blank">' . esc_html__( 'admin widgets page', 'fixedtoc' ) . '</a>',
				esc_attr( admin_url( 'widgets.php' ) )
			)
		);
	}

	/**
	 * Quick minimize
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function shortcut() {
		$this->section_data['general_shortcut'] = array(
			'name'    => 'general_shortcut',
			'label'   => esc_html__( 'Shortcut', 'fixedtoc' ),
			'default' => array( 'quick', 'esc', 'enter' ),
			'type'    => 'multi_checkbox',
			'choices' => array(
				'quick' => esc_html__( 'Quick Min', 'fixedtoc' ),
				'esc'   => esc_html__( 'Esc Min', 'fixedtoc' ),
				'enter' => esc_html__( 'Enter Max', 'fixedtoc' ),
			),
			'des'     => nl2br( esc_html__( "Quick Min: Click anywhere to minimize TOC.\nEsc: Press the 'esc' keyboard to minimize TOC.\nEnter: Press the 'enter' keyboard to maximize TOC.", 'fixedtoc' ) )
		);
	}

	/**
	 * Fixed menu CSS selectors.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function fixed_menu_selector() {
		$this->section_data['debug_menu_selector'] = array(
			'name'        => 'debug_menu_selector',
			'label'       => esc_html__( 'Fixed Headers', 'fixedtoc' ),
			'default'     => '',
			'type'        => 'text',
			'input_attrs' => array(
				'class' => 'regular-text'
			),
			'sanitize'    => 'sanitize_text_field',
			'des'         => nl2br( esc_html__( "If your theme's header is fixed, click the contents link might cover the Heading. Input CSS selectors of the fixed menu in here.\nSupport multiple fixed headers.", 'fixedtoc' ) )
		);
	}

	/**
	 * Scroll offset
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function scroll_offset() {
		$this->section_data['debug_scroll_offset'] = array(
			'name'        => 'debug_scroll_offset',
			'label'       => esc_html__( 'Scroll Offset', 'fixedtoc' ),
			'default'     => 10,
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => array( 'Fixedtoc_Validate_Data', 'intval_base10' ),
			'des'         => esc_html__( 'Setting spacing between heading and the top of browser after clicking on the contents link. (Excluding the fixed head menu and the admin toolbar)', 'fixedtoc' )
		);
	}

}