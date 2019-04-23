<?php
namespace IDX\Widgets\Omnibar;

/**
 * Advanced_Fields class.
 */
class Advanced_Fields {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param mixed $idxID
	 * @param mixed $mls_name
	 * @param mixed $field_names
	 * @param mixed $property_types
	 * @return void
	 */
	public function __construct( $idxID, $mls_name, $field_names, $property_types ) {
		$this->idxID          = $idxID;
		$this->field_names    = $field_names;
		$this->mls_name       = $mls_name;
		$this->property_types = $property_types;
	}

	/**
	 * idxID
	 *
	 * @var mixed
	 * @access public
	 */
	public $idxID;

	/**
	 * mls_name
	 *
	 * @var mixed
	 * @access public
	 */
	public $mls_name;

	/**
	 * field_names
	 *
	 * @var mixed
	 * @access public
	 */
	public $field_names;

	/**
	 * property_types
	 *
	 * @var mixed
	 * @access public
	 */
	public $property_types;

	/**
	 * return_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function return_fields() {
		return array(
			'idxID'       => $this->idxID,
			'mls_name'    => $this->mls_name,
			'field_names' => $this->field_names,
		);
	}

	/**
	 * return_mlsPtIDs function.
	 *
	 * @access public
	 * @return void
	 */
	public function return_mlsPtIDs() {
		return array(
			'idxID'          => $this->idxID,
			'mls_name'       => $this->mls_name,
			'property_types' => $this->property_types,
		);
	}
}
