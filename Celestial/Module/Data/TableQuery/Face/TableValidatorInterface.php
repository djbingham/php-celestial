<?php
namespace Celestial\Module\Data\TableQuery\Face;

use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Validation\Face\ValidationResultInterface;

interface TableValidatorInterface
{
	/**
	 * @param TableInterface $resourceDefinition
	 * @param array $attributeValues
	 * @return ValidationResultInterface
	 */
	public function validate(TableInterface $resourceDefinition, array $attributeValues);
}
