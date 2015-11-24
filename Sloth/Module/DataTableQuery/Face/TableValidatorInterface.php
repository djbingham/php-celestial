<?php
namespace Sloth\Module\DataTableQuery\Face;

use Sloth\Module\DataTable\Face\TableInterface;

interface TableValidatorInterface
{
	public function validate(TableInterface $resourceDefinition, array $attributeValues);
}
