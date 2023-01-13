<?php

/**
 * textarea field
 *
 * @since 3.0.0
 */
class Fixedtoc_Field_textarea extends Fixedtoc_Field {

	/**
	 * Get html code.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_html() {
		$name = esc_attr( $this->args['name'] );
		if ( '_fixed_toc[general_exclude_keywords]' === $name ) {
			$content = esc_textarea( fixedtoc_get_meta( 'general_exclude_keywords', false, true ) );
		} else {
			$content = esc_textarea( $this->args['value'] );
		}
		$extra_attrs = $this->get_extra_attrs();

		return "<textarea name=\"$name\" $extra_attrs>$content</textarea>";
	}

}