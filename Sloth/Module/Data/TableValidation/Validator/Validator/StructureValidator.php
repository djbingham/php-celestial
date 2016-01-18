<?php

namespace Sloth\Module\Data\TableValidation\Validator\Validator;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class StructureValidator extends BaseValidator
{
	private static $requiredProperties = array(
		'rule',
		'fields'
	);

	private static $optionalProperties = array(
		'options'
	);

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($validator, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (is_object($validator)) {
			foreach (self::$requiredProperties as $propertyName) {
				if (!property_exists($validator, $propertyName)) {
					$errors->push($this->buildError(
						sprintf('Missing required property `%s`', $propertyName)
					));
				}
			}

			$allowedProperties = array_merge(self::$optionalProperties, self::$requiredProperties);

			foreach ($validator as $propertyName => $propertyValue) {
				if (!in_array($propertyName, $allowedProperties)) {
					$errors->push(
						$this->buildError(sprintf('Unrecognised property `%s` defined', $propertyName))
					);
				}
			}
		} else {
			$errors->push($this->buildError('Validator must be an object'));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
