<?php
namespace Sloth\Module\Data\TableDataValidator\Validator\Base;

use Sloth\Exception\InvalidRequestException;
use Sloth\Module\DataTable\Face\ConstraintInterface;
use Sloth\Module\DataTable\Face\FieldInterface;
use Sloth\Module\DataTable\Face\JoinInterface;
use Sloth\Module\DataTable\Face\TableInterface;
use Sloth\Module\DataTable\Face\ValidatorInterface;
use Sloth\Module\DataTableQuery\Face\TableValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

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
		$isValid = true;

		$isValid = $isValid && $this->validateTable($table, $data);
		$isValid = $isValid && $this->validateJoinedTables($table, $data);

		return $isValid;
	}

	protected function validateTable(TableInterface $table, array $data)
	{
		$isValid = true;

		/** @var FieldInterface $field */
		foreach ($table->fields as $field) {
			$fieldPassed = $this->validateField($field, $data);

			if ($fieldPassed !== true) {
				$isValid = false;
				break;
			}
		}

		return $isValid;
	}

	protected function validateField(FieldInterface $field, array $tableData)
	{
		$isValid = true;

		/** @var ValidatorInterface $validatorDefinition */
		foreach ($field->validators as $validatorDefinition) {
			if (array_key_exists($field->name, $tableData)) {
				$validator = $this->validationModule->getValidator($validatorDefinition->rule);

				$validatorPassed = $validator->validate($tableData[$field->name], (array)$validatorDefinition->options);

				if ($validatorDefinition->negate === true) {
					$validatorPassed = !$validatorPassed;
				}

				if ($validatorPassed !== true) {
					$isValid = false;
					break;
				}
			}
		}

		return $isValid;
	}

	protected function validateJoinedTables(TableInterface $parentTable, array $data)
	{
		$isValid = true;

		/** @var JoinInterface $join */
		foreach ($parentTable->links as $join) {
			if (array_key_exists($join->name, $data)) {
				if ($this->joinRequiresValidation($join)) {
					if (in_array($join->type, array(JoinInterface::ONE_TO_MANY, JoinInterface::MANY_TO_MANY))) {
						foreach ($data[$join->name] as $subRow) {
							$tablePassed = $this->validateTableAndChildren($join->getChildTable(), $subRow);

							if ($tablePassed !== true) {
								$isValid = false;
								break(2);
							}
						}
					} else {
						$tablePassed = $this->validateTableAndChildren($join->getChildTable(), $data[$join->name]);

						if ($tablePassed !== true) {
							$isValid = false;
							break;
						}
					}
				} elseif ($this->joinRequiresLinkValidation($join)) {
					$joinPassed = $this->validateJoinData($join, $data[$join->name]);

					if ($joinPassed !== true) {
						$isValid = false;
						break;
					}
				}
			}
		}

		return $isValid;
	}

	private function validateJoinData(JoinInterface $join, array $data)
	{
		$isValid = true;

		foreach ($data as $subRow) {
			/** @var ConstraintInterface $constraint */
			foreach ($join->getConstraints() as $constraint) {
				$childField = $constraint->childField;

				if (array_key_exists($childField->name, $subRow)) {
					$fieldPassed = $this->validateField($childField, $subRow);

					if ($fieldPassed !== true) {
						$isValid = false;
						break(2);
					}
				}

			}
		}

		return $isValid;
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
