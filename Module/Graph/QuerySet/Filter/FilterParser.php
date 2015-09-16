<?php
namespace Sloth\Module\Graph\QuerySet\Filter;

use Sloth\Module\Graph\Definition;
use Sloth\Module\Graph\QuerySet\Filter\Filter;
use Sloth\Module\Graph\QuerySet\Face\FilterParserInterface;

class FilterParser implements FilterParserInterface
{
	public function parse(Definition\Table $resourceDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($resourceDefinition->fields as $field) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $field */
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
			/** @var \Sloth\Module\Graph\Definition\Table\Join $link */
			if (array_key_exists($link->name, $filters)) {
				$parsedFilters[$link->name] = $this->parse($link->getChildTable(), $filters[$link->name]);
			}
		}
		return $parsedFilters;
	}
}
