<?php

namespace Sloth\Module\Data\TableValidation\Validator\Validator\Property;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class RuleValidator extends BaseValidator
{
	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($value, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!is_string($value)) {
			$error = $this->buildError(
				'Validation rule must be a string, matching a validator defined in application configuration'
			);
			$errors->push($error);
		} elseif (!$this->validationModule->validatorExists($value)) {
			$error = $this->buildError(
				sprintf('Validation rule `%s` not found in application configuration', $value)
			);
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
