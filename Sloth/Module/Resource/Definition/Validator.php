<?php
namespace Sloth\Module\Resource\Definition;

class Validator
{
	/**
	 * @var string
	 */
	public $rule;

	/**
	 * @var AttributeList
	 */
	public $attributes = array();

	/**
	 * @var boolean
	 */
	public $negate = false;

	/**
	 * @var array
	 */
	public $options = array();
}
