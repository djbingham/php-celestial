<?php
namespace Sloth\Module\Data\TableValidation\Validator\Field;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class StructureValidator extends BaseValidator
{
	private static $requiredProperties = array(
		'field',
		'type'
	);

	private static $allowedProperties = array(
		'autoIncrement',
		'isUnique',
		'validators'
	);

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($field, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (is_object($field)) {
			foreach (self::$requiredProperties as $propertyName) {
				if (!property_exists($field, $propertyName)) {
					$errors->push($this->buildError(
							sprintf('Missing required property `%s`', $propertyName)
					));
				}
			}

			foreach ($field as $propertyName => $propertyValue) {
				if (!in_array($propertyName, array_merge(self::$allowedProperties, self::$requiredProperties))) {
					$errors->push(
						$this->buildError(sprintf('Unrecognised property `%s` defined', $propertyName))
					);
				}
			}
		} else {
			$errors->push($this->buildError('Field must be an object'));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
