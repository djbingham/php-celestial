<?php
namespace Sloth\Module\Resource\QuerySet\Filter;

use Sloth\Module\Resource\Definition\Table\Field;

class Filter
{
	/**
	 * @var Field
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
