<?php
namespace Sloth\Module\Data\TableValidation\Validator\Join\Property;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidatorInterface;

class JoinsValidator extends BaseValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $fieldNameValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->fieldNameValidator = $dependencyManager->getFieldNameValidator();
	}

	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('joinAlias', $options)) {
			$error = $this->buildError('Missing `joinAlias` in options given to validator for join property `joins`');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($join, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to validator for join property `joins`');
		}

		$errors = $this->validationModule->buildValidationErrorList();

		if (empty((array)$join->joins)) {
			$error = $this->validationModule->buildValidationError(array(
				'message' => 'Join fields are required'
			));
			$errors->push($error);
		} else {
			$availableParentTables = array('this');
			$availableChildTables = array($options['joinAlias']);

			if (property_exists($join, 'via')) {
				foreach ($join->via as $intermediaryTableAlias => $intermediaryTable) {
					$availableParentTables[] = $intermediaryTableAlias;
					$availableChildTables[] = $intermediaryTableAlias;
				}
			}

			$foundThisInJoinParents = false;
			$foundTargetInJoinChildren = false;

			foreach ($join->joins as $parent => $child) {
				$formatsValid = true;

				if (!preg_match('/^.+\..+$/', $parent)) {
					$formatsValid = false;
					$error = $this->validationModule->buildValidationError(array(
						'message' => sprintf('Parent `%s` in joins list is not in required format (tableName.FieldName)', $parent)
					));
					$errors->push($error);
				}

				if (!preg_match('/^.+\..+$/', $child)) {
					$formatsValid = false;
					$error = $this->validationModule->buildValidationError(array(
						'message' => sprintf('Child `%s` in joins list is not in required format (tableName.FieldName)', $child)
					));
					$errors->push($error);
				}

				if ($formatsValid === true) {
					list($parentTable, $parentField) = explode('.', $parent);
					list($childTable, $childField) = explode('.', $child);

					if ($parentTable === 'this') {
						$foundThisInJoinParents = true;
					}
					if ($childTable === $options['joinAlias']) {
						$foundTargetInJoinChildren = true;
					}

					$parentFieldValidation = $this->fieldNameValidator->validate($parentField);
					if (!$parentFieldValidation->isValid()) {
						$parentFieldError = $this->buildError(
							sprintf('Join parent field `%s` is invalid', $parentField),
							$parentFieldValidation->getErrors()
						);
						$errors->push($parentFieldError);
					}

					$childFieldValidation = $this->fieldNameValidator->validate($childField);
					if (!$childFieldValidation->isValid()) {
						$childFieldError = $this->buildError(
							sprintf('Join child field `%s` is invalid', $childField),
							$childFieldValidation->getErrors()
						);
						$errors->push($childFieldError);
					}

					if (!in_array($parentTable, $availableParentTables)) {
						$error = $this->validationModule->buildValidationError(array(
							'message' => sprintf('Parent table for join not found: `%s`', $parentTable)
						));
						$errors->push($error);
					}

					if (!in_array($childTable, $availableChildTables)) {
						$error = $this->validationModule->buildValidationError(array(
							'message' => sprintf('Child table for join not found: `%s`', $childTable)
						));
						$errors->push($error);
					}

					if ($parentTable === $childTable) {
						$error = $this->validationModule->buildValidationError(array(
							'message' => sprintf('Cannot join a table to itself: `%s`', $childTable)
						));
						$errors->push($error);
					}
				}
			}

			if (!$foundThisInJoinParents) {
				$error = $this->validationModule->buildValidationError(array(
					'message' => 'Join parent `this` is not referenced as parent in any joins'
				));
				$errors->push($error);
			}

			if (!$foundTargetInJoinChildren) {
				$error = $this->validationModule->buildValidationError(array(
					'message' => sprintf(
						'Join target `%s` is not referenced as child in any joins',
						$options['joinAlias']
					)
				));
				$errors->push($error);
			}

			$intermediateTables = array();
			if (property_exists($join, 'via')) {
				$intermediateTables = $join->via;
			}

			foreach ($intermediateTables as $intermediateTableAlias => $intermediateTableName) {
				$foundInJoinParents = false;
				$foundInJoinChildren = false;

				foreach ($join->joins as $parent => $child) {
					$parentTable = explode('.', $parent)[0];
					$childTable = explode('.', $child)[0];

					if ($parentTable === $intermediateTableAlias) {
						$foundInJoinParents = true;
					}
					if ($childTable === $intermediateTableAlias) {
						$foundInJoinChildren = true;
					}
				}

				if (!$foundInJoinParents) {
					$error = $this->validationModule->buildValidationError(array(
						'message' => sprintf(
							'Intermediate table `%s` in via list is not referenced as parent in any joins',
							$intermediateTableAlias
						)
					));
					$errors->push($error);
				}

				if (!$foundInJoinChildren) {
					$error = $this->validationModule->buildValidationError(array(
						'message' => sprintf(
							'Intermediate table `%s` in via list is not referenced as child in any joins',
							$intermediateTableAlias
						)
					));
					$errors->push($error);
				}
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
