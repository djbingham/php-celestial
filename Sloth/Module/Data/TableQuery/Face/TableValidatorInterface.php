<?php
namespace Sloth\Module\Data\TableQuery\Face;

use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Validation\Face\ValidationResultInterface;

interface TableValidatorInterface
{
	/**
	 * @param TableInterface $resourceDefinition
	 * @param array $attributeValues
	 * @return ValidationResultInterface
	 */
	public function validate(TableInterface $resourceDefinition, array $attributeValues);
}
