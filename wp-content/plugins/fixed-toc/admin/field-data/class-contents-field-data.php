<?php

/**
 * Contents section field data.
 *
 * @since 3.0.0
 */
class Fixedtoc_Field_Contents_Section_Data extends Fixedtoc_Field_Section_Data {

	/**
	 * Create section data.
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	protected function create_section_data() {
		$this->fixed_width();
		$this->fixed_height();
		$this->shape();
		$this->border_width();
		$this->display_in_post();
		$this->position_in_post();
		$this->float_in_post();
		$this->width_in_post();
		$this->height_in_post();
		$this->col_exp_init();
		$this->col_exp_init_mobile();
	}

	/**
	 * Width for fixed position.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function fixed_width() {
		$this->section_data['contents_fixed_width'] = array(
			'name'        => 'contents_fixed_width',
			'label'       => esc_html__( 'Width', 'fixedtoc' ),
			'default'     => 250,
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => 'absint',
			'des'         => nl2br( esc_html__( "When the TOC is fixed to the post.\nUnit: px.\nEmpty means auto calculate the width.", 'fixedtoc' ) ),
			'transport'   => 'postMessage'
		);
	}

	/**
	 * Height for fixed position.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function fixed_height() {
		$this->section_data['contents_fixed_height'] = array(
			'name'        => 'contents_fixed_height',
			'label'       => esc_html__( 'Height', 'fixedtoc' ),
			'default'     => '',
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => 'absint',
			'des'         => nl2br( esc_html__( "When the TOC is fixed to the post.\nUnit: px.\nEmpty means auto calculate the height.", 'fixedtoc' ) ),
			'transport'   => 'postMessage'
		);
	}

	/**
	 * Shape.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function shape() {
		$this->section_data['contents_shape'] = array(
			'name'      => 'contents_shape',
			'label'     => esc_html__( 'Shape', 'fixedtoc' ),
			'default'   => 'square',
			'type'      => 'select',
			'choices'   => $this->obj_field_data->get_shape_choices(),
			'sanitize'  => '',
			'des'       => '',
			'transport' => 'postMessage'
		);
	}

	/**
	 * Border width.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function border_width() {
		$this->section_data['contents_border_width'] = array(
			'name'      => 'contents_border_width',
			'label'     => esc_html__( 'Border', 'fixedtoc' ),
			'default'   => 'medium',
			'type'      => 'select',
			'choices'   => $this->obj_field_data->get_border_width_choices(),
			'sanitize'  => '',
			'des'       => '',
			'transport' => 'postMessage'
		);
	}

	/**
	 * Display in post
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function display_in_post() {
		$this->section_data['contents_display_in_post'] = array(
			'name'      => 'contents_display_in_post',
			'label'     => esc_html__( 'Display In Post', 'fixedtoc' ),
			'default'   => '1',
			'type'      => 'checkbox',
			'sanitize'  => '',
			'transport' => 'refresh',
			'des'       => esc_html__( "It doesn't work if you have checked the 'Display in Widget' option.", 'fixedtoc' ),
			'meta_des'  => esc_html__( "Make sure that you have unchecked the 'Display in Widget' option.", 'fixedtoc' )
		);
	}

	/**
	 * Position in post
	 *
	 * @since 3.1.8
	 * @access private
	 *
	 * @return void
	 */
	private function position_in_post() {
		/** @noinspection HtmlUnknownTarget */
		$this->section_data['contents_position_in_post'] = array(
			'name'      => 'contents_position_in_post',
			'label'     => esc_html__( 'Position In Post', 'fixedtoc' ),
			'default'   => 'top',
			'type'      => 'radio',
			'choices'   => array(
				'top'                => esc_html__( 'Top of the post', 'fixedtoc' ),
				'before_1st_heading' => esc_html__( 'Before the 1st heading', 'fixedtoc' ),
				'after_1st_heading'  => esc_html__( 'After the 1st heading', 'fixedtoc' ),
				'before_2nd_heading' => esc_html__( 'Before the 2nd heading', 'fixedtoc' ),
				'after_2nd_heading'  => esc_html__( 'After the 2nd heading', 'fixedtoc' )
			),
			'transport' => 'refresh',
			'des'       => esc_html__( 'Select the position where the TOC places.', 'fixedtoc' ),
			'meta_des'  => esc_html__( 'Or insert the shortcode [toc] anywhere. ', 'fixedtoc' ) .
			               sprintf( '<a href="%s" target="_blank">%s</a>',
				               'https://codex.wordpress.org/Shortcode',
				               esc_html__( 'What is shortcode?', 'fixedtoc' )
			               )
		);
	}

