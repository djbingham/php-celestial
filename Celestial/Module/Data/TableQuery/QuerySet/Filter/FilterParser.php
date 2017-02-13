<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Filter;

use Celestial\Module\Data\Table\Face\FieldInterface;
use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\FilterParserInterface;

class FilterParser implements FilterParserInterface
{
	public function parse(TableInterface $tableDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($tableDefinition->fields as $field) {
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
		foreach ($tableDefinition->links as $link) {
			/** @var JoinInterface $link */
			if (array_key_exists($link->name, $filters)) {
				$parsedFilters[$link->name] = $this->parse($link->getChildTable(), $filters[$link->name]);
			}
		}
		return $parsedFilters;
	}
}
