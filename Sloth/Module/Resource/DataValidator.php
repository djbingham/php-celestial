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
	private $tableFieldsValidator;

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
		$this->tableFieldsValidator = $subValidators['tableFieldsValidator'];
		$this->resourceValidator = $subValidators['resourceValidator'];
		$this->tablesInsertValidator = $subValidators['tablesInsertValidator'];
		$this->tablesUpdateValidator = $subValidators['tablesUpdateValidator'];
	}

	public function validateInsertData(Definition\Resource $resourceDefinition, array $attributes)
	{
		return $this->tableFieldsValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
			&& $this->tablesInsertValidator->validate($resourceDefinition, $attributes)
			&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}

	public function validateUpdateData(Definition\Resource $resourceDefinition, array $attributes)
	{
		return $this->tableFieldsValidator->validate($resourceDefinition, $attributes)
		&& $this->resourceAttributesValidator->validate($resourceDefinition, $attributes)
		&& $this->tablesUpdateValidator->validate($resourceDefinition, $attributes)
		&& $this->resourceValidator->validate($resourceDefinition, $attributes);
	}
}
