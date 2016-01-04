<?php
namespace Sloth\Module\Data\TableValidation\Validator\Field;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;
use Sloth\Module\Data\TableValidation\DependencyManager;
use Sloth\Module\Validation\Face\ValidatorInterface;
use Sloth\Module\Validation\ValidationModule;

class FieldValidator extends BaseValidator
{
	private static $requiredProperties = array(
		'name',
		'type'
	);

	private static $allowedProperties = array(
		'autoIncrement',
		'isUnique',
		'validators'
	);

	/**
	 * @var ValidatorInterface
	 */
	private $structureValidator;

	/**
	 * @var array
	 */
	private $propertyValidators = array();

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->structureValidator = $dependencyManager->getFieldStructureValidator();
		$this->propertyValidators = array(
			'autoIncrement' => $dependencyManager->getFieldAutoIncrementValidator(),
			'isUnique' => $dependencyManager->getFieldIsUniqueValidator(),
			'field' => $dependencyManager->getFieldNameValidator(),
			'type' => $dependencyManager->getFieldTypeValidator(),
			'validators' => $dependencyManager->getFieldValidatorListValidator()
		);
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($field, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$structureResult = $this->structureValidator->validate($field);
		if (!$structureResult->isValid()) {
			$error = $this->buildError(sprintf('Field structure is invalid'), $structureResult->getErrors());
			$errors->push($error);
		}

		foreach ($this->propertyValidators as $propertyName => $propertyValidator) {
			$propertyValue = null;
			if (property_exists($field, $propertyName)) {
				$propertyValue = $field->$propertyName;
			}

			$propertyResult = $this->executeValidator($propertyValidator, $propertyValue);

			if (!$propertyResult->isValid()) {
				$errorMessage = sprintf('Value of `%s` property is invalid', $propertyName);
				$error = $this->buildError($errorMessage, $propertyResult->getErrors());
				$errors->push($error);
			}
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	private function executeValidator(ValidatorInterface $validator, $value)
	{
		return $validator->validate($value);
	}
}
