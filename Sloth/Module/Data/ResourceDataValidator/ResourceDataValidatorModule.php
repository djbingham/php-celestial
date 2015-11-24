<?php
namespace Sloth\Module\Data\ResourceDataValidator;

use Sloth\Module\Data\TableDataValidator\TableDataValidatorModule;
use Sloth\Module\Resource\Face\Definition\ResourceInterface;
use Sloth\Module\Resource\Face\ResourceValidatorInterface;

class ResourceDataValidatorModule
{
	/**
	 * @var TableDataValidatorModule
	 */
	private $tableDataValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $resourceAttributesValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $resourceValidator;

	public function __construct(array $subValidators)
	{
		$this->tableDataValidator = $subValidators['tableDataValidator'];
		$this->resourceAttributesValidator = $subValidators['resourceAttributesValidator'];
		$this->resourceValidator = $subValidators['resourceValidator'];
	}

	public function validateInsertData(ResourceInterface $resourceDefinition, array $attributes)
	{
		return $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
			&& $this->tableDataValidator->validateInsertData($resourceDefinition->table, $attributes)
			&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}

	public function validateUpdateData(ResourceInterface $resourceDefinition, array $attributes)
	{
		return $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
			&& $this->tableDataValidator->validateUpdateData($resourceDefinition->table, $attributes)
			&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}
}
