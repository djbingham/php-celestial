<?php
namespace Celestial\Module\Data\TableValidation\Validator\JoinList;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;

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

		if (!is_object($value)) {
			$error = $this->buildError('Join list must be an object');
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
