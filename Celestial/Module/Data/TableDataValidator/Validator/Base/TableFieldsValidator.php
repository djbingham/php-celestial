<?php
namespace Celestial\Module\Data\TableDataValidator\Validator\Base;

use Celestial\Exception\InvalidRequestException;
use Celestial\Module\Data\Table\Face\ConstraintInterface;
use Celestial\Module\Data\Table\Face\FieldInterface;
use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\Table\Face\TableInterface;
use Celestial\Module\Data\Table\Face\ValidatorInterface;
use Celestial\Module\Data\TableDataValidator\Result\ExecutedValidator;
use Celestial\Module\Data\TableDataValidator\Result\ExecutedValidatorList;
use Celestial\Module\Data\TableDataValidator\Result\Result;
use Celestial\Module\Data\TableQuery\Face\TableValidatorInterface;
use Celestial\Module\Validation\ValidationModule;

abstract class TableFieldsValidator implements TableValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	/**
	 * @param JoinInterface $tableJoin
	 * @return boolean
	 */
	abstract protected function joinRequiresValidation(JoinInterface $tableJoin);

	/**
	 * @param JoinInterface $tableJoin
	 * @return boolean
	 */
	abstract protected function joinRequiresLinkValidation(JoinInterface $tableJoin);

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	public function validate(TableInterface $tableDefinition, array $data)
	{
		return $this->validateTableAndChildren($tableDefinition, $data);
	}

	protected function validateTableAndChildren(TableInterface $table, array $data)
	{
		$tableResults = $this->validateTable($table, $data);
		$joinedResults = $this->validateJoinedTables($table, $data);

		$executedValidators = new ExecutedValidatorList();

		foreach ($tableResults as $result) {
			$executedValidators->push($result);
		}

		foreach ($joinedResults as $result) {
			$executedValidators->push($result);
		}

		return $executedValidators;
	}

	protected function validateTable(TableInterface $table, array $data)
	{
		$executedValidators = new ExecutedValidatorList();

		/** @var FieldInterface $field */
		foreach ($table->fields as $field) {
			$fieldResultList = $this->validateField($field, $data);

			foreach ($fieldResultList as $executedValidator) {
				$executedValidators->push($executedValidator);
			}
		}

		return $executedValidators;
	}

	protected function validateField(FieldInterface $field, array $tableData)
	{
		$executedValidators = new ExecutedValidatorList();

		/** @var ValidatorInterface $validatorDefinition */
		foreach ($field->validators as $validatorDefinition) {
			if (array_key_exists($field->name, $tableData)) {
				$validator = $this->validationModule->getValidator($validatorDefinition->rule);

				$validatorResult = $validator->validate($tableData[$field->name], (array)$validatorDefinition->options);

				$executedValidator = new ExecutedValidator(array(
					'definition' => $validatorDefinition,
					'result' => $validatorResult
				));

				$executedValidators->push($executedValidator);
			}
		}

		return $executedValidators;
	}

	protected function validateJoinedTables(TableInterface $parentTable, array $data)
	{
		$executedValidators = new ExecutedValidatorList();

		/** @var JoinInterface $join */
		foreach ($parentTable->links as $join) {
			if (array_key_exists($join->name, $data)) {
				if ($this->joinRequiresValidation($join)) {
					if (in_array($join->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
						foreach ($data[$join->name] as $subRow) {
							$childResults = $this->validateTableAndChildren($join->getChildTable(), $subRow);

							foreach ($childResults as $executedValidator) {
								$executedValidators->push($executedValidator);
							}
						}
					} else {
						$childResults = $this->validateTableAndChildren($join->getChildTable(), $data[$join->name]);

						foreach ($childResults as $executedValidator) {
							$executedValidators->push($executedValidator);
						}
					}
				} elseif ($this->joinRequiresLinkValidation($join)) {
					$joinResults = $this->validateJoinData($join, $data[$join->name]);

					foreach ($joinResults as $executedValidator) {
						$executedValidators->push($executedValidator);
					}
				}
			}
		}

		return $executedValidators;
	}

	private function validateJoinData(JoinInterface $join, array $data)
	{
		$executedValidators = new ExecutedValidatorList();

		foreach ($data as $subRow) {
			/** @var ConstraintInterface $constraint */
			foreach ($join->getConstraints() as $constraint) {
				$childField = $constraint->childField;

				if (array_key_exists($childField->name, $subRow)) {
					$fieldResults = $this->validateField($childField, $subRow);

					foreach ($fieldResults as $result) {
						$executedValidators->push($result);
					}
				}

			}
		}

		return $executedValidators;
	}

	protected function getFieldValue($flattenedFieldName, array $data)
	{
		$fieldNameParts = explode('.', $flattenedFieldName);
		$firstPart = array_shift($fieldNameParts);

		if (count($fieldNameParts) > 0) {
			if (array_key_exists($firstPart, $data)) {
				$fieldData = $data[$firstPart];
			} else {
				$fieldData = array();
			}

			if (is_array($fieldData)) {
				/*
					If field data is numerically indexed then it must be multi-row from a *-to-many relationship,
					since *-to-one relationships only ever have a single row of child data keyed by field name strings.
				*/
				if (array_keys($fieldData)[0] === 0) {
					$value = array();
					foreach ($fieldData as $index => $fieldDataRow) {
						$value[] = $this->getFieldValue(implode('.', $fieldNameParts), $fieldDataRow);
					}
				} else {
					$value = $this->getFieldValue(implode('.', $fieldNameParts), $fieldData);
				}
			} else {
				throw new InvalidRequestException('Fields do not match resource definition');
			}
		} elseif (array_key_exists($firstPart, $data)) {
			$value = $data[$firstPart];
		} else {
			$value = null;
		}

		return $value;
	}

	protected function flattenFields(array $fields, $prefix = null)
	{
		$flattenedFields = array();

		foreach ($fields as $fieldName => $fieldValue) {
			if ($prefix !== null) {
				$prefixedFieldName = $prefix . '.' . $fieldName;
			} else {
				$prefixedFieldName = $fieldName;
			}

			if (is_array($fieldValue)) {
				$subFields = $this->flattenFields($fieldValue, $prefixedFieldName);
				$flattenedFields = array_merge($flattenedFields, $subFields);
			} else {
				$flattenedFields[$prefixedFieldName] = $fieldValue;
			}
		}

		return $flattenedFields;
	}
}
