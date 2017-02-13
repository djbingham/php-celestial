<?php
namespace Celestial\Module\Data\TableQuery\QuerySet\Face;

use Celestial\Module\Data\Table\Face\TableInterface;

interface FilterParserInterface
{
	public function parse(TableInterface $resourceDefinition, array $filters);
}
