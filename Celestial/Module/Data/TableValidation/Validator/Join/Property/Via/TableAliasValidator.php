<?php
namespace Celestial\Module\Data\TableValidation\Validator\Join\Property\Via;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;

class TableAliasValidator extends BaseValidator
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
			$error = $this->buildError('The alias of a table listed in join property `via` must be a string');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
