<?php
namespace Sloth\Module\Resource\Validator\Base;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\Face\ResourceValidatorInterface;
use Sloth\Module\Resource\Validator\InvalidRequestException;
use Sloth\Module\Validation\ValidationModule;

abstract class TableFieldsValidator implements ResourceValidatorInterface
{
	/**
	 * @var ValidationModule
	 */
	private $validationModule;

	/**
	 * @param Definition\Table\Join $tableJoin
	 * @return boolean
	 */
	abstract protected function joinRequiresValidation(Definition\Table\Join $tableJoin);

	/**
	 * @param Definition\Table\Join $tableJoin
	 * @return boolean
	 */
	abstract protected function joinRequiresLinkValidation(Definition\Table\Join $tableJoin);

	public function __construct(ValidationModule $validationModule)
	{
		$this->validationModule = $validationModule;
	}

	public function validate(Definition\Resource $resourceDefinition, array $data)
	{
		$isValid = $this->validateTableAndChildren($resourceDefinition->table, $data);

		return $isValid;
	}

	protected function validateTableAndChildren(Definition\Table $table, array $data)
	{
		$isValid = true;

		$isValid = $isValid && $this->validateTable($table, $data);
		$isValid = $isValid && $this->validateJoinedTables($table, $data);

		return $isValid;
	}

	protected function validateTable(Definition\Table $table, array $data)
	{
		$isValid = true;

		/** @var Definition\Table\Field $field */
		foreach ($table->fields as $field) {
			$fieldPassed = $this->validateField($field, $data);

			if ($fieldPassed !== true) {
				$isValid = false;
				break(2);
			}
		}

		return $isValid;
	}

	protected function validateField(Definition\Table\Field $field, array $tableData)
	{
		$isValid = true;

		/** @var Definition\Table\Validator $validatorDefinition */
		foreach ($field->validators as $validatorDefinition) {
			$validator = $this->validationModule->getValidator($validatorDefinition->rule);
			$dataToValidate = $this->getFieldsData($validatorDefinition->fields, $tableData);

			foreach ($dataToValidate as $dataSet) {
				$validatorPassed = $validator->validate($dataSet, (array)$validatorDefinition->options);

				if ($validatorDefinition->negate === true) {
					$validatorPassed = !$validatorPassed;
				}

				if ($validatorPassed !== true) {
					$isValid = false;
					break(2);
				}
			}
		}

		return $isValid;
	}

	protected function validateJoinedTables(Definition\Table $parentTable, array $data)
	{
		$isValid = true;

		/** @var Definition\Table\Join $join */
		foreach ($parentTable->links as $join) {
			if (array_key_exists($join->name, $data)) {
				if ($this->joinRequiresValidation($join)) {
					if (in_array($join->type, array(Definition\Table\Join::ONE_TO_MANY, Definition\Table\Join::MANY_TO_MANY))) {
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

	private function validateJoinData(Definition\Table\Join $join, array $data)
	{
		$isValid = true;

		foreach ($data as $subRow) {
			/** @var Definition\Table\Join\Constraint $constraint */
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
