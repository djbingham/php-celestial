<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\Face\ResourceValidatorInterface;

class DataValidator
{

	/**
	 * @var ResourceValidatorInterface
	 */
	private $resourceAttributesValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $tableFieldsInsertValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $tableFieldsUpdateValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $resourceValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $tablesInsertValidator;

	/**
	 * @var ResourceValidatorInterface
	 */
	private $tablesUpdateValidator;

	public function __construct(array $subValidators)
	{
		$this->resourceAttributesValidator = $subValidators['resourceAttributesValidator'];
		$this->tableFieldsInsertValidator = $subValidators['tableFieldsInsertValidator'];
		$this->tableFieldsUpdateValidator = $subValidators['tableFieldsUpdateValidator'];
		$this->resourceValidator = $subValidators['resourceValidator'];
		$this->tablesInsertValidator = $subValidators['tablesInsertValidator'];
		$this->tablesUpdateValidator = $subValidators['tablesUpdateValidator'];
	}

	public function validateInsertData(Definition\Resource $resourceDefinition, array $attributes)
	{
		return $this->tableFieldsInsertValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
			&& $this->tablesInsertValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}

	public function validateUpdateData(Definition\Resource $resourceDefinition, array $attributes)
	{
		return $this->tableFieldsUpdateValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
			&& $this->tablesUpdateValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}
}
