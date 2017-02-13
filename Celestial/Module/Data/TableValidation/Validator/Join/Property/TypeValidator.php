<?php
namespace Celestial\Module\Data\TableValidation\Validator\Join\Property;

use Celestial\Module\Data\Table\Face\JoinInterface;
use Celestial\Module\Data\TableValidation\Base\BaseValidator;

class TypeValidator extends BaseValidator
{
	protected static $validTypes = array(
		JoinInterface::ONE_TO_ONE,
		JoinInterface::ONE_TO_MANY,
		JoinInterface::MANY_TO_ONE,
		JoinInterface::MANY_TO_MANY
	);

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($join, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		$value = null;
		if (is_object($join) && property_exists($join, 'type')) {
			$value = $join->type;
		}

		if (!is_string($value) || !in_array($value, self::$validTypes)) {
			$error = $this->buildError('Join type must be one of the following: ' . implode(', ', self::$validTypes));
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
