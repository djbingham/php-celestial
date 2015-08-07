<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;

class FilterParser
{
	public function parse(Definition\Table $resourceDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($resourceDefinition->fields as $field) {
			/** @var \Sloth\Module\Graph\Definition\Table\Field $field */
			if (array_key_exists($field->name, $filters)) {
				$parsedFilters[$field->name] = new Filter();
				$parsedFilters[$field->name]->field = $field;
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
