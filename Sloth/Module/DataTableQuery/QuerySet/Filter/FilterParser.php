<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Filter;

use Sloth\Module\DataTable\Face\FieldInterface;
use Sloth\Module\DataTable\Face\JoinInterface;
use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\DataTableQuery\QuerySet\Face\FilterParserInterface;

class FilterParser implements FilterParserInterface
{
	public function parse(TableInterface $resourceDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($resourceDefinition->fields as $field) {
			/** @var FieldInterface $field */
			if (array_key_exists($field->name, $filters)) {
				$parsedFilters[$field->name] = new Filter();
				$parsedFilters[$field->name]->field = $field;
				if (is_array($filters[$field->name])) {
					$parsedFilters[$field->name]->comparator = 'IN';
				}
				$parsedFilters[$field->name]->value = $filters[$field->name];
			}
		}
		foreach ($resourceDefinition->links as $link) {
			/** @var JoinInterface $link */
			if (array_key_exists($link->name, $filters)) {
				$parsedFilters[$link->name] = $this->parse($link->getChildTable(), $filters[$link->name]);
			}
		}
		return $parsedFilters;
	}
}
