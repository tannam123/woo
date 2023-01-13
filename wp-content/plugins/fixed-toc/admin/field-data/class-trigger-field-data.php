<?php

/**
 * Debug section field data.
 *
 * @since 3.0.0
 */
class Fixedtoc_Field_Trigger_Section_Data extends Fixedtoc_Field_Section_Data {

	/**
	 * Create section data.
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	protected function create_section_data() {
		$this->icon();
		$this->size();
		$this->shape();
		$this->border_width();
		$this->initial_visibility();
	}

	/**
	 * Icon.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function icon() {
		$this->section_data['trigger_icon'] = array(
			'name'      => 'trigger_icon',
			'label'     => esc_html__( 'Icon', 'fixedtoc' ),
			'default'   => 'number',
			'type'      => 'select',
			'choices'   => array(
				'number'    => esc_html__( 'List Number', 'fixedtoc' ),
				'bullet'    => esc_html__( 'List Bullet', 'fixedtoc' ),
				'menu'      => esc_html__( 'Menu', 'fixedtoc' ),
				'ellipsis'  => esc_html__( 'Ellipsis', 'fixedtoc' ),
				'vellipsis' => esc_html__( 'Ellipsis Vertical', 'fixedtoc' ),
				'none'      => esc_html__( 'None', 'fixedtoc' )
			),
			'des'       => '',
			'transport' => 'postMessage'
		);
	}

	/**
	 * Size.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function size() {
		$this->section_data['trigger_size'] = array(
			'name'        => 'trigger_size',
			'label'       => esc_html__( 'Size', 'fixedtoc' ),
			'default'     => 50,
			'type'        => 'range',
			'input_attrs' => array(
				'min'  => 25,
				'max'  => 70,
				'step' => 1,
			),
			'sanitize'    => 'absint',
			'des'         => '',
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
		$this->section_data['trigger_shape'] = array(
			'name'      => 'trigger_shape',
			'label'     => esc_html__( 'Shape', 'fixedtoc' ),
			'default'   => 'round',
			'type'      => 'select',
			'choices'   => $this->obj_field_data->get_shape_choices( true ),
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
		$this->section_data['trigger_border_width'] = array(
			'name'      => 'trigger_border_width',
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
	 * Initial visibility.
	 *
	 * @since 3.1.4
	 * @access private
	 *
	 * @return void
	 */
	private function initial_visibility() {
		$this->section_data['trigger_initial_visibility'] = array(
			'name'      => 'trigger_initial_visibility',
			'label'     => esc_html__( 'Initial Visibility', 'fixedtoc' ),
			'default'   => 'show',
			'type'      => 'radio',
			'choices'   => array(
				'show' => esc_html__( 'Show', 'fixedtoc' ),
				'hide' => esc_html__( 'Hide', 'fixedtoc' )
			),
			'sanitize'  => '',
			'des'       => nl2br( esc_html__( "Show: Display the trigger button and hide the contents at initial state.\nHide: Hide the trigger button and display the contents at initial state.", 'fixedtoc' ) ),
			'transport' => 'postMessage'
		);
	}

}