<?php

namespace Sloth\Module\Data\TableValidation\Validator\Validator\Property;

use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class OptionsValidator extends BaseValidator
{
	public function validateOptions(array $options)
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (!array_key_exists('rule', $options)) {
			$error = $this->buildError(
				'Missing `rule` in options given to validator for validator property `options`'
			);
			$errors->push($error);
		} elseif (!$this->validationModule->validatorExists($options['rule'])) {
			$error = $this->buildError(
				'Unrecognised rule in options given to validator for validator property `options`'
			);
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}

	public function validate($validatorOptions, array $options = array())
	{
		$optionsResult = $this->validateOptions($options);

		if (!$optionsResult->isValid()) {
			throw new InvalidArgumentException('Invalid options given to validator for validator property `options`');
		}

		$errors = $this->validationModule->buildValidationErrorList();

		$ruleName = $options['rule'];

		if (is_array($validatorOptions)) {
			$validator = $this->validationModule->getValidator($ruleName);
			$validationResult = $validator->validateOptions($validatorOptions);

			if (!$validationResult->isValid()) {
				$error = $this->buildError('Validator options are invalid', $validationResult->getErrors());
				$errors->push($error);
			}
		} else {
			$error = $this->buildError('Validator options must be an array');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
