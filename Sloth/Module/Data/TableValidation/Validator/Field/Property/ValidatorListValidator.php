<?php
namespace Sloth\Module\Data\TableValidation\Validator\Field\Property;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class ValidatorListValidator extends BaseValidator
{
	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($validators, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (is_object($validators)) {
			foreach ($validators as $validatorName => $validatorOptions) {
				if ($this->validationModule->validatorExists($validatorName)) {
					$validator = $this->validationModule->getValidator($validatorName);

					if (is_object($validatorOptions)) {
						$validatorOptions = (array)$validatorOptions;
					}

					if (!is_array($validatorOptions)) {
						$validatorOptions = array(
							'compareTo' => $validatorOptions
						);
					}

					$optionsValidation = $validator->validateOptions($validatorOptions);

					if (!$optionsValidation->isValid()) {
						$errors->push($this->buildError(
							sprintf('Invalid options declared for validator `%s`', $validatorName),
							$optionsValidation->getErrors()
						));
					}
				} else {
					$errors->push($this->buildError(
						sprintf('Invalid validator declared. No validator named `%s` exists.', $validatorName)
					));
				}
			}
		} else {
			$errors->push($this->buildError(
				sprintf('Field validators must be an array')
			));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
