<?php

namespace Sloth\Module\Data\TableValidation\Validator\ValidatorList;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class StructureValidator extends BaseValidator
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

		if (!is_array($value)) {
			$error = $this->buildError('Validator list must be an array');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
