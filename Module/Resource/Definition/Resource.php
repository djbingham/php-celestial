<?php
namespace Sloth\Module\Resource\Definition;

use Sloth\Module\Resource\Definition;

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
	 * @var string
	 */
	public $primaryAttribute;

	/**
	 * @var \Sloth\Module\Render\ViewList
	 */
	public $views;

	/**
	 * @var Definition\ValidatorList
	 */
	public $validators;
}
