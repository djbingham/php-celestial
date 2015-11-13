<?php
namespace Sloth\Module\Resource\Definition\Table;

class Validator
{
	/**
	 * @var string
	 */
	public $rule;

	/**
	 * @var FieldList
	 */
	public $fields;

	/**
	 * @var boolean
	 */
	public $negate = false;

	/**
	 * @var array
	 */
	public $options = array();
}
