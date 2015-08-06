<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\Definition;

class FilterParser
{
	public function parse(Definition\Table $resourceDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($resourceDefinition->fields as $attribute) {
			/** @var \DemoGraph\Module\Graph\Definition\Table\Field $attribute */
			if (array_key_exists($attribute->name, $filters)) {
				$parsedFilters[$attribute->name] = new Filter();
				$parsedFilters[$attribute->name]->attribute = $attribute;
				$parsedFilters[$attribute->name]->value = $filters[$attribute->name];
			}
		}
		foreach ($resourceDefinition->links as $link) {
			/** @var \DemoGraph\Module\Graph\Definition\Table\Join $link */
			if (array_key_exists($link->name, $filters)) {
				$parsedFilters[$link->name] = $this->parse($link->getChildTable(), $filters[$link->name]);
			}
		}
		return $parsedFilters;
	}
}
