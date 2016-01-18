<?php
namespace Sloth\Module\Data\TableValidation\Validator\Join\Property;

use Sloth\Module\Data\Table\Face\JoinInterface;
use Sloth\Module\Data\TableValidation\Base\BaseValidator;

class OnDeleteValidator extends BaseValidator
{
	protected static $validActions = array(
		JoinInterface::ACTION_ASSOCIATE,
		JoinInterface::ACTION_DELETE,
		JoinInterface::ACTION_IGNORE,
		JoinInterface::ACTION_REJECT
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
		if (is_object($join) && property_exists($join, 'onDelete')) {
			$value = $join->onDelete;
		}

		if (!is_string($value) || !in_array($value, self::$validActions)) {
			$error = $this->buildError('Join onDelete value must be one of the following: ' . implode(', ', self::$validActions));
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
