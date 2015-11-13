<?php
namespace Sloth\Module\Resource\Definition;

use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Resource\Definition\Resource\ValidatorList;

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
	 * @var AttributeList
	 */
	public $attributes;

	/**
	 * @var string
	 */
	public $primaryAttribute;

	/**
	 * @var ValidatorList
	 */
	public $validators;
}
