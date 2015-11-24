<?php
namespace Sloth\Module\DataTableQuery\QuerySet\Face;

use Sloth\Module\DataTable\Face\TableInterface;

interface FilterParserInterface
{
	public function parse(TableInterface $resourceDefinition, array $filters);
}
