<?php
namespace Sloth\Module\Data\TableValidation\Validator\FieldList;

use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class AliasValidator extends BaseValidator
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
			$error = $this->buildError('Field alias must be a string');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
