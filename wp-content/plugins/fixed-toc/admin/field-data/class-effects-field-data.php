<?php

/**
 * Effects section field data.
 *
 * @since 3.0.0
 */
class Fixedtoc_Field_Effects_Section_Data extends Fixedtoc_Field_Section_Data {

	/**
	 * Create section data.
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	protected function create_section_data() {
		$this->in_out();
		$this->active_link();
	}

	/**
	 * In/out Effects.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function in_out() {
		$this->section_data['effects_in_out'] = array(
			'name'      => 'effects_in_out',
			'label'     => esc_html__( 'In/Out', 'fixedtoc' ),
			'default'   => 'zoom',
			'type'      => 'select',
			'choices'   => array(
				'none' => esc_html__( 'None', 'fixedtoc' ),
				'fade' => esc_html__( 'Fade', 'fixedtoc' ),
				'zoom' => esc_html__( 'Zoom', 'fixedtoc' )
			),
			'sanitize'  => '',
			'des'       => esc_html__( 'Select how the TOC show in and hide out.', 'fixedtoc' ),
			'transport' => 'refresh'
		);
	}

	/**
	 * Active link Effects.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function active_link() {
		$this->section_data['effects_active_link'] = array(
			'name'      => 'effects_active_link',
			'label'     => esc_html__( 'Active Link', 'fixedtoc' ),
			'default'   => 'bounce-to-right',
			'type'      => 'select',
			'choices'   => array(
				'none'                  => esc_html__( 'None', 'fixedtoc' ),
				'fade'                  => esc_html__( 'Fade', 'fixedtoc' ),
				'sweep-to-right'        => esc_html__( 'Sweep To Right', 'fixedtoc' ),
				'sweep-to-left'         => esc_html__( 'Sweep To Left', 'fixedtoc' ),
				'bounce-to-right'       => esc_html__( 'Bounce To Right', 'fixedtoc' ),
				'bounce-to-left'        => esc_html__( 'Bounce To Left', 'fixedtoc' ),
				'radial-in'             => esc_html__( 'Radial In', 'fixedtoc' ),
				'radial-out'            => esc_html__( 'Radial Out', 'fixedtoc' ),
				'rectangle-in'          => esc_html__( 'Rectangle In', 'fixedtoc' ),
				'rectangle-out'         => esc_html__( 'Rectangle Out', 'fixedtoc' ),
				'shutter-in'            => esc_html__( 'Shutter In Horizontal', 'fixedtoc' ),
				'shutter-out'           => esc_html__( 'Shutter Out Horizontal', 'fixedtoc' ),
				'underline-from-right'  => esc_html__( 'Underline From Right', 'fixedtoc' ),
				'underline-from-left'   => esc_html__( 'Underline From Left', 'fixedtoc' ),
				'underline-from-center' => esc_html__( 'Underline From Center', 'fixedtoc' ),
				'reveal-underline'      => esc_html__( 'Underline Reveal', 'fixedtoc' ),
				'reveal-rightline'      => esc_html__( 'Rightline Reveal', 'fixedtoc' ),
				'reveal-leftline'       => esc_html__( 'Leftline Reveal', 'fixedtoc' ),
				'round-corners'         => esc_html__( 'Round Corners', 'fixedtoc' ),
				'border-fade'           => esc_html__( 'Border Fade', 'fixedtoc' ),
			),
			'sanitize'  => '',
			'des'       => '',
			'transport' => 'postMessage'
		);
	}

}