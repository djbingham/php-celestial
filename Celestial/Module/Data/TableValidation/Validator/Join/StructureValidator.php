<?php
namespace Celestial\Module\Data\TableValidation\Validator\Join;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;

class StructureValidator extends BaseValidator
{
	private static $requiredProperties = array(
		'type',
		'table',
		'joins'
	);

	private static $allowedProperties = array(
		'onInsert',
		'onUpdate',
		'onDelete',
		'via'
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

			foreach ($validator as $propertyName => $propertyValue) {
				if (!in_array($propertyName, array_merge(self::$allowedProperties, self::$requiredProperties))) {
					$errors->push(
						$this->buildError(sprintf('Unrecognised property `%s` defined', $propertyName))
					);
				}
			}
		} else {
			$errors->push($this->buildError('Join must be an object'));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
