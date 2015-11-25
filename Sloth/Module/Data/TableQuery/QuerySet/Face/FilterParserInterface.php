<?php
namespace Sloth\Module\Data\TableQuery\QuerySet\Face;

use Sloth\Module\Data\Table\Face\TableInterface;

interface FilterParserInterface
{
	public function parse(TableInterface $resourceDefinition, array $filters);
}
