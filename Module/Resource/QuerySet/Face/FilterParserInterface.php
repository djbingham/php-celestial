<?php
namespace Sloth\Module\Resource\QuerySet\Face;

use Sloth\Module\Resource\Definition;

interface FilterParserInterface
{
	public function parse(Definition\Table $resourceDefinition, array $filters);
}
