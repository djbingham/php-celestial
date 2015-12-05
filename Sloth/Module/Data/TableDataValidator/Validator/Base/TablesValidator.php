<?php
namespace Sloth\Module\Data\TableDataValidator\Validator\Base;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\Table\Face\TableInterface;
use Sloth\Module\Data\Table\Face\ValidatorInterface;
use Sloth\Module\Data\TableDataValidator\Result\ExecutedValidator;
use Sloth\Module\Data\TableDataValidator\Result\ExecutedValidatorList;
use Sloth\Module\Data\TableQuery\Face\TableValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

abstract class TablesValidator implements TableValidatorInterface
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

		/** @var ValidatorInterface $validatorDefinition */
		foreach ($table->validators as $validatorDefinition) {
			$validator = $this->validationModule->getValidator($validatorDefinition->rule);

			$dataToValidate = $this->getFieldsData($validatorDefinition->fields, $data);

			foreach ($dataToValidate as $dataSet) {
				$validatorResult = $validator->validate($dataSet, (array)$validatorDefinition->options);

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
				}
			}
		}

		return $executedValidators;
	}

	private function getFieldsData($fieldNames, array $data)
	{
		$dataToValidate = array();
		if (is_object($fieldNames)) {
			foreach ($fieldNames as $fieldLabel => $fieldName) {
				if (is_array($fieldName)) {
					$dataToValidate[$fieldLabel] = array();
					foreach ($fieldName as $subFieldName) {
						$subFieldData = $this->getFieldValue($subFieldName, $data);
						$dataToValidate[$fieldLabel] = array_merge($dataToValidate[$fieldLabel], $subFieldData);
					}
				} else {
					$dataToValidate[$fieldLabel] = $this->getFieldValue($fieldName, $data);
				}
			}
			$dataToValidate = array($dataToValidate);
		} elseif (is_array($fieldNames)) {
			foreach ($fieldNames as $fieldName) {
				$dataToValidate[] = $this->getFieldValue($fieldName, $data);
			}
		} else {
			$dataToValidate[] = $this->getFieldValue($fieldNames, $data);
		}

		return $dataToValidate;
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
