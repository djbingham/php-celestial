<?php
namespace Sloth\Module\Graph\QuerySet\Face;

use Sloth\Module\Graph\Definition;

interface FilterParserInterface
{
	public function parse(Definition\Table $resourceDefinition, array $filters);
}
