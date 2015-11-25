<?php
namespace Sloth\Module\Data\Table\Definition\Table;

use Sloth\Module\Data\Table\Face\ValidatorInterface;

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
