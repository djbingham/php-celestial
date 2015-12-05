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
	 * @var string
	 */
	public $message;

	/**
	 * @var array
	 */
	public $options = array();
}
