<?php
/**
 * Datum supper class.
 *
 * @since 3.0.0
 */

abstract class Fixedtoc_Datum {

	/**
	 * The name for a datum.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	protected $name;

	/**
	 * Value for a datum.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
	}

	/**
	 * Set the name.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	abstract public function set_name();

	/**
	 * Set the value.
	 *
	 * @since 3.0.0
	 *
	 * @param Fixedtoc_Data $obj_data an Instance of Fixedtoc_Data.
	 *
	 * @return void
	 */
	abstract public function set_value( Fixedtoc_Data $obj_data );

	/**
	 * Get the name.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the value.
	 *
	 * @since 3.0.0
	 */
	public function get_value() {
		return $this->value;
	}

}