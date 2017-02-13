<?php
namespace Celestial\Module\Data\ResourceDataValidator;

use Celestial\Helper\InternalCacheTrait;
use Celestial\Module\Data\ResourceDataValidator\Result\ExecutedValidator;
use Celestial\Module\Data\ResourceDataValidator\Result\ExecutedValidatorList;
use Celestial\Module\Data\Resource\Face\Definition\ResourceInterface;
use Celestial\Module\Data\Resource\Face\ResourceValidatorInterface;
use Celestial\Module\Data\TableDataValidator\TableDataValidatorModule;
use Celestial\Module\Validation\ValidationModule;

class ResourceDataValidatorModule
{
	use InternalCacheTrait;

	/**
	 * @var ValidationModule
	 */
	private $validationModule;

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

	public function __construct(array $properties)
	{
		$this->validationModule = $properties['validationModule'];
		$this->tableDataValidator = $properties['tableDataValidator'];
		$this->resourceAttributesValidator = $properties['resourceAttributesValidator'];
		$this->resourceValidator = $properties['resourceValidator'];
	}

	/**
	 * @param ResourceInterface $resourceDefinition
	 * @param array $attributes
	 * @return ExecutedValidatorList
	 */
	public function validateInsertData(ResourceInterface $resourceDefinition, array $attributes)
	{
		$cacheKey = array('insert', md5(json_encode($attributes)));

		if (!$this->isCached($cacheKey)) {
			$tableValidation = $this->tableDataValidator->validateInsertData($resourceDefinition->table, $attributes);
			$attributeValidation = $this->resourceAttributesValidator->validate($resourceDefinition, $attributes);
			$resourceValidation = $this->resourceValidator->validate($resourceDefinition, $attributes);

			$executedValidators = new ExecutedValidatorList();

			/** @var \Celestial\Module\Data\TableDataValidator\Result\ExecutedValidator $executedTableValidator */
			foreach ($tableValidation as $executedTableValidator) {
				$executedValidator = new ExecutedValidator(array(
					'definition' => $executedTableValidator->getDefinition(),
					'result' => $executedTableValidator->getResult()
				));
				$executedValidators->push($executedValidator);
			}
			foreach ($attributeValidation as $executedValidator) {
				$executedValidators->push($executedValidator);
			}
			foreach ($resourceValidation as $executedValidator) {
				$executedValidators->push($executedValidator);
			}

			$this->setCached($cacheKey, $executedValidators);
		}

		return $this->getCached($cacheKey);
	}

	/**
	 * @param ResourceInterface $resourceDefinition
	 * @param array $attributes
	 * @return ExecutedValidatorList
	 */
	public function validateUpdateData(ResourceInterface $resourceDefinition, array $attributes)
	{
		$cacheKey = array('insert', md5(json_encode($attributes)));

		if (!$this->isCached($cacheKey)) {
			$tableValidation = $this->tableDataValidator->validateUpdateData($resourceDefinition->table, $attributes);
			$attributeValidation = $this->resourceAttributesValidator->validate($resourceDefinition, $attributes);
			$resourceValidation = $this->resourceValidator->validate($resourceDefinition, $attributes);

			$executedValidators = new ExecutedValidatorList();

			/** @var \Celestial\Module\Data\TableDataValidator\Result\ExecutedValidator $executedTableValidator */
			foreach ($tableValidation as $executedTableValidator) {
				$executedValidator = new ExecutedValidator(array(
					'definition' => $executedTableValidator->getDefinition(),
					'result' => $executedTableValidator->getResult()
				));
				$executedValidators->push($executedValidator);
			}
			foreach ($attributeValidation as $executedValidator) {
				$executedValidators->push($executedValidator);
			}
			foreach ($resourceValidation as $executedValidator) {
				$executedValidators->push($executedValidator);
			}

			$this->setCached($cacheKey, $executedValidators);
		}

		return $this->getCached($cacheKey);
	}
}
