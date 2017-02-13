<?php
namespace Celestial\Module\Data\TableDataValidator;

use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\TableDataValidator\Result\ExecutedValidatorList;
use Celestial\Module\Data\TableQuery\Face\TableValidatorInterface;
use Celestial\Module\Validation\ValidationModule;

class TableDataValidatorModule
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	/**
	 * @var TableValidatorInterface
	 */
	private $tableFieldsInsertValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tableFieldsUpdateValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tablesInsertValidator;

	/**
	 * @var TableValidatorInterface
	 */
	private $tablesUpdateValidator;

	public function __construct(array $properties)
	{
		$this->validationModule = $properties['validationModule'];
		$this->tableFieldsInsertValidator = $properties['tableFieldsInsertValidator'];
		$this->tableFieldsUpdateValidator = $properties['tableFieldsUpdateValidator'];
		$this->tablesInsertValidator = $properties['tablesInsertValidator'];
		$this->tablesUpdateValidator = $properties['tablesUpdateValidator'];
	}

	public function validateInsertData(TableInterface $tableDefinition, array $attributes)
	{
		$fieldValidationResults = $this->tableFieldsInsertValidator->validate($tableDefinition, $attributes);
		$tableValidationResults = $this->tablesInsertValidator->validate($tableDefinition, $attributes);

		$executedValidators = new ExecutedValidatorList();

		foreach ($fieldValidationResults as $executedValidator) {
			$executedValidators->push($executedValidator);
		}
		foreach ($tableValidationResults as $executedValidator) {
			$executedValidators->push($executedValidator);
		}

		return $executedValidators;
	}

	public function validateUpdateData(TableInterface $tableDefinition, array $attributes)
	{
		$fieldValidationResults = $this->tableFieldsUpdateValidator->validate($tableDefinition, $attributes);
		$tableValidationResults = $this->tablesUpdateValidator->validate($tableDefinition, $attributes);

		$executedValidators = new ExecutedValidatorList();

		foreach ($fieldValidationResults as $executedValidator) {
			$executedValidators->push($executedValidator);
		}
		foreach ($tableValidationResults as $executedValidator) {
			$executedValidators->push($executedValidator);
		}

		return $executedValidators;
	}
}
