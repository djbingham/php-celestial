<?php
namespace Sloth\Module\Data\TableQuery\Face;

use Sloth\Module\Data\Table\Face\TableInterface;

interface TableValidatorInterface
{
	public function validate(TableInterface $resourceDefinition, array $attributeValues);
}