	/**
	 * Float in post
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function float_in_post() {
		$this->section_data['contents_float_in_post'] = array(
			'name'      => 'contents_float_in_post',
			'label'     => esc_html__( 'Alignment In Post', 'fixedtoc' ),
			'default'   => 'none',
			'type'      => 'radio',
			'choices'   => array(
				'left'   => esc_html__( 'Float to left', 'fixedtoc' ),
				'right'  => esc_html__( 'Float to right', 'fixedtoc' ),
				'center' => esc_html__( 'Center', 'fixedtoc' ),
				'none'   => esc_html__( 'None', 'fixedtoc' )
			),
			'transport' => 'refresh'
		);
	}

	/**
	 * Width in post.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function width_in_post() {
		$this->section_data['contents_width_in_post'] = array(
			'name'        => 'contents_width_in_post',
			'label'       => esc_html__( 'Width In Post', 'fixedtoc' ),
			'default'     => 250,
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => 'absint',
			'des'         => nl2br( esc_html__( "When the TOC displays in the post.\nUnit: px.\nEmpty means auto calculate the width.", 'fixedtoc' ) ),
			'transport'   => 'postMessage'
		);
	}

	/**
	 * Height in post.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function height_in_post() {
		$this->section_data['contents_height_in_post'] = array(
			'name'        => 'contents_height_in_post',
			'label'       => esc_html__( 'Height In Post', 'fixedtoc' ),
			'default'     => '',
			'type'        => 'number',
			'input_attrs' => array(
				'class' => 'small-text'
			),
			'sanitize'    => 'absint',
			'des'         => nl2br( esc_html__( "When the TOC displays in the post.\nUnit: px.\nEmpty means auto calculate the height.", 'fixedtoc' ) ),
			'transport'   => 'postMessage'
		);
	}

	/**
	 * Collapse/expand in initial state.
	 *
	 * @since 3.1.4
	 * @access private
	 *
	 * @return void
	 */
	private function col_exp_init() {
		$this->section_data['contents_col_exp_init'] = array(
			'name'      => 'contents_col_exp_init',
			'label'     => esc_html__( 'Collapsing For Initiation(Desktop)', 'fixedtoc' ),
			'default'   => '',
			'type'      => 'checkbox',
			'sanitize'  => '',
			'des'       => esc_html__( 'Check or uncheck for collapsing or expanding the contents after the page loaded.', 'fixedtoc' ),
			'meta_des'  => esc_html__( 'Available when the TOC displays in the post or in the Widget.', 'fixedtoc' ),
			'transport' => 'refresh'
		);
	}

	/**
	 * Collapse/expand in initial state on mobile.
	 *
	 * @since 3.1.17
	 * @access private
	 *
	 * @return void
	 */
	private function col_exp_init_mobile() {
		$this->section_data['contents_col_exp_init_mobile'] = array(
			'name'      => 'contents_col_exp_init_mobile',
			'label'     => esc_html__( 'Collapsing For Initiation(Mobile)', 'fixedtoc' ),
			'default'   => '1',
			'type'      => 'checkbox',
			'sanitize'  => '',
			'des'       => '',
			'meta_des'  => '',
			'transport' => 'refresh'
		);
	}

}