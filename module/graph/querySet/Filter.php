<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition\Table\Field;

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
