<?php
namespace DemoGraph\Module\Graph\QuerySet;

use DemoGraph\Module\Graph\ResourceDefinition;

class FilterParser
{
	public function parse(ResourceDefinition\Resource $resourceDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($resourceDefinition->attributes as $attribute) {
			/** @var ResourceDefinition\Attribute $attribute */
			if (array_key_exists($attribute->name, $filters)) {
				$parsedFilters[$attribute->name] = new Filter();
				$parsedFilters[$attribute->name]->attribute = $attribute;
				$parsedFilters[$attribute->name]->value = $filters[$attribute->name];
			}
		}
		foreach ($resourceDefinition->links as $link) {
			/** @var ResourceDefinition\Link $link */
			if (array_key_exists($link->name, $filters)) {
				$parsedFilters[$link->name] = $this->parse($link->getChildResource(), $filters[$link->name]);
			}
		}
		return $parsedFilters;
	}
}
