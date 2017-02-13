<?php
namespace Celestial\Module\Data\Table\Definition\Table;

use Celestial\Module\Data\Table\Face\ValidatorInterface;

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
	 * @var string
	 */
	public $message;

	/**
	 * @var array
	 */
	public $options = array();
}
