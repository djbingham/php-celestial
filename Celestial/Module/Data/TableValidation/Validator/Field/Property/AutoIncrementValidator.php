<?php
namespace Celestial\Module\Data\TableValidation\Validator\Field\Property;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;

class AutoIncrementValidator extends BaseValidator
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

		if ($value !== null && !is_bool($value)) {
			$errors->push($this->buildError('Must be a boolean value'));
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
