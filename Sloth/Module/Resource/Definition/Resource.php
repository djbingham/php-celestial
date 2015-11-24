<?php
namespace Sloth\Module\Resource\Definition;

use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Resource\Definition\Resource\ValidatorList;
use Sloth\Module\Resource\Face\Definition\ResourceInterface;

class Resource implements ResourceInterface
{
	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var TableInterface;
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
