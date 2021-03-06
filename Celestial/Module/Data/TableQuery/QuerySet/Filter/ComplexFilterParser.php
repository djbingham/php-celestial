<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Filter;

use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableQuery\QuerySet\Face\FilterParserInterface;

class ComplexFilterParser implements FilterParserInterface
{
	public function parse(TableInterface $tableDefinition, array $filters)
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
