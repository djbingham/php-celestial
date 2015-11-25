<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Filter;

use Sloth\Module\Data\Table\Face\FieldInterface;

class Filter
{
	/**
	 * @var FieldInterface
	 */
	public $field;

	/**
	 * @var string
	 */
	public $comparator = '=';

	/**
	 * @var mixed
	 */
	public $value;
}
