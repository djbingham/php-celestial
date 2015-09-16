<?php
namespace Sloth\Module\Graph\QuerySet\Filter;

use Sloth\Module\Graph\Definition;
use Sloth\Module\Graph\QuerySet\Face\FilterParserInterface;
use Sloth\Module\Graph\QuerySet\Filter\Filter;

class ComplexFilterParser implements FilterParserInterface
{
	public function parse(Definition\Table $tableDefinition, array $filters)
	{
		$parsedFilters = array();
		foreach ($filters as $filterParams) {
			$thisParsedFilters = &$parsedFilters;

			$subjectPath = explode('.', $filterParams['subject']);
			$table = $tableDefinition;
			while (count($subjectPath) > 1) {
				$linkName = array_shift($subjectPath);
				if (!array_key_exists($linkName, $parsedFilters)) {
					$parsedFilters[$linkName] = array();
				}
				$thisParsedFilters = &$parsedFilters[$linkName];
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
