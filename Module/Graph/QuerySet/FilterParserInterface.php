<?php
namespace Sloth\Module\Graph\QuerySet;

use Sloth\Module\Graph\Definition;

interface FilterParserInterface
{
	public function parse(Definition\Table $resourceDefinition, array $filters);
}
