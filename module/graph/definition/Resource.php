<?php
namespace Sloth\Module\Graph\Definition;

use Sloth\Module\Graph\Definition;

class Resource
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var Definition\Table;
	 */
	public $table;

	/**
	 * @var array
	 */
	public $attributes;

	/**
	 * @var Definition\ViewList
	 */
	public $views;

	/**
	 * @var Definition\ValidatorList
	 */
	public $validators;
}
