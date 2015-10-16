<?php
namespace Sloth\Module\Resource\QuerySet\Filter;

use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\QuerySet\Face\FilterParserInterface;
use Sloth\Module\Resource\QuerySet\Filter\Filter;

class ComplexFilterParser implements FilterParserInterface
{
	public function parse(Definition\Table $tableDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($filters as $filterParams) {
			$subjectPath = explode('.', $filterParams['subject']);
			$table = $tableDefinition;
			$thisParsedFilters = &$parsedFilters;

			while (count($subjectPath) > 1) {
				$linkName = array_shift($subjectPath);
				if (!array_key_exists($linkName, $thisParsedFilters)) {
					$thisParsedFilters[$linkName] = array();
				}
				$thisParsedFilters = &$thisParsedFilters[$linkName];
				$table = $table->links->getByName($linkName)->getChildTable();
			}

			$field = $table->fields->getByName($subjectPath[0]);

			$parsedFilter = new Filter();
			$parsedFilter->field = $field;
			$parsedFilter->comparator = $filterParams['comparator'];
			$parsedFilter->value = $filterParams['value'];
			$thisParsedFilters[$subjectPath[0]] = $parsedFilter;
		}
		return $parsedFilters;
	}
}
