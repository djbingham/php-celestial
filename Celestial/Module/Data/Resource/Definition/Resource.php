<?php
namespace Celestial\Module\Data\Resource\Definition;

use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\Resource\Definition\Resource\AttributeList;
use Celestial\Module\Data\Resource\Definition\Resource\ValidatorList;
use Celestial\Module\Data\Resource\Face\Definition\ResourceInterface;

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
