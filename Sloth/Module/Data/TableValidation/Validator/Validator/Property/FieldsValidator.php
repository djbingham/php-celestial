<?php

namespace Sloth\Module\Data\TableValidation\Validator\Validator\Property;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class FieldsValidator extends BaseValidator
{
	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('tableFields', $options)) {
			$error = $this->buildError(
				'Missing `tableFields` in options given to validator for validator property `fields`'
			);
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($validatorFields, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to validator for validator property `fields`');
		}

		$errors = $this->validationModule->buildValidationErrorList();

		$tableFields = $options['tableFields'];

		if (!is_object($validatorFields)) {
			$error = $this->buildError('Validator fields must be an array');
			$errors->push($error);
		} elseif (empty((array)$validatorFields)) {
			$error = $this->buildError('Validator fields must not be empty');
			$errors->push($error);
		} else {
			foreach ($validatorFields as $fieldName) {
				if (!array_key_exists($fieldName, (array)$tableFields)) {
					$error = $this->buildError(
						sprintf('Field `%s` not found in table manifest', $fieldName)
					);
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
