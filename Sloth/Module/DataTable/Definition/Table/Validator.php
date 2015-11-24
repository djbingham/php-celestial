<?php
namespace Sloth\Module\DataTable\Definition\Table;

use Sloth\Module\DataTable\Face\ValidatorInterface;

class Validator implements ValidatorInterface
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
