<?php
namespace Celestial\Module\Data\TableValidation\Validator\TableManifest;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;

class StructureValidator extends BaseValidator
{
	private static $requiredProperties = array(
		'fields'
	);

	private static $optionalProperties = array(
		'validators',
		'links'
	);

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($manifest, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (is_object($manifest)) {
			$propertyNames = array_keys((array)$manifest);
			$missingProperties = array_diff(self::$requiredProperties, $propertyNames);
			$allowedProperties = array_merge(self::$requiredProperties, self::$optionalProperties);
			$unrecognisedProperties = array_diff($propertyNames, $allowedProperties);


			if (!empty($missingProperties)) {
				$error = $this->buildError(
					sprintf('Manifest is missing required properties: %s', implode(', ', $missingProperties))
				);
				$errors->push($error);
			}

			if (!empty($unrecognisedProperties)) {
				$error = $this->buildError(
					sprintf('Manifest contains unrecognised properties: %s', implode(', ', $unrecognisedProperties))
				);
				$errors->push($error);
			}
		} else {
			$error = $this->buildError('Manifest structure should be an object');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
