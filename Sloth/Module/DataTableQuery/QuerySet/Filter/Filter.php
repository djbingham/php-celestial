<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Filter;

use Sloth\Module\DataTable\Face\FieldInterface;

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
