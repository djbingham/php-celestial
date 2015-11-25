<?php
namespace Sloth\Module\Data\Resource\Definition\Resource;

class Validator
{
	/**
	 * @var string
	 */
	public $rule;

	/**
	 * @var AttributeList
	 */
	public $attributes;

	/**
	 * @var boolean
	 */
	public $negate = false;

	/**
	 * @var array
	 */
	public $options = array();
}
