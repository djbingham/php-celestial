<?php
namespace Sloth\Module\Data\Resource\Definition;

use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Data\Resource\Definition\Resource\ValidatorList;
use Sloth\Module\Data\Resource\Face\Definition\ResourceInterface;

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
